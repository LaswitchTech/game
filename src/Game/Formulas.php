<?php

namespace Game;

class Formulas
{
    /**
     * Calculate cost to build/upgrade a building to the given level.
     * OGame formula: cost = base_cost * 1.5^level
     */
    public static function buildingCost(string $key, int $currentLevel): array
    {
        $building = BuildingTypes::get($key);
        if ($building === null) {
            return ['supply' => 0, 'gas' => 0];
        }
        $multiplier = pow(1.5, $currentLevel);
        return [
            'supply' => (int)($building['base_cost']['supply'] * $multiplier),
            'gas' => (int)($building['base_cost']['gas'] * $multiplier),
        ];
    }

    /**
     * Calculate building production at a given level.
     * OGame formula: production = base_production * level^2 * 0.1
     */
    public static function buildingProduction(int $baseProduction, int $level): int
    {
        if ($level < 1) return 0;
        return (int)($baseProduction * $level * $level * 0.1);
    }

    /**
     * Calculate energy production at a given level.
     * OGame formula: energy_production * level^2 * 0.1
     */
    public static function energyProduction(int $baseEnergyProduction, int $level): int
    {
        if ($level < 1) return 0;
        return (int)($baseEnergyProduction * $level * $level * 0.1);
    }

    /**
     * Calculate building energy consumption at a given level.
     */
    public static function buildingEnergyConsumption(int $energyConsumption, int $level): int
    {
        return $energyConsumption * $level;
    }

    /**
     * Calculate storage capacity bonus at a given level.
     */
    public static function storageCapacity(int $storageBonus, int $level): int
    {
        return $storageBonus * $level;
    }

    /**
     * Calculate research cost to research a technology to the given level.
     * OGame formula: cost = base_cost * (level+1)^3 * 1.5
     */
    public static function researchCost(array $baseCost, int $currentLevel): array
    {
        $multiplier = pow($currentLevel + 1, 3) * 1.5;
        return [
            'supply' => (int)($baseCost['supply'] * $multiplier),
            'gas' => (int)($baseCost['gas'] * $multiplier),
        ];
    }

    /**
     * Calculate research time at a given level.
     * OGame formula: time = base_time * (level+1)
     */
    public static function researchTime(array $techType, int $currentLevel): int
    {
        return (int)($techType['base_time'] * ($currentLevel + 1));
    }

    /**
     * Calculate construction time at a given level.
     * OGame formula: time = base_time * target_level^2
     */
    public static function constructionTime(array $buildingType, int $targetLevel): int
    {
        if ($buildingType['base_time'] <= 0) return 0;
        return (int)($buildingType['base_time'] * $targetLevel * $targetLevel);
    }

    /**
     * Calculate energy balance.
     */
    public static function energyBalance(int $production, int $consumption): int
    {
        return $production - $consumption;
    }

    /**
     * Calculate total storage capacity for a resource based on building levels.
     */
    public static function calculateStorage(array $resourceKey, array $buildings): int
    {
        $storage = 5000;
        foreach ($buildings as $building) {
            $type = BuildingTypes::get($building['building_key']);
            if ($type === null || !isset($type['storage_bonus'])) continue;
            $storage += self::storageCapacity($type['storage_bonus'][$resourceKey] ?? 0, $building['level']);
        }
        return $storage;
    }

    /**
     * Calculate fleet speed based on propulsion tech level.
     */
    public static function fleetSpeed(int $baseSpeed, int $propulsionLevel): float
    {
        return $baseSpeed * (1 + ($propulsionLevel * 0.5));
    }

    /**
     * Calculate fleet attack with tech bonuses.
     */
    public static function fleetAttack(int $baseAttack, int $weaponsLevel): int
    {
        return (int)($baseAttack * (1 + ($weaponsLevel * 0.25)));
    }

    /**
     * Calculate fleet shield with tech bonuses.
     */
    public static function fleetShield(int $baseShield, int $shieldLevel): int
    {
        return (int)($baseShield * (1 + ($shieldLevel * 0.25)));
    }

    /**
     * Calculate fleet armor with tech bonuses.
     */
    public static function fleetArmor(int $baseArmor, int $armorLevel): int
    {
        return (int)($baseArmor * (1 + ($armorLevel * 0.25)));
    }

    /**
     * Calculate combat value for a fleet.
     */
    public static function fleetCombatValue(array $ships, array $shipTypes, string $faction): int
    {
        $weaponsLevel = \Model\Technology::getLevel(0, 'weapons_tech');
        $shieldLevel = \Model\Technology::getLevel(0, 'shielding_tech');
        $armorLevel = \Model\Technology::getLevel(0, 'armor_tech');

        $total = 0;
        foreach ($ships as $key => $count) {
            $type = \Game\TechnologyTypes::getShip($key, $faction);
            if ($type === null) continue;
            $p = $type['properties'];
            $total += self::fleetAttack($p['attack'], $weaponsLevel) +
                      self::fleetShield($p['shield'], $shieldLevel) +
                      self::fleetArmor($p['armor'], $armorLevel);
        }
        return (int)$total;
    }

    /**
     * Calculate travel time for a fleet.
     */
    public static function travelTime(int $distance, int $baseSpeed, int $hyperDriveLevel = 0): int
    {
        if ($distance < 1) return 0;
        $speed = self::fleetSpeed($baseSpeed, 0) * (1 + ($hyperDriveLevel * 1.0));
        return (int)($distance / $speed * 3600);
    }
}
