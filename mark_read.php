<?php
// AJAX endpoint: marks a single notification as read for the authenticated user
require_once __DIR__ . '/includes/config.php';
// Verify the user is logged in and a notification ID was provided
if (isAuthenticated() && isset($_GET['id'])) {
    $stmt = getDB()->prepare('UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?');
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
}
