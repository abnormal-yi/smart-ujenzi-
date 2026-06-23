<?php
function sendEmail(string $to, string $subject, string $body): bool {
    $from     = defined('SMTP_FROM') ? SMTP_FROM : 'noreply@trisa.luxurywebs.com';
    $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'SmartUjenzi';
    $lastError = '';

    $methods = [];

    $methods[] = function() use ($to, $subject, $body, $from, $fromName) {
        $headers = "From: $fromName <$from>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "Return-Path: <$from>\r\n";
        $result = @mail($to, $subject, $body, $headers, "-f $from");
        if ($result) return true;
        return false;
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
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isMail();
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