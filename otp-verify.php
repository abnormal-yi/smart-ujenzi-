<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isAuthenticated()) {
    redirect('dashboard.php');
}

$error = '';
$userId = $_SESSION['otp_user_id'] ?? null;

if (!$userId) {
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');

    $stmt = getDB()->prepare("SELECT id FROM otp_codes WHERE user_id = ? AND code = ? AND expires_at > NOW() AND used = 0 ORDER BY id DESC LIMIT 1");
    $stmt->execute([$userId, $code]);
    $otp = $stmt->fetch();

    if ($otp) {
        executeQuery("UPDATE otp_codes SET used = 1 WHERE id = ?", [$otp['id']]);

        $deviceToken = getDeviceToken();
        registerDevice($userId, $deviceToken);

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $_SESSION['otp_user_name'];
        $_SESSION['user_email'] = $_SESSION['otp_user_email'];
        $_SESSION['role'] = $_SESSION['otp_role'];

        unset($_SESSION['otp_user_id'], $_SESSION['otp_user_name'], $_SESSION['otp_user_email'], $_SESSION['otp_role']);
        redirect('dashboard.php');
    } else {
        $error = 'Invalid or expired OTP code.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartUjenzi - Verify OTP</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#524B6B] flex items-center justify-center p-4">

<div class="bg-[#0C0D10] text-white rounded-3xl shadow-2xl p-8 sm:p-12 w-full max-w-md">
    <div class="text-center mb-8">
        <span class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center text-slate-900 text-xl font-bold mx-auto mb-4">S</span>
        <h2 class="text-3xl font-bold">Verify OTP</h2>
        <p class="text-gray-400 mt-2">Enter the 6-digit code sent to your email</p>
    </div>

    <form method="POST" class="space-y-6">
        <?php if ($error): ?>
            <div class="p-4 bg-red-500/10 border border-red-500/50 rounded-lg text-red-500 text-sm text-center"><?= $error ?></div>
        <?php endif; ?>

        <div>
            <input type="text" name="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="off"
                   class="w-full bg-transparent border border-gray-600 rounded-xl px-4 py-4 text-white text-center text-2xl tracking-widest focus:outline-none focus:border-yellow-500 transition-colors"
                   placeholder="000000">
        </div>

        <button type="submit"
                class="w-full bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-4 px-4 rounded-xl transition-colors">
            Verify
        </button>

        <p class="text-center text-gray-400 text-sm">
            <a href="login.php" class="text-yellow-500 hover:underline">Back to Login</a>
        </p>
    </form>
</div>

</body>
</html>
