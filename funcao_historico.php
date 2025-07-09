<?php
// FUNÇÃO HISTÓRICO 

/**
 * Registra uma ação no histórico (chama a procedure ps_historico_insert).
 *
 * @param PDO    $conn          Conexão PDO.
 * @param int    $psicologoId   ID do psicólogo logado.
 * @param string $acao          'INSERT', 'UPDATE', 'DELETE', 'CONFIRM'...
 * @param string $entidade      'paciente' ou 'sessao'.
 * @param string $descricao     Texto explicativo.
 * @param string $dataHora      Timestamp formatado 'Y-m-d H:i:s' (opcional).
 * @return array                O registro recém-inserido (array associativo).
 */
function registrarHistorico(PDO $conn, int $psicologoId, string $acao, string $entidade, string $descricao, string $dataHora = null): array
{
    if ($dataHora === null) {
        $dataHora = date('Y-m-d H:i:s');
    }

    $stmt = $conn->prepare("
        CALL ps_historico_insert(
            :pspsicologo_id,
            :psacao,
            :psdescricao,
            :psdata_hora,
            :pstipo_entidade
        )
    ");
    $stmt->bindValue(':pspsicologo_id', $psicologoId, PDO::PARAM_INT);
    $stmt->bindValue(':psacao', $acao, PDO::PARAM_STR);
    $stmt->bindValue(':psdescricao', $descricao, PDO::PARAM_STR);
    $stmt->bindValue(':psdata_hora', $dataHora, PDO::PARAM_STR);
    $stmt->bindValue(':pstipo_entidade', $entidade, PDO::PARAM_STR);
    $stmt->execute();

    // A procedure já retorna o registro inserido em um SELECT
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Remove um registro do histórico (chama a procedure ps_historico_delete).
 *
 * @param PDO $conn          Conexão PDO.
 * @param int $historicoId   ID do registro a remover.
 */
function apagarHistorico(PDO $conn, int $historicoId): void
{
    $stmt = $conn->prepare("CALL ps_historico_delete(:psid)");
    $stmt->bindValue(':psid', $historicoId, PDO::PARAM_INT);
    $stmt->execute();
}
