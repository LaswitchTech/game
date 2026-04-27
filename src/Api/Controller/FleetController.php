<?php

namespace Api\Controller;

use Service\AuthService;
use Model\Planet;
use Model\Mission;

class FleetController
{
    public function sendFleet(array $params): array
    {
        $user = AuthService::requireAuth();
        $faction = $user->getFaction();
        $planet = Planet::findByUserId($user->getId(), $faction);

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) return ['error' => 'Invalid request'];

        $ships = [];
        foreach ($data['ships'] ?? [] as $key => $count) {
            if ($count > 0) $ships[$key] = $count;
        }

        if (empty($ships)) return ['error' => 'No ships specified'];

        return Mission::send(
            $planet->getId(),
            $ships,
            $data['target_system'] ?? 1,
            $data['target_planet'] ?? 1,
            $data['mission_type'] ?? 'trade',
            $faction
        );
    }

    public function getActiveFleets(array $params): array
    {
        $user = AuthService::requireAuth();
        $faction = $user->getFaction();
        $planet = Planet::findByUserId($user->getId(), $faction);

        $fleets = [];
        $db = \Database\Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM fleets WHERE planet_id = ? AND status != ?');
        $stmt->execute([$planet->getId(), 'completed']);
        return ['fleets' => $stmt->fetchAll()];
    }

    public function deployToOrbit(array $params): array
    {
        $user = AuthService::requireAuth();
        $faction = $user->getFaction();
        $planet = Planet::findByUserId($user->getId(), $faction);

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) return ['error' => 'Invalid request'];

        $ships = $data['ships'] ?? [];
        if (empty($ships)) return ['error' => 'No ships specified'];

        $db = \Database\Connection::getInstance();
        $results = [];
        foreach ($ships as $key => $count) {
            $result = \Model\Ship::deployToOrbit($planet->getId(), $key, $count, $faction);
            $results[$key] = $result;
            if (!$result['success']) {
                return $result;
            }
        }

        return ['success' => true, 'message' => 'Ships deployed to orbit'];
    }

    public function recallFromOrbit(array $params): array
    {
        $user = AuthService::requireAuth();
        $faction = $user->getFaction();
        $planet = Planet::findByUserId($user->getId(), $faction);

        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) return ['error' => 'Invalid request'];

        $ships = $data['ships'] ?? [];
        if (empty($ships)) return ['error' => 'No ships specified'];

        $db = \Database\Connection::getInstance();
        $results = [];
        foreach ($ships as $key => $count) {
            $result = \Model\Ship::recallFromOrbit($planet->getId(), $key, $count, $faction);
            $results[$key] = $result;
            if (!$result['success']) {
                return $result;
            }
        }

        return ['success' => true, 'message' => 'Ships recalled to planet'];
    }

    public function getOrbitalDefense(array $params): array
    {
        $user = AuthService::requireAuth();
        $faction = $user->getFaction();
        $planet = Planet::findByUserId($user->getId(), $faction);

        $defense = \Model\Ship::getOrbitalDefenseValue($planet->getId());
        $orbitCount = \Model\Ship::getOrbitCount($planet->getId());
        $orbitCapacity = \Model\Ship::getOrbitCapacity($planet->getId());

        return [
            'attack' => $defense['attack'],
            'shield' => $defense['shield'],
            'armor' => $defense['armor'],
            'orbit_ships' => $orbitCount,
            'orbit_capacity' => $orbitCapacity,
        ];
    }
}
