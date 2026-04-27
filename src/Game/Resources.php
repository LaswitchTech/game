<?php

namespace Game;

class Resources
{
    const SUPPLY = 'supply';
    const POWER = 'power';
    const GAS = 'gas';
    const GOLD = 'gold';

    const TYPES = [
        self::SUPPLY,
        self::POWER,
        self::GAS,
    ];

    const STORAGE_BUILDINGS = [
        self::SUPPLY => 'supply_storage',
        self::GAS => 'gas_storage',
    ];
}
