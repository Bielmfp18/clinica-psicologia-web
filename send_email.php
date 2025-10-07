<?php
// send_email.php (corrigido para Gmail / PHPMailer)
// Requisitos: composer require phpmailer/phpmailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

// --- CONFIGURE AQUI ---
const SMTP_HOST = 'smtp.gmail.com';
const SMTP_USER = 'mente.renovada019@gmail.com';
// Se você colou a app password com espaços (ex: "ujow tqeb mgsl mkyq"),
// eu removo os espaços automaticamente antes de usar.
const SMTP_PASS = 'ujow tqeb mgsl mkyq';
const SMTP_PORT = 587;       // 587 para TLS, 465 para SSL
const SMTP_SECURE = 'tls';   // 'tls' (STARTTLS) quando porta 587
const SMTP_FROM_NAME = 'Mente Renovada';
// -----------------------

function send_verification_email($toEmail, $toName, $subject, $htmlBody, $plainBody = null) {
    $plainBody = $plainBody ?? strip_tags(str_replace(["</p>","<br>"], "\n", $htmlBody));

    $mail = new PHPMailer(true);
    try {
        // Remove espaços na app password caso o usuário tenha copiado com espaços
        $smtpPass = str_replace(' ', '', SMTP_PASS);

        // DEBUG: se precisar ver o log, descomente as duas linhas abaixo.
        // Atenção: não deixe em produção com debug ativo.
        // $mail->SMTPDebug = 2;
        // $mail->Debugoutput = 'html';

        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = SMTP_SECURE; // 'tls' ou 'ssl'
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Em alguns ambientes de teste (localhost) o OpenSSL pode reclamar.
        // Isso reduz a checagem SSL — use apenas para desenvolvimento.
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Use SMTP_USER como remetente (evita rejeição em Gmail)
        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
        // opcional: setar um Reply-To diferente se quiser receber replies
        // $mail->addReplyTo('contato@menterenovada.com', 'Mente Renovada - Suporte');

        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = $plainBody;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log mais completo para diagnosticar (guarda ErrorInfo e mensagem da Exception)
        error_log("PHPMailer error ({$toEmail}) - " . $mail->ErrorInfo . " | Exception: " . $e->getMessage());
        return false;
    }
}
