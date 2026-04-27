<?php

namespace Api\Controller;

use Service\AuthService;
use Model\Planet;
use Model\Building;
use Model\Technology;
use Game\BuildingTypes;
use Game\Formulas;

class PlanetController
{
    public function get(array $params): array
    {
        $user = AuthService::requireAuth();
        $faction = $user->getFaction();
        $planet = Planet::findByUserId($user->getId(), $faction);

        $buildings = [];
        foreach ($planet->getBuildings() as $b) {
            if ($b['faction'] === $faction) {
                $buildings[] = ['key' => $b['building_key'], 'level' => $b['level']];
            }
        }

        $technologies = [];
        foreach ($planet->getTechnologies() as $t) {
            if ($t['faction'] === $faction) {
                $technologies[] = ['key' => $t['technology_key'], 'level' => $t['level']];
            }
        }

        $ships = [];
        foreach ($planet->getShips() as $s) {
            if ($s['faction'] === $faction) {
                $ships[] = ['key' => $s['ship_key'], 'count' => $s['count']];
            }
        }

        $constructionQueue = [];
        foreach ($planet->getBuildings() as $b) {
            if ($b['faction'] === $faction && isset($b['constructed_at']) && $b['constructed_at'] !== $b['completed_at']) {
                $type = BuildingTypes::get($b['building_key'], $faction);
                if ($type === null) continue;
                $elapsed = time() - strtotime($b['constructed_at']);
                $totalTime = Formulas::constructionTime($type, $b['level']);
                $constructionQueue[] = [
                    'key' => $b['building_key'],
                    'target_level' => $b['level'],
                    'time_remaining' => max(0, $totalTime - $elapsed),
                ];
            }
        }

        $researchQueue = [];
        foreach ($planet->getTechnologies() as $t) {
            if ($t['faction'] === $faction && isset($t['completed_at']) && $t['completed_at'] !== null) {
                $tech = \Game\TechnologyTypes::get($t['technology_key']);
                if ($tech === null) continue;
                $elapsed = time() - strtotime($t['completed_at']);
                $totalTime = Formulas::researchTime($tech, $t['level'] - 1);
                $researchQueue[] = [
                    'technology_key' => $t['technology_key'],
                    'time_remaining' => max(0, $totalTime - $elapsed),
                ];
            }
        }

        $resources = $planet->getResources();
        $energyBalance = $resources['energy_production'] - $resources['energy_consumption'];

        return [
            'planet' => ['id' => $planet->getId(), 'name' => $planet->getName(), 'system' => $planet->getSystem(), 'position' => $planet->getPosition(), 'faction' => $faction],
            'resources' => $resources,
            'energy_balance' => $energyBalance,
            'buildings' => $buildings,
            'technologies' => $technologies,
            'ships' => $ships,
            'construction_queue' => $constructionQueue,
            'research_queue' => $researchQueue,
        ];
    }

