<!-- PERFIL_PS -->

<?php
// Ativa exibição de erros para facilitar depuração
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define o nome da sessão e inicia a sessão, se necessário
session_name('Mente_Renovada');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Resgata e apaga o flash, se existir
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Inicia conexão com o banco de dados (PDO) e inclui o init.php para tratamento de erros
include 'conn/conexao.php';
include 'conn/init.php';


// Verifica se o psicólogo está logado
if (!isset($_SESSION['login_admin'])) {
    // preparar flash de aviso
    $_SESSION['flash'] = [
        'type'    => 'warning',  // ou 'danger', como preferir
        'message' => 'Você precisa estar logado para acessar essa página.'
    ];
    header('Location: index.php');
    exit;
}

$email_psicologo = $_SESSION['login_admin'];

// Busca o psicólogo pelo email armazenado na sessão
$sql = "SELECT * FROM psicologo WHERE email = :email LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':email', $email_psicologo);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    $_SESSION['flash'] = [
        'type'    => 'danger',
        'message' => 'Usuário não encontrado.'
    ];
    header('Location: index.php');
    exit;
}


// Monta caminho da foto de perfil
$caminhoImagem = 'image/';
$fotoArquivo = $usuario['foto_perfil'];
$foto = (!empty($fotoArquivo) && file_exists($caminhoImagem . $fotoArquivo))
    ? $caminhoImagem . $fotoArquivo
    : $caminhoImagem . 'default.png';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- Link para o ícone da aba -->
    <link rel="shortcut icon" href="image/MTM-Photoroom.png" type="image/x-icon">
    <style>
        body.bg-light {
            background-size: cover;
            padding-top: 70px;
            z-index: 1;
        }

        .btn-anim {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-anim:hover {
            transform: scale(1.07);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .btn-anim:active {
            transform: scale(0.97);
        }

        /* Estilos para o alerta fixo no topo */
        .alert-wrapper {
            position: fixed;
            top: 1rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2000;
            display: inline-block;
        }

        .alert-wrapper .alert {
            position: relative;
            display: inline-block;
            padding: 0.5rem 2.5rem 0.5rem 0.75rem;
            font-size: 0.95rem;
            border-radius: 0.375rem;
        }

        .alert-wrapper .btn-close {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: none !important;
            border: none;
            padding: 0;
            font-size: 1rem;
            line-height: 1;
            opacity: .6;
        }
    </style>
</head>

<body class="bg-light">

    <!-- Exibe flash, se existir -->
    <?php if ($flash): ?>
        <div class="alert-wrapper">
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Menu público -->
    <?php include 'menu_publico.php'; ?>

    <div class="container py-5">

        <!-- card recebe position-relative -->
        <div class="card shadow-lg position-relative mx-auto" style="max-width:600px;">

            <!-- Ícone de Histórico -->
            <a href="historico.php"
                class="btn btn-outline-primary position-absolute top-0 end-0 m-3 btn-anim"
                title="Histórico">
                <i class="bi bi-clock-history"></i>
            </a>

            <div class="card-body text-center">
                <img src="<?= htmlspecialchars($foto) ?>"
                    alt="Foto de perfil"
                    class="rounded-circle mb-3"
                    width="150" height="150">

                <h3><?= htmlspecialchars($usuario['nome']) ?></h3>
                <p>Email: <?= htmlspecialchars($usuario['email']) ?></p>

                <div class="mt-3 d-flex justify-content-center flex-wrap gap-2">
                    <button class="btn btn-anim"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEditarPerfil"
                        style="background-color: #DBA632; color: white; box-shadow:0 4px 6px rgba(0,0,0,0.1);"
                        onmouseover="this.style.filter='brightness(90%)'"
                        onmouseout="this.style.filter='brightness(100%)'">
                        Editar Perfil
                    </button>

                    <a href="logout.php" class="btn btn-outline-danger btn-anim">
                        Sair da Conta
                    </a>
                </div>
            </div>
        </div> <!-- fim card -->

        <!-- Modal Editar Perfil -->
        <div class="modal fade" id="modalEditarPerfil" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="editar_perfil.php" enctype="multipart/form-data">
                        <div class="modal-header text-white justify-content-center" style="background-color: #DBA632;">
                            <h5 class="modal-title" id="modalLabel">EDITAR PERFIL</h5>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id']) ?>">
                            <div class="mb-3">
                                <label class="form-label">Nome</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background-color: #DBA632">
                                        <i class="bi bi-person-fill text-white"></i>
                                    </span>
                                    <input type="text" class="form-control" name="nome"
                                        value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background-color: #DBA632">
                                        <i class="bi bi-envelope-fill text-white"></i>
                                    </span>
                                    <input type="email" class="form-control" name="email"
                                        value="<?= htmlspecialchars($usuario['email']) ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Senha</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background-color: #DBA632">
                                        <i class="bi bi-lock-fill text-white"></i>
                                    </span>
                                    <input type="password" class="form-control" name="senha"
                                        placeholder="Digite sua nova senha">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Foto de Perfil</label>
                                <div class="input-group">
                                    <span class="input-group-text" style="background-color: #DBA632">
                                        <i class="bi bi-camera-fill text-white"></i>
                                    </span>
                                    <input type="file" class="form-control" name="foto_perfil" accept="image/*">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg">
                            <button type="button" class="btn btn-danger btn-anim" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-anim"
                                style="background-color: #DBA632; color: white;">
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div> <!-- fim container -->

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fecha o alert de flash automaticamente
        setTimeout(() => {
            const alertEl = document.querySelector('.alert-dismissible');
            if (alertEl) {
                bootstrap.Alert.getOrCreateInstance(alertEl).close();
            }
        }, 5000);
    </script>
</body>

</html>