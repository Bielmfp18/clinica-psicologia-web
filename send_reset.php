<?php
// SEND RESET

session_name('Mente_Renovada');
session_start();

include "conn/conexao.php";   // sua conexão PDO que define $conn
require 'send_email.php';    // aqui está o PHPMailer (vendor/autoload + função send_verification_email)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.php');
  exit;
}

$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
if (!$email) {
  $_SESSION['flash'] = ['type'=>'danger','message'=>'Informe um email válido.'];
  header('Location: index.php?login=1');
  exit;
}

// Busca psicólogo
$stmt = $conn->prepare("SELECT id, ativo, nome FROM psicologo WHERE email = :email LIMIT 1");
$stmt->bindParam(':email', $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || (int)$user['ativo'] !== 1) {
  // Segurança: não informar se o email existe. Exibe mensagem genérica.
  $_SESSION['flash'] = ['type'=>'success','message'=>'Se o email estiver cadastrado enviaremos instruções para redefinir a senha.'];
  header('Location: index.php');
  exit;
}

// Rate limit básico: impedir muitos pedidos consecutivos (ex.: última solicitação)
$psicologo_id = (int)$user['id'];
$now = new DateTime();

// checar se já existe um token não usado e dentro do prazo
$stmt = $conn->prepare("SELECT created_at FROM password_resets WHERE psicologo_id = :id AND used = 0 ORDER BY created_at DESC LIMIT 1");
$stmt->bindParam(':id', $psicologo_id);
$stmt->execute();
$last = $stmt->fetch(PDO::FETCH_ASSOC);
if ($last) {
  $created = new DateTime($last['created_at']);
  $interval = $now->getTimestamp() - $created->getTimestamp();
  if ($interval < 60) { // se pediu há menos de 60s bloqueia
    $_SESSION['flash'] = ['type'=>'danger','message'=>'Aguarde um minuto antes de solicitar outro email.'];
    header('Location: index.php');
    exit;
  }
}

// gera token seguro
$token = bin2hex(random_bytes(32)); // 64 hex chars
// se você usa PEPPER, adicione aqui (opcional): $token_hash = hash('sha256', $token . $PEPPER);
$token_hash = hash('sha256', $token);
$expires_at = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

// grava na tabela password_resets
$ins = $conn->prepare("INSERT INTO password_resets (psicologo_id, token_hash, expires_at) VALUES (:pid, :th, :exp)");
$ins->bindParam(':pid', $psicologo_id, PDO::PARAM_INT);
$ins->bindParam(':th', $token_hash);
$ins->bindParam(':exp', $expires_at);
$ins->execute();

// prepara link (use seu domínio/rota correta)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL);
$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$resetLink = sprintf('%s://%s%s/senha_redefinir.php?token=%s', $scheme, $host, $path, urlencode($token));

// montar mensagem HTML para o PHPMailer
$userName = $user['nome'] ?? '';
$subject = "Redefinição de senha - Mente Renovada";
$htmlBody = "
  <p>Olá " . htmlspecialchars($userName) . ",</p>
  <p>Recebemos uma solicitação para redefinir sua senha.</p>
  <p>Clique no link abaixo para criar uma nova senha (válido por 1 hora):</p>
  <p><a href=\"" . htmlspecialchars($resetLink) . "\">Redefinir minha senha</a></p>
  <p>Se você não solicitou, ignore este e-mail.</p>
  <br>
  <p>Atenciosamente,<br>Mente Renovada</p>
";

// envia via PHPMailer usando a função do send_email.php
$sent = false;
try {
    $sent = send_verification_email($email, $userName, $subject, $htmlBody);
    if (!$sent) {
        error_log("send_reset: PHPMailer retornou false ao enviar para {$email}");
    }
} catch (Exception $e) {
    error_log("send_reset: exceção ao chamar send_verification_email: " . $e->getMessage());
    $sent = false;
}

// Feedback genérico para o usuário (sempre igual, evita enumeração)
$_SESSION['flash'] = ['type'=>'success','message'=>'Se o email estiver cadastrado enviaremos instruções para redefinir a senha.'];
header('Location: index.php');
exit;
