<?php
require_once 'conexao.php';
$dados = ler_json();
$id = trim($dados['id'] ?? '');
if (!$id) responder(false, 'Informe a sala.', [], 400);
try {
    $stmt = $pdo->prepare('DELETE FROM salas WHERE id = ?');
    $stmt->execute([$id]);
    responder(true, 'Sala removida.');
} catch (PDOException $erro) {
    responder(false, 'Erro ao remover sala.', ['erro' => $erro->getMessage()], 500);
}
?>
