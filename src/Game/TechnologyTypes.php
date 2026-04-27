<?php

namespace Game;

class TechnologyTypes
{
    private static array $technologies = [
        'shared' => [
            'energy_tech' => [
                'name' => 'Energy Tech',
                'description' => 'Basic energy research. Required for many technologies.',
                'base_cost' => ['supply' => 1000, 'gas' => 500],
                'base_time' => 600,
                'prerequisites' => [],
                'effects' => [],
            ],
        ],
        'unsc' => [
            'industrial_mastery' => [
                'name' => 'Industrial Mastery',
                'description' => 'Enhanced industrial processes. Required for advanced UNSC ships.',
                'base_cost' => ['supply' => 1200, 'gas' => 600],
                'base_time' => 600,
                'prerequisites' => ['energy_tech'],
                'effects' => [],
            ],
            'propulsion_tech' => [
                'name' => 'Propulsion Tech',
                'description' => 'Advanced UNSC fleet propulsion. Increases fleet speed.',
                'base_cost' => ['supply' => 6000, 'gas' => 6000],
                'base_time' => 1800,
                'prerequisites' => ['industrial_mastery'],
                'effects' => ['speed_bonus' => 0.5],
            ],
            'electronics_tech' => [
                'name' => 'Electronics Tech',
                'description' => 'Improves UNSC ship computer efficiency.',
                'base_cost' => ['supply' => 10000, 'gas' => 8000],
                'base_time' => 4200,
                'prerequisites' => ['energy_tech'],
                'effects' => ['defense_bonus' => 0.2],
            ],
            'robotics_tech' => [
                'name' => 'Robotics Tech',
                'description' => 'Enables robotic UNSC ship construction.',
                'base_cost' => ['supply' => 180000, 'gas' => 180000],
                'base_time' => 72000,
                'prerequisites' => ['propulsion_tech', 'electronics_tech'],
                'effects' => [],
            ],
            'ai_computer' => [
                'name' => 'AI Computer',
                'description' => 'Advanced UNSC AI fleet control.',
                'base_cost' => ['supply' => 220000, 'gas' => 200000],
                'base_time' => 72000,
                'prerequisites' => ['robotics_tech', 'electronics_tech'],
                'effects' => [],
            ],
            'weapons_tech' => [
                'name' => 'Weapons Tech',
                'description' => 'UNSC weapon enhancement systems.',
                'base_cost' => ['supply' => 8000, 'gas' => 4000],
                'base_time' => 3600,
                'prerequisites' => ['industrial_mastery', 'energy_tech'],
                'effects' => ['damage_bonus' => 0.25],
            ],
            'shield_gen_tech' => [
                'name' => 'Shield Generator Tech',
                'description' => 'UNSC shield generator technology.',
                'base_cost' => ['supply' => 6000, 'gas' => 6000],
                'base_time' => 3000,
                'prerequisites' => ['energy_tech'],
                'effects' => ['shield_bonus' => 0.25],
            ],
            'armor_tech' => [
                'name' => 'Armor Tech',
                'description' => 'UNSC hull armor reinforcement.',
                'base_cost' => ['supply' => 10000, 'gas' => 10000],
                'base_time' => 3600,
                'prerequisites' => ['industrial_mastery'],
                'effects' => ['armor_bonus' => 0.25],
            ],
            'research_bonus' => [
                'name' => 'Research Bonus',
                'description' => 'Accelerates UNSC research speed.',
                'base_cost' => ['supply' => 20000, 'gas' => 60000],
                'base_time' => 7200,
                'prerequisites' => ['energy_tech'],
                'effects' => ['research_speed' => 0.1],
            ],
            'observation_tech' => [
                'name' => 'Observation Tech',
                'description' => 'UNSC deep-space scanning arrays.',
                'base_cost' => ['supply' => 600000, 'gas' => 400000],
                'base_time' => 14400,
                'prerequisites' => ['electronics_tech', 'shield_gen_tech'],
                'effects' => [],
            ],
            'hyperdrive_tech' => [
                'name' => 'Hyperdrive Tech',
                'description' => 'UNSC slipspace drive technology.',
                'base_cost' => ['supply' => 300000, 'gas' => 300000],
                'base_time' => 14400,
                'prerequisites' => ['propulsion_tech'],
                'effects' => ['hyperdrive_speed' => 2.0],
            ],
        ],
        'covenant' => [
            'psionic_enhancement' => [
                'name' => 'Psionic Enhancement',
                'description' => 'Enhanced Covenant psionic research. Required for advanced Covenant ships.',
                'base_cost' => ['supply' => 1200, 'gas' => 600],
                'base_time' => 600,
                'prerequisites' => ['energy_tech'],
                'effects' => [],
            ],
            'crystalline_tech' => [
                'name' => 'Crystalline Tech',
                'description' => 'Covenant crystalline technology. Increases fleet speed.',
                'base_cost' => ['supply' => 6000, 'gas' => 6000],
                'base_time' => 1800,
                'prerequisites' => ['psionic_enhancement'],
                'effects' => ['speed_bonus' => 0.5],
            ],
            'shadow_mastery' => [
                'name' => 'Shadow Mastery',
                'description' => 'Covenant shadow technology mastery.',
                'base_cost' => ['supply' => 16000, 'gas' => 12000],
                'base_time' => 4200,
                'prerequisites' => ['psionic_enhancement'],
                'effects' => ['defense_bonus' => 0.2],
            ],
            'gifted_tech' => [
                'name' => 'Gifted Tech',
                'description' => 'Enables gifted Covenant construction.',
                'base_cost' => ['supply' => 300000, 'gas' => 300000],
                'base_time' => 72000,
                'prerequisites' => ['crystalline_tech', 'shadow_mastery'],
                'effects' => [],
            ],
            'ai_tech' => [
                'name' => 'AI Tech',
                'description' => 'Covenant advanced AI control systems.',
                'base_cost' => ['supply' => 240000, 'gas' => 240000],
                'base_time' => 72000,
                'prerequisites' => ['gifted_tech', 'shadow_mastery'],
                'effects' => [],
            ],
            'weapons_gravity' => [
                'name' => 'Gravity Weapons',
                'description' => 'Covenant gravity weapon enhancement.',
                'base_cost' => ['supply' => 12000, 'gas' => 8000],
                'base_time' => 3600,
                'prerequisites' => ['psionic_enhancement', 'energy_tech'],
                'effects' => ['damage_bonus' => 0.25],
            ],
            'shield_energy' => [
                'name' => 'Energy Shield Tech',
                'description' => 'Covenant energy shield technology.',
                'base_cost' => ['supply' => 10000, 'gas' => 12000],
                'base_time' => 3000,
                'prerequisites' => ['energy_tech'],
                'effects' => ['shield_bonus' => 0.25],
            ],
            'armor_plating' => [
                'name' => 'Armor Plating',
                'description' => 'Covenant hull plating reinforcement.',
                'base_cost' => ['supply' => 20000, 'gas' => 0],
                'base_time' => 3600,
                'prerequisites' => ['psionic_enhancement'],
                'effects' => ['armor_bonus' => 0.25],
            ],
            'research_psionic' => [
                'name' => 'Research Psionic',
                'description' => 'Accelerates Covenant research through psionic bonds.',
                'base_cost' => ['supply' => 30000, 'gas' => 90000],
                'base_time' => 7200,
                'prerequisites' => ['energy_tech'],
                'effects' => ['research_speed' => 0.1],
            ],
            'observation_sight' => [
                'name' => 'Observation Sight',
                'description' => 'Covenant prophetic sight arrays.',
                'base_cost' => ['supply' => 600000, 'gas' => 500000],
                'base_time' => 14400,
                'prerequisites' => ['shadow_mastery', 'shield_energy'],
                'effects' => [],
            ],
            'gift_prophets' => [
                'name' => 'Gift of the Prophets',
                'description' => 'The ultimate Covenant advancement.',
                'base_cost' => ['supply' => 300000, 'gas' => 300000],
                'base_time' => 14400,
                'prerequisites' => ['crystalline_tech'],
                'effects' => ['hyperdrive_speed' => 2.0],
            ],
        ],
    ];

