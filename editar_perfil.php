<!-- EDITAR PERFIL -->

<?php 
include "conn/conexao.php"; // Conexão com o banco de dados

// Verifica se há uma session nome e inicia a sessão.
session_name('Mente_Renovada');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    // Captura os dados enviados pelo formulário
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    // Só gera hash se a senha foi informada
    $senha = !empty(trim($_POST['senha'])) ? password_hash(trim($_POST['senha']), PASSWORD_DEFAULT) : null;

    // Diretório onde as imagens serão salvas
    $diretorio_fotos = "image/";
    $foto_perfil = null;

    // Verifica se o usuário enviou nova imagem
    if (!empty($_FILES['foto_perfil']['name'])) {
        $extensao = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid('perfil_', true) . '.' . $extensao;
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
            $foto_perfil = 'default.png'; // Caso o upload falhe
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
        $sql = "CALL ps_psicologo_update(:psid, :psnome, :psemail, :pssenha, :psfoto_perfil
                )";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam("psid", $id, PDO::PARAM_INT);
        $stmt->bindParam("psnome", $nome);
        $stmt->bindParam("psemail", $email);

        // Se senha foi enviada, usa o hash. Caso contrário, busca a senha atual no banco.
        if ($senha) {
            $stmt->bindParam("pssenha", $senha);
        } else {
            // Busca senha atual
            $stmt_senha = $conn->prepare("SELECT senha FROM psicologo WHERE id = :id");
            $stmt_senha->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt_senha->execute();
            $senha_atual = $stmt_senha->fetchColumn();
            $stmt->bindParam("pssenha", $senha_atual);
        }

        $stmt->bindParam("psfoto_perfil", $foto_perfil, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();

        // Atualiza a sessão com o novo e-mail
        $_SESSION['login_admin'] = $email;

        // Redireciona para a página de perfil
        echo "<script>
                alert('Perfil atualizado com sucesso!');
                window.location.href='perfil_ps.php';
              </script>";
        exit;

    } catch (PDOException $e) {
        echo "Erro ao atualizar o perfil: " . $e->getMessage();
    }
}
?>
