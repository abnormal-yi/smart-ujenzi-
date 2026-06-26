<?php
$pageTitle = 'Reset Password';
require_once __DIR__ . '/includes/functions.php';

$success = '';
$error = '';
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token) {
    $password = $_POST['password'] ?? '';
    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $row = runQuery("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()", [$token]);
        if ($row) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            executeQuery("UPDATE users SET password = ? WHERE email = ?", [$hash, $row[0]['email']]);
            executeQuery("UPDATE password_resets SET used = 1 WHERE id = ?", [$row[0]['id']]);
            $success = 'Password reset successful! <a href="login.php" class="text-yellow-500 underline">Log in here</a>';
        } else {
            $error = 'Invalid or expired reset token.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - SmartUjenzi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0C0D10] min-h-screen flex items-center justify-center">
    <div class="bg-[#1a1b1e] p-8 rounded-xl border border-gray-800 w-full max-w-md">
        <h1 class="text-2xl font-bold text-white mb-2">Reset Password</h1>
        <?php if ($success): ?>
            <div class="p-3 mb-4 rounded-lg text-sm bg-green-500/10 border border-green-500/50 text-green-500"><?= $success ?></div>
        <?php elseif ($error): ?>
            <div class="p-3 mb-4 rounded-lg text-sm bg-red-500/10 border border-red-500/50 text-red-500"><?= htmlspecialchars($error) ?></div>
        <?php elseif (!$token): ?>
            <p class="text-red-500">Missing reset token.</p>
        <?php else: ?>
            <p class="text-gray-400 text-sm mb-6">Enter your new password.</p>
            <form method="POST" class="space-y-4">
                <div class="relative">
                    <label class="absolute -top-2.5 left-4 bg-[#1a1b1e] px-2 text-xs font-medium text-gray-400">New Password</label>
                    <input type="password" name="password" minlength="6" required class="w-full bg-transparent border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-[#20D08A] transition-colors" placeholder="******">
                </div>
                <button type="submit" class="w-full bg-[#1BD988] hover:bg-[#15b771] text-black font-bold py-4 px-4 rounded-xl transition-colors">Reset Password</button>
            </form>
        <?php endif; ?>
        <p class="text-center text-sm text-gray-400 mt-4"><a href="login.php" class="text-yellow-500 hover:underline">Back to Login</a></p>
    </div>
</body>
</html>