    private static array $ships = [
        'unsc' => [
            'rocket_soldier' => [
                'name' => 'Rocket Soldier',
                'description' => 'Cheapest UNSC military ship.',
                'base_cost' => ['supply' => 200, 'gas' => 0],
                'build_time' => 30,
                'required_tech' => 'industrial_mastery',
                'properties' => ['capacity' => 5, 'speed' => 10000, 'attack' => 10, 'shield' => 0, 'armor' => 0],
            ],
            'hornet' => [
                'name' => 'Hornet Fighter',
                'description' => 'Fast UNSC fighter.',
                'base_cost' => ['supply' => 4000, 'gas' => 0],
                'build_time' => 300,
                'required_tech' => 'propulsion_tech',
                'properties' => ['capacity' => 50, 'speed' => 50000, 'attack' => 10, 'shield' => 10, 'armor' => 5],
            ],
            'falcon' => [
                'name' => 'Falcon Gunship',
                'description' => 'UNSC troop transport gunship.',
                'base_cost' => ['supply' => 12000, 'gas' => 8000],
                'build_time' => 600,
                'required_tech' => 'industrial_mastery',
                'properties' => ['capacity' => 8000, 'speed' => 25000, 'attack' => 0, 'shield' => 50, 'armor' => 30],
            ],
            'small_transporter' => [
                'name' => 'Small Transporter',
                'description' => 'UNSC basic cargo transport.',
                'base_cost' => ['supply' => 6000, 'gas' => 6000],
                'build_time' => 300,
                'required_tech' => 'industrial_mastery',
                'properties' => ['capacity' => 2000, 'speed' => 25000, 'attack' => 0, 'shield' => 20, 'armor' => 10],
            ],
            'pelican' => [
                'name' => 'Pelican Dropship',
                'description' => 'UNSC multi-role dropship.',
                'base_cost' => ['supply' => 11000, 'gas' => 8500],
                'build_time' => 900,
                'required_tech' => 'electronics_tech',
                'properties' => ['capacity' => 250, 'speed' => 37500, 'attack' => 80, 'shield' => 40, 'armor' => 30],
            ],
            'corvette' => [
                'name' => 'Corvette',
                'description' => 'UNSC basic military frigate.',
                'base_cost' => ['supply' => 4000, 'gas' => 0],
                'build_time' => 300,
                'required_tech' => 'industrial_mastery',
                'properties' => ['capacity' => 50, 'speed' => 10000, 'attack' => 50, 'shield' => 20, 'armor' => 10],
            ],
            'light_corvette' => [
                'name' => 'Light Corvette',
                'description' => 'UNSC advanced corvette.',
                'base_cost' => ['supply' => 100000, 'gas' => 100000],
                'build_time' => 1800,
                'required_tech' => 'weapons_tech',
                'properties' => ['capacity' => 200, 'speed' => 7500, 'attack' => 200, 'shield' => 80, 'armor' => 40],
            ],
            'marathon_class' => [
                'name' => 'Marathon-class Destroyer',
                'description' => 'UNSC heavy destroyer.',
                'base_cost' => ['supply' => 42000, 'gas' => 42000],
                'build_time' => 3600,
                'required_tech' => 'shield_gen_tech',
                'properties' => ['capacity' => 250, 'speed' => 12500, 'attack' => 250, 'shield' => 200, 'armor' => 300],
            ],
            'osiris_class' => [
                'name' => 'Osiris-class Destroyer',
                'description' => 'UNSC assault destroyer.',
                'base_cost' => ['supply' => 95000, 'gas' => 60000],
                'build_time' => 3600,
                'required_tech' => 'shield_gen_tech',
                'properties' => ['capacity' => 500, 'speed' => 12500, 'attack' => 150, 'shield' => 150, 'armor' => 200],
            ],
            'arrowhead' => [
                'name' => 'Arrowhead Station',
                'description' => 'UNSC flagship orbital station.',
                'base_cost' => ['supply' => 750000, 'gas' => 750000],
                'build_time' => 36000,
                'required_tech' => 'ai_computer',
                'properties' => ['capacity' => 1000, 'speed' => 5000, 'attack' => 5000, 'shield' => 5000, 'armor' => 5000],
            ],
            'prowler' => [
                'name' => 'Prowler',
                'description' => 'UNSC stealth frigate.',
                'base_cost' => ['supply' => 9000, 'gas' => 12000],
                'build_time' => 600,
                'required_tech' => 'hyperdrive_tech',
                'properties' => ['capacity' => 100, 'speed' => 100000, 'attack' => 20, 'shield' => 20, 'armor' => 10],
            ],
            'longsword' => [
                'name' => 'Longsword Fighter',
                'description' => 'UNSC heavy interceptor.',
                'base_cost' => ['supply' => 120000, 'gas' => 120000],
                'build_time' => 14400,
                'required_tech' => 'hyperdrive_tech',
                'properties' => ['capacity' => 500, 'speed' => 25000, 'attack' => 1000, 'shield' => 1000, 'armor' => 1000],
            ],
            'pillar_of_autumn' => [
                'name' => 'PILLAR OF AUTUMN',
                'description' => 'UNSC supercarrier. The ultimate warship.',
                'base_cost' => ['supply' => 1100000, 'gas' => 1100000],
                'build_time' => 18000,
                'required_tech' => 'ai_computer',
                'properties' => ['capacity' => 800, 'speed' => 8000, 'attack' => 3000, 'shield' => 3000, 'armor' => 3500],
            ],
            'halcyon_cruiser' => [
                'name' => 'Halcyon Cruiser',
                'description' => 'UNSC heavy cruiser.',
                'base_cost' => ['supply' => 55000, 'gas' => 60000],
                'build_time' => 3000,
                'required_tech' => 'shield_gen_tech',
                'properties' => ['capacity' => 400, 'speed' => 15000, 'attack' => 200, 'shield' => 250, 'armor' => 300],
            ],
            'heavy_marine' => [
                'name' => 'Heavy Marine',
                'description' => 'Heavily armed UNSC marine transport.',
                'base_cost' => ['supply' => 16000, 'gas' => 0],
                'build_time' => 450,
                'required_tech' => 'weapons_tech',
                'properties' => ['capacity' => 10, 'speed' => 5000, 'attack' => 80, 'shield' => 30, 'armor' => 50],
            ],
        ],
        'covenant' => [
            'rocket_soldier' => [
                'name' => 'Rocket Soldier',
                'description' => 'Cheapest Covenant military ship.',
                'base_cost' => ['supply' => 200, 'gas' => 0],
                'build_time' => 30,
                'required_tech' => 'psionic_enhancement',
                'properties' => ['capacity' => 5, 'speed' => 8000, 'attack' => 15, 'shield' => 5, 'armor' => 10],
            ],
            'ghost' => [
                'name' => 'Ghost',
                'description' => 'Covenant fast troop transport.',
                'base_cost' => ['supply' => 6000, 'gas' => 6000],
                'build_time' => 300,
                'required_tech' => 'crystalline_tech',
                'properties' => ['capacity' => 2000, 'speed' => 15000, 'attack' => 30, 'shield' => 15, 'armor' => 20],
            ],
            'type_43_wraith' => [
                'name' => 'Type-43 Wraith',
                'description' => 'Covenant troop carrier.',
                'base_cost' => ['supply' => 14000, 'gas' => 8000],
                'build_time' => 600,
                'required_tech' => 'psionic_enhancement',
                'properties' => ['capacity' => 8000, 'speed' => 8000, 'attack' => 100, 'shield' => 50, 'armor' => 80],
            ],
            'photon' => [
                'name' => 'Photon',
                'description' => 'Covenant fast fighter.',
                'base_cost' => ['supply' => 10000, 'gas' => 8500],
                'build_time' => 900,
                'required_tech' => 'gifted_tech',
                'properties' => ['capacity' => 250, 'speed' => 45000, 'attack' => 90, 'shield' => 45, 'armor' => 35],
            ],
            'phase_transport' => [
                'name' => 'Phase Transport',
                'description' => 'Covenant stealth transport.',
                'base_cost' => ['supply' => 4000, 'gas' => 4000],
                'build_time' => 300,
                'required_tech' => 'crystalline_tech',
                'properties' => ['capacity' => 50, 'speed' => 40000, 'attack' => 15, 'shield' => 15, 'armor' => 8],
            ],
            'type_26_corvette' => [
                'name' => 'Type-26 Corvette',
                'description' => 'Covenant basic military ship.',
                'base_cost' => ['supply' => 4000, 'gas' => 0],
                'build_time' => 300,
                'required_tech' => 'psionic_enhancement',
                'properties' => ['capacity' => 50, 'speed' => 10000, 'attack' => 60, 'shield' => 30, 'armor' => 15],
            ],
            'harbinger' => [
                'name' => 'Harbinger',
                'description' => 'Covenant advanced fighter.',
                'base_cost' => ['supply' => 100000, 'gas' => 100000],
                'build_time' => 1800,
                'required_tech' => 'weapons_gravity',
                'properties' => ['capacity' => 200, 'speed' => 7500, 'attack' => 220, 'shield' => 90, 'armor' => 50],
            ],
            'type_50_titan' => [
                'name' => 'Type-50 Titan',
                'description' => 'Covenant heavy destroyer.',
                'base_cost' => ['supply' => 42000, 'gas' => 42000],
                'build_time' => 3600,
                'required_tech' => 'shield_energy',
                'properties' => ['capacity' => 250, 'speed' => 12500, 'attack' => 270, 'shield' => 220, 'armor' => 320],
            ],
            'gift_of_empyrean' => [
                'name' => 'Gift of Empyrean',
                'description' => 'Covenant flagship. The ultimate warship.',
                'base_cost' => ['supply' => 750000, 'gas' => 750000],
                'build_time' => 36000,
                'required_tech' => 'gift_prophets',
                'properties' => ['capacity' => 1000, 'speed' => 6000, 'attack' => 5500, 'shield' => 5500, 'armor' => 5500],
            ],
            'type_94_abaddon' => [
                'name' => 'Type-94 Abaddon',
                'description' => 'Covenant assault carrier.',
                'base_cost' => ['supply' => 80000, 'gas' => 60000],
                'build_time' => 3600,
                'required_tech' => 'shield_energy',
                'properties' => ['capacity' => 500, 'speed' => 10000, 'attack' => 180, 'shield' => 180, 'armor' => 250],
            ],
            'scarab' => [
                'name' => 'Scarab',
                'description' => 'Covenant heavy ground support.',
                'base_cost' => ['supply' => 10000, 'gas' => 8000],
                'build_time' => 600,
                'required_tech' => 'gifted_tech',
                'properties' => ['capacity' => 100, 'speed' => 5000, 'attack' => 40, 'shield' => 40, 'armor' => 15],
            ],
            'destroyer' => [
                'name' => 'Destroyer',
                'description' => 'Covenant heavy destroyer.',
                'base_cost' => ['supply' => 120000, 'gas' => 120000],
                'build_time' => 14400,
                'required_tech' => 'gift_prophets',
                'properties' => ['capacity' => 500, 'speed' => 20000, 'attack' => 1100, 'shield' => 1100, 'armor' => 1100],
            ],
            'regalia' => [
                'name' => 'Regalia',
                'description' => 'Covenant heavy cruiser.',
                'base_cost' => ['supply' => 62000, 'gas' => 60000],
                'build_time' => 3000,
                'required_tech' => 'shield_energy',
                'properties' => ['capacity' => 400, 'speed' => 13000, 'attack' => 220, 'shield' => 280, 'armor' => 350],
            ],
            'prowler' => [
                'name' => 'Prowler',
                'description' => 'Covenant stealth vessel.',
                'base_cost' => ['supply' => 12000, 'gas' => 16000],
                'build_time' => 600,
                'required_tech' => 'shadow_mastery',
                'properties' => ['capacity' => 100, 'speed' => 35000, 'attack' => 35, 'shield' => 35, 'armor' => 15],
            ],
            'heavy_shade' => [
                'name' => 'Heavy Shade',
                'description' => 'Heavily armed Covenant ground support.',
                'base_cost' => ['supply' => 18000, 'gas' => 0],
                'build_time' => 450,
                'required_tech' => 'weapons_gravity',
                'properties' => ['capacity' => 10, 'speed' => 6000, 'attack' => 90, 'shield' => 40, 'armor' => 60],
            ],
        ],
    ];

