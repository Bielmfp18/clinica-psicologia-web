<?php
// HISTÓRICO

// Configurações de fuso horário
date_default_timezone_set('America/Sao_Paulo');

// Debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'conn/conexao.php';
// Inclui funções de histórico
include 'funcao_historico.php';

// Sessão e validação de login
session_name('Mente_Renovada');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['login_admin'])) {
    echo "<script>
        alert('Você precisa estar logado para acessar essa página.');
        window.location.href = 'index.php';
    </script>";
    exit;
}

// Busca dados do psicólogo
$email_psicologo = $_SESSION['login_admin'];
$stmt_user = $conn->prepare(
    "SELECT id, nome FROM psicologo WHERE email = :email LIMIT 1"
);
$stmt_user->bindParam(':email', $email_psicologo);
$stmt_user->execute();
$usuario = $stmt_user->fetch(PDO::FETCH_ASSOC);
if (!$usuario) die('Usuário não encontrado.');

// Processa exclusão individual ou limpeza total
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Limpeza total
    if (isset($_POST['clear_all'])) {
        $conn->prepare("DELETE FROM historico WHERE psicologo_id = :id")
             ->execute([':id' => $usuario['id']]);
        header('Location: ' . $_SERVER['PHP_SELF']); exit;
    }
    // Exclusão individual
    if (isset($_POST['delete_id'])) {
        apagarHistorico($conn, (int)$_POST['delete_id']);
        header('Location: ' . $_SERVER['PHP_SELF']); exit;
    }
}

// Busca histórico de atividades
$stmt_hist = $conn->prepare(
    "SELECT id, data_hora, acao, tipo_entidade, descricao
       FROM historico
      WHERE psicologo_id = :id
   ORDER BY data_hora DESC"
);
$stmt_hist->bindParam(':id', $usuario['id'], PDO::PARAM_INT);
$stmt_hist->execute();
$registros = $stmt_hist->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Histórico</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            text-align: center;
        }

        .card-historico {
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .historico-header {
            background-color: #eef2f6;
            padding: 1rem 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            position: relative;
        }

        .historico-header h1 {
            font-size: 1.75rem;
            color: #333;
            flex: 1;
        }

        .btn-back,
        .btn-apagar-tudo {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
              top: 0.5rem;
            align-items: center;
            justify-content: center;
        }

        .btn-back {
            position: absolute;
            left: 1rem;
        }

        .btn-apagar-tudo {
            position: absolute;
            right: 1rem;
            background-color: #e55353;
            color: #fff;
            border: none;
        }

        .btn-apagar-tudo:hover {
            background-color: #c43e3e;
        }

        .table th {
            text-transform: uppercase;
            font-weight: 600;
        }

        .table-responsive {
            max-height: 65vh;
            overflow-y: auto;
        }

        table td, table th {
            vertical-align: middle;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php include 'menu_publico.php'; ?>

      <div class="container py-4">
        <div class="card card-historico mx-auto" style="max-width:1000px;">
            <div class="historico-header">
                <button onclick="location.href='perfil_ps.php'" class="btn btn-outline-primary btn-back">
                    <i class="bi bi-arrow-left"></i>
                </button>
                <h1>HISTÓRICO</h1>
                <form method="POST">
                    <button name="clear_all" type="submit" class="btn-apagar-tudo">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Data e Hora</th>
                                <th>Ação</th>
                                <th>Entidade</th>
                                <th>Descrição</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($registros): ?>
                            <?php foreach ($registros as $item): ?>
                                <tr>
                                    <td><?= date('d/m/Y H:i', strtotime($item['data_hora'])) ?></td>
                                    <td><?= htmlspecialchars($item['acao']) ?></td>
                                    <td><?= htmlspecialchars($item['tipo_entidade']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($item['descricao'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#confirmDelete<?= $item['id'] ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <div class="modal fade" id="confirmDelete<?= $item['id'] ?>" tabindex="-1">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirmar exclusão</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        Deseja apagar este registro?
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="delete_id" value="<?= $item['id'] ?>">
                                                            <button type="submit" class="btn btn-danger">Apagar</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="py-4">Nenhuma atividade registrada.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
