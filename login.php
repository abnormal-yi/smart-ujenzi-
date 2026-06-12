<?php
// Load config for database access and session management
require_once __DIR__ . '/includes/config.php';

// Redirect already authenticated users straight to the dashboard
if (isAuthenticated()) {
    redirect('dashboard.php');
}

$error = ''; // Will hold login error message if authentication fails

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Look up user by email in the database
    $stmt = getDB()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify password hash and set session variables on success
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        redirect('dashboard.php');
    } else {
        // Show error message if credentials don't match
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartUjenzi - Login</title>
    <!-- Tailwind CSS for styling the login page -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-[#524B6B] flex items-center justify-center p-4 sm:p-8">

<!-- Main login card container: image panel on left, form on right -->
<div class="flex w-full max-w-6xl h-[800px] overflow-hidden rounded-3xl shadow-2xl">

    <!-- Left panel: hero image with branding and floating stats cards -->
    <div class="hidden lg:flex flex-col w-1/2 relative bg-slate-900 overflow-hidden">
        <!-- Background construction site image with gradient overlay -->
        <img src="public/login-hero.jpg" alt="Construction" class="absolute inset-0 w-full h-full object-cover opacity-80">
        <div class="absolute inset-0 bg-gradient-to-t from-[#0C0D10] via-transparent to-transparent opacity-90"></div>

        <div class="relative z-10 p-12 flex flex-col h-full justify-between">
            <!-- Brand logo and name -->
            <div class="flex items-center space-x-3 text-white">
                <span class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center text-slate-900 text-lg font-bold">S</span>
                <span class="text-2xl font-bold tracking-wider">SMART UJENZI</span>
            </div>

            <!-- Decorative animated stats cards -->
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
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white font-bold text-xs mr-3">
                            M</div>
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

    <!-- Right panel: login form -->
    <div class="w-full lg:w-1/2 bg-[#0C0D10] text-white flex flex-col p-8 sm:p-16 lg:px-24 justify-center relative">

        <div class="max-w-md w-full mx-auto">
            <h2 class="text-4xl font-bold text-center mb-10">Welcome to SmartUjenzi!</h2>

            <form method="POST" class="space-y-6">
                <!-- Error message banner displayed on failed login -->
                <?php if ($error): ?>
                    <div class="p-4 bg-red-500/10 border border-red-500/50 rounded-lg text-red-500 text-sm text-center"><?= $error ?></div>
                <?php endif; ?>

                <!-- Email input field with floating label -->
                <div class="relative">
                    <label class="absolute -top-2.5 left-4 bg-[#0C0D10] px-2 text-xs font-medium text-gray-400">Email</label>
                    <input type="email" name="email" value="admin@example.com"
                           class="w-full bg-transparent border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-[#20D08A] transition-colors"
                           placeholder="admin@example.com" required>
                </div>

                <!-- Password input field with floating label -->
                <div class="relative">
                    <label class="absolute -top-2.5 left-4 bg-[#0C0D10] px-2 text-xs font-medium text-gray-400">Password</label>
                    <input type="password" name="password" value="<?= APP_ENV === 'local' ? 'admin123' : '' ?>"
                           class="w-full bg-transparent border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-[#20D08A] transition-colors"
                           placeholder="********" required>
                </div>

                <!-- Submit button -->
                <button type="submit"
                        class="w-full bg-[#1BD988] hover:bg-[#15b771] text-black font-bold py-4 px-4 rounded-xl transition-colors mt-8">
                    Log in
                </button>

                <!-- Demo accounts reference for testing -->
                <p class="text-gray-400 text-sm mt-6">
                    Don't have an account?
                    <a href="register.php" class="text-yellow-500 hover:underline font-medium">Register</a>
                </p>
                <div class="text-center mt-4">
                    <p class="text-gray-400 text-sm leading-relaxed">
                        Demo Accounts:<br>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        Demo Accounts:<br>
                        <span class="text-gray-300">admin@example.com / admin123 (Admin)</span><br>
                        <span class="text-gray-300">steve@example.com / pass123 (Manager)</span><br>
                        <span class="text-gray-300">teleza@example.com / pass123 (Supervisor)</span><br>
                        <span class="text-gray-300">constructor@example.com / pass123 (Constructor)</span><br>
                        <span class="text-gray-300">mteja@example.com / pass123 (Customer)</span>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
