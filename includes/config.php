<?php
// Load local overrides if exists (for shared hosting without env vars)
$localConfig = __DIR__ . '/../config.local.php';
if (file_exists($localConfig)) {
    require_once $localConfig;
}

// Database config: use local overrides or env vars, fallback to local defaults
$dbHost    = defined('OVERRIDE_DB_HOST') ? OVERRIDE_DB_HOST : (getenv('DB_HOST') ?: 'localhost');
$dbName    = defined('OVERRIDE_DB_NAME') ? OVERRIDE_DB_NAME : (getenv('DB_NAME') ?: 'test_smart_ujenzi');
$dbUser    = defined('OVERRIDE_DB_USER') ? OVERRIDE_DB_USER : (getenv('DB_USER') ?: '');
$dbPass    = defined('OVERRIDE_DB_PASS') ? OVERRIDE_DB_PASS : (getenv('DB_PASS') ?: '');
$dbSocket  = defined('OVERRIDE_DB_SOCKET') ? OVERRIDE_DB_SOCKET : (getenv('DB_SOCKET') ?: '/var/run/mysqld/mysqld.sock');
$appEnv    = getenv('APP_ENV') ?: 'local'; // local or production

// Error reporting: hide details in production
if ($appEnv === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Session config (must be set before session_start)
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_httponly', '1');
if ($appEnv === 'production') {
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', '7200');
} else {
    ini_set('session.gc_maxlifetime', '86400');
}
session_start();

define('DB_HOST', $dbHost);
define('DB_NAME', $dbName);
define('DB_USER', $dbUser);
define('DB_PASS', $dbPass);
define('DB_SOCKET', $dbSocket);
define('APP_ENV', $appEnv);

define('SMTP_HOST', defined('OVERRIDE_SMTP_HOST') ? OVERRIDE_SMTP_HOST : 'trisa.luxurywebs.com');
define('SMTP_PORT', defined('OVERRIDE_SMTP_PORT') ? OVERRIDE_SMTP_PORT : 587);
define('SMTP_USER', defined('OVERRIDE_SMTP_USER') ? OVERRIDE_SMTP_USER : 'noreply@trisa.luxurywebs.com');
define('SMTP_PASS', defined('OVERRIDE_SMTP_PASS') ? OVERRIDE_SMTP_PASS : '');
define('SMTP_FROM', defined('OVERRIDE_SMTP_FROM') ? OVERRIDE_SMTP_FROM : 'noreply@trisa.luxurywebs.com');
define('SMTP_FROM_NAME', defined('OVERRIDE_SMTP_FROM_NAME') ? OVERRIDE_SMTP_FROM_NAME : 'SmartUjenzi');

define('APP_URL', defined('OVERRIDE_APP_URL') ? OVERRIDE_APP_URL : 'https://trisa.luxurywebs.com');

define('GOOGLE_CLIENT_ID', defined('OVERRIDE_GOOGLE_CLIENT_ID') ? OVERRIDE_GOOGLE_CLIENT_ID : '');
define('GOOGLE_CLIENT_SECRET', defined('OVERRIDE_GOOGLE_CLIENT_SECRET') ? OVERRIDE_GOOGLE_CLIENT_SECRET : '');
define('GOOGLE_REDIRECT_URI', (defined('APP_URL') ? APP_URL : 'https://trisa.luxurywebs.com') . '/google-callback.php');

define('ENCRYPTION_KEY', defined('OVERRIDE_ENCRYPTION_KEY') ? OVERRIDE_ENCRYPTION_KEY : 'sM4rtUj3nz1K3y!2026#Secr3t');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = DB_SOCKET && DB_USER === ''
                ? "mysql:unix_socket=" . DB_SOCKET . ";dbname=" . DB_NAME . ";charset=utf8mb4"
                : "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            if (APP_ENV === 'production') {
                die('Database connection failed. Please check your configuration.');
            }
            throw $e;
        }
    }
    return $pdo;
}

function isAuthenticated(): bool {
    return isset($_SESSION['user_id']);
}

function requireAuth(): void {
    if (!isAuthenticated()) {
        header('Location: login.php');
        exit;
    }
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}
