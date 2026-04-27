<?php

namespace Database;

use PDO;
use PDOException;

class Connection
{
    private static ?\PDO $instance = null;

    public static function getInstance(): \PDO
    {
        if (self::$instance === null) {
            $dbPath = defined('DB_PATH') ? DB_PATH : __DIR__ . '/../../data/game.sqlite';

            // Ensure data directory exists
            $dir = dirname($dbPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            self::$instance = new PDO(
                "sqlite:{$dbPath}",
                null,
                null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );

            // Enable foreign keys in SQLite
            self::$instance->exec('PRAGMA foreign_keys = ON');
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