    public static function get(string $key): ?array
    {
        // Check shared first
        if (isset(self::$technologies['shared'][$key])) {
            return self::$technologies['shared'][$key];
        }
        // Then UNSC
        if (isset(self::$technologies['unsc'][$key])) {
            return self::$technologies['unsc'][$key];
        }
        // Then Covenant
        if (isset(self::$technologies['covenant'][$key])) {
            return self::$technologies['covenant'][$key];
        }
        return null;
    }

    public static function getByFaction(string $faction): array
    {
        return self::$technologies[$faction] ?? [];
    }

    public static function getAll(): array
    {
        return self::$technologies;
    }

    public static function getKeys(): array
    {
        $keys = array_keys(self::$technologies['shared'] ?? []);
        foreach (self::$technologies as $factionTechs) {
            $keys = array_merge($keys, array_keys($factionTechs));
        }
        return array_unique($keys);
    }

    public static function getShip(string $key, string $faction): ?array
    {
        return self::$ships[$faction][$key] ?? null;
    }

    public static function getShips(string $faction): array
    {
        return self::$ships[$faction] ?? [];
    }

    public static function getShipKeys(string $faction): array
    {
        return array_keys(self::$ships[$faction] ?? []);
    }

    public static function getSharedTechs(): array
    {
        return self::$technologies['shared'] ?? [];
    }

    public static function getFactionTechs(string $faction): array
    {
        return self::$technologies[$faction] ?? [];
    }
}
