<?php
require_once 'conexao.php';
$dados = ler_json();
$nome = trim($dados['nome'] ?? '');
$tipo = trim($dados['tipo'] ?? '');
$capacidade = intval($dados['capacidade'] ?? 0);
$horaInicio = trim($dados['hora_inicio'] ?? '');
$horaFim = trim($dados['hora_fim'] ?? '');
$ilustracao = trim($dados['ilustracao'] ?? 'study');
$status = trim($dados['status_sala'] ?? 'ativa');
$statusValidos = ['ativa','revisao','bloqueada'];

function slug_sala($texto) {
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT', $texto);
    $texto = strtolower($texto);
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    $texto = trim($texto, '-');
    return $texto ?: 'sala-' . time();
}

if (strlen($nome) < 3) responder(false, 'Digite um nome válido para a sala.', [], 400);
if (!$tipo) responder(false, 'Informe o tipo da sala.', [], 400);
if ($capacidade < 1) responder(false, 'Capacidade inválida.', [], 400);
if (!preg_match('/^\d{2}:\d{2}$/', $horaInicio) || !preg_match('/^\d{2}:\d{2}$/', $horaFim)) responder(false, 'Horário inválido.', [], 400);
if ($horaInicio >= $horaFim) responder(false, 'O horário final precisa ser maior que o inicial.', [], 400);
if (!in_array($status, $statusValidos, true)) responder(false, 'Status inválido.', [], 400);

try {
    $idBase = slug_sala($nome);
    $id = $idBase;
    $contador = 2;
    while (true) {
        $check = $pdo->prepare('SELECT id FROM salas WHERE id = ? OR nome = ?');
        $check->execute([$id, $nome]);
        $existe = $check->fetch();
        if (!$existe) break;
        if ($existe['id'] !== $id) responder(false, 'Já existe uma sala com esse nome.', [], 409);
        $id = $idBase . '-' . $contador++;
    }

    $stmt = $pdo->prepare('INSERT INTO salas (id, nome, tipo, capacidade, hora_inicio, hora_fim, ilustracao, status_sala) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$id, $nome, $tipo, $capacidade, $horaInicio, $horaFim, $ilustracao, $status]);
    responder(true, 'Sala cadastrada com sucesso.', ['sala_id' => $id]);
} catch (PDOException $erro) {
    responder(false, 'Erro ao cadastrar sala.', ['erro' => $erro->getMessage()], 500);
}
?>
