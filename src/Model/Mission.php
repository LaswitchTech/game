<?php

namespace Model;

use Database\Connection;

class Mission
{
    public static function send(int $planetId, array $ships, int $targetSystem, int $targetPlanet, string $missionType, string $faction): ?array
    {
        // Check fleet capacity
        foreach ($ships as $key => $count) {
            if ($count <= 0) continue;
            $current = Ship::getCount($planetId, $key);
            if ($current < $count) return ['error' => "Insufficient {$key} on planet"];
        }

        // Calculate travel time
        $distance = abs($targetSystem - 1) + abs($targetPlanet - 1);
        if ($distance < 1) return ['error' => 'Cannot send fleet to current planet'];

        $propulsionLevel = Technology::getLevel($planetId, 'propulsion_tech');
        $hyperDriveLevel = Technology::getLevel($planetId, 'hyperdrive_tech');
        $speed = 10000;
        $travelTime = \Game\Formulas::travelTime($distance, $speed, $hyperDriveLevel);

        $departureAt = date('Y-m-d H:i:s');
        $arrivalAt = date('Y-m-d H:i:s', strtotime("+{$travelTime} seconds"));

        $db = Connection::getInstance();

        // Remove ships from planet
        foreach ($ships as $key => $count) {
            if ($count <= 0) continue;
            $db->prepare('UPDATE ships SET count = count - ? WHERE planet_id = ? AND ship_key = ? AND count >= ?')
                ->execute([$count, $planetId, $key, $count]);
        }

        $shipsJson = json_encode($ships);
        $db->prepare('INSERT INTO fleets (planet_id, ships, target_system, target_planet, departure_at, arrival_at, mission_type, faction) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([$planetId, $shipsJson, $targetSystem, $targetPlanet, $departureAt, $arrivalAt, $missionType, $faction]);

        return ['success' => true, 'fleet_id' => (int)$db->lastInsertId(), 'arrival_at' => $arrivalAt];
    }

    public static function getActive(): array
    {
        $db = Connection::getInstance();
        $stmt = $db->query("SELECT * FROM fleets WHERE status != 'completed' AND status != 'arrived' AND arrival_at > CURRENT_TIMESTAMP");
        return $stmt->fetchAll();
    }

    public static function getForPlanet(int $planetId): array
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM fleets WHERE planet_id = ? AND status != ?');
        $stmt->execute([$planetId, 'completed']);
        return $stmt->fetchAll();
    }

    public static function complete(int $fleetId): void
    {
        $db = Connection::getInstance();
        $db->prepare('UPDATE fleets SET status = ? WHERE id = ?')
            ->execute(['completed', $fleetId]);
    }
}
