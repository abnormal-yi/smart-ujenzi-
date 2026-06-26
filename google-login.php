<?php
require_once __DIR__ . '/includes/functions.php';
if (!defined('GOOGLE_CLIENT_ID') || !GOOGLE_CLIENT_ID) {
    die('Google SSO not configured. Contact the administrator.');
}
$params = http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'email profile',
    'access_type' => 'online',
    'prompt' => 'select_account',
]);
header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
exit;
