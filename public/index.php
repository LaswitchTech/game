<?php
// Main web entry point - serves the frontend pages
require_once __DIR__ . '/../config/config.php';

// Start session if needed
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple page router
$page = $_GET['page'] ?? 'login';
$pagesDir = __DIR__ . '/pages/';

switch ($page) {
    case 'login':
    case 'planet':
    case 'shipyard':
    case 'research':
    case 'fleet':
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
