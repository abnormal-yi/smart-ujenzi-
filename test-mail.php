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

// Test 3: PHP mail() function to EXTERNAL address (your Gmail)
echo "--- Test 3: PHP mail() to hoseaayub322@gmail.com ---\n";
if (function_exists('mail')) {
    echo "mail() exists\n";
    $testTo = 'hoseaayub322@gmail.com';
    $headers = "From: $fromName <$from>\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
    $headers .= "Return-Path: <$from>\r\n";
    $sent = @mail($testTo, 'Test from SmartUjenzi', '<h2>Test</h2><p>If you see this, PHP mail() works!</p>', $headers, "-f $from");
    echo $sent ? "mail() returned true (handed to MTA)\n" : "mail() returned false\n";
} else {
    echo "mail() is DISABLED\n";
}
echo "\n";

// Test 4: mail() to your own from address
echo "--- Test 4: PHP mail() to $from ---\n";
$headers2 = "From: $fromName <$from>\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\n";
$headers2 .= "Return-Path: <$from>\r\n";
$sent2 = @mail($from, 'Test to self', '<h2>Test</h2><p>Local delivery test</p>', $headers2, "-f $from");
echo $sent2 ? "mail() returned true\n" : "mail() returned false\n";
echo "\n";

// Test 5: PHPMailer SMTP via configured host
echo "--- Test 5: PHPMailer SMTP ($host:$port) ---\n";
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
    $mail->setFrom($from, $fromName);
    $mail->addAddress('hoseaayub322@gmail.com');
    $mail->isHTML(true);
    $mail->Subject = 'SMTP Test';
    $mail->Body = '<p>SMTP works</p>';
    $mail->send();
    echo "SENT via SMTP\n";
} catch (Exception $e) {
    echo "FAILED: " . $mail->ErrorInfo . "\n";
}
echo "\n";

// Test 6: PHPMailer localhost:25 no auth (to external)
echo "--- Test 6: localhost:25 no auth (to Gmail) ---\n";
$mail2 = new PHPMailer(true);
try {
    $mail2->isSMTP();
    $mail2->Host = 'localhost';
    $mail2->SMTPAuth = false;
    $mail2->Port = 25;
    $mail2->Timeout = 5;
    $mail2->setFrom($from, $fromName);
    $mail2->addAddress('hoseaayub322@gmail.com');
    $mail2->isHTML(true);
    $mail2->Subject = 'Localhost Test';
    $mail2->Body = '<p>Localhost works</p>';
    $mail2->send();
    echo "SENT via localhost:25\n";
} catch (Exception $e) {
    echo "FAILED: " . $mail2->ErrorInfo . "\n";
}
echo "\n";

// Test 7: PHPMailer localhost:587 with auth
echo "--- Test 7: localhost:587 with auth (to Gmail) ---\n";
$mail3 = new PHPMailer(true);
try {
    $mail3->isSMTP();
    $mail3->Host = 'localhost';
    $mail3->SMTPAuth = true;
    $mail3->Username = $user;
    $mail3->Password = $pass;
    $mail3->Port = 587;
    $mail3->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail3->Timeout = 5;
    $mail3->setFrom($from, $fromName);
    $mail3->addAddress('hoseaayub322@gmail.com');
    $mail3->isHTML(true);
    $mail3->Subject = 'Localhost 587 Test';
    $mail3->Body = '<p>Localhost 587 works</p>';
    $mail3->send();
    echo "SENT via localhost:587\n";
} catch (Exception $e) {
    echo "FAILED: " . $mail3->ErrorInfo . "\n";
}

echo "\n=== Diagnostic Complete ===";
