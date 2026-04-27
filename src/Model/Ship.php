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

    /**
     * Deploy ships from planet to orbit.
     */
    public static function deployToOrbit(int $planetId, string $key, int $count, ?string $faction = null): array
    {
        $db = Connection::getInstance();
        $f = $faction ?: (Planet::findById($planetId)?->getFaction() ?? 'unsc');

        // Check ships available on planet
        $stmt = $db->prepare('SELECT count FROM ships WHERE planet_id = ? AND ship_key = ? AND orbit_status = ? AND faction = ?');
        $stmt->execute([$planetId, $key, 'planet', $f]);
        $row = $stmt->fetch();
        if (!$row || (int)$row['count'] < $count) {
            return ['success' => false, 'error' => "Insufficient ships on planet to deploy"];
        }

        // Check if orbit is full (max orbital capacity)
        $orbitCount = self::getOrbitCount($planetId);
        $orbitCapacity = self::getOrbitCapacity($planetId);
        if ($orbitCount + $count > $orbitCapacity) {
            return ['success' => false, 'error' => 'Orbit is full'];
        }

        // Move ships to orbit
        $db->prepare('UPDATE ships SET count = count - ? WHERE planet_id = ? AND ship_key = ? AND orbit_status = ? AND faction = ?')
            ->execute([$count, $planetId, $key, 'planet', $f]);
        $db->prepare('INSERT INTO ships (planet_id, ship_key, count, orbit_status, faction) VALUES (?, ?, ?, "orbit", ?) ON CONFLICT(planet_id, ship_key, faction) DO UPDATE SET count = orbit_status != "orbit" THEN EXCLUDED.count + ?, orbit_status = "orbit", faction = ?')
            ->execute([$planetId, $key, $count, $f, $count, $f]);

        return ['success' => true];
    }

    /**
     * Recall ships from orbit back to planet.
     */
    public static function recallFromOrbit(int $planetId, string $key, int $count, ?string $faction = null): array
    {
        $db = Connection::getInstance();
        $f = $faction ?: (Planet::findById($planetId)?->getFaction() ?? 'unsc');

        // Check ships in orbit
        $stmt = $db->prepare('SELECT count FROM ships WHERE planet_id = ? AND ship_key = ? AND orbit_status = ? AND faction = ?');
        $stmt->execute([$planetId, $key, 'orbit', $f]);
        $row = $stmt->fetch();
        if (!$row || (int)$row['count'] < $count) {
            return ['success' => false, 'error' => 'No ships in orbit to recall'];
        }

        // Recall ships
        $db->prepare('UPDATE ships SET count = count - ? WHERE planet_id = ? AND ship_key = ? AND orbit_status = "orbit" AND faction = ?')
            ->execute([$count, $planetId, $key, $f]);
        $db->prepare('INSERT INTO ships (planet_id, ship_key, count, orbit_status, faction) VALUES (?, ?, ?, "planet", ?) ON CONFLICT(planet_id, ship_key, faction) DO UPDATE SET count = orbit_status != "planet" THEN EXCLUDED.count + ?, orbit_status = "planet", faction = ?')
            ->execute([$planetId, $key, $count, $f, $count, $f]);

        return ['success' => true];
    }

    /**
     * Get total ships in orbit for a planet.
     */
    public static function getOrbitCount(int $planetId): int
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT COALESCE(SUM(count), 0) as total FROM ships WHERE planet_id = ? AND orbit_status = ?');
        $stmt->execute([$planetId, 'orbit']);
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    /**
     * Get max orbital capacity for a planet (based on orbital cannons and AI cores).
     */
    public static function getOrbitCapacity(int $planetId): int
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT SUM(level) as total FROM buildings WHERE planet_id = ? AND building_key IN ("orbital_cannon", "disruptor_cannon")');
        $stmt->execute([$planetId]);
        $row = $stmt->fetch();
        // Each orbital cannon/disruptor adds 500 orbital capacity
        $baseCapacity = 500;
        $buildingBonus = (($row['total'] ?? 0) * 500);
        return $baseCapacity + $buildingBonus;
    }

    /**
     * Get fleet defense value for a planet (ships in orbit only).
     */
    public static function getOrbitalDefenseValue(int $planetId): array
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM ships WHERE planet_id = ? AND orbit_status = ?');
        $stmt->execute([$planetId, 'orbit']);
        $ships = $stmt->fetchAll();

        $faction = Planet::findById($planetId)?->getFaction();
        $attack = 0;
        $shield = 0;
        $armor = 0;
        $weaponsLevel = Technology::getLevel($planetId, 'weapons_tech');
        $shieldLevel = Technology::getLevel($planetId, 'shield_gen_tech');
        $armorLevel = Technology::getLevel($planetId, 'armor_tech');

        foreach ($ships as $ship) {
            $type = \Game\TechnologyTypes::getShip($ship['ship_key'], $faction ?? 'unsc');
            if ($type === null) continue;
            $p = $type['properties'];
            $count = (int)$ship['count'];
            $attack += Formulas::fleetAttack($p['attack'], $weaponsLevel) * $count;
            $shield += Formulas::fleetShield($p['shield'], $shieldLevel) * $count;
            $armor += Formulas::fleetArmor($p['armor'], $armorLevel) * $count;
        }

        // Add planetary defense buildings
        $planetaryDefense = self::getPlanetaryDefense($planetId);
        $attack += $planetaryDefense['attack'];
        $shield += $planetaryDefense['shield'];
        $armor += $planetaryDefense['armor'];

        return ['attack' => $attack, 'shield' => $shield, 'armor' => $armor];
    }

    /**
     * Get planetary (static) defense value from buildings.
     */
    private static function getPlanetaryDefense(int $planetId): array
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT building_key, level FROM buildings WHERE planet_id = ? AND level > 0');
        $stmt->execute([$planetId]);
        $buildings = $stmt->fetchAll();

        $attack = 0;
        $shield = 0;
        $armor = 0;

        foreach ($buildings as $building) {
            $type = \Game\BuildingTypes::get($building['building_key']);
            if ($type === null) continue;
            switch ($building['building_key']) {
                case 'orbital_cannon':
                case 'disruptor_cannon':
                    $attack += 10 * $building['level'];
                    break;
                case 'shield_gen':
                case 'shadow_shield':
                    $shield += 10 * $building['level'];
                    break;
                case 'plating_tech':
                case 'reactor_plating':
                    $armor += 10 * $building['level'];
                    break;
            }
        }

        return ['attack' => $attack, 'shield' => $shield, 'armor' => $armor];
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
