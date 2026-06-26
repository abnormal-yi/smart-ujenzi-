<?php
require_once __DIR__ . '/includes/functions.php';

$error = '';

if (!isset($_GET['code'])) {
    $error = 'No authorization code received.';
    goto render;
}

$tokenUrl = 'https://oauth2.googleapis.com/token';
$postData = http_build_query([
    'code' => $_GET['code'],
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code',
]);

$ch = curl_init($tokenUrl);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_TIMEOUT => 10,
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200 || !$response) {
    $error = 'Failed to authenticate with Google.';
    goto render;
}

$tokenData = json_decode($response, true);
$accessToken = $tokenData['access_token'] ?? '';

$ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => ["Authorization: Bearer $accessToken"],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
]);
$userInfo = curl_exec($ch);
curl_close($ch);

$googleUser = json_decode($userInfo, true);
if (!$googleUser || !isset($googleUser['email'])) {
    $error = 'Failed to fetch user info from Google.';
    goto render;
}

$googleId = $googleUser['id'];
$email = $googleUser['email'];
$name = $googleUser['name'] ?? explode('@', $email)[0];
$avatar = $googleUser['picture'] ?? '';

$existing = runQuery("SELECT * FROM users WHERE google_id = ?", [$googleId]);
if ($existing) {
    $user = $existing[0];
} else {
    $existingEmail = runQuery("SELECT * FROM users WHERE email = ?", [$email]);
    if ($existingEmail) {
        executeQuery("UPDATE users SET google_id = ?, avatar_url = ? WHERE id = ?", [$googleId, $avatar, $existingEmail[0]['id']]);
        $user = $existingEmail[0];
    } else {
        $hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        executeQuery("INSERT INTO users (name, email, password, role, google_id, avatar_url, approved) VALUES (?, ?, ?, 'client', ?, ?, 1)",
            [$name, $email, $hash, $googleId, $avatar]);
        $user = runQuery("SELECT * FROM users WHERE google_id = ?", [$googleId])[0];
    }
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['role'] = $user['role'];
registerDevice($user['id']);
logActivity('login', 'user', $user['id'], 'Google SSO login');
redirect('dashboard.php');

render:
$pageTitle = 'Google Login';
require_once __DIR__ . '/includes/header.php';
?>
<div class="min-h-screen flex items-center justify-center">
    <div class="bg-[#1a1b1e] p-8 rounded-xl border border-gray-800 max-w-md w-full text-center">
        <h1 class="text-xl font-bold text-red-500 mb-2">Login Failed</h1>
        <p class="text-gray-400"><?= htmlspecialchars($error) ?></p>
        <a href="login.php" class="inline-block mt-4 text-yellow-500 hover:underline">Back to Login</a>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
