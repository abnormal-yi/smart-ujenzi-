<?php
$pageTitle = 'Forgot Password';
require_once __DIR__ . '/includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $user = runQuery("SELECT id, name FROM users WHERE email = ?", [$email]);
    if ($user) {
        $token = bin2hex(random_bytes(32));
        executeQuery("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 30 MINUTE))", [$email, $token]);
        require_once __DIR__ . '/includes/mailer.php';
        $resetLink = (defined('APP_URL') ? APP_URL : 'https://trisa.luxurywebs.com') . "/reset-password.php?token=$token";
        sendEmail($email, 'SmartUjenzi Password Reset',
            "<h2>Password Reset</h2>
             <p>Hi <strong>" . htmlspecialchars($user[0]['name']) . "</strong>,</p>
             <p>Click the link below to reset your password:</p>
             <p><a href=\"$resetLink\" style=\"display:inline-block;padding:12px 24px;background:#2563eb;color:white;text-decoration:none;border-radius:6px;\">Reset Password</a></p>
             <p>Link expires in 30 minutes.</p>
             <p>If you did not request this, please ignore this email.</p>");
    }
    $success = 'If that email is registered, a reset link has been sent.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - SmartUjenzi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#0C0D10] min-h-screen flex items-center justify-center">
    <div class="bg-[#1a1b1e] p-8 rounded-xl border border-gray-800 w-full max-w-md">
        <h1 class="text-2xl font-bold text-white mb-2">Forgot Password</h1>
        <p class="text-gray-400 text-sm mb-6">Enter your email to receive a reset link.</p>
        <?php if ($success): ?>
            <div class="p-3 mb-4 rounded-lg text-sm bg-green-500/10 border border-green-500/50 text-green-500"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="p-3 mb-4 rounded-lg text-sm bg-red-500/10 border border-red-500/50 text-red-500"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" class="space-y-4">
            <div class="relative">
                <label class="absolute -top-2.5 left-4 bg-[#1a1b1e] px-2 text-xs font-medium text-gray-400">Email</label>
                <input type="email" name="email" required class="w-full bg-transparent border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-[#20D08A] transition-colors" placeholder="you@example.com">
            </div>
            <button type="submit" class="w-full bg-[#1BD988] hover:bg-[#15b771] text-black font-bold py-4 px-4 rounded-xl transition-colors">Send Reset Link</button>
        </form>
        <p class="text-center text-sm text-gray-400 mt-4"><a href="login.php" class="text-yellow-500 hover:underline">Back to Login</a></p>
    </div>
</body>
</html>
