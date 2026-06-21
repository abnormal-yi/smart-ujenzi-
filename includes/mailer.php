<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/Exception.php';

function sendEmail(string $to, string $subject, string $body): bool {
    $from     = defined('SMTP_FROM') ? SMTP_FROM : 'noreply@smartujenzi.com';
    $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'SmartUjenzi';

    $methods = [];

    // 1: SMTP via configured host
    $methods[] = function() use ($to, $subject, $body, $from, $fromName) {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = defined('SMTP_HOST') ? SMTP_HOST : 'localhost';
        $mail->SMTPAuth   = true;
        $mail->Username   = defined('SMTP_USER') ? SMTP_USER : '';
        $mail->Password   = defined('SMTP_PASS') ? SMTP_PASS : '';
        $port = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $mail->SMTPSecure = $port == 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)$port;
        $mail->Timeout    = 5;
        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
        return true;
    };

    // 2: SMTP via localhost:25 (no auth — cPanel Exim accepts local)
    $methods[] = function() use ($to, $subject, $body, $from, $fromName) {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'localhost';
        $mail->SMTPAuth   = false;
        $mail->Port       = 25;
        $mail->Timeout    = 5;
        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
        return true;
    };

    // 3: SMTP via localhost:587 with auth (Dovecot SASL)
    $methods[] = function() use ($to, $subject, $body, $from, $fromName) {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'localhost';
        $mail->SMTPAuth   = true;
        $mail->Username   = defined('SMTP_USER') ? SMTP_USER : $from;
        $mail->Password   = defined('SMTP_PASS') ? SMTP_PASS : '';
        $mail->Port       = 587;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Timeout    = 5;
        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
        return true;
    };

    // 4: sendmail binary
    $methods[] = function() use ($to, $subject, $body, $from, $fromName) {
        $mail = new PHPMailer(true);
        $mail->isSendmail();
        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
        return true;
    };

    // 5: PHP mail() via PHPMailer
    $methods[] = function() use ($to, $subject, $body, $from, $fromName) {
        $mail = new PHPMailer(true);
        $mail->isMail();
        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
        return true;
    };

    // 6: Direct mail() with -f for cPanel
    $methods[] = function() use ($to, $subject, $body, $from, $fromName) {
        $headers = "From: $fromName <$from>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Return-Path: <$from>\r\n";
        $result = @mail($to, $subject, $body, $headers, "-f $from");
        if ($result) return true;
        return false;
    };

    foreach ($methods as $i => $method) {
        try {
            if ($method()) return true;
        } catch (Exception $e) {
            $lastError = $e->getMessage();
            error_log("MAIL: method " . ($i+1) . " failed: " . $lastError);
        } catch (\Throwable $e) {
            $lastError = $e->getMessage();
            error_log("MAIL: method " . ($i+1) . " exception: " . $lastError);
        }
    }

    return false;
}
