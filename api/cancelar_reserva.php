<?php
require_once 'conexao.php';
$dados = ler_json();
$id = intval($dados['id'] ?? 0);
if ($id <= 0) responder(false, 'Informe a reserva.', [], 400);
try {
    $stmt = $pdo->prepare('DELETE FROM reservas WHERE id = ?');
    $stmt->execute([$id]);
    responder(true, 'Reserva cancelada.');
} catch (PDOException $erro) {
    responder(false, 'Erro ao cancelar reserva.', ['erro' => $erro->getMessage()], 500);
}
?>
