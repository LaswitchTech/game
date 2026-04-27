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
}
