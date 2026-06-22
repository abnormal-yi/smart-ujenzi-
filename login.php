<?php
if (isset($_GET['debug'])) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isAuthenticated()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    try {
        $db = getDB();
    } catch (Exception $e) {
        $error = 'DB error: ' . $e->getMessage();
    }

    if (empty($error)) {
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = 'No user found with that email';
            logActivity('login_failed', 'user', null, "Failed login attempt for: $email", 'warning');
        } elseif (!password_verify($password, $user['password'])) {
            $error = 'Password does not match stored hash';
            logActivity('login_failed', 'user', null, "Wrong password for: $email", 'warning');
        } elseif ($user['role'] === 'fundi' && empty($user['approved'])) {
            $error = 'Your account has not been verified. Please check your email for the verification code or <a href="fundi-register.php" class="underline">register again</a>.';
            logActivity('login_blocked', 'user', $user['id'], "Unapproved fundi attempted login: {$user['email']}", 'warning');
        } else {
            $deviceToken = getDeviceToken();

            if (isKnownDevice($user['id'], $deviceToken)) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                logActivity('login', 'user', $user['id'], 'Known device login: ' . $user['email']);
                redirect('dashboard.php');
            } else {
                $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                executeQuery("INSERT INTO otp_codes (user_id, code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))", [$user['id'], $code]);

                require_once __DIR__ . '/includes/mailer.php';
                $sent = sendEmail($user['email'], 'Your SmartUjenzi OTP Code',
                    "<h2>OTP Verification</h2>
                     <p>Hello <strong>" . htmlspecialchars($user['name']) . "</strong>,</p>
                     <p>Your verification code is:</p>
                     <h1 style='font-size: 32px; letter-spacing: 8px; text-align: center; background: #f3f4f6; padding: 16px; border-radius: 8px;'>" . $code . "</h1>
                     <p>This code expires in 5 minutes.</p>
                     <p>If you did not attempt to log in, please ignore this email.</p>");

                if ($sent) {
                    $_SESSION['otp_user_id'] = $user['id'];
                    $_SESSION['otp_user_name'] = $user['name'];
                    $_SESSION['otp_user_email'] = $user['email'];
                    $_SESSION['otp_role'] = $user['role'];
                    redirect('otp-verify.php');
                }

                $_SESSION['otp_code_debug'] = $code;
                $_SESSION['otp_user_id'] = $user['id'];
                $_SESSION['otp_user_name'] = $user['name'];
                $_SESSION['otp_user_email'] = $user['email'];
                $_SESSION['otp_role'] = $user['role'];
                redirect('otp-verify.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartUjenzi - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#524B6B] flex items-center justify-center p-4 sm:p-8">

<div class="flex w-full max-w-6xl h-[800px] overflow-hidden rounded-3xl shadow-2xl">

    <div class="hidden lg:flex flex-col w-1/2 relative bg-slate-900 overflow-hidden">
        <img src="public/login-hero.jpg" alt="Construction" class="absolute inset-0 w-full h-full object-cover opacity-80">
        <div class="absolute inset-0 bg-gradient-to-t from-[#0C0D10] via-transparent to-transparent opacity-90"></div>
        <div class="relative z-10 p-12 flex flex-col h-full justify-between">
            <div class="flex items-center space-x-3 text-white">
                <span class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center text-slate-900 text-lg font-bold">S</span>
                <span class="text-2xl font-bold tracking-wider">SMART UJENZI</span>
            </div>
            <div class="space-y-4">
                <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl w-64">
                    <div class="flex items-center text-white mb-2">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <span class="text-white text-xs font-bold">T</span>
                        </div>
                        <div>
                            <div class="font-bold text-sm">Tasks</div>
                            <div class="text-xs text-gray-300">Progress today</div>
                        </div>
                        <div class="ml-auto text-right">
                            <div class="font-bold text-sm">+12</div>
                            <div class="text-xs text-green-400">+15.2%</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl w-64 ml-8">
                    <div class="flex items-center text-white">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white font-bold text-xs mr-3">M</div>
                        <div>
                            <div class="font-bold text-sm">Materials</div>
                            <div class="text-xs text-gray-300">Stock updates</div>
                        </div>
                        <div class="ml-auto text-right">
                            <div class="font-bold text-sm">+850</div>
                            <div class="text-xs text-green-400">+8.4%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full lg:w-1/2 bg-[#0C0D10] text-white flex flex-col p-8 sm:p-16 lg:px-24 justify-center relative">
        <div class="max-w-md w-full mx-auto">
            <h2 class="text-4xl font-bold text-center mb-10">Welcome to SmartUjenzi!</h2>

            <form method="POST" class="space-y-6">
                <?php if ($error): ?>
                    <div class="p-4 bg-red-500/10 border border-red-500/50 rounded-lg text-red-500 text-sm text-center"><?= $error ?></div>
                <?php endif; ?>

                <div class="relative">
                    <label class="absolute -top-2.5 left-4 bg-[#0C0D10] px-2 text-xs font-medium text-gray-400">Email</label>
                    <input type="email" name="email"
                           class="w-full bg-transparent border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-[#20D08A] transition-colors"
                           placeholder="you@example.com" required>
                </div>

                <div class="relative">
                    <label class="absolute -top-2.5 left-4 bg-[#0C0D10] px-2 text-xs font-medium text-gray-400">Password</label>
                    <input type="password" name="password"
                           class="w-full bg-transparent border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-[#20D08A] transition-colors"
                           placeholder="********" required>
                </div>

                <button type="submit"
                        class="w-full bg-[#1BD988] hover:bg-[#15b771] text-black font-bold py-4 px-4 rounded-xl transition-colors mt-8">
                    Log in
                </button>

                <p class="text-gray-400 text-sm mt-6 text-center">
                    Don't have an account?
                    <a href="register.php" class="text-yellow-500 hover:underline font-medium">Register</a>
                </p>
            </form>
        </div>
    </div>
</div>

</body>
</html>
