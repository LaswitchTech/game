<?php

namespace Service;

use Model\Building;
use Model\Planet;
use Model\Technology;
use Model\Ship;

class GameTickService
{
    public static function tickPlanet(int $userId, string $faction): void
    {
        $planet = Planet::findByUserId($userId, $faction);
        if ($planet === null) return;

        $planetId = $planet->getId();
        $buildings = $planet->getBuildings();
        $technologies = $planet->getTechnologies();
        $resources = $planet->getResources();

        // Calculate energy balance
        $energyProduction = 0;
        $energyConsumption = 0;

        foreach ($buildings as $building) {
            $type = \Game\BuildingTypes::get($building['building_key'], $faction);
            if ($type === null) continue;
            if ($building['level'] > 0) {
                $energyProduction += \Game\Formulas::energyProduction($type['base_energy_production'] ?? 0, $building['level']);
                $energyConsumption += \Game\Formulas::buildingEnergyConsumption($type['energy_consumption'], $building['level']);
            }
        }

        $energyBalance = $energyProduction - $energyConsumption;

        // Calculate resource production
        $supplyProd = 0;
        $gasProd = 0;

        foreach ($buildings as $building) {
            $type = \Game\BuildingTypes::get($building['building_key'], $faction);
            if ($type === null || $building['level'] < 1) continue;
            foreach ($type['base_production'] ?? [] as $res => $base) {
                $prod = \Game\Formulas::buildingProduction($base, $building['level']);
                if ($res === 'supply') $supplyProd += $prod;
                if ($res === 'gas') $gasProd += $prod;
            }
        }

        // Energy penalty: if production < consumption, reduce production by 75%
        if ($energyBalance < 0) {
            $supplyProd = max(0, (int)($supplyProd * 0.25));
            $gasProd = max(0, (int)($gasProd * 0.25));
        }

        // Calculate storage
        $supplyStorage = 5000;
        $gasStorage = 5000;

        foreach ($buildings as $building) {
            $type = \Game\BuildingTypes::get($building['building_key'], $faction);
            if ($type === null || !isset($type['storage_bonus'])) continue;
            foreach ($type['storage_bonus'] as $res => $bonus) {
                if ($building['level'] > 0) {
                    $storage = \Game\Formulas::storageCapacity($bonus, $building['level']);
                    if ($res === 'supply') $supplyStorage += $storage;
                    if ($res === 'gas') $gasStorage += $storage;
                }
            }
        }

        // Apply resource production
        $newSupply = min($supplyStorage, $resources['supply'] + $supplyProd);
        $newGas = min($gasStorage, $resources['gas'] + $gasProd);

        $planet->updateResources(
            $newSupply,
            $resources['power'],
            $newGas,
            $energyProduction, $energyConsumption,
            $energyBalance,
            $supplyStorage, $gasStorage
        );

        // Process construction queue
        foreach (Building::getActiveQueues($planetId) as $queue) {
            if (time() >= strtotime($queue['completed_at'] ?? '1970-01-01')) {
                $targetLevel = $queue['target_level'];
                Building::complete($queue['id']);
                Building::updateLevel($planetId, $queue['building_key'], $targetLevel, date('Y-m-d H:i:s'));
            }
        }

        // Process research queue
        foreach (Technology::getInProgress($planetId) as $queue) {
            $elapsed = time() - strtotime($queue['started_at']);
            $tech = \Game\TechnologyTypes::get($queue['technology_key']);
            if ($tech !== null) {
                $researchTime = \Game\Formulas::researchTime($tech, Technology::getLevel($planetId, $queue['technology_key']));
                if ($elapsed >= $researchTime) {
                    Technology::complete($queue['id']);
                    Technology::updateLevel($planetId, $queue['technology_key'], Technology::getLevel($planetId, $queue['technology_key']) + 1, date('Y-m-d H:i:s'));
                }
            }
        }

        // Process fleets
        self::processFleets($planetId);
    }

    private static function processFleets(int $planetId): void
    {
        $db = \Database\Connection::getInstance();
        $stmt = $db->prepare("SELECT * FROM fleets WHERE planet_id = ? AND status != ? AND arrival_at <= CURRENT_TIMESTAMP");
        $stmt->execute([$planetId, 'completed']);
        $fleets = $stmt->fetchAll();

        foreach ($fleets as $fleet) {
            $ships = json_decode($fleet['ships'], true) ?? [];
            foreach ($ships as $key => $count) {
                if ($count > 0) {
                    $current = Ship::getCount($planetId, $key);
                    Ship::updateCount($planetId, $key, $current + $count);
                }
            }
            $db->prepare("UPDATE fleets SET status = ? WHERE id = ?")
                ->execute(['arrived', $fleet['id']]);
        }
    }

    public static function tickAllUsers(): void
    {
        $db = \Database\Connection::getInstance();
        $stmt = $db->query('SELECT id, faction FROM users WHERE faction IS NOT NULL');
        $users = $stmt->fetchAll();

        foreach ($users as $userRow) {
            try {
                self::tickPlanet($userRow['id'], $userRow['faction']);
            } catch (\Throwable $e) {
                error_log('Game tick error for user ' . $userRow['id'] . ': ' . $e->getMessage());
            }
        }
    }
}
