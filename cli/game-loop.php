<?php
/**
 * CLI Game Loop
 *
 * Run this script periodically (e.g., every 60 seconds via cron) to advance
 * the game state for all users.
 *
 * Usage: php cli/game-loop.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Start session (needed for some services)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Run migrations if needed
$dbPath = defined('DB_PATH') ? DB_PATH : __DIR__ . '/../data/game.sqlite';
$dbDir = dirname($dbPath);
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

$db = \Database\Connection::getInstance();
$migrationsTableExists = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='migrations'")->fetch();
if (!$migrationsTableExists) {
    require_once __DIR__ . '/../src/Database/Migration.php';
    $migration = new \Database\Migration();
    $migration->run();
}

// Load game tick service
use Service\GameTickService;

echo "[" . date('Y-m-d H:i:s') . "] Running game tick...\n";

GameTickService::tickAllUsers();

echo "[" . date('Y-m-d H:i:s') . "] Game tick complete.\n";
