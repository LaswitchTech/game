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

        // 1. Calculate energy balance
        $energyProduction = 0;
        $energyConsumption = 0;

        foreach ($buildingLevels as $key => $level) {
            if ($level < 1) {
                continue;
            }

            if (BuildingTypes::isEnergyBuilding($key)) {
                $energyProduction += Formulas::energyProduction($key, $level);
            } else {
                $energyConsumption += Formulas::buildingEnergyConsumption($key, $level);
            }
        }

        $energyBalance = Formulas::energyBalance($energyProduction, $energyConsumption);

        // If energy is negative, reduce resource production by 75%
        $productionPenalty = $energyBalance < 0 ? 0.25 : 1.0;

        // 2. Calculate resource production
        $metalProduction = 0;
        $crystalProduction = 0;
        $deuteriumProduction = 0;

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

            $production = Formulas::buildingProduction($key, $level) * $productionPenalty;

            if (isset($building['base_production']['metal'])) {
                $metalProduction += $production;
            }
            if (isset($building['base_production']['crystal'])) {
                $crystalProduction += $production;
            }
            if (isset($building['base_production']['deuterium'])) {
                $deuteriumProduction += $production;
            }
        }

        // 3. Calculate storage capacities
        $metalStorage = Formulas::calculateStorage('metal', $buildingLevels);
        $crystalStorage = Formulas::calculateStorage('crystal', $buildingLevels);
        $deuteriumStorage = Formulas::calculateStorage('deuterium', $buildingLevels);

        // 4. Apply production (capped at storage)
        $newMetal = min($resources['metal'] + $metalProduction, $metalStorage);
        $newCrystal = min($resources['crystal'] + $crystalProduction, $crystalStorage);
        $newDeuterium = min($resources['deuterium'] + $deuteriumProduction, $deuteriumStorage);

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
            $newMetal,
            $newCrystal,
            $newDeuterium,
            $energyProduction,
            $energyConsumption,
            $metalStorage,
            $crystalStorage,
            $deuteriumStorage
        );

        // 8. Check fleet missions
        Mission::checkAndCompleteMissions($planet->getUserId(), $this->gameTick);

        // 9. Record production history (every 10 ticks to save space)
        if ($this->gameTick % 10 === 0) {
            $planet->recordProductionHistory(
                $this->gameTick,
                $newMetal,
                $newCrystal,
                $newDeuterium,
                $energyProduction,
                $energyConsumption
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
