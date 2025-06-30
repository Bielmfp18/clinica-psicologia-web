<?php 

include "conn/conexao.php"; // Inclui o arquivo de conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $id = $_POST['id']; // Captura o ID do psicólogo
    $nome = $_POST['nome']; // Captura o nome do psicólogo
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL); // Valida o email
    $senha = password_hash(trim($_POST['senha']), PASSWORD_DEFAULT); // Hash da senha

    // Diretório onde as fotos serão salvas
    $diretorio_fotos = "image/";

    // Verifica se uma nova imagem foi enviada
    if (!empty($_FILES['foto_perfil']['name'])) {
        // Gera um nome único para a nova imagem
        $extensao = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid('perfil_', true) . '.' . $extensao;
        $caminho_imagem = $diretorio_fotos . $novo_nome;

        // Move a nova imagem para o diretório
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $caminho_imagem)) {
            // Busca a imagem atual na tabela `psicologo` para deletar se não for a padrão
            $stmt_img = $conn->prepare("
                SELECT foto_perfil
                FROM psicologo
                WHERE id = :id
            ");
            $stmt_img->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt_img->execute();
            $dados = $stmt_img->fetch(PDO::FETCH_ASSOC);

            if ($dados && $dados['foto_perfil'] !== 'default.png') {
                $foto_antiga = $diretorio_fotos . $dados['foto_perfil'];
                if (file_exists($foto_antiga)) {
                    unlink($foto_antiga); // Deleta a imagem antiga
                }
            }

            $foto_perfil = $novo_nome; // Nome da nova imagem
        } else {
            $foto_perfil = 'default.png'; // Se falhar, mantém a padrão
        }
    } else {
        // Se nenhuma nova imagem for enviada, mantém a existente
        $stmt_img = $conn->prepare("
            SELECT foto_perfil
            FROM psicologo
            WHERE id = :id
        ");
        $stmt_img->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt_img->execute();
        $dados = $stmt_img->fetch(PDO::FETCH_ASSOC);
        $foto_perfil = $dados ? $dados['foto_perfil'] : 'default.png';
    }

    try {
        // Chama o procedimento armazenado para atualizar o psicólogo
        $sql = "CALL ps_psicologo_update(
                    :psid, 
                    :psnome, 
                    :psemail, 
                    :pssenha, 
                    :psfoto_perfil
                )";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam("psid", $id, PDO::PARAM_INT);
        $stmt->bindParam("psnome", $nome);
        $stmt->bindParam("psemail", $email);
        $stmt->bindParam("pssenha", $senha);
        $stmt->bindParam("psfoto_perfil", $foto_perfil, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();

        echo "<script>
                alert('Perfil atualizado com sucesso!');
                window.location.href='perfil_ps.php';
              </script>";
    } catch (PDOException $e) {
        echo "Erro ao atualizar o perfil: " . $e->getMessage();
    }
    exit;
}
?>
