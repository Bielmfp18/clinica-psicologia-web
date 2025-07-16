<?php
// HISTÓRICO

// Inicia a sessão caso ainda não tenha sido iniciada
session_name('Mente_Renovada');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Define fuso horário
date_default_timezone_set('America/Sao_Paulo');

// Ativa erros para debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui conexão e função para apagar histórico
include 'conn/conexao.php';
include 'funcao_historico.php';

// Verifica se o psicólogo está logado
if (!isset($_SESSION['psicologo_id'])) {
    $_SESSION['flash'] = [
        'type' => 'warning',
        'message' => 'Faça login antes de acessar o histórico.'
    ];
    header('Location: index.php');
    exit;
}

// Recupera dados do psicólogo logado
$id_psico = $_SESSION['psicologo_id'];
$stmt = $conn->prepare("SELECT id, nome FROM psicologo WHERE id = :id LIMIT 1");
$stmt->bindParam(':id', $id_psico, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não encontrar o usuário, encerra
if (!$usuario) {
    die('Usuário não encontrado.');
}

// Exclusão de histórico (todos ou individual)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['clear_all'])) {
        // Apaga todos os registros do histórico do psicólogo
        $conn->prepare("DELETE FROM historico WHERE psicologo_id = :id")
            ->execute([':id' => $usuario['id']]);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    if (isset($_POST['delete_id'])) {
        // Apaga um registro específico do histórico
        apagarHistorico($conn, (int)$_POST['delete_id']);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Busca todos os registros do histórico
$stmt = $conn->prepare("
    SELECT id, data_hora, acao, tipo_entidade, descricao
      FROM historico
     WHERE psicologo_id = :id
  ORDER BY data_hora DESC
");
$stmt->bindParam(':id', $usuario['id'], PDO::PARAM_INT);
$stmt->execute();
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Recupera e limpa o flash da sessão (alerta)
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
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
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .historico-header {
            background-color: #eef2f6;
            padding: 1rem 0;
            display: flex;
            align-items: center;
            position: relative;
        }

        .historico-header h1 {
            font-size: 1.75rem;
            color: #333;
            margin: 0 auto;
            /* título centralizado */
        }

        .btn-back,
        .btn-apagar-tudo {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: 1rem;
        }

        .btn-back {
            left: 1rem;
        }

        .btn-apagar-tudo {
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

        table td,
        table th {
            vertical-align: middle;
            text-align: center;
        }

        button {
            transition:
                transform 0.8s cubic-bezier(0.4, 0, 0.2, 1),
                box-shadow 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }

        button:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        button:active {
            transform: scale(0.97);
            transition-duration: 0.2s;
        }

        /* Modal styling e animações */

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-content {
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);
            animation: modalFadeIn 0.3s ease-out;
        }

        .modal-header {
            background: linear-gradient(90deg, #DBA632, #E55353);
            border-bottom: none;
            justify-content: center;
        }

        .modal-title {
            color: #fff;
            font-weight: 600;
        }

        .modal-header .btn-close {
            filter: invert(1);
            opacity: 0.8;
        }

        .modal-header .btn-close:hover {
            opacity: 1;
        }

        .modal-footer {
            justify-content: center;
            border-top: none;
            gap: 1rem;
            padding: 1rem;
        }

        .modal-footer .btn {
            min-width: 100px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .modal-footer .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .modal-footer .btn:active {
            transform: scale(0.95);
            transition-duration: 0.1s;
        }
    </style>
</head>

<body>
    <?php include 'menu_publico.php'; ?>

    <!-- ALERTA FIXO NO TOPO -->
    <?php if ($flash): ?>
        <div class="alert-wrapper">
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show mb-0 justify-content-center" role="alert">
                <span><?= htmlspecialchars($flash['message']) ?></span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="container py-4">
        <div class="card card-historico mx-auto" style="max-width:1000px;">
            <div class="historico-header">
                <!-- Botão voltar -->
                <button onclick="location.href='perfil_ps.php'" class="btn btn-outline-primary btn-back">
                    <i class="bi bi-arrow-left"></i>
                </button>
                <!-- Título centralizado -->
                <h1>HISTÓRICO</h1>
                <!-- Botão apagar tudo -->
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
                                            <!-- Botão abrir modal de confirmação -->
                                            <button class="btn btn-sm btn-outline-danger btn-anim"
                                                data-bs-toggle="modal"
                                                data-bs-target="#confirmDelete<?= $item['id'] ?>">
                                                <i class="bi bi-trash"></i>
                                            </button>

                                            <!-- Modal de confirmação individual -->
                                            <div class="modal fade" id="confirmDelete<?= $item['id'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <!-- HEADER COM GRADIENT E TÍTULO CENTRALIZADO -->
                                                        <div class="modal-header justify-content-center text-white">
                                                            <h5 class="modal-title">
                                                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                                                Confirmar Exclusão
                                                            </h5>
                                                        </div>
                                                        <!-- CORPO DO MODAL -->
                                                        <div class="modal-body text-center">
                                                            <strong> Deseja apagar este registro? </strong>
                                                        </div>
                                                        <!-- FOOTER COM BOTÕES PADRÃO btn-anim -->
                                                        <div class="modal-footer">
                                                            <button type="button"
                                                                class="btn btn-outline-success btn-anim"
                                                                data-bs-dismiss="modal">
                                                                <i class="bi bi-x-lg me-1"></i> Cancelar
                                                            </button>
                                                            <form method="POST" class="d-inline">
                                                                <input type="hidden" name="delete_id" value="<?= $item['id'] ?>">
                                                                <button type="submit" class="btn btn-danger btn-anim">
                                                                    <i class="bi bi-trash me-1"></i> Apagar
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-4">Nenhuma atividade registrada.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Rodapé -->
    <?php include 'rodape.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>