<?php
// funcao_historico.php — registro correto no histórico (assinatura e binding ajustados)

/**
 * Registra uma ação no histórico (chama a procedure ps_historico_insert).
 *
 * Assinatura esperada pelos chamadores do seu projeto:
 *   registrarHistorico($conn, $psicologoId, $acao, $descricao, $tipo [, $dataHora]);
 *
 * @param PDO    $conn         Conexão PDO.
 * @param int    $psicologoId  ID do psicólogo logado.
 * @param string $acao         Ação curta (ex: 'Ativação', 'Desativação', 'Confirmação').
 * @param string $descricao    Descrição livre (pode ser longa).
 * @param string $tipo         Tipo de entidade (ex: 'Paciente', 'Sessão') — campo curto.
 * @param string $dataHora     Timestamp 'Y-m-d H:i:s' opcional (se nulo usa agora).
 * @return array|bool          Array com o registro retornado pela procedure ou true.
 * @throws Exception se houver erro de DB.
 */
function registrarHistorico(PDO $conn, int $psicologoId, string $acao, string $descricao, string $tipo, string $dataHora = null)
{
    // limite do campo tipo_entidade no DB (de acordo com seu schema atual)
    $TIPO_MAX = 50;

    // normaliza inputs
    $acao = trim($acao);
    $descricao = trim($descricao);
    $tipo = trim($tipo);

    // garante data/hora
    if ($dataHora === null) {
        try {
            $dt = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
            $dataHora = $dt->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            $dataHora = gmdate('Y-m-d H:i:s');
        }
    }

    // evita enviar tipo maior que o campo (trunca silenciosamente; se preferir lance exceção)
    if (mb_strlen($tipo) > $TIPO_MAX) {
        $tipo = mb_substr($tipo, 0, $TIPO_MAX);
    }

    // Chama a procedure com a ordem correta: psicologo_id, acao, descricao, data_hora, tipo_entidade
    $sql = "CALL ps_historico_insert(:pspsicologo_id, :psacao, :psdescricao, :psdata_hora, :pstipo_entidade)";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':pspsicologo_id', $psicologoId, PDO::PARAM_INT);
    $stmt->bindValue(':psacao', $acao, PDO::PARAM_STR);
    $stmt->bindValue(':psdescricao', $descricao, PDO::PARAM_STR);
    $stmt->bindValue(':psdata_hora', $dataHora, PDO::PARAM_STR);
    $stmt->bindValue(':pstipo_entidade', $tipo, PDO::PARAM_STR);

    $stmt->execute();

    // A procedure seleciona o registro inserido; buscamos e retornamos (se houver)
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // fechar cursor para permitir próximos CALLs
    $stmt->closeCursor();

    return $result ?: true;
}

/**
 * Remove um registro do histórico (chama a procedure ps_historico_delete).
 */
function apagarHistorico(PDO $conn, int $historicoId): void
{
    $stmt = $conn->prepare("CALL ps_historico_delete(:psid)");
    $stmt->bindValue(':psid', $historicoId, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor();
}
