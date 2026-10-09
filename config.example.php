<?php
// Copy this file to config.php and update the values for your environment.
define('DB_HOST', 'localhost');
define('DB_NAME', 'tasktrack');
define('DB_USER', 'root');
define('DB_PASS', '');

date_default_timezone_set('UTC');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
