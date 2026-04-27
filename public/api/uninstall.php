<?php

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if installed
$lockFile = DATA_PATH . '/install.lock';
if (!file_exists($lockFile)) {
    echo json_encode(['success' => true, 'message' => 'Not installed, nothing to uninstall.']);
    exit;
}

// Remove lock file
unlink($lockFile);

// Remove database
$dbPath = defined('DB_PATH') ? DB_PATH : DATA_PATH . '/game.sqlite';
if (file_exists($dbPath)) {
    unlink($dbPath);
}

echo json_encode(['success' => true, 'message' => 'Uninstall complete.']);
