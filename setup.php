<?php
// SmartUjenzi - Database Setup Script
// Usage: php -d extension=pdo_mysql setup.php
//   To re-run after already set up: php setup.php force=1

// Only allow CLI execution for security
if (php_sapi_name() !== 'cli') {
    die('Setup can only be run from command line. Usage: php setup.php');
}

$force = isset($argv[1]) && $argv[1] === 'force=1';

$socket = getenv('DB_SOCKET') ?: '/var/run/mysqld/mysqld.sock';
$dbname = getenv('DB_NAME') ?: 'test_smart_ujenzi';

echo "=== SmartUjenzi Database Setup ===\n\n";

try {
    $dsn = $socket
        ? "mysql:unix_socket=$socket;charset=utf8mb4"
        : "mysql:host=localhost;charset=utf8mb4";

    $pdo = new PDO($dsn, '', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Check if DB already has tables
    $exists = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'")->fetch();
    $hasTables = false;
    if ($exists) {
        $pdo->exec("USE `$dbname`");
        $tables = $pdo->query("SHOW TABLES")->fetchAll();
        $hasTables = count($tables) > 0;
    }

    if ($hasTables && !$force) {
        echo "[OK] Database '$dbname' already set up with " . count($tables) . " tables.\n";
        echo "      To re-run (will wipe data): php setup.php force=1\n\n";
        exit(0);
    }

    if ($hasTables && $force) {
        echo "[WARN] Force re-run: dropping all tables first...\n";
        $pdo->exec("DROP DATABASE `$dbname`");
    }

    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "[OK] Database '$dbname' created or already exists\n";

    $pdo->exec("USE `$dbname`");

    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    $sql = preg_replace('/^CREATE DATABASE.*?;\s*/im', '', $sql);
    $sql = preg_replace('/^USE .*?;\s*/im', '', $sql);
    $sql = preg_replace('/^-- .*$/m', '', $sql);

    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($s) => !empty($s)
    );

    foreach ($statements as $stmt) {
        $pdo->exec($stmt);
    }

    echo "[OK] Tables created and seed data inserted\n";
    echo "\n--- Demo Accounts ---\n";
    echo "admin@example.com / admin123 (Admin)\n";
    echo "steve@example.com / pass123 (Manager)\n";
    echo "teleza@example.com / pass123 (Supervisor)\n";
    echo "constructor@example.com / pass123 (Constructor)\n";
    echo "mteja@example.com / pass123 (Customer)\n";
    echo "\n--- Start Server ---\n";
    echo "php start.sh\n";
    echo "  or: php -d extension=pdo_mysql -S localhost:8000\n\n";

} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
