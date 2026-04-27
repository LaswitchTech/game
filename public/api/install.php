<?php

require_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

// Check if already installed
$lockFile = DATA_PATH . '/install.lock';
if (file_exists($lockFile)) {
    http_response_code(400);
    echo json_encode(['error' => 'Application is already installed. Use /uninstall first.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!$data && isset($_POST['username'])) {
        $data = $_POST;
    }
    if (!$data) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request']);
        exit;
    }

    $username = trim($data['username'] ?? '');
    $email = trim($data['email'] ?? '');
    $password = $data['password'] ?? '';
    $faction = $data['faction'] ?? 'unsc';

    // Validate
    if (strlen($username) < 3 || strlen($username) > 20) {
        http_response_code(400);
        echo json_encode(['error' => 'Username must be 3-20 characters']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email address']);
        exit;
    }
    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'Password must be at least 6 characters']);
        exit;
    }
    if (!in_array($faction, ['unsc', 'covenant'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid faction']);
        exit;
    }

    // Start the database
    require_once __DIR__ . '/../../src/Database/Connection.php';
    $db = \Database\Connection::getInstance();

    // Run all migrations
    require_once __DIR__ . '/../../src/Database/Migration.php';
    $migration = new \Database\Migration();
    $migration->run();

    // Create admin user (also creates default planet)
    require_once __DIR__ . '/../../src/Model/User.php';
    require_once __DIR__ . '/../../src/Model/Planet.php';

    $user = \Model\User::create($username, $email, password_hash($password, PASSWORD_BCRYPT), $faction);

    // Write lock file — marks app as installed
    if (!is_dir(DATA_PATH)) mkdir(DATA_PATH, 0755, true);
    file_put_contents($lockFile, date('Y-m-d H:i:s'));

    echo json_encode([
        'success' => true,
        'message' => 'Installation complete',
        'user_id' => $user->getId(),
    ]);
    exit;
}

// GET — should never happen since index.php redirects to install.html
http_response_code(404);
echo json_encode(['error' => 'Not found']);
