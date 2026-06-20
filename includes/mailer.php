<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/Exception.php';

function sendEmail(string $to, string $subject, string $body): bool {
    // Try SMTP first (5s timeout), fall back to PHP mail()
    $from     = defined('SMTP_FROM') ? SMTP_FROM : 'noreply@smartujenzi.com';
    $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'SmartUjenzi';

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'localhost';
        $mail->SMTPAuth   = true;
        $mail->Username   = defined('SMTP_USER') ? SMTP_USER : '';
        $mail->Password   = defined('SMTP_PASS') ? SMTP_PASS : '';
        $port = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $mail->SMTPSecure = $port == 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $port;
        $mail->Timeout    = 5;
        $mail->SMTPKeepAlive = false;

        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("SMTP failed, falling back to mail(): " . $mail->ErrorInfo);
    }

    // Fallback: PHP mail() function (works on all shared hosting)
    try {
        $mail2 = new PHPMailer(true);
        $mail2->isMail();
        $mail2->setFrom($from, $fromName);
        $mail2->addAddress($to);
        $mail2->isHTML(true);
        $mail2->Subject = $subject;
        $mail2->Body    = $body;
        $mail2->send();
        return true;
    } catch (Exception $e) {
        error_log("mail() fallback also failed: " . $mail2->ErrorInfo);
        return false;
    }
}
