<?php

namespace Model;

use Database\Connection;

class Building
{
    public static function getLevel(int $planetId, string $key): int
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT level FROM buildings WHERE planet_id = ? AND building_key = ?');
        $stmt->execute([$planetId, $key]);
        $row = $stmt->fetch();
        return $row ? (int)$row['level'] : 0;
    }

    public static function updateLevel(int $planetId, string $key, int $level, ?string $completedAt = null, ?string $faction = null): void
    {
        $db = Connection::getInstance();
        $f = $faction ?: (Planet::findById($planetId)?->getFaction() ?? 'unsc');
        $db->prepare('INSERT INTO buildings (planet_id, building_key, level, completed_at, faction) VALUES (?, ?, ?, ?, ?) ON CONFLICT(planet_id, building_key, faction) DO UPDATE SET level = ?, completed_at = ?, faction = ?')
            ->execute([$planetId, $key, $level, $completedAt, $f, $level, $completedAt, $f]);
    }

    public static function startConstruction(int $planetId, string $key, int $targetLevel, string $faction): int
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('INSERT INTO construction_queue (planet_id, building_key, target_level, faction) VALUES (?, ?, ?, ?)');
        $stmt->execute([$planetId, $key, $targetLevel, $faction]);
        return (int)$db->lastInsertId();
    }

    public static function getInProgress(): array
    {
        $db = Connection::getInstance();
        $stmt = $db->query('SELECT * FROM construction_queue WHERE completed_at IS NULL');
        return $stmt->fetchAll();
    }

    public static function complete(int $queueId): void
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('UPDATE construction_queue SET completed_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$queueId]);
    }

    public static function getActiveQueues(int $planetId): array
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM construction_queue WHERE planet_id = ? AND completed_at IS NULL');
        $stmt->execute([$planetId]);
        return $stmt->fetchAll();
    }
}
