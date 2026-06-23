<?php
require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/Exception.php';

function sendEmail(string $to, string $subject, string $body): bool {
    $from     = defined('SMTP_FROM') ? SMTP_FROM : 'noreply@trisa.luxurywebs.com';
    $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'SmartUjenzi';
    $lastError = '';

    $methods = [];

    $methods[] = function() use ($to, $subject, $body, $from, $fromName) {
        if (!defined('SMTP_USER') || !SMTP_USER || !defined('SMTP_PASS') || !SMTP_PASS) return false;
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->Port       = SMTP_PORT;
        $mail->SMTPSecure = SMTP_PORT == 465 ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->SMTPOptions = ['ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true]];
        $mail->Timeout    = 10;
        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
        return true;
    };

    $methods[] = function() use ($to, $subject, $body, $from, $fromName) {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
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

    $methods[] = function() use ($to, $subject, $body, $from, $fromName) {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSendmail();
        $mail->setFrom($from, $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
        return true;
    };

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
            if ($method()) {
                error_log("MAIL: method " . ($i+1) . " succeeded to $to");
                return true;
            }
        } catch (Exception $e) {
            $lastError = $e->getMessage();
            error_log("MAIL: method " . ($i+1) . " failed: $lastError");
        } catch (\Throwable $e) {
            $lastError = $e->getMessage();
            error_log("MAIL: method " . ($i+1) . " exception: $lastError");
        }
    }

    error_log("MAIL: ALL methods failed for $to. Last error: $lastError");
    return false;
}
