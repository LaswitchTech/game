<?php

namespace Model;

use Database\Connection;

class Ship
{
    public static function getAllForPlanet(int $planetId): array
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM ships WHERE planet_id = ?');
        $stmt->execute([$planetId]);
        return $stmt->fetchAll();
    }

    public static function getCount(int $planetId, string $key): int
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT count FROM ships WHERE planet_id = ? AND ship_key = ?');
        $stmt->execute([$planetId, $key]);
        $row = $stmt->fetch();
        return $row ? (int)$row['count'] : 0;
    }

    public static function updateCount(int $planetId, string $key, int $count, ?string $faction = null): void
    {
        $db = Connection::getInstance();
        $f = $faction ?: (Planet::findById($planetId)?->getFaction() ?? 'unsc');
        $db->prepare('INSERT INTO ships (planet_id, ship_key, count, faction) VALUES (?, ?, ?, ?) ON CONFLICT(planet_id, ship_key, faction) DO UPDATE SET count = ?, faction = ?')
            ->execute([$planetId, $key, $count, $f, $count, $f]);
    }

    public static function build(int $planetId, array $ships, int $supply, int $gas): array
    {
        $db = Connection::getInstance();
        $faction = Planet::findByUserId(\Model\User::findById($planetId)?->getUserId())?->getFaction();

        // Deduct resources
        $totalSupply = 0, $totalGas = 0;
        foreach ($ships as $key => $count) {
            $type = \Game\TechnologyTypes::getShip($key, $faction);
            if ($type === null) continue;
            $totalSupply += ($type['base_cost']['supply'] ?? 0) * $count;
            $totalGas += ($type['base_cost']['gas'] ?? 0) * $count;
        }

        if ($supply < $totalSupply || $gas < $totalGas) {
            return ['success' => false, 'error' => 'Insufficient resources'];
        }

        $db->prepare('UPDATE resources SET supply = supply - ?, gas = gas - ? WHERE planet_id = ?')
            ->execute([$totalSupply, $totalGas, $planetId]);

        // Build ships
        foreach ($ships as $key => $count) {
            $current = self::getCount($planetId, $key);
            self::updateCount($planetId, $key, $current + $count, $faction);
        }

        return ['success' => true, 'message' => 'Ships built successfully'];
    }
}
