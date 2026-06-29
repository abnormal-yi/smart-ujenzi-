<?php
require_once __DIR__ . '/includes/functions.php';
$userId = $_SESSION['user_id'] ?? null;
$email = $_SESSION['user_email'] ?? '';

logActivity('logout', 'user', $userId, 'User logged out: ' . $email);

$_SESSION = [];
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');
header('Location: login.php');
exit;
