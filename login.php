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
            $error = 'Invalid username or password';
            logActivity('login_failed', 'user', null, "Failed login attempt for: $email", 'warning');
        } elseif (!password_verify($password, $user['password'])) {
            $error = 'Invalid username or password';
            logActivity('login_failed', 'user', null, "Wrong password for: $email", 'warning');
        } elseif ($user['role'] === 'fundi' && empty($user['approved'])) {
            $error = 'Your account has not been verified. Please check your email for the verification code or <a href="fundi-register.php" class="underline">register again</a>.';
            logActivity('login_blocked', 'user', $user['id'], "Unapproved fundi attempted login: {$user['email']}", 'warning');
        } else {
            $demoEmails = ['super@example.com', 'zainab@example.com', 'steve@example.com', 'teleza@example.com', 'client@example.com', 'ali@example.com', 'david@example.com'];

            if (in_array($user['email'], $demoEmails)) {
                registerDevice($user['id']);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                logActivity('login', 'user', $user['id'], 'Demo account login: ' . $user['email']);
                redirect('dashboard.php');
            }

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
                executeQuery("INSERT INTO otp_codes (user_id, code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))", [$user['id'], $code]);

                require_once __DIR__ . '/includes/mailer.php';
                $mailResult = sendEmail($user['email'], 'Your SmartUjenzi OTP Code',
                    "<h2>OTP Verification</h2>
                     <p>Hello <strong>" . htmlspecialchars($user['name']) . "</strong>,</p>
                     <p>Your verification code is:</p>
                     <h1 style='font-size: 32px; letter-spacing: 8px; text-align: center; background: #f3f4f6; padding: 16px; border-radius: 8px;'>" . $code . "</h1>
                     <p>This code expires in 5 minutes.</p>
                     <p>If you did not attempt to log in, please ignore this email.</p>");
                error_log("OTP send to {$user['email']}: " . ($mailResult ? 'SUCCESS' : 'FAILED'));

                if ($mailResult) {
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
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
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

            <form method="POST" class="space-y-6" autocomplete="off">
                <?php if ($error): ?>
                    <div class="p-4 bg-red-500/10 border border-red-500/50 rounded-lg text-red-500 text-sm text-center"><?= $error ?></div>
                <?php endif; ?>

                <div class="relative">
                    <label class="absolute -top-2.5 left-4 bg-[#0C0D10] px-2 text-xs font-medium text-gray-400">Email</label>
                    <input type="email" name="email" autocomplete="off"
                           class="w-full bg-transparent border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-[#20D08A] transition-colors"
                           placeholder="you@example.com" required>
                </div>

                <div class="relative">
                    <label class="absolute -top-2.5 left-4 bg-[#0C0D10] px-2 text-xs font-medium text-gray-400">Password</label>
                    <input type="password" name="password" autocomplete="new-password"
                           class="w-full bg-transparent border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-[#20D08A] transition-colors"
                           placeholder="********" required>
                </div>

                <div class="text-right -mt-4 mb-2">
                    <a href="forgot-password.php" class="text-sm text-yellow-500 hover:underline">Forgot Password?</a>
                </div>

                <button type="submit"
                        class="w-full bg-[#1BD988] hover:bg-[#15b771] text-black font-bold py-4 px-4 rounded-xl transition-colors mt-8">
                    Log in
                </button>

                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-600"></div></div>
                    <div class="relative flex justify-center text-sm"><span class="px-3 bg-[#0C0D10] text-gray-400">or continue with</span></div>
                </div>

                <a href="google-login.php"
                   class="flex items-center justify-center gap-3 w-full bg-transparent border border-gray-600 hover:border-gray-500 text-white font-medium py-3 px-4 rounded-xl transition-colors">
                    <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Sign in with Google
                </a>

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
