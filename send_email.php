<?php
// SEND EMAIL
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 

function send_verification_email(string $toEmail, string $toName, string $subject, string $htmlBody): bool
{
    $mail = new PHPMailer(true);

    $mail->CharSet = 'UTF-8';

    try {
        // Servidor SMTP - ajustar para seu provedor
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // ex: smtp.gmail.com (verificar configs)
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mente.renovada019@gmail.com';
        $mail->Password   = 'xlsl lfay ymaf jolm';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // ou PHPMailer::ENCRYPTION_SMTPS
        $mail->Port       = 587; // 465 para SMTPS

        $mail->setFrom('mente.renovada019@gmail.com', 'Mente Renovada');
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace('<br>', "\n", $htmlBody));

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $mail->ErrorInfo);
        return false;
    }
}
