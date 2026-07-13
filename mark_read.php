<?php
require_once __DIR__ . '/includes/config.php';

if (!isAuthenticated() || !isset($_GET['id'])) {
    http_response_code(403);
    exit;
}

$stmt = getDB()->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);

$countStmt = getDB()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0');
$countStmt->execute([$_SESSION['user_id']]);
$remaining = (int)$countStmt->fetchColumn();

header('Content-Type: application/json');
echo json_encode(['ok' => true, 'remaining' => $remaining, 'link' => $_GET['link'] ?? null]);
