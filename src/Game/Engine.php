<?php

namespace Game;

use Model\Building;
use Model\Mission;
use Model\Planet;
use Model\Ship as ShipModel;
use Model\Technology;
use Service\GameTickService;

class Engine
{
    private int $gameTick = 0;

    public function __construct(int $gameTick = 0)
    {
        $this->gameTick = $gameTick;
    }

    public function getGameTick(): int
    {
        return $this->gameTick;
    }

    /**
     * Run a single game tick for all planets.
     */
    public function tickAll(): void
    {
        // This is called by GameTickService which loads all planets
        // and passes them one by one to tickPlanet()
    }

    /**
     * Run a game tick for a single planet.
     */
    public function tickPlanet(Planet $planet, array $buildings, array $technologies, array $ships): void
    {
        $resources = $planet->getResources();
        $buildingLevels = [];

        foreach ($buildings as $b) {
            $buildingLevels[$b['building_key']] = $b['level'];
        }

        // 1. Calculate power balance
        $powerProduction = 0;
        $powerConsumption = 0;

        foreach ($buildingLevels as $key => $level) {
            if ($level < 1) {
                continue;
            }

            if (BuildingTypes::isEnergyBuilding($key)) {
                $building = BuildingTypes::get($key);
                if ($building !== null && isset($building['base_energy_production'])) {
                    $powerProduction += Formulas::energyProduction($building['base_energy_production'], $level);
                }
            } else {
                $building = BuildingTypes::get($key);
                if ($building !== null && isset($building['energy_consumption'])) {
                    $powerConsumption += Formulas::buildingEnergyConsumption($building['energy_consumption'], $level);
                }
            }
        }

        $energyBalance = Formulas::energyBalance($powerProduction, $powerConsumption);

        // If power is negative, reduce resource production by 75%
        $productionPenalty = $energyBalance < 0 ? 0.25 : 1.0;

        // 2. Calculate resource production
        $supplyProduction = 0;
        $gasProduction = 0;

        foreach ($buildingLevels as $key => $level) {
            if ($level < 1) {
                continue;
            }

            if (!BuildingTypes::isResourceBuilding($key)) {
                continue;
            }

            $building = BuildingTypes::get($key);
            if ($building === null) {
                continue;
            }

            if (isset($building['base_production']['supply'])) {
                $supplyProduction += Formulas::buildingProduction($building['base_production']['supply'], $level) * $productionPenalty;
            }
            if (isset($building['base_production']['gas'])) {
                $gasProduction += Formulas::buildingProduction($building['base_production']['gas'], $level) * $productionPenalty;
            }
        }

        // 3. Calculate storage capacities
        $supplyStorage = Formulas::calculateStorage('supply', $buildingLevels);
        $gasStorage = Formulas::calculateStorage('gas', $buildingLevels);

        // 4. Apply production (capped at storage)
        $newSupply = min($resources['supply'] + $supplyProduction, $supplyStorage);
        $newGas = min($resources['gas'] + $gasProduction, $gasStorage);

        // 5. Complete construction
        $buildingsUpdated = $this->completeConstruction($planet, $buildingLevels);
        if ($buildingsUpdated) {
            $buildingLevels = $this->refreshBuildingLevels($planet);
        }

        // 6. Complete research
        $technologiesUpdated = $this->completeResearch($planet, $technologies);
        if ($technologiesUpdated) {
            $technologies = $this->refreshTechnologies($planet);
        }

        // 7. Update planet resources
        $planet->updateResources(
            $newSupply,
            0,
            $newGas,
            $powerProduction,
            $powerConsumption,
            $energyBalance,
            $supplyStorage,
            $gasStorage
        );

        // 8. Check fleet missions
        Mission::checkAndCompleteMissions($planet->getUserId(), $this->gameTick);

        // 9. Record production history (every 10 ticks to save space)
        if ($this->gameTick % 10 === 0) {
            $planet->recordProductionHistory(
                $this->gameTick,
                $newSupply,
                $newPower,
                $newGas,
                $energyBalance
            );
        }
    }

    private function completeConstruction(Planet $planet, array &$buildingLevels): bool
    {
        $now = time();
        $changed = false;

        // Get in-progress buildings
        $pending = \Model\Building::getInProgress($planet->getId());

        foreach ($pending as $entry) {
            $elapsed = $now - strtotime($entry['started_at']);
            $totalTime = Formulas::constructionTime($entry['building_key'], $entry['target_level']);

            if ($elapsed >= $totalTime) {
                // Complete the building
                \Model\Building::complete($planet->getId(), $entry['building_key'], $entry['target_level']);
                $buildingLevels[$entry['building_key']] = $entry['target_level'];
                $changed = true;
            }
        }

        return $changed;
    }

    private function refreshBuildingLevels(Planet $planet): array
    {
        $buildings = Building::getAllForPlanet($planet->getId());
        $levels = [];
        foreach ($buildings as $b) {
            $levels[$b['building_key']] = $b['level'];
        }
        return $levels;
    }

    private function completeResearch(Planet $planet, array &$technologies): bool
    {
        $now = time();
        $changed = false;

        $pending = \Model\Technology::getInProgress($planet->getId());

        foreach ($pending as $entry) {
            $elapsed = $now - strtotime($entry['started_at']);
            $totalTime = Formulas::researchTime($entry['technology_key'], $entry['current_level']);

            if ($elapsed >= $totalTime) {
                \Model\Technology::complete($planet->getId(), $entry['technology_key']);
                $technologies[$entry['technology_key']]['level'] = $entry['current_level'] + 1;
                $changed = true;
            }
        }

        return $changed;
    }

    private function refreshTechnologies(Planet $planet): array
    {
        $techs = Technology::getAllForPlanet($planet->getId());
        $result = [];
        foreach ($techs as $t) {
            $result[$t['technology_key']] = ['level' => $t['level']];
        }
        return $result;
    }
}
