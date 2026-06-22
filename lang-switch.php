<?php
session_start();

$lang = $_POST['lang'] ?? 'en';
if (!in_array($lang, ['en', 'sw'])) {
    $lang = 'en';
}

$_SESSION['lang'] = $lang;
setcookie('app_lang', $lang, time() + 86400 * 365, '/', '', false, true);

$redirect = $_SERVER['HTTP_REFERER'] ?? 'dashboard.php';
header('Location: ' . $redirect);
exit;
