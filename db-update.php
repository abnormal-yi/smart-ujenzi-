<?php
// db-update.php - Web-based database setup & update tool
// Access: http://your-site/db-update.php?key=admin123

$allowedKey = 'admin123';

if (empty($_GET['key']) || $_GET['key'] !== $allowedKey) {
    header('HTTP/1.0 403 Forbidden');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Access Denied</title>';
    echo '<script src="https://cdn.tailwindcss.com"></script></head>';
    echo '<body class="flex items-center justify-center min-h-screen bg-gray-100">';
    echo '<div class="bg-white p-8 rounded-2xl shadow-lg text-center"><h1 class="text-2xl font-bold text-red-600 mb-2">403 Forbidden</h1>';
    echo '<p class="text-gray-600">Add <code class="bg-gray-100 px-2 py-0.5 rounded">?key=admin123</code> to the URL.</p></div></body></html>';
    exit;
}

require_once __DIR__ . '/includes/config.php';
$sqlFile = __DIR__ . '/database/schema.sql';
$action = $_POST['action'] ?? '';
$message = '';

if ($action === 'init' || $action === 'force') {
    try {
        $dsn = DB_SOCKET && DB_USER === ''
            ? "mysql:unix_socket=" . DB_SOCKET . ";charset=utf8mb4"
            : "mysql:host=" . DB_HOST . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        if ($action === 'force') {
            $pdo->exec("DROP DATABASE IF EXISTS `" . DB_NAME . "`");
            $message .= "[OK] Database dropped.\n";
        }
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `" . DB_NAME . "`");
        $sql = file_get_contents($sqlFile);
        $sql = preg_replace('/^CREATE DATABASE.*?;\s*/im', '', $sql);
        $sql = preg_replace('/^USE .*?;\s*/im', '', $sql);
        $sql = preg_replace('/^-- .*$/m', '', $sql);
        $statements = array_filter(array_map('trim', explode(';', $sql)), fn($s) => $s !== '');
        foreach ($statements as $stmt) {
            $pdo->exec($stmt);
        }
        $message .= "[OK] Database initialized (" . ($action === 'force' ? 'reset' : 'fresh') . ").\n";
    } catch (Exception $e) {
        $message .= "[ERROR] " . $e->getMessage() . "\n";
    }
}

try {
    $pdoCheck = getDB();
    $tables = $pdoCheck->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $counts = [];
    foreach ($tables as $tbl) {
        $counts[$tbl] = $pdoCheck->query("SELECT COUNT(*) FROM `$tbl`")->fetchColumn();
    }
} catch (Exception $e) {
    $tables = [];
    $counts = [];
}

$demoAccounts = [
    ['super@example.com', 'admin123', 'Super Admin'],
    ['zainab@example.com', 'manager123', 'Admin'],
    ['steve@example.com', 'manager123', 'Project Manager'],
    ['teleza@example.com', 'manager123', 'Project Manager'],
        ['client@example.com', 'manager123', 'Client'],
    ['ali@example.com', 'manager123', 'Fundi'],
    ['david@example.com', 'manager123', 'Fundi'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SmartUjenzi - Database Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">SmartUjenzi Database Setup</h1>
            <p class="text-gray-500 text-sm mb-6">DB: <code class="bg-gray-100 px-2 py-0.5 rounded"><?= DB_NAME ?></code></p>

            <?php if ($message): ?>
            <div class="bg-gray-900 text-green-300 font-mono text-sm p-4 rounded-xl mb-6 whitespace-pre-wrap"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <form method="post">
                    <input type="hidden" name="action" value="init">
                    <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-colors">Initialize DB</button>
                </form>
                <form method="post" onsubmit="return confirm('Wipe all data and re-import?')">
                    <input type="hidden" name="action" value="force">
                    <button type="submit" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition-colors">Reset DB (destructive)</button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Tables Status</h2>
            <?php if (empty($tables)): ?>
                <p class="text-gray-500">No tables found. Click <strong>Initialize DB</strong> above.</p>
            <?php else: ?>
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 border-b"><th class="pb-2 font-medium">Table</th><th class="pb-2 font-medium">Rows</th></tr></thead>
                    <tbody>
                        <?php foreach ($tables as $tbl): ?>
                        <tr class="border-b border-gray-50"><td class="py-2 text-gray-800"><?= $tbl ?></td><td class="py-2 text-gray-600"><?= $counts[$tbl] ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="bg-white rounded-2xl shadow-lg p-8">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Demo Accounts</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-gray-500 border-b"><th class="pb-2 font-medium">Email</th><th class="pb-2 font-medium">Password</th><th class="pb-2 font-medium">Role</th></tr></thead>
                    <tbody>
                        <?php foreach ($demoAccounts as $a): ?>
                        <tr class="border-b border-gray-50"><td class="py-2 text-gray-800"><?= $a[0] ?></td><td class="py-2 font-mono text-gray-600"><?= $a[1] ?></td><td class="py-2"><span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700"><?= $a[2] ?></span></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-gray-400 mt-4">Access: localhost:8080/login.php</p>
        </div>
    </div>
</body>
</html>