    public function buildBuilding(array $params): array
    {
        $user = AuthService::requireAuth();
        $faction = $user->getFaction();
        $planet = Planet::findByUserId($user->getId(), $faction);

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['building_key'])) return ['error' => 'Missing building_key'];

        $key = $data['building_key'];
        $type = BuildingTypes::get($key, $faction);
        if ($type === null) return ['error' => 'Unknown building'];

        $level = Building::getLevel($planet->getId(), $key);
        if ($type['base_time'] <= 0) {
            $newLevel = $level + 1;
            Building::updateLevel($planet->getId(), $key, $newLevel, date('Y-m-d H:i:s'));
            return ['message' => "{$type['name']} upgraded to level {$newLevel}"];
        }

        $costMult = pow(1.5, $level);
        $cost = [
            'metal' => (int)($type['base_cost']['metal'] * $costMult),
            'crystal' => (int)($type['base_cost']['crystal'] * $costMult),
            'deuterium' => (int)($type['base_cost']['deuterium'] * $costMult),
        ];
        $res = $planet->getResources();
        if ($res['metal'] < $cost['metal'] || $res['crystal'] < $cost['crystal'] || $res['deuterium'] < $cost['deuterium']) {
            return ['error' => 'Insufficient resources'];
        }

        $planet->updateResources(
            $res['metal'] - $cost['metal'], $res['crystal'] - $cost['crystal'], $res['deuterium'] - $cost['deuterium'],
            $res['energy_production'], $res['energy_consumption'],
            $res['metal_storage'], $res['crystal_storage'], $res['deuterium_storage']
        );

        $buildTime = Formulas::constructionTime($type, $level + 1);
        $targetLevel = $level + 1;
        Building::startConstruction($planet->getId(), $key, $targetLevel, $faction);
        Building::updateLevel($planet->getId(), $key, $targetLevel, date('Y-m-d H:i:s', strtotime("+{$buildTime} seconds")));

        return ['message' => "Building {$type['name']} to level {$targetLevel}. Time: {$buildTime}s"];
    }

    public function startResearch(array $params): array
    {
        $user = AuthService::requireAuth();
        $faction = $user->getFaction();
        $planet = Planet::findByUserId($user->getId(), $faction);

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['technology_key'])) return ['error' => 'Missing technology_key'];

        $key = $data['technology_key'];
        $tech = \Game\TechnologyTypes::get($key);
        if ($tech === null) return ['error' => 'Unknown technology'];

        $level = Technology::getLevel($planet->getId(), $key);
        if ($level > 0) return ['error' => 'Technology already researched'];

        foreach ($tech['prerequisites'] as $prereq) {
            if (!Technology::hasTech($planet->getId(), $prereq)) return ['error' => "Requires {$prereq}"];
        }

        $cost = Formulas::researchCost($tech['base_cost'], $level);
        $res = $planet->getResources();
        if ($res['metal'] < $cost['metal'] || $res['crystal'] < $cost['crystal'] || $res['deuterium'] < $cost['deuterium']) {
            return ['error' => 'Insufficient resources'];
        }

        $planet->updateResources(
            $res['metal'] - $cost['metal'], $res['crystal'] - $cost['crystal'], $res['deuterium'] - $cost['deuterium'],
            $res['energy_production'], $res['energy_consumption'],
            $res['metal_storage'], $res['crystal_storage'], $res['deuterium_storage']
        );

        $researchTime = Formulas::researchTime($tech, $level);
        $researchQueue = Technology::getInProgress($planet->getId());
        if (!empty($researchQueue)) return ['error' => 'Research queue is busy'];

        Technology::startResearch($planet->getId(), $key, $faction);
        Technology::updateLevel($planet->getId(), $key, 1, date('Y-m-d H:i:s', strtotime("+{$researchTime} seconds")));

        return ['message' => "Researching {$tech['name']}. Time: {$researchTime}s"];
    }

    public function getBuildingTypes(array $params): array
    {
        $user = AuthService::requireAuth();
        $faction = $user->getFaction();
        $planet = Planet::findByUserId($user->getId(), $faction);

        $result = [];
        foreach (BuildingTypes::getByFaction($faction) as $key => $building) {
            $level = Building::getLevel($planet->getId(), $key);
            $costMult = pow(1.5, $level);
            $cost = [
                'metal' => (int)($building['base_cost']['metal'] * $costMult),
                'crystal' => (int)($building['base_cost']['crystal'] * $costMult),
                'deuterium' => (int)($building['base_cost']['deuterium'] * $costMult),
            ];
            $constructionTime = Formulas::constructionTime($building, $level + 1);
            $prereqMet = $building['required_tech'] === null || Technology::hasTech($planet->getId(), $building['required_tech']);

            $result[$key] = [
                'name' => $building['name'],
                'description' => $building['description'],
                'current_level' => $level,
                'cost' => $cost,
                'base_time' => $building['base_time'],
                'construction_time' => $constructionTime,
                'base_production' => $building['base_production'] ?? [],
                'base_energy_production' => $building['base_energy_production'] ?? null,
                'energy_production' => $building['base_energy_production'] ?? 0,
                'energy_consumption' => $building['energy_consumption'],
                'storage_bonus' => $building['storage_bonus'] ?? [],
                'required_tech' => $building['required_tech'],
                'prereq_met' => $prereqMet,
            ];
        }
        return $result;
    }

    public function getResearchTypes(array $params): array
    {
        $user = AuthService::requireAuth();
        $faction = $user->getFaction();
        $planet = Planet::findByUserId($user->getId(), $faction);

        $result = [];
        foreach (\Game\TechnologyTypes::getSharedTechs() as $key => $tech) {
            $level = Technology::getLevel($planet->getId(), $key);
            $cost = Formulas::researchCost($tech['base_cost'], $level);
            $researchTime = Formulas::researchTime($tech, $level);
            $prereqMet = $tech['prerequisites'] === [] || array_reduce($tech['prerequisites'], function ($carry, $p) use ($planet) {
                return $carry && Technology::hasTech($planet->getId(), $p);
            }, true);

            $result[$key] = [
                'name' => $tech['name'], 'description' => $tech['description'],
                'current_level' => $level, 'cost' => $cost, 'research_time' => $researchTime,
                'base_time' => $tech['base_time'], 'prerequisites' => $tech['prerequisites'],
                'effects' => $tech['effects'], 'prereq_met' => $prereqMet,
            ];
        }

        foreach (\Game\TechnologyTypes::getFactionTechs($faction) as $key => $tech) {
            $level = Technology::getLevel($planet->getId(), $key);
            $cost = Formulas::researchCost($tech['base_cost'], $level);
            $researchTime = Formulas::researchTime($tech, $level);
            $prereqMet = $tech['prerequisites'] === [] || array_reduce($tech['prerequisites'], function ($carry, $p) use ($planet) {
                return $carry && Technology::hasTech($planet->getId(), $p);
            }, true);

            $result[$key] = [
                'name' => $tech['name'], 'description' => $tech['description'],
                'current_level' => $level, 'cost' => $cost, 'research_time' => $researchTime,
                'base_time' => $tech['base_time'], 'prerequisites' => $tech['prerequisites'],
                'effects' => $tech['effects'], 'prereq_met' => $prereqMet,
            ];
        }
        return $result;
    }
}
