<?php

namespace Game;

class BuildingTypes
{
    private static array $buildings = [
        'unsc' => [
            'metal_refinery' => [
                'name' => 'Metal Refinery',
                'description' => 'Refines metal ore from the planet crust.',
                'base_cost' => ['metal' => 70, 'crystal' => 20, 'deuterium' => 0],
                'base_production' => ['metal' => 35],
                'base_time' => 20,
                'energy_consumption' => 10,
                'required_tech' => null,
            ],
            'crystal_extractor' => [
                'name' => 'Crystal Extractor',
                'description' => 'Extracts crystal deposits.',
                'base_cost' => ['metal' => 55, 'crystal' => 30, 'deuterium' => 0],
                'base_production' => ['crystal' => 25],
                'base_time' => 15,
                'energy_consumption' => 10,
                'required_tech' => null,
            ],
            'deuterium_condenser' => [
                'name' => 'Deuterium Condenser',
                'description' => 'Condenses deuterium from heavy water.',
                'base_cost' => ['metal' => 250, 'crystal' => 80, 'deuterium' => 0],
                'base_production' => ['deuterium' => 10],
                'base_time' => 60,
                'energy_consumption' => 25,
                'required_tech' => 'energy_tech',
            ],
            'fusion_reactor' => [
                'name' => 'Fusion Reactor',
                'description' => 'Generates energy from fusion reactions.',
                'base_cost' => ['metal' => 80, 'crystal' => 35, 'deuterium' => 0],
                'base_production' => [],
                'base_energy_production' => 25,
                'base_time' => 30,
                'energy_consumption' => 0,
                'required_tech' => null,
            ],
            'heavy_fusion' => [
                'name' => 'Heavy Fusion Reactor',
                'description' => 'Generates high-yield energy from fusion.',
                'base_cost' => ['metal' => 250, 'crystal' => 80, 'deuterium' => 200],
                'base_production' => [],
                'base_energy_production' => 125,
                'base_time' => 200,
                'energy_consumption' => 0,
                'required_tech' => 'industrial_mastery',
            ],
            'metal_vault' => [
                'name' => 'Metal Vault',
                'description' => 'Stores refined metal.',
                'base_cost' => ['metal' => 120, 'crystal' => 70, 'deuterium' => 0],
                'base_production' => [],
                'storage_bonus' => ['metal' => 600],
                'base_time' => 10,
                'energy_consumption' => 0,
                'required_tech' => null,
            ],
            'crystal_vault' => [
                'name' => 'Crystal Vault',
                'description' => 'Stores refined crystal.',
                'base_cost' => ['metal' => 120, 'crystal' => 70, 'deuterium' => 0],
                'base_production' => [],
                'storage_bonus' => ['crystal' => 600],
                'base_time' => 10,
                'energy_consumption' => 0,
                'required_tech' => null,
            ],
            'deuterium_tank' => [
                'name' => 'Deuterium Tank',
                'description' => 'Stores processed deuterium.',
                'base_cost' => ['metal' => 120, 'crystal' => 70, 'deuterium' => 70],
                'base_production' => [],
                'storage_bonus' => ['deuterium' => 600],
                'base_time' => 10,
                'energy_consumption' => 0,
                'required_tech' => 'energy_tech',
            ],
            'research_facility' => [
                'name' => 'Research Facility',
                'description' => 'Enables scientific research.',
                'base_cost' => ['metal' => 250, 'crystal' => 450, 'deuterium' => 250],
                'base_production' => [],
                'base_energy_production' => 50,
                'base_time' => 400,
                'energy_consumption' => 50,
                'required_tech' => null,
            ],
            'military_dock' => [
                'name' => 'Military Dock',
                'description' => 'Enables UNSC fleet construction.',
                'base_cost' => ['metal' => 2500, 'crystal' => 1200, 'deuterium' => 8000],
                'base_production' => [],
                'base_energy_production' => 120,
                'base_time' => 1200,
                'energy_consumption' => 120,
                'required_tech' => 'robotics_tech',
            ],
            'armoury' => [
                'name' => 'Armoury',
                'description' => 'Enables advanced UNSC ship construction.',
                'base_cost' => ['metal' => 50000, 'crystal' => 50000, 'deuterium' => 18000],
                'base_production' => [],
                'base_energy_production' => 220,
                'base_time' => 28800,
                'energy_consumption' => 220,
                'required_tech' => 'ai_computer',
            ],
            'ai_core' => [
                'name' => 'AI Core',
                'description' => 'Advanced AI command system.',
                'base_cost' => ['metal' => 200000, 'crystal' => 45000, 'deuterium' => 22000],
                'base_production' => [],
                'base_energy_production' => 350,
                'base_time' => 72000,
                'energy_consumption' => 350,
                'required_tech' => 'ai_tech',
            ],
            'orbital_cannon' => [
                'name' => 'Orbital Cannon',
                'description' => 'Enhances orbital defense capability.',
                'base_cost' => ['metal' => 2500, 'crystal' => 500, 'deuterium' => 700],
                'base_production' => [],
                'base_energy_production' => 35,
                'base_time' => 600,
                'energy_consumption' => 35,
                'required_tech' => 'combustion_engine',
            ],
            'shield_gen' => [
                'name' => 'Shield Generator',
                'description' => 'Enhances planetary defense shields.',
                'base_cost' => ['metal' => 1800, 'crystal' => 600, 'deuterium' => 1800],
                'base_production' => [],
                'base_energy_production' => 30,
                'base_time' => 450,
                'energy_consumption' => 30,
                'required_tech' => 'energy_tech',
            ],
            'plating_tech' => [
                'name' => 'Plating Tech',
                'description' => 'Reinforces hull plating technology.',
                'base_cost' => ['metal' => 1800, 'crystal' => 1800, 'deuterium' => 0],
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
                'base_cost' => ['metal' => 60, 'crystal' => 15, 'deuterium' => 0],
                'base_production' => ['metal' => 30],
                'base_time' => 20,
                'energy_consumption' => 10,
                'required_tech' => null,
            ],
            'crystal_extraction_array' => [
                'name' => 'Crystal Extraction Array',
                'description' => 'Extracts crystal deposits.',
                'base_cost' => ['metal' => 48, 'crystal' => 24, 'deuterium' => 0],
                'base_production' => ['crystal' => 20],
                'base_time' => 15,
                'energy_consumption' => 10,
                'required_tech' => null,
            ],
            'deuterium_converter' => [
                'name' => 'Deuterium Converter',
                'description' => 'Converts heavy water to deuterium.',
                'base_cost' => ['metal' => 225, 'crystal' => 75, 'deuterium' => 0],
                'base_production' => ['deuterium' => 10],
                'base_time' => 60,
                'energy_consumption' => 20,
                'required_tech' => 'energy_tech',
            ],
            'power_generator' => [
                'name' => 'Power Generator',
                'description' => 'Generates energy from plasma.',
                'base_cost' => ['metal' => 75, 'crystal' => 30, 'deuterium' => 0],
                'base_production' => [],
                'base_energy_production' => 20,
                'base_time' => 30,
                'energy_consumption' => 0,
                'required_tech' => null,
            ],
            'plasma_reactor' => [
                'name' => 'Plasma Reactor',
                'description' => 'Generates massive energy from plasma.',
                'base_cost' => ['metal' => 225, 'crystal' => 75, 'deuterium' => 150],
                'base_production' => [],
                'base_energy_production' => 100,
                'base_time' => 200,
                'energy_consumption' => 0,
                'required_tech' => 'psionic_enhancement',
            ],
            'energy_cell' => [
                'name' => 'Energy Cell',
                'description' => 'Stores harvested energy cells.',
                'base_cost' => ['metal' => 100, 'crystal' => 60, 'deuterium' => 0],
                'base_production' => [],
                'storage_bonus' => ['metal' => 500],
                'base_time' => 10,
                'energy_consumption' => 0,
                'required_tech' => null,
            ],
            'power_cell' => [
                'name' => 'Power Cell',
                'description' => 'Stores harvested power cells.',
                'base_cost' => ['metal' => 100, 'crystal' => 60, 'deuterium' => 0],
                'base_production' => [],
                'storage_bonus' => ['crystal' => 500],
                'base_time' => 10,
                'energy_consumption' => 0,
                'required_tech' => null,
            ],
            'deuterium_capacitor' => [
                'name' => 'Deuterium Capacitor',
                'description' => 'Stores deuterium capacitors.',
                'base_cost' => ['metal' => 100, 'crystal' => 60, 'deuterium' => 60],
                'base_production' => [],
                'storage_bonus' => ['deuterium' => 500],
                'base_time' => 10,
                'energy_consumption' => 0,
                'required_tech' => 'energy_tech',
            ],
            'forge' => [
                'name' => 'Forge',
                'description' => 'Enables Covenant fleet construction.',
                'base_cost' => ['metal' => 2000, 'crystal' => 1000, 'deuterium' => 6000],
                'base_production' => [],
                'base_energy_production' => 100,
                'base_time' => 1200,
                'energy_consumption' => 100,
                'required_tech' => 'robotics_tech',
            ],
            'psionic_temple' => [
                'name' => 'Psionic Temple',
                'description' => 'Enables advanced Covenant construction.',
                'base_cost' => ['metal' => 48000, 'crystal' => 48000, 'deuterium' => 16000],
                'base_production' => [],
                'base_energy_production' => 200,
                'base_time' => 28800,
                'energy_consumption' => 200,
                'required_tech' => 'gifted_tech',
            ],
            'sangheili_engine' => [
                'name' => 'Sangheili Engine',
                'description' => 'Advanced Sangheili engineering facility.',
                'base_cost' => ['metal' => 200000, 'crystal' => 40000, 'deuterium' => 20000],
                'base_production' => [],
                'base_energy_production' => 300,
                'base_time' => 72000,
                'energy_consumption' => 300,
                'required_tech' => 'ai_tech',
            ],
            'disruptor_cannon' => [
                'name' => 'Disruptor Cannon',
                'description' => 'Enhances orbital defense capability.',
                'base_cost' => ['metal' => 2000, 'crystal' => 400, 'deuterium' => 600],
                'base_production' => [],
                'base_energy_production' => 30,
                'base_time' => 600,
                'energy_consumption' => 30,
                'required_tech' => 'combustion_engine',
            ],
            'shadow_shield' => [
                'name' => 'Shadow Shield',
                'description' => 'Enhances defensive shields.',
                'base_cost' => ['metal' => 1500, 'crystal' => 500, 'deuterium' => 1500],
                'base_production' => [],
                'base_energy_production' => 25,
                'base_time' => 450,
                'energy_consumption' => 25,
                'required_tech' => 'energy_tech',
            ],
            'reactor_plating' => [
                'name' => 'Reactor Plating',
                'description' => 'Reinforces reactor plating technology.',
                'base_cost' => ['metal' => 1500, 'crystal' => 1500, 'deuterium' => 0],
                'base_production' => [],
                'base_energy_production' => 20,
                'base_time' => 450,
                'energy_consumption' => 20,
                'required_tech' => 'combustion_engine',
            ],
            'monitor_stasis_chamber' => [
                'name' => 'Monitor Stasis Chamber',
                'description' => 'Unique Covenant structure that powers nearby buildings.',
                'base_cost' => ['metal' => 0, 'crystal' => 3000, 'deuterium' => 1000],
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
        return in_array($key, ['metal_refinery', 'metal_mine', 'crystal_extractor', 'crystal_mine', 'deuterium_condenser', 'deuterium_synthesizer']);
    }

    public static function isStorageBuilding(string $key): bool
    {
        return in_array($key, ['metal_vault', 'metal_storage', 'crystal_vault', 'crystal_storage', 'deuterium_tank', 'deuterium_storage']);
    }

    public static function isEnergyBuilding(string $key): bool
    {
        return in_array($key, ['fusion_reactor', 'heavy_fusion', 'solar_plant', 'power_generator', 'plasma_reactor']);
    }
}
