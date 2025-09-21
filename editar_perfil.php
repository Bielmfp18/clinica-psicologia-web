<!-- EDITAR PERFIL -->

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

// Garante que só usuários logados possam alterar o perfil.
if (!isset($_SESSION['login_admin'])) {
    $_SESSION['flash'] = [
        'type'    => 'warning',
        'message' => 'Você precisa estar logado para editar o perfil.'
    ];
    header('Location: index.php');
    exit;
}

// Inclui a conexão com o banco de dados
include "conn/conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura os dados enviados pelo formulário
    $id    = (int) $_POST['id'];
    $nome  = trim($_POST['nome']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Só gera o hash se a senha for informada
    $senha = !empty(trim($_POST['senha']))
        ? password_hash(trim($_POST['senha']), PASSWORD_DEFAULT)
        : null;

    // Diretório onde as imagens serão salvas
    $diretorio_fotos = "image/";
    $foto_perfil = null;

    // Verifica se o usuário enviou nova imagem
    if (!empty($_FILES['foto_perfil']['name'])) {
        $extensao     = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $novo_nome    = uniqid('perfil_', true) . '.' . $extensao;
        $caminho_imagem = $diretorio_fotos . $novo_nome;

        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $caminho_imagem)) {
            // Busca imagem atual para deletar, se necessário
            $stmt_img = $conn->prepare("SELECT foto_perfil FROM psicologo WHERE id = :id");
            $stmt_img->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt_img->execute();
            $dados = $stmt_img->fetch(PDO::FETCH_ASSOC);

            if ($dados && $dados['foto_perfil'] !== 'default.png') {
                $foto_antiga = $diretorio_fotos . $dados['foto_perfil'];
                if (file_exists($foto_antiga)) {
                    unlink($foto_antiga);
                }
            }

            $foto_perfil = $novo_nome;
        } else {
            // Caso o upload falhe, define imagem padrão
            $foto_perfil = 'default.png';
        }

    } else {
        // Se não enviar nova foto, mantém a existente
        $stmt_img = $conn->prepare("SELECT foto_perfil FROM psicologo WHERE id = :id");
        $stmt_img->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt_img->execute();
        $dados = $stmt_img->fetch(PDO::FETCH_ASSOC);
        $foto_perfil = $dados ? $dados['foto_perfil'] : 'default.png';
    }

    try {
        // Monta a chamada do procedimento armazenado
        $sql = "CALL ps_psicologo_update(
                    :psid,
                    :psnome,
                    :psemail,
                    :pssenha,
                    :psfoto_perfil
                )";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam("psid",           $id,            PDO::PARAM_INT);
        $stmt->bindParam("psnome",         $nome,          PDO::PARAM_STR);
        $stmt->bindParam("psemail",        $email,         PDO::PARAM_STR);

        // Se a senha for enviada, usa o hash; caso contrário, busca a senha atual
        if ($senha) {
            $stmt->bindParam("pssenha", $senha, PDO::PARAM_STR);
        } else {
            $stmt_senha = $conn->prepare("SELECT senha FROM psicologo WHERE id = :id");
            $stmt_senha->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt_senha->execute();
            $senha_atual = $stmt_senha->fetchColumn();
            $stmt->bindParam("pssenha", $senha_atual, PDO::PARAM_STR);
        }

        // Associa o nome do arquivo de perfil (nova ou existente)
        $stmt->bindParam("psfoto_perfil", $foto_perfil, PDO::PARAM_STR);

        // Executa o update via procedure
        $stmt->execute();
        $stmt->closeCursor();

        // Atualiza a sessão com o novo email
        $_SESSION['login_admin'] = $email;

        // Flash de sucesso para ser exibido em perfil_ps.php
        $_SESSION['flash'] = [
            'type'    => 'success',
            'message' => 'Perfil atualizado com sucesso!'
        ];

        header('Location: perfil_ps.php');
        exit;

    } catch (PDOException $e) {
        // Flash de erro em caso de exceção
        $_SESSION['flash'] = [
            'type'    => 'danger',
            'message' => 'Erro no servidor: ' . $e->getMessage()
        ];
        header('Location: perfil_ps.php');
        exit;
    }
}
?>
