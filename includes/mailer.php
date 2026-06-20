<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/Exception.php';

function sendEmail(string $to, string $subject, string $body): bool {
    $from     = defined('SMTP_FROM') ? SMTP_FROM : 'noreply@smartujenzi.com';
    $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'SmartUjenzi';

    // 1: Try SMTP
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
        error_log("MAIL: SMTP failed: " . $mail->ErrorInfo);
    }

    // 2: Try sendmail binary directly
    $mail3 = new PHPMailer(true);
    try {
        $mail3->isSendmail();
        $mail3->setFrom($from, $fromName);
        $mail3->addAddress($to);
        $mail3->isHTML(true);
        $mail3->Subject = $subject;
        $mail3->Body    = $body;
        $mail3->send();
        return true;
    } catch (Exception $e) {
        error_log("MAIL: sendmail failed: " . $mail3->ErrorInfo);
    }

    // 3: Try PHP mail() via PHPMailer
    $mail2 = new PHPMailer(true);
    try {
        $mail2->isMail();
        $mail2->setFrom($from, $fromName);
        $mail2->addAddress($to);
        $mail2->isHTML(true);
        $mail2->Subject = $subject;
        $mail2->Body    = $body;
        $mail2->send();
        return true;
    } catch (Exception $e) {
        error_log("MAIL: mail() failed: " . $mail2->ErrorInfo);
    }

    // 4: Try direct mail() as last resort
    try {
        ini_set('sendmail_from', $from);
        $headers = "From: $fromName <$from>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $result = mail($to, $subject, $body, $headers);
        if ($result) return true;
        error_log("MAIL: direct mail() returned false");
    } catch (\Throwable $e) {
        error_log("MAIL: direct mail() exception: " . $e->getMessage());
    }

    return false;
}
