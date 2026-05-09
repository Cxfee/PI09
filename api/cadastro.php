<?php
require_once 'conexao.php';
$dados = ler_json();
$email = strtolower(trim($dados['email'] ?? ''));
$nome = trim($dados['nome'] ?? '');
$cpf = somente_digitos($dados['cpf'] ?? '');
$senha = trim($dados['senha'] ?? '');
$tipo = trim($dados['tipo_acesso'] ?? 'aluno');
$tiposValidos = ['aluno','professor','admin'];

if (!$email || !$nome || !$cpf || !$senha || !$tipo) responder(false, 'Preencha todos os campos.', [], 400);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) responder(false, 'E-mail inválido.', [], 400);
if (strlen($cpf) !== 11) responder(false, 'CPF deve conter 11 dígitos.', [], 400);
if (strlen($senha) < 6) responder(false, 'A senha precisa ter pelo menos 6 caracteres.', [], 400);
if (!in_array($tipo, $tiposValidos, true)) responder(false, 'Tipo de acesso inválido.', [], 400);

try {
    $verificar = $pdo->prepare('SELECT email FROM usuarios WHERE email = ? OR cpf = ?');
    $verificar->execute([$email, $cpf]);
    if ($verificar->fetch()) responder(false, 'Já existe uma conta com esse e-mail ou CPF.', [], 409);

    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO usuarios (email, nome, cpf, senha, tipo_acesso) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$email, $nome, $cpf, $hash, $tipo]);
    responder(true, 'Usuário cadastrado com sucesso.');
} catch (PDOException $erro) {
    responder(false, 'Erro ao cadastrar usuário.', ['erro' => $erro->getMessage()], 500);
}
?>
