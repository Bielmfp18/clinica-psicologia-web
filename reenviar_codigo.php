<?php
//REENVIAR CÓDIGO 

session_name('Mente_Renovada');
session_start();
require 'conn/conexao.php';
require 'send_email.php';

$email = filter_var($_GET['email'] ?? '', FILTER_VALIDATE_EMAIL);
if (!$email) {
    header('Location: index.php');
    exit;
}

try {
    // Busca o usuário
    $stmt = $conn->prepare("SELECT id, nome, verification_sent_at FROM psicologo WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        $_SESSION['flash'] = ['type'=>'danger','message'=>'E-mail não encontrado.'];
        header('Location: index.php');
        exit;
    }

    // Limitar reenvio (ex: 1 por minuto)
    if (!empty($row['verification_sent_at'])) {
        $last = new DateTime($row['verification_sent_at']);
        $diff = (new DateTime())->getTimestamp() - $last->getTimestamp();
        if ($diff < 60) {
            $_SESSION['flash'] = ['type'=>'danger','message'=>'Aguarde antes de reenviar o código (1 minuto).'];
            header('Location: verificar_email.php?email=' . urlencode($email));
            exit;
        }
    }

    // Gera novo código e atualiza
    $codigo = random_int(100000, 999999);
    $codigoHash = password_hash((string)$codigo, PASSWORD_DEFAULT);
    $expires = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');
    $now = (new DateTime())->format('Y-m-d H:i:s');

    $upd = $conn->prepare("UPDATE psicologo SET verification_code_hash = :vhash, verification_expires = :vexp, verification_attempts = 0, verification_sent_at = :sent_at WHERE id = :id");
    $upd->bindParam(':vhash', $codigoHash);
    $upd->bindParam(':vexp', $expires);
    $upd->bindParam(':sent_at', $now);
    $upd->bindParam(':id', $row['id'], PDO::PARAM_INT);
    $upd->execute();

    $subject = "Reenvio do código de verificação - Mente Renovada";
    $body = "Olá {$row['nome']},<br><br>Seu novo código de verificação é: <b>{$codigo}</b><br>Expira em 15 minutos.<br><br>Atenciosamente,<br>Mente Renovada";
    send_verification_email($email, $row['nome'], $subject, $body);

    $_SESSION['flash'] = ['type'=>'success','message'=>'Código reenviado para seu e-mail.'];
    header('Location: verificar_email.php?email=' . urlencode($email));
    exit;

} catch (Exception $e) {
    $_SESSION['flash'] = ['type'=>'danger','message'=>'Erro ao reenviar: ' . $e->getMessage()];
    header('Location: verificar_email.php?email=' . urlencode($email));
    exit;
}
