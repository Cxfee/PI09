<?php
require_once 'conexao.php';
$dados = ler_json();
$identificador = trim($dados['identificador'] ?? '');
$senha = trim($dados['senha'] ?? '');
$cpf = somente_digitos($identificador);

if (!$identificador || !$senha) responder(false, 'Informe e-mail/CPF e senha.', [], 400);

try {
    $stmt = $pdo->prepare('SELECT email, nome, cpf, senha, tipo_acesso FROM usuarios WHERE email = ? OR cpf = ?');
    $stmt->execute([strtolower($identificador), $cpf]);
    $usuario = $stmt->fetch();
    if (!$usuario || !password_verify($senha, $usuario['senha'])) responder(false, 'Dados de login inválidos.', [], 401);
    responder(true, 'Login realizado com sucesso.', ['usuario' => [
        'email' => $usuario['email'],
        'nome' => $usuario['nome'],
        'cpf' => $usuario['cpf'],
        'tipo_acesso' => $usuario['tipo_acesso']
    ]]);
} catch (PDOException $erro) {
    responder(false, 'Erro ao fazer login.', ['erro' => $erro->getMessage()], 500);
}
?>
