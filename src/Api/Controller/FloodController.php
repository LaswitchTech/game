<?php

namespace Api\Controller;

use Service\AuthService;
use Model\Flood;
use Game\FloodTypes;

class FloodController
{
    public function getState(array $params): array
    {
        AuthService::requireAuth();
        $state = Flood::getState();

        return [
            'phase' => $state['phase'],
            'phase_label' => FloodTypes::PHASE_LABELS[$state['phase']] ?? '',
            'tick' => $state['tick'],
            'emerged' => Flood::isEmerged(),
            'threatening' => Flood::isThreatening(),
            'suppressed' => Flood::isSuppressed(),
            'ended' => Flood::isEnded(),
            'infected_planets' => $state['infected_planets'],
            'wave_count' => $state['wave_count'],
            'wave_interval_remaining' => $state['phase'] === FloodTypes::PHASE_WAVES
                ? max(0, FloodTypes::PHASE_WAVE_INTERVAL[FloodTypes::PHASE_WAVES] - ($state['tick'] - $state['last_wave_tick']))
                : 0,
            'controlled_rings' => self::countControlledRings(),
        ];
    }

    public function getEvents(array $params): array
    {
        AuthService::requireAuth();
        $db = \Database\Connection::getInstance();
        $limit = $params['limit'] ?? 50;
        $since = $params['since'] ?? 0;

        $stmt = $db->prepare(
            "SELECT * FROM flood_events WHERE id > ? ORDER BY id DESC LIMIT ?"
        );
        $stmt->execute([$since, $limit]);
        return $stmt->fetchAll();
    }

    private static function countControlledRings(): int
    {
        $db = \Database\Connection::getInstance();
        $stmt = $db->query("SELECT COUNT(*) as count FROM halo_rings WHERE controlled_by IS NOT NULL");
        $row = $stmt->fetch();
        return (int)($row['count'] ?? 0);
    }
}
