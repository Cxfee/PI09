<?php
require_once 'conexao.php';
try {
    $stmt = $pdo->query('SELECT email, nome, cpf, tipo_acesso FROM usuarios ORDER BY nome');
    responder(true, 'Usuários carregados.', ['usuarios' => $stmt->fetchAll()]);
} catch (PDOException $erro) {
    responder(false, 'Erro ao listar usuários.', ['erro' => $erro->getMessage()], 500);
}
?>
