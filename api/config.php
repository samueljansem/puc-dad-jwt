<?php
define('DB_PATH', __DIR__ . '/../data/database.sqlite');

define('JWT_SECRET', getenv('JWT_SECRET') ?: 'your-secret-key-change-in-production-' . bin2hex(random_bytes(16)));
define('JWT_ALGORITHM', 'HS256');
define('ACCESS_TOKEN_EXPIRY', 900); // 15 minutes
define('REFRESH_TOKEN_EXPIRY', 604800); // 7 days

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

function initDatabase() {
    $dbDir = dirname(DB_PATH);
    if (!file_exists($dbDir)) {
        mkdir($dbDir, 0777, true);
    }

    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create users table
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        email TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Create refresh tokens table
    $db->exec("CREATE TABLE IF NOT EXISTS refresh_tokens (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        token TEXT NOT NULL UNIQUE,
        expires_at DATETIME NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    return $db;
}

function getDatabase() {
    static $db = null;
    if ($db === null) {
        $db = initDatabase();
    }
    return $db;
}
