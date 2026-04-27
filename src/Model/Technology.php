<?php

namespace Model;

use Database\Connection;

class Technology
{
    public static function getLevel(int $planetId, string $key): int
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT level FROM technologies WHERE planet_id = ? AND technology_key = ?');
        $stmt->execute([$planetId, $key]);
        $row = $stmt->fetch();
        return $row ? (int)$row['level'] : 0;
    }

    public static function updateLevel(int $planetId, string $key, int $level, ?string $completedAt = null, ?string $faction = null): void
    {
        $db = Connection::getInstance();
        $f = $faction ?: (Planet::findById($planetId)?->getFaction() ?? 'unsc');
        $db->prepare('INSERT INTO technologies (planet_id, technology_key, level, completed_at, faction) VALUES (?, ?, ?, ?, ?) ON CONFLICT(planet_id, technology_key, faction) DO UPDATE SET level = ?, completed_at = ?, faction = ?')
            ->execute([$planetId, $key, $level, $completedAt, $f, $level, $completedAt, $f]);
    }

    public static function startResearch(int $planetId, string $key, string $faction): int
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('INSERT INTO research_queue (planet_id, technology_key, faction) VALUES (?, ?, ?)');
        $stmt->execute([$planetId, $key, $faction]);
        return (int)$db->lastInsertId();
    }

    public static function getInProgress(int $planetId): array
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('SELECT * FROM research_queue WHERE planet_id = ? AND completed_at IS NULL');
        $stmt->execute([$planetId]);
        return $stmt->fetchAll();
    }

    public static function complete(int $queueId): void
    {
        $db = Connection::getInstance();
        $stmt = $db->prepare('UPDATE research_queue SET completed_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$queueId]);
    }

    public static function hasTech(int $planetId, string $key): bool
    {
        return self::getLevel($planetId, $key) >= 1;
    }
}
