<?php
// Start session for authentication and flash messages across pages
session_start();

// Database connection constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'test_smart_ujenzi');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_SOCKET', '/var/run/mysqld/mysqld.sock');

// Returns a singleton PDO database connection
// Uses Unix socket if available, otherwise TCP connection
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        // Build DSN string: prefer Unix socket over host-based connection
        $dsn = DB_SOCKET
            ? "mysql:unix_socket=" . DB_SOCKET . ";dbname=" . DB_NAME . ";charset=utf8mb4"
            : "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        // Create PDO instance with exception error mode and associative fetch
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}

// Checks whether the current user has an active session
function isAuthenticated(): bool {
    return isset($_SESSION['user_id']);
}

// Redirects unauthenticated users to the login page
function requireAuth(): void {
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit;
    }
}

// Performs an HTTP redirect to the given URL and stops execution
function redirect(string $url): void {
    header("Location: $url");
    exit;
}
