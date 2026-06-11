<?php
require_once __DIR__ . '/config.php';

function runQuery(string $sql, array $params = []): array {
    $stmt = getDB()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function executeQuery(string $sql, array $params = []): array {
    $db = getDB();
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return ['id' => (int)$db->lastInsertId(), 'changes' => $stmt->rowCount()];
}

function getNotifications(int $userId): array {
    return runQuery('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20', [$userId]);
}

function getUnreadCount(int $userId): int {
    $res = runQuery('SELECT COUNT(*) as c FROM notifications WHERE user_id = ? AND is_read = 0', [$userId]);
    return (int)$res[0]['c'];
}

function markNotificationRead(int $id): void {
    executeQuery('UPDATE notifications SET is_read = 1 WHERE id = ?', [$id]);
}

function hasRole(string $role): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function hasAnyRole(array $roles): bool {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], $roles);
}

function requireRole(array $roles): void {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles)) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'You do not have permission to access this page.'];
        redirect('dashboard.php');
    }
}

function requireAdminManager(): void {
    requireRole(['admin', 'manager']);
}
