<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'test_smart_ujenzi');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_SOCKET', '/var/run/mysqld/mysqld.sock');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = DB_SOCKET
            ? "mysql:unix_socket=" . DB_SOCKET . ";dbname=" . DB_NAME . ";charset=utf8mb4"
            : "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

function isAuthenticated(): bool {
    return isset($_SESSION['user_id']);
}

function requireAuth(): void {
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit;
    }
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}
