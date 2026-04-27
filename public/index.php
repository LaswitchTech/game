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

// If not installed, redirect to installer
if (!$isInstalled) {
    header('Location: install.html');
    exit;
}

// Page router
$page = $_GET['page'] ?? 'login';

// Special pages (always served when app is installed)
if ($page === 'install') {
    http_response_code(200);
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Galactic Empire</title><link rel="stylesheet" href="css/global.css"></head><body style="text-align:center;padding:4rem 1rem;background:var(--bg-dark);color:var(--text-primary);"><h1 style="color:var(--success)">Already Installed</h1><p style="color:var(--text-secondary);margin:1rem 0;">The game is already set up. Go to the <a href="index.php?page=login" style="color:var(--accent);">login page</a>, or <a href="uninstall" style="color:var(--danger);">uninstall</a>.</p></body></html>';
    exit;
}
if ($page === 'uninstall') {
    http_response_code(200);
    require __DIR__ . '/uninstall.php';
    exit;
}

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
