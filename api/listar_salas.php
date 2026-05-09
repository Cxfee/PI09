<?php
require_once 'conexao.php';
try {
    $apenasAtivas = ($_GET['ativas'] ?? '') === '1';
    $sql = 'SELECT id, nome, tipo, capacidade, TIME_FORMAT(hora_inicio, "%H:%i") AS hora_inicio, TIME_FORMAT(hora_fim, "%H:%i") AS hora_fim, ilustracao, status_sala FROM salas';
    if ($apenasAtivas) $sql .= " WHERE status_sala = 'ativa'";
    $sql .= ' ORDER BY nome';
    $stmt = $pdo->query($sql);
    responder(true, 'Salas carregadas.', ['salas' => $stmt->fetchAll()]);
} catch (PDOException $erro) {
    responder(false, 'Erro ao listar salas.', ['erro' => $erro->getMessage()], 500);
}
?>
