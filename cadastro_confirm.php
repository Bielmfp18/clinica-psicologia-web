<?php
// CADASTRO CONFIRM

session_name('Mente_Renovada');
session_start();

require 'conn/conexao.php'; // sua conexão PDO
require 'send_email.php'; // função que envia e-mail (ver abaixo)

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Sanitização básica
$nome  = trim($_POST['nome'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$senha = trim($_POST['senha'] ?? '');
$CRP   = trim($_POST['CRP'] ?? '');

if (!$email || empty($nome) || empty($senha) || empty($CRP)) {
    $_SESSION['flash'] = ['type'=>'danger','message'=>'Preencha todos os campos corretamente.'];
    header('Location: index.php?Cadastro=1');
    exit;
}

// Hash da senha e do CRP como no seu padrão
$senhaHash = password_hash($senha, PASSWORD_DEFAULT);
$CRPHash   = password_hash($CRP, PASSWORD_DEFAULT);

try {
    // 1) Insere o psicólogo com ativo = 0
    // Se tiver a procedure que aceita psativo, passe 0; caso contrário insira diretamente.
    $sql = "CALL ps_psicologo_insert(:psnome, :psemail, :pssenha, :psCRP, :psativo)";
    $stmt = $conn->prepare($sql);
    $ativo = 0; // importante: criar inativo até confirmar
    $stmt->bindParam(':psnome', $nome);
    $stmt->bindParam(':psemail', $email);
    $stmt->bindParam(':pssenha', $senhaHash);
    $stmt->bindParam(':psCRP', $CRPHash);
    $stmt->bindParam(':psativo', $ativo, PDO::PARAM_INT);
    $ok = $stmt->execute();
    $stmt->closeCursor();

    if (!$ok) {
        throw new Exception('Erro ao inserir registro.');
    }

    // 2) Gera código de 6 dígitos e armazena hash + expiração
    $codigo = random_int(100000, 999999);
    $codigoHash = password_hash((string)$codigo, PASSWORD_DEFAULT);
    $expires = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');
    $now = (new DateTime())->format('Y-m-d H:i:s');

    // Atualiza o registro do psicólogo (por email)
    $upd = $conn->prepare("
      UPDATE psicologo
      SET verification_code_hash = :vhash,
          verification_expires = :vexp,
          verification_attempts = 0,
          verification_sent_at = :sent_at,
          ativo = 0
      WHERE email = :email
      LIMIT 1
    ");
    $upd->bindParam(':vhash', $codigoHash);
    $upd->bindParam(':vexp', $expires);
    $upd->bindParam(':sent_at', $now);
    $upd->bindParam(':email', $email);
    $upd->execute();

    $sent = send_verification_email($email, $nome, $subject, $body);

    if (!$sent) {
        // opcional: registrar log, permitir reenvio
        $_SESSION['flash'] = ['type'=>'danger','message'=>'Não foi possível enviar o e-mail de verificação. Entre em contato com o suporte.'];
        header('Location: index.php?Cadastro=1');
        exit;
    }

    // 4) Redireciona para a página de inserir código
    $_SESSION['flash'] = ['type'=>'success','message'=>'Código de verificação enviado ao seu e-mail.'];
    header('Location: verificar_email.php?email=' . urlencode($email));
    exit;

} catch (Exception $e) {
    $_SESSION['flash'] = ['type'=>'danger','message'=>'Erro no servidor: ' . $e->getMessage()];
    header('Location: index.php?Cadastro=1');
    exit;
}


