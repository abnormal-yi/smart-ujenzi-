<?php
/**
 * SmartUjenzi - Database Setup Script
 * Usage: php -d extension=pdo_mysql setup.php
 *
 * This script:
 * 1. Creates the database (if it doesn't exist)
 * 2. Executes schema.sql to create tables and insert seed data
 * 3. Displays demo account credentials
 */

$socket = '/var/run/mysqld/mysqld.sock';
$dbname = 'test_smart_ujenzi';

echo "=== SmartUjenzi Database Setup ===\n\n";

try {
    // Connect to MySQL server without specifying a database
    $dsn = $socket
        ? "mysql:unix_socket=$socket;charset=utf8mb4"
        : "mysql:host=localhost;charset=utf8mb4";

    $pdo = new PDO($dsn, '', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Create the application database with utf8mb4 support
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "[OK] Database '$dbname' created or already exists\n";

    // Switch to the application database
    $pdo->exec("USE `$dbname`");

    // Read the schema SQL file and strip the CREATE DATABASE/USE and comment lines
    // since they were already handled above
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    $sql = preg_replace('/^CREATE DATABASE.*?;\s*/im', '', $sql);
    $sql = preg_replace('/^USE .*?;\s*/im', '', $sql);
    $sql = preg_replace('/^-- .*$/m', '', $sql);

    // Split the SQL into individual statements and execute each one
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
    // Display any database connection or execution errors
    echo "[ERROR] " . $e->getMessage() . "\n";
    exit(1);
}
