<?php

namespace Game;

class BuildingTypes
{
    private static array $buildings = [
        'unsc' => [
            'supply_mine' => [
                'name' => 'Supply Mine',
                'description' => 'Extracts supply materials from the planet crust.',
                'base_cost' => ['supply' => 70, 'gas' => 20],
                'base_production' => ['supply' => 35],
                'base_time' => 20,
                'energy_consumption' => 10,
                'required_tech' => null,
            ],
            'gas_extractor' => [
                'name' => 'Gas Extractor',
                'description' => 'Harvests atmospheric gas deposits.',
                'base_cost' => ['supply' => 55, 'gas' => 30],
                'base_production' => ['gas' => 25],
                'base_time' => 15,
                'energy_consumption' => 10,
                'required_tech' => null,
            ],
            'gas_condenser' => [
                'name' => 'Gas Condenser',
                'description' => 'Condenses gas from heavy deposits.',
                'base_cost' => ['supply' => 250, 'gas' => 80],
                'base_production' => ['gas' => 10],
                'base_time' => 60,
                'energy_consumption' => 25,
                'required_tech' => 'energy_tech',
            ],
            'fusion_reactor' => [
                'name' => 'Fusion Reactor',
                'description' => 'Generates power from fusion reactions.',
                'base_cost' => ['supply' => 80, 'gas' => 35],
                'base_production' => [],
                'base_energy_production' => 25,
                'base_time' => 30,
                'energy_consumption' => 0,
                'required_tech' => null,
            ],
            'heavy_fusion' => [
                'name' => 'Heavy Fusion Reactor',
                'description' => 'Generates high-yield power from fusion.',
                'base_cost' => ['supply' => 250, 'gas' => 80],
                'base_production' => [],
                'base_energy_production' => 125,
                'base_time' => 200,
                'energy_consumption' => 0,
                'required_tech' => 'industrial_mastery',
            ],
            'supply_storage' => [
                'name' => 'Supply Vault',
                'description' => 'Stores refined supply.',
                'base_cost' => ['supply' => 120, 'gas' => 70],
                'base_production' => [],
                'storage_bonus' => ['supply' => 600],
                'base_time' => 10,
                'energy_consumption' => 0,
                'required_tech' => null,
            ],
            'gas_storage' => [
                'name' => 'Gas Vault',
                'description' => 'Stores refined gas.',
                'base_cost' => ['supply' => 120, 'gas' => 70],
                'base_production' => [],
                'storage_bonus' => ['gas' => 600],
                'base_time' => 10,
                'energy_consumption' => 0,
                'required_tech' => null,
            ],
            'research_facility' => [
                'name' => 'Research Facility',
                'description' => 'Enables scientific research.',
                'base_cost' => ['supply' => 250, 'gas' => 450],
                'base_production' => [],
                'base_energy_production' => 50,
                'base_time' => 400,
                'energy_consumption' => 50,
                'required_tech' => null,
            ],
            'military_dock' => [
                'name' => 'Military Dock',
                'description' => 'Enables UNSC fleet construction.',
                'base_cost' => ['supply' => 2500, 'gas' => 1200],
                'base_production' => [],
                'base_energy_production' => 120,
                'base_time' => 1200,
                'energy_consumption' => 120,
                'required_tech' => 'robotics_tech',
            ],
            'armoury' => [
                'name' => 'Armoury',
                'description' => 'Enables advanced UNSC ship construction.',
                'base_cost' => ['supply' => 50000, 'gas' => 50000],
                'base_production' => [],
                'base_energy_production' => 220,
                'base_time' => 28800,
                'energy_consumption' => 220,
                'required_tech' => 'ai_computer',
            ],
            'ai_core' => [
                'name' => 'AI Core',
                'description' => 'Advanced AI command system.',
                'base_cost' => ['supply' => 200000, 'gas' => 45000],
                'base_production' => [],
                'base_energy_production' => 350,
                'base_time' => 72000,
                'energy_consumption' => 350,
                'required_tech' => 'ai_tech',
            ],
            'orbital_cannon' => [
                'name' => 'Orbital Cannon',
                'description' => 'Enhances orbital defense capability.',
                'base_cost' => ['supply' => 2500, 'gas' => 500],
                'base_production' => [],
                'base_energy_production' => 35,
                'base_time' => 600,
                'energy_consumption' => 35,
                'required_tech' => 'combustion_engine',
            ],
            'shield_gen' => [
                'name' => 'Shield Generator',
                'description' => 'Enhances planetary defense shields.',
                'base_cost' => ['supply' => 1800, 'gas' => 600],
                'base_production' => [],
                'base_energy_production' => 30,
                'base_time' => 450,
                'energy_consumption' => 30,
                'required_tech' => 'energy_tech',
            ],
            'plating_tech' => [
                'name' => 'Plating Tech',
                'description' => 'Reinforces hull plating technology.',
                'base_cost' => ['supply' => 1800, 'gas' => 1800],
                'base_production' => [],
                'base_energy_production' => 25,
                'base_time' => 450,
                'energy_consumption' => 25,
                'required_tech' => 'combustion_engine',
            ],
        ],
        'covenant' => [
            'energy_mine' => [
                'name' => 'Energy Mine',
                'description' => 'Harvests energy crystals from the crust.',
                'base_cost' => ['supply' => 60, 'gas' => 15],
                'base_production' => ['supply' => 30],
                'base_time' => 20,
                'energy_consumption' => 10,
                'required_tech' => null,
            ],
            'crystal_extraction_array' => [
                'name' => 'Crystal Extraction Array',
                'description' => 'Extracts gas deposits.',
                'base_cost' => ['supply' => 48, 'gas' => 24],
                'base_production' => ['gas' => 20],
                'base_time' => 15,
                'energy_consumption' => 10,
                'required_tech' => null,
            ],
            'deuterium_converter' => [
                'name' => 'Gas Converter',
                'description' => 'Converts heavy water to gas.',
                'base_cost' => ['supply' => 225, 'gas' => 75],
                'base_production' => ['gas' => 10],
                'base_time' => 60,
                'energy_consumption' => 20,
                'required_tech' => 'energy_tech',
            ],
            'power_generator' => [
                'name' => 'Power Generator',
                'description' => 'Generates power from plasma.',
                'base_cost' => ['supply' => 75, 'gas' => 30],
                'base_production' => [],
                'base_energy_production' => 20,
                'base_time' => 30,
                'energy_consumption' => 0,
                'required_tech' => null,
            ],
            'plasma_reactor' => [
                'name' => 'Plasma Reactor',
                'description' => 'Generates massive power from plasma.',
                'base_cost' => ['supply' => 225, 'gas' => 75],
                'base_production' => [],
                'base_energy_production' => 100,
                'base_time' => 200,
                'energy_consumption' => 0,
                'required_tech' => 'psionic_enhancement',
            ],
            'supply_cell' => [
                'name' => 'Supply Cell',
                'description' => 'Stores harvested supply cells.',
                'base_cost' => ['supply' => 100, 'gas' => 60],
                'base_production' => [],
                'storage_bonus' => ['supply' => 500],
                'base_time' => 10,
                'energy_consumption' => 0,
                'required_tech' => null,
            ],
            'gas_cell' => [
                'name' => 'Gas Cell',
                'description' => 'Stores harvested gas cells.',
                'base_cost' => ['supply' => 100, 'gas' => 60],
                'base_production' => [],
                'storage_bonus' => ['gas' => 500],
                'base_time' => 10,
                'energy_consumption' => 0,
                'required_tech' => null,
            ],
            'research_facility' => [
                'name' => 'Research Facility',
                'description' => 'Enables scientific research.',
                'base_cost' => ['supply' => 250, 'gas' => 450],
                'base_production' => [],
                'base_energy_production' => 50,
                'base_time' => 400,
                'energy_consumption' => 50,
                'required_tech' => null,
            ],
            'forge' => [
                'name' => 'Forge',
                'description' => 'Enables Covenant fleet construction.',
                'base_cost' => ['supply' => 2000, 'gas' => 1000],
                'base_production' => [],
                'base_energy_production' => 100,
                'base_time' => 1200,
                'energy_consumption' => 100,
                'required_tech' => 'robotics_tech',
            ],
            'psionic_temple' => [
                'name' => 'Psionic Temple',
                'description' => 'Enables advanced Covenant construction.',
                'base_cost' => ['supply' => 48000, 'gas' => 48000],
                'base_production' => [],
                'base_energy_production' => 200,
                'base_time' => 28800,
                'energy_consumption' => 200,
                'required_tech' => 'gifted_tech',
            ],
            'sangheili_engine' => [
                'name' => 'Sangheili Engine',
                'description' => 'Advanced Sangheili engineering facility.',
                'base_cost' => ['supply' => 200000, 'gas' => 40000],
                'base_production' => [],
                'base_energy_production' => 300,
                'base_time' => 72000,
                'energy_consumption' => 300,
                'required_tech' => 'ai_tech',
            ],
            'disruptor_cannon' => [
                'name' => 'Disruptor Cannon',
                'description' => 'Enhances orbital defense capability.',
                'base_cost' => ['supply' => 2000, 'gas' => 400],
                'base_production' => [],
                'base_energy_production' => 30,
                'base_time' => 600,
                'energy_consumption' => 30,
                'required_tech' => 'combustion_engine',
            ],
            'shadow_shield' => [
                'name' => 'Shadow Shield',
                'description' => 'Enhances defensive shields.',
                'base_cost' => ['supply' => 1500, 'gas' => 500],
                'base_production' => [],
                'base_energy_production' => 25,
                'base_time' => 450,
                'energy_consumption' => 25,
                'required_tech' => 'energy_tech',
            ],
            'reactor_plating' => [
                'name' => 'Reactor Plating',
                'description' => 'Reinforces reactor plating technology.',
                'base_cost' => ['supply' => 1500, 'gas' => 1500],
                'base_production' => [],
                'base_energy_production' => 20,
                'base_time' => 450,
                'energy_consumption' => 20,
                'required_tech' => 'combustion_engine',
            ],
            'monitor_stasis_chamber' => [
                'name' => 'Monitor Stasis Chamber',
                'description' => 'Unique Covenant structure that powers nearby buildings.',
                'base_cost' => ['supply' => 0, 'gas' => 3000],
                'base_production' => [],
                'base_energy_production' => 50,
                'base_time' => 1200,
                'energy_consumption' => 50,
                'required_tech' => 'crystalline_tech',
            ],
        ],
    ];

