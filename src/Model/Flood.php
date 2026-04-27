<?php

namespace Model;

use Database\Connection;
use Game\FloodTypes;

class Flood
{
    public static function getState(): array
    {
        $db = Connection::getInstance();
        $row = $db->query('SELECT * FROM flood_state LIMIT 1')->fetch();

        if (!$row) {
            return [
                'phase' => FloodTypes::PHASE_DORMANT,
                'tick' => 0,
                'infected_planets' => [],
                'wave_count' => 0,
                'last_wave_tick' => 0,
                'emerge_tick' => 0,
            ];
        }

        return [
            'phase' => $row['phase'],
            'tick' => (int)$row['tick'],
            'infected_planets' => $row['infected_planets'] ? json_decode($row['infected_planets'], true) : [],
            'wave_count' => (int)$row['wave_count'],
            'last_wave_tick' => (int)$row['last_wave_tick'],
            'emerge_tick' => (int)$row['emerge_tick'],
        ];
    }

    public static function updatePhase(string $phase): void
    {
        $db = Connection::getInstance();
        $db->prepare('UPDATE flood_state SET phase = ?, last_wave_tick = 0, wave_count = 0')
            ->execute([$phase]);
    }

    public static function setTick(int $tick): void
    {
        $db = Connection::getInstance();
        $db->exec("UPDATE flood_state SET tick = {$tick}");
    }

    public static function addInfectedPlanet(int $planetId, int $systemId, int $planetPos): void
    {
        $state = self::getState();
        $infected = $state['infected_planets'];

        $infected[] = [
            'planet_id' => $planetId,
            'system_id' => $systemId,
            'planet_pos' => $planetPos,
            'infected_at' => date('Y-m-d H:i:s'),
        ];

        $db = Connection::getInstance();
        $db->prepare('UPDATE flood_state SET infected_planets = ?')
            ->execute([json_encode($infected)]);
    }

    public static function removeInfectedPlanet(int $planetId): void
    {
        $state = self::getState();
        $infected = array_values(array_filter(
            $state['infected_planets'],
            fn($p) => $p['planet_id'] !== $planetId
        ));

        $db = Connection::getInstance();
        $db->prepare('UPDATE flood_state SET infected_planets = ?')
            ->execute([json_encode($infected)]);
    }

    public static function initialize(): void
    {
        $db = Connection::getInstance();
        $db->exec("
            INSERT OR IGNORE INTO flood_state (id, phase, tick, infected_planets, wave_count, last_wave_tick, emerge_tick)
            VALUES (1, '" . FloodTypes::PHASE_DORMANT . "', 0, '[]', 0, 0, 0)
        ");
    }

    public static function isEmerged(): bool
    {
        $state = self::getState();
        return $state['phase'] !== FloodTypes::PHASE_DORMANT;
    }

    public static function isThreatening(): bool
    {
        $state = self::getState();
        return in_array($state['phase'], [FloodTypes::PHASE_SPREAD, FloodTypes::PHASE_WAVES, FloodTypes::PHASE_SUPPRESSION]);
    }

    public static function isSuppressed(): bool
    {
        $state = self::getState();
        return $state['phase'] === FloodTypes::PHASE_SUPPRESSION;
    }

    public static function isEnded(): bool
    {
        $state = self::getState();
        return $state['phase'] === FloodTypes::PHASE_ENDGAME;
    }

    public static function getActiveWave(): ?array
    {
        $state = self::getState();
        if ($state['phase'] !== FloodTypes::PHASE_WAVES) {
            return null;
        }
        return [
            'wave' => $state['wave_count'],
            'target_planets' => $state['infected_planets'],
        ];
    }
}
