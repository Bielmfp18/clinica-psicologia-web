<?php
// reset_password.php
include "conn/conexao.php";

if (!isset($_GET['token'])) {
    die("Token inválido.");
}

$token = $_GET['token'];

// 1. Busca token na tabela reset_password
$stmt = $conn->prepare("SELECT rp.user_id, rp.expires, p.id FROM reset_password rp 
                        JOIN psicologo p ON rp.user_id = p.id 
                        WHERE rp.token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Token inválido ou expirado.");
}

$data = $result->fetch_assoc();

// 2. Checa validade
if (strtotime($data['expires']) < time()) {
    die("Token expirado. Solicite novamente.");
}

$userId = $data['user_id'];

// 3. Se formulário enviado, troca senha
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $novaSenha = password_hash($_POST["senha"], PASSWORD_DEFAULT);

    // Atualiza senha
    $stmt = $conn->prepare("UPDATE psicologo SET senha = ? WHERE id = ?");
    $stmt->bind_param("si", $novaSenha, $userId);
    $stmt->execute();

    // Remove token após uso
    $stmt = $conn->prepare("DELETE FROM reset_password WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();

    echo "<script>alert('Senha alterada com sucesso!');window.location.href='index.php';</script>";
    exit;
}
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Redefinir senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="mn-logo">
        <img src="image/MENTE_RENOVADA-LOGO.png" alt="Mente Renovada">
    </div>

    <div class="container d-flex justify-content-center align-items-center" style="min-height:100vh;">
        <div class="card p-4 shadow" style="max-width:400px;width:100%;">
            <h4 class="mb-3 text-center" style="color:#DBA632;">Defina sua nova senha</h4>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nova senha</label>
                    <input type="password" name="senha" class="form-control" required>
                </div>
                <button type="submit" class="btn w-100" style="background-color:#DBA632;color:#fff;">Redefinir</button>
            </form>
        </div>
    </div>
</body>

</html>