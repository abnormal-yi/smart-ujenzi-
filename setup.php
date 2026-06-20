<?php
// SmartUjenzi - Database Setup Script
// CLI: php -d extension=pdo_mysql setup.php [force=1]
// Web: http://site/setup.php?key=admin123
// Web force: http://site/setup.php?key=admin123&force=1

$isCLI = php_sapi_name() === 'cli';
$allowedKey = 'admin123';

if (!$isCLI) {
    require_once __DIR__ . '/includes/config.php';
}

if ($isCLI) {
    $force = isset($argv[1]) && $argv[1] === 'force=1';
    $socket = getenv('DB_SOCKET') ?: '/var/run/mysqld/mysqld.sock';
    $dbname = getenv('DB_NAME') ?: 'test_smart_ujenzi';
    echo "=== SmartUjenzi Database Setup ===\n\n";
    out(null, true);
} else {
    if (empty($_GET['key']) || $_GET['key'] !== $allowedKey) {
        header('HTTP/1.0 403 Forbidden');
        echo '<h1>403 Forbidden</h1><p>Add <code>?key=admin123</code> to the URL.</p>';
        exit;
    }
    $force = isset($_GET['force']);
    $dbname = DB_NAME;
    out('<h2>SmartUjenzi Database Setup</h2>', false);
}

try {
    if ($isCLI) {
        $dsn = $socket
            ? "mysql:unix_socket=$socket;charset=utf8mb4"
            : "mysql:host=localhost;charset=utf8mb4";
        $pdo = new PDO($dsn, '', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    } else {
        $dsn = DB_SOCKET && DB_USER === ''
            ? "mysql:unix_socket=" . DB_SOCKET . ";charset=utf8mb4"
            : "mysql:host=" . DB_HOST . ";charset=utf8mb4";
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    $exists = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'")->fetch();
    $hasTables = false;
    if ($exists) {
        $pdo->exec("USE `$dbname`");
        $tables = $pdo->query("SHOW TABLES")->fetchAll();
        $hasTables = count($tables) > 0;
    }

    if ($hasTables && !$force) {
        out("[OK] Database '$dbname' already set up with " . count($tables) . " tables.\n" . ($isCLI ? "      To re-run (will wipe data): php setup.php force=1\n" : "<br><a href='?key=$allowedKey&amp;force=1'>Force re-run (wipe data)</a>"), $isCLI);
        if (!$isCLI) echo '<p><a href="login.php">Go to Login →</a></p>';
        exit(0);
    }

    if ($hasTables && $force) {
        out("[WARN] Force re-run: dropping all tables...", $isCLI);
        $pdo->exec("DROP DATABASE `$dbname`");
    }

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    out("[OK] Database '$dbname' created or already exists", $isCLI);

    $pdo->exec("USE `$dbname`");

    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    $sql = preg_replace('/^CREATE DATABASE.*?;\s*/im', '', $sql);
    $sql = preg_replace('/^USE .*?;\s*/im', '', $sql);
    $sql = preg_replace('/^-- .*$/m', '', $sql);

    $statements = array_filter(array_map('trim', explode(';', $sql)), fn($s) => $s !== '');
    foreach ($statements as $stmt) {
        $pdo->exec($stmt);
    }

    out("[OK] Tables created and seed data inserted", $isCLI);

    $locationSeed = file_get_contents(__DIR__ . '/database/seed_locations.sql');
    $locStatements = array_filter(array_map('trim', explode(';', $locationSeed)), fn($s) => $s !== '');
    foreach ($locStatements as $stmt) {
        $pdo->exec($stmt);
    }
    out("[OK] Tanzania location data seeded", $isCLI);
    out("", $isCLI);
    out("--- Demo Accounts ---", $isCLI);
    out("super@example.com / admin123 (Super Admin)", $isCLI);
    out("zainab@example.com / manager123 (Admin)", $isCLI);
    out("steve@example.com / manager123 (Project Manager)", $isCLI);
    out("teleza@example.com / manager123 (Project Manager)", $isCLI);
    out("mteja@example.com / manager123 (Client)", $isCLI);
    out("ali@example.com / manager123 (Fundi)", $isCLI);
    out("david@example.com / manager123 (Fundi)", $isCLI);

    if ($isCLI) {
        echo "\n--- Start Server ---\nphp start.sh\n  or: php -d extension=pdo_mysql -S localhost:8000\n\n";
    } else {
        echo '<p><a href="login.php" class="btn">Go to Login</a></p>';
    }

} catch (Exception $e) {
    out("[ERROR] " . $e->getMessage(), $isCLI);
    if (!$isCLI) echo '<p>Check your <code>config.local.php</code> database settings.</p>';
    exit(1);
}

function out($msg, $isCLI) {
    if ($isCLI) {
        echo $msg . "\n";
    } else {
        echo nl2br(htmlspecialchars($msg)) . "\n";
    }
}
