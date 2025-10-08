<?php
// funcao_historico.php
// Funções para registrar e apagar histórico (versão padronizada e tolerante).

/**
 * Registra uma ação no histórico.
 *
 * Assinatura usada no projeto:
 *   registrarHistorico($conn, $psicologoId, $acao, $descricao, $tipo [, $dataHora]);
 *
 * @param PDO    $conn
 * @param int    $psicologoId
 * @param string $acao
 * @param string $descricao
 * @param string $tipo
 * @param string|null $dataHora  formato 'Y-m-d H:i:s' (opcional)
 * @return array|array<string,mixed>  resultado retornado pela procedure ou ['success'=>true]
 * @throws PDOException
 */
function registrarHistorico(PDO $conn, int $psicologoId, string $acao, string $descricao, string $tipo, string $dataHora = null)
{
    // Fallback para mb_* se necessário
    if (!function_exists('mb_strlen')) {
        function mb_strlen($s) { return strlen($s); }
        function mb_substr($s, $start, $len = null) {
            return $len === null ? substr($s, $start) : substr($s, $start, $len);
        }
    }

    $TIPO_MAX = 100;

    $acao = trim($acao);
    $descricao = trim($descricao);
    $tipo = trim($tipo);

    // Data/hora padrão (fuso de São Paulo)
    if ($dataHora === null) {
        try {
            $dt = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
            $dataHora = $dt->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            $dataHora = gmdate('Y-m-d H:i:s');
        }
    }

    if (mb_strlen($tipo) > $TIPO_MAX) {
        $tipo = mb_substr($tipo, 0, $TIPO_MAX);
    }

    $sql = "CALL ps_historico_insert(:pspsicologo_id, :psacao, :psdescricao, :psdata_hora, :pstipo_entidade)";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':pspsicologo_id', $psicologoId, PDO::PARAM_INT);
    $stmt->bindValue(':psacao', $acao, PDO::PARAM_STR);
    $stmt->bindValue(':psdescricao', $descricao, PDO::PARAM_STR);
    $stmt->bindValue(':psdata_hora', $dataHora, PDO::PARAM_STR);
    $stmt->bindValue(':pstipo_entidade', $tipo, PDO::PARAM_STR);

    try {
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $result ?: ['success' => true];
    } catch (PDOException $e) {
        // Loga e relança para o caller decidir (pode trocar por return false se preferir)
        error_log('Falha ao gravar historico: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Apaga um registro do histórico usando procedure ps_historico_delete(:id)
 */
function apagarHistorico(PDO $conn, int $historicoId): void
{
    $stmt = $conn->prepare("CALL ps_historico_delete(:psid)");
    $stmt->bindValue(':psid', $historicoId, PDO::PARAM_INT);
    $stmt->execute();
    $stmt->closeCursor();
}
