<?php
// Load database configuration and session helpers
require_once __DIR__ . '/config.php';

// Executes a SELECT query and returns all matching rows as an associative array
function runQuery(string $sql, array $params = []): array {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Executes an INSERT/UPDATE/DELETE query and returns the last insert ID and affected row count
function executeQuery(string $sql, array $params = []): array {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return ['id' => (int)$db->lastInsertId(), 'changes' => $stmt->rowCount()];
}

// Fetches the 20 most recent notifications for a given user
function getNotifications(int $userId): array {
    return runQuery('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20', [$userId]);
}

// Counts unread notifications for a given user
function getUnreadCount(int $userId): int {
    $res = runQuery('SELECT COUNT(*) as c FROM notifications WHERE user_id = ? AND is_read = 0', [$userId]);
    return (int)$res[0]['c'];
}

// Marks a single notification as read by its ID
function markNotificationRead(int $id): void {
    executeQuery('UPDATE notifications SET is_read = 1 WHERE id = ?', [$id]);
}

// Checks if the current user has a specific role
function hasRole(string $role): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Checks if the current user has any of the given roles
function hasAnyRole(array $roles): bool {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], $roles);
}

// Redirects user to dashboard with an error flash message if they lack the required roles
function requireRole(array $roles): void {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'You do not have permission to access this page.'];
        redirect('dashboard.php');
    }
}

// Convenience guard that restricts access to admin, super_admin, and project_manager roles
function requireAdminManager(): void {
    requireRole(['super_admin', 'admin', 'project_manager']);
}

// Alias for requireAuth() used in templates
function requireLogin(): void {
    requireAuth();
}

function getDeviceToken(): string {
    if (isset($_COOKIE['device_token'])) {
        return $_COOKIE['device_token'];
    }
    $token = bin2hex(random_bytes(32));
    setcookie('device_token', $token, time() + 86400 * 365, '/', '', false, true);
    return $token;
}

function isKnownDevice(int $userId, string $token): bool {
    $res = runQuery("SELECT id FROM user_devices WHERE user_id = ? AND device_token = ?", [$userId, $token]);
    return !empty($res);
}

function registerDevice(int $userId, string $token): void {
    executeQuery("INSERT INTO user_devices (user_id, device_token) VALUES (?, ?)", [$userId, $token]);
}
