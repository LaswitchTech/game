<?php

namespace Api\Controller;

use Service\AuthService;
use Model\Planet;
use Model\Ship;
use Game\Formulas;

class ShipyardController
{
    public function getShips(array $params): array
    {
        $user = AuthService::requireAuth();
        $faction = $user->getFaction();
        $planet = Planet::findByUserId($user->getId(), $faction);
        $ships = $planet->getShips();
        $fleet = array_filter($ships, fn($s) => $s['faction'] === $faction);

        return ['ships' => array_values($fleet)];
    }

    public function getShipTypes(array $params): array
    {
        $user = AuthService::requireAuth();
        $faction = $user->getFaction();
        $planet = Planet::findByUserId($user->getId(), $faction);

        $ships = \Game\TechnologyTypes::getShips($faction);
        $result = [];

        foreach ($ships as $key => $ship) {
            $count = 0;
            foreach ($planet->getShips() as $s) {
                if ($s['faction'] === $faction && $s['ship_key'] === $key) {
                    $count = (int)$s['count'];
                    break;
                }
            }

            $cost = $ship['base_cost'];
            $buildTime = $ship['build_time'];

            // Check if prerequisites are met
            $prereqMet = true;
            if ($ship['required_tech'] !== null) {
                $prereqMet = \Model\Technology::hasTech($planet->getId(), $ship['required_tech']);
            }

            $result[$key] = [
                'name' => $ship['name'],
                'description' => $ship['description'],
                'count' => $count,
                'cost' => $cost,
                'build_time' => $buildTime,
                'required_tech' => $ship['required_tech'],
                'properties' => $ship['properties'],
                'prereq_met' => $prereqMet,
            ];
        }
        return $result;
    }

    public function buildShips(array $params): array
    {
        $user = AuthService::requireAuth();
        $faction = $user->getFaction();
        $planet = Planet::findByUserId($user->getId(), $faction);

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) return ['error' => 'Invalid request'];

        $ships = [];
        foreach ($data as $key => $count) {
            if ($count > 0) $ships[$key] = $count;
        }

        if (empty($ships)) return ['error' => 'No ships specified'];

        $resources = $planet->getResources();
        return Ship::build($planet->getId(), $ships, $resources['supply'], $resources['gas']);
    }
}