    public static function get(string $key, ?string $faction = null): ?array
    {
        if ($faction) {
            return self::$buildings[$faction][$key] ?? null;
        }
        foreach (self::$buildings as $factionBuildings) {
            if (isset($factionBuildings[$key])) {
                return $factionBuildings[$key];
            }
        }
        return null;
    }

    public static function getByFaction(string $faction): array
    {
        return self::$buildings[$faction] ?? [];
    }

    public static function getAll(): array
    {
        return self::$buildings;
    }

    public static function getKeys(?string $faction = null): array
    {
        if ($faction) {
            return array_keys(self::$buildings[$faction] ?? []);
        }
        $keys = [];
        foreach (self::$buildings as $factionBuildings) {
            $keys = array_merge($keys, array_keys($factionBuildings));
        }
        return array_unique($keys);
    }

    public static function isResourceBuilding(string $key): bool
    {
        return in_array($key, ['supply_mine', 'gas_extractor', 'gas_condenser']);
    }

    public static function isStorageBuilding(string $key): bool
    {
        return in_array($key, ['supply_storage', 'gas_storage']);
    }

    public static function isEnergyBuilding(string $key): bool
    {
        return in_array($key, ['fusion_reactor', 'heavy_fusion', 'solar_plant', 'power_generator', 'plasma_reactor']);
    }
}
