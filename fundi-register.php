<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isAuthenticated()) {
    redirect('dashboard.php');
}

$error = '';
$success = '';

// Load location data from cache
$cacheFile = sys_get_temp_dir() . '/smartujenzi-location-cache.json';
if (file_exists($cacheFile) && filemtime($cacheFile) > time() - 86400) {
    $cached = file_get_contents($cacheFile);
    $parts = explode("\n\n\n", $cached, 3);
    $jsonRegions = $parts[0] ?? '[]';
    $jsonDistricts = $parts[1] ?? '{}';
    $jsonWards = $parts[2] ?? '{}';
} else {
    try {
        $db = getDB();
        $regions = $db->query("SELECT name FROM regions ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
        $distRows = $db->query("SELECT d.name AS district, r.name AS region FROM districts d JOIN regions r ON r.id = d.region_id ORDER BY d.id")->fetchAll(PDO::FETCH_ASSOC);
        $wardRows = $db->query("SELECT d.name AS district, w.name AS ward FROM wards w JOIN districts d ON d.id = w.district_id ORDER BY d.id, w.name")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $regions = []; $distRows = []; $wardRows = [];
    }
    $distMap = [];
    foreach ($distRows as $d) $distMap[$d['district']] = $d['region'];
    $wardMap = [];
    foreach ($wardRows as $w) $wardMap[$w['district']][] = $w['ward'];
    $jsonRegions = json_encode($regions, JSON_UNESCAPED_UNICODE);
    $jsonDistricts = json_encode($distMap, JSON_UNESCAPED_UNICODE);
    $jsonWards = json_encode($wardMap, JSON_UNESCAPED_UNICODE);
    @file_put_contents($cacheFile, $jsonRegions . "\n\n\n" . $jsonDistricts . "\n\n\n" . $jsonWards);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $region   = trim($_POST['region_id'] ?? '');
    $district = trim($_POST['district_id'] ?? '');
    $ward     = trim($_POST['ward_id'] ?? '');
    $location = trim("$region, $district, $ward", ' ,');
    $skills   = trim($_POST['skills'] ?? '');

    if (!$name || !$email || !$password) {
        $error = 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $stmt = getDB()->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Email already registered. <a href="login.php" class="underline">Log in</a>';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = getDB()->prepare('INSERT INTO users (name, email, password, role, location, skills) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, $hash, 'fundi', $location, $skills]);
            $userId = getDB()->lastInsertId();
            logActivity('user_registered', 'user', $userId, "New fundi registration: $name ($email)", 'info');

            $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            executeQuery("INSERT INTO otp_codes (user_id, code, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))", [$userId, $code]);

            require_once __DIR__ . '/includes/mailer.php';
            sendEmail($email, 'Verify your SmartUjenzi Fundi Account',
                "<h2>Email Verification</h2>
                 <p>Hello <strong>" . htmlspecialchars($name) . "</strong>,</p>
                 <p>Your verification code is:</p>
                 <h1 style='font-size: 32px; letter-spacing: 8px; text-align: center; background: #f3f4f6; padding: 16px; border-radius: 8px;'>" . $code . "</h1>
                 <p>This code expires in 5 minutes.</p>
                 <p>Enter this code to activate your fundi account.</p>");

            $_SESSION['otp_user_id'] = $userId;
            $_SESSION['otp_user_name'] = $name;
            $_SESSION['otp_user_email'] = $email;
            $_SESSION['otp_role'] = 'fundi';
            redirect('otp-verify.php');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartUjenzi - Fundi Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        select option { background: #1f2937; color: #fff; }
    </style>
</head>
<body class="min-h-screen bg-[#524B6B] flex items-center justify-center p-4 sm:p-8">

<script>
var TZ_REGIONS = <?= $jsonRegions ?>;
var TZ_DISTRICTS = <?= $jsonDistricts ?>;
var TZ_WARDS = <?= $jsonWards ?>;
document.addEventListener('DOMContentLoaded', function() {
    var RS = document.getElementById('region_id');
    var DS = document.getElementById('district_id');
    var WS = document.getElementById('ward_id');
    if (!RS) return;
    var RD = TZ_DISTRICTS || {}, WD = TZ_WARDS || {};
    function fill(sel, arr, ph) {
        sel.innerHTML = '<option value="">' + ph + '</option>';
        arr.forEach(function(x) {
            var o = document.createElement('option');
            o.value = x; o.textContent = x; sel.appendChild(o);
        });
    }
    function getDistricts(region) {
        var a = [];
        for (var d in RD) { if (RD[d] === region) a.push(d); }
        return a.sort();
    }
    fill(RS, TZ_REGIONS || [], 'Select Region');
    RS.addEventListener('change', function() {
        var v = this.value;
        DS.disabled = !v; DS.innerHTML = '<option value="">' + (v ? 'Select District' : 'Loading...') + '</option>';
        WS.disabled = true; WS.innerHTML = '<option value="">Select Ward</option>';
        if (v) fill(DS, getDistricts(v), 'Select District');
    });
    if (DS && WS) {
        DS.addEventListener('change', function() {
            var v = this.value;
            WS.disabled = !v; WS.innerHTML = '<option value="">' + (v ? 'Loading...' : 'Select Ward') + '</option>';
            if (v) fill(WS, WD[v] || [], 'Select Ward');
        });
    }
});
</script>

<div class="flex w-full max-w-6xl min-h-[700px] overflow-hidden rounded-3xl shadow-2xl">
    <div class="hidden lg:flex flex-col w-1/2 relative bg-slate-900 overflow-hidden">
        <img src="public/login-hero.jpg" alt="Construction" class="absolute inset-0 w-full h-full object-cover opacity-60">
        <div class="absolute inset-0 bg-gradient-to-t from-[#0C0D10] via-transparent to-transparent opacity-90"></div>
        <div class="relative z-10 p-12 flex flex-col h-full justify-between">
            <div class="flex items-center space-x-3 text-white">
                <span class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center text-slate-900 text-lg font-bold">S</span>
                <span class="text-2xl font-bold tracking-wider">SMART UJENZI</span>
            </div>
            <div class="text-white">
                <h2 class="text-3xl font-bold mb-4">Join as Fundi</h2>
                <p class="text-gray-300 text-lg">Register your skills and start getting hired for construction projects.</p>
            </div>
        </div>
    </div>

    <div class="w-full lg:w-1/2 bg-[#0C0D10] text-white flex flex-col p-8 sm:p-16 lg:px-24 justify-center">
        <div class="max-w-md w-full mx-auto">
            <h2 class="text-4xl font-bold text-center mb-4">Fundi Registration</h2>
            <p class="text-gray-400 text-center mb-10">Register your skills to get hired</p>

            <form method="POST" class="space-y-5">
                <?php if ($error): ?>
                    <div class="p-4 bg-red-500/10 border border-red-500/50 rounded-lg text-red-500 text-sm text-center"><?= $error ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="p-4 bg-green-500/10 border border-green-500/50 rounded-lg text-green-500 text-sm text-center"><?= $success ?></div>
                <?php endif; ?>

                <div class="relative">
                    <label class="absolute -top-2.5 left-4 bg-[#0C0D10] px-2 text-xs font-medium text-gray-400">Full Name</label>
                    <input type="text" name="name" required
                           class="w-full bg-transparent border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-yellow-500 transition-colors"
                           placeholder="John Doe">
                </div>

                <div class="relative">
                    <label class="absolute -top-2.5 left-4 bg-[#0C0D10] px-2 text-xs font-medium text-gray-400">Email</label>
                    <input type="email" name="email" required
                           class="w-full bg-transparent border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-yellow-500 transition-colors"
                           placeholder="fundi@example.com">
                </div>

                <div class="relative">
                    <label class="absolute -top-2.5 left-4 bg-[#0C0D10] px-2 text-xs font-medium text-gray-400">Password</label>
                    <input type="password" name="password" required minlength="6"
                           class="w-full bg-transparent border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-yellow-500 transition-colors"
                           placeholder="Min 6 characters">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Skills / Ujuzi</label>
                    <input type="text" name="skills"
                           class="w-full bg-transparent border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-yellow-500 transition-colors"
                           placeholder="e.g. Mason, Plumber, Electrician, Carpenter">
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Region</label>
                    <select name="region_id" id="region_id"
                            class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-yellow-500 transition-colors">
                        <option value="" class="bg-gray-800 text-gray-400">Select Region</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">District</label>
                    <select name="district_id" id="district_id" disabled
                            class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-yellow-500 transition-colors">
                        <option value="" class="bg-gray-800 text-gray-400">Select District</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1">Ward / Mtaa</label>
                    <select name="ward_id" id="ward_id" disabled
                            class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-4 text-white focus:outline-none focus:border-yellow-500 transition-colors">
                        <option value="" class="bg-gray-800 text-gray-400">Select Ward</option>
                    </select>
                </div>

                <button type="submit"
                         class="w-full bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-4 px-4 rounded-xl transition-colors mt-4">
                    Register as Fundi
                </button>

                <p class="text-center text-gray-400 text-sm mt-6">
                    Already have an account?
                    <a href="login.php" class="text-yellow-500 hover:underline font-medium">Log in</a>
                </p>
            </form>
        </div>
    </div>
</div>

</body>
</html>
