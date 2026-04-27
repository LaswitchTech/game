<?php
// Main web entry point - serves the frontend pages
require_once __DIR__ . '/../config/config.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if installed
$lockFile = DATA_PATH . '/install.lock';
$isInstalled = file_exists($lockFile);

// Allow install/uninstall routes regardless of install state
if (preg_match('#^/?(install|uninstall)$#', $_GET['page'] ?? '', $m)) {
    $action = $m[1];
    if ($action === 'install') {
        http_response_code(200);
        require __DIR__ . '/install.html';
        exit;
    }
    if ($action === 'uninstall') {
        http_response_code(200);
        require __DIR__ . '/uninstall.php';
        exit;
    }
}

// If not installed, redirect to installer
if (!$isInstalled) {
    header('Location: install.html');
    exit;
}

// Page router
$page = $_GET['page'] ?? 'login';
$pagesDir = __DIR__ . '/pages/';

switch ($page) {
    case 'login':
    case 'planet':
    case 'shipyard':
    case 'research':
    case 'fleet':
    case 'galaxy':
        $file = $pagesDir . $page . '.html';
        if (file_exists($file)) {
            require $file;
        } else {
            http_response_code(404);
            echo 'Page not found';
        }
        break;
    default:
        http_response_code(404);
        echo 'Page not found';
}
