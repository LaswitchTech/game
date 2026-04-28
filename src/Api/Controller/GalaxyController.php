<?php

namespace Api\Controller;

use Service\AuthService;
use Model\Planet;

class GalaxyController
{
    public function getMap(array $params): array
    {
        $user = AuthService::requireAuth();
        if ($user === null) return ['error' => 'Authentication required', 'code' => 401];
        $faction = $user->getFaction();

        $db = \Database\Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM planets ORDER BY system, position');
        $stmt->execute();
        $rows = $stmt->fetchAll();

        // Group by (system, position)
        $grid = [];
        foreach ($rows as $row) {
            $key = $row['system'] . ',' . $row['position'];
            if (!isset($grid[$key])) {
                $grid[$key] = ['system' => (int)$row['system'], 'position' => (int)$row['position'], 'planets' => []];
            }
            $grid[$key]['planets'][] = [
                'id' => (int)$row['id'],
                'name' => $row['name'] ?? 'Unnamed',
                'faction' => $row['faction'] ?? 'unsc',
                'primary' => (int)$row['primary_planet'] === 1,
            ];
        }

        // Mark player's planets
        foreach ($grid as &$group) {
            foreach ($group['planets'] as &$p) {
                $p['is_player'] = ($p['faction'] === $faction && $p['id'] === $user->getId());
            }
        }

        // Compute grid bounds
        $minSys = 1; $maxSys = 1; $minPos = 1; $maxPos = 1;
        foreach ($grid as $group) {
            if ($group['system'] < $minSys) $minSys = $group['system'];
            if ($group['system'] > $maxSys) $maxSys = $group['system'];
            if ($group['position'] < $minPos) $minPos = $group['position'];
            if ($group['position'] > $maxPos) $maxPos = $group['position'];
        }

        // Find adjacent pairs for path generation
        $paths = [];
        $keys = array_keys($grid);
        $keySet = array_flip($keys);
        foreach ($keys as $key) {
            [$sys, $pos] = explode(',', $key);
            $sys = (int)$sys; $pos = (int)$pos;
            // Right neighbor
            $rKey = ($sys+1) . ',' . $pos;
            if (isset($keySet[$rKey])) {
                $paths[] = [$key, $rKey];
            }
            // Bottom neighbor
            $bKey = $sys . ',' . ($pos+1);
            if (isset($keySet[$bKey])) {
                $paths[] = [$key, $bKey];
            }
        }

        return [
            'grid' => array_values($grid),
            'paths' => $paths,
            'bounds' => ['min_sys' => $minSys, 'max_sys' => $maxSys, 'min_pos' => $minPos, 'max_pos' => $maxPos],
        ];
    }
}
