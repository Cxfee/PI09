<?php
require_once 'conexao.php';
$dados = ler_json();
$id = trim($dados['id'] ?? '');
$status = trim($dados['status_sala'] ?? '');
if (!$id || !in_array($status, ['ativa','revisao','bloqueada'], true)) responder(false, 'Dados inválidos.', [], 400);
try {
    $stmt = $pdo->prepare('UPDATE salas SET status_sala = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
    responder(true, 'Status atualizado.');
} catch (PDOException $erro) {
    responder(false, 'Erro ao atualizar status.', ['erro' => $erro->getMessage()], 500);
}
?>
