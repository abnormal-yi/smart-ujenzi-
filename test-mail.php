<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/SMTP.php';
require_once __DIR__ . '/vendor/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: text/plain');

echo "=== SmartUjenzi Email Diagnostic ===\n\n";
echo "PHP version: " . phpversion() . "\n";
echo "sendmail_path: " . (ini_get('sendmail_path') ?: 'NOT SET') . "\n";
echo "disable_functions: " . (ini_get('disable_functions') ?: 'none') . "\n\n";

$from = defined('SMTP_FROM') ? SMTP_FROM : 'noreply@smartujenzi.com';
$fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'SmartUjenzi';
$host = defined('SMTP_HOST') ? SMTP_HOST : 'localhost';
$port = defined('SMTP_PORT') ? SMTP_PORT : 587;
$user = defined('SMTP_USER') ? SMTP_USER : '';
$pass = defined('SMTP_PASS') ? SMTP_PASS : '';

echo "SMTP Config:\n";
echo "  Host: $host\n";
echo "  Port: $port\n";
echo "  User: $user\n";
echo "  Pass: " . str_repeat('*', strlen($pass)) . "\n";
echo "  From: $from\n\n";

// Test 1: DNS resolution
echo "--- Test 1: DNS lookup for $host ---\n";
$ip = gethostbyname($host);
echo $ip !== $host ? "RESOLVED: $ip\n\n" : "FAILED: Cannot resolve\n\n";

// Test 2: Socket connection
echo "--- Test 2: Socket connection to $host:$port ---\n";
$sock = @fsockopen($host, $port, $errno, $errstr, 5);
if ($sock) {
    echo "CONNECTED\n";
    fclose($sock);
} else {
    echo "FAILED: ($errno) $errstr\n";
}
echo "\n";

// Test 3: PHP mail() function
echo "--- Test 3: PHP mail() function ---\n";
if (function_exists('mail')) {
    echo "mail() exists\n";
    $testTo = $from;
    $headers = "From: $fromName <$from>\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
    $sent = @mail($testTo, 'Test from SmartUjenzi', '<h2>Test</h2><p>If you see this, PHP mail() works!</p>', $headers);
    echo $sent ? "MAIL SENT to $testTo\n" : "mail() returned false\n";
} else {
    echo "mail() is DISABLED\n";
}
echo "\n";

// Test 4: PHPMailer SMTP
echo "--- Test 4: PHPMailer SMTP ---\n";
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $host;
    $mail->SMTPAuth = true;
    $mail->Username = $user;
    $mail->Password = $pass;
    $mail->Port = $port;
    $mail->SMTPSecure = $port == 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Timeout = 5;
    $mail->SMTPKeepAlive = false;
    $mail->setFrom($from, $fromName);
    $mail->addAddress($from);
    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test';
    $mail->Body = '<p>SMTP works</p>';
    $mail->send();
    echo "SMTP SENT via PHPMailer\n";
} catch (Exception $e) {
    echo "SMTP FAILED: " . $mail->ErrorInfo . "\n";
}
echo "\n";

// Test 5: Try localhost:25 without auth (common cPanel)
echo "--- Test 5: localhost:25 no auth ---\n";
$mail2 = new PHPMailer(true);
try {
    $mail2->isSMTP();
    $mail2->Host = 'localhost';
    $mail2->SMTPAuth = false;
    $mail2->Port = 25;
    $mail2->Timeout = 5;
    $mail2->setFrom($from, $fromName);
    $mail2->addAddress($from);
    $mail2->isHTML(true);
    $mail2->Subject = 'Localhost Test';
    $mail2->Body = '<p>Localhost works</p>';
    $mail2->send();
    echo "localhost:25 SENT\n";
} catch (Exception $e) {
    echo "localhost:25 FAILED: " . $mail2->ErrorInfo . "\n";
}

echo "\n=== Diagnostic Complete ===";
