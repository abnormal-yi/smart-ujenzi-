<?php
session_start();
$userId = $_SESSION['user_id'] ?? null;
$email = $_SESSION['user_email'] ?? '';

require_once __DIR__ . '/includes/functions.php';
logActivity('logout', 'user', $userId, 'User logged out: ' . $email);

session_destroy();
header('Location: login.php');
exit;
