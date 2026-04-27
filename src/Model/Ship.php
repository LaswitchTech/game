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

    public static function build(int $planetId, array $ships, int $metal, int $crystal, int $deuterium): array
    {
        $db = Connection::getInstance();
        $faction = Planet::findByUserId(\Model\User::findById($planetId)?->getUserId())?->getFaction();

        // Deduct resources
        $totalMetal = 0, $totalCrystal = 0, $totalDeuterium = 0;
        foreach ($ships as $key => $count) {
            $type = \Game\TechnologyTypes::getShip($key, $faction);
            if ($type === null) continue;
            $totalMetal += $type['base_cost']['metal'] * $count;
            $totalCrystal += $type['base_cost']['crystal'] * $count;
            $totalDeuterium += $type['base_cost']['deuterium'] * $count;
        }

        if ($metal < $totalMetal || $crystal < $totalCrystal || $deuterium < $totalDeuterium) {
            return ['success' => false, 'error' => 'Insufficient resources'];
        }

        $db->prepare('UPDATE resources SET metal = metal - ?, crystal = crystal - ?, deuterium = deuterium - ? WHERE planet_id = ?')
            ->execute([$totalMetal, $totalCrystal, $totalDeuterium, $planetId]);

        // Build ships
        foreach ($ships as $key => $count) {
            $current = self::getCount($planetId, $key);
            self::updateCount($planetId, $key, $current + $count, $faction);
        }

        return ['success' => true, 'message' => 'Ships built successfully'];
    }
}
