<?php

namespace Service;

use Model\Planet;
use Model\Ship;
use Model\Technology;
use Model\Mission;

class FleetService
{
    public static function getActiveForUser(int $userId, string $faction): array
    {
        $planet = Planet::findByUserId($userId, $faction);
        if ($planet === null) return [];

        $db = \Database\Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM fleets WHERE planet_id = ? AND status != ?');
        $stmt->execute([$planet->getId(), 'completed']);
        return $stmt->fetchAll();
    }

    public static function getFleetCombatValue(array $ships, string $faction): int
    {
        $weaponsLevel = Technology::getLevel(0, 'weapons_tech'); // This should come from the planet
        $shieldLevel = Technology::getLevel(0, 'shielding_tech');
        $armorLevel = Technology::getLevel(0, 'armor_tech');

        $total = 0;
        foreach ($ships as $key => $count) {
            $type = \Game\TechnologyTypes::getShip($key, $faction);
            if ($type === null) continue;
            $p = $type['properties'];
            $wMult = 1 + ($weaponsLevel ?? 0) * 0.25;
            $sMult = 1 + ($shieldLevel ?? 0) * 0.25;
            $aMult = 1 + ($armorLevel ?? 0) * 0.25;
            $total += (($p['attack'] * $wMult) + ($p['shield'] * $sMult) + ($p['armor'] * $aMult)) * $count;
        }
        return (int)$total;
    }

    public static function getMissionTypes(): array
    {
        return [
            'trade' => 'Trade Mission',
            'attack' => 'Attack Mission',
            'colonize' => 'Colonize Mission',
        ];
    }
}
