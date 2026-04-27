<?php
// REST API entry point
require_once __DIR__ . '/../../config/config.php';


require_once __DIR__ . '/../../vendor/autoload.php';

use Api\Router;

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Run migrations if needed
$dbPath = defined('DB_PATH') ? DB_PATH : __DIR__ . '/../../data/game.sqlite';
$dbDir = dirname($dbPath);
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}
$db = \Database\Connection::getInstance();
$migrationsTableExists = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='migrations'")->fetch();
if (!$migrationsTableExists) {
    require_once __DIR__ . '/../../src/Database/Migration.php';
    $migration = new \Database\Migration();
    $migration->run();
}

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Handle install/uninstall API endpoints
$q = $_GET['q'] ?? '';
$reqPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (str_ends_with($reqPath, '/install') || $reqPath === '/api/install' || $q === 'install') {
    require __DIR__ . '/install.php';
    exit;
}
if (str_ends_with($reqPath, '/uninstall') || $reqPath === '/api/uninstall' || $q === 'uninstall') {
    require __DIR__ . '/uninstall.php';
    exit;
}

// Strip API prefix
$path = preg_replace('#^/api#', '', $path);
if ($path === '' || $path === '/') {
    $path = '';
}

// Dispatch
$router = Router::register();
$result = $router->dispatch($method, $path);

// Send JSON response
header('Content-Type: application/json');
if (isset($result['error']) && in_array($result['error'], ['Not found'])) {
    http_response_code($result['code'] ?? 404);
} elseif (isset($result['error'])) {
    // Check if it's an auth error
    if (strpos($result['error'], 'Authentication') !== false || strpos($result['error'], 'required') !== false) {
        http_response_code(401);
    }
}
echo json_encode($result);
