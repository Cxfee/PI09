<?php
require_once 'conexao.php';
$dados = ler_json();
$email = strtolower(trim($dados['email_usuario'] ?? ''));
$salaId = trim($dados['sala_id'] ?? '');
$data = trim($dados['data_reserva'] ?? '');
$horaInicio = trim($dados['hora_inicio'] ?? '');
$horaFim = trim($dados['hora_fim'] ?? '');
if (!$horaFim && preg_match('/^\d{2}:\d{2}$/', $horaInicio)) $horaFim = date('H:i', strtotime($horaInicio . ' +1 hour'));

if (!$email || !$salaId || !$data || !$horaInicio) responder(false, 'Dados da reserva incompletos.', [], 400);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) responder(false, 'Data inválida.', [], 400);
if (!preg_match('/^\d{2}:\d{2}$/', $horaInicio) || !preg_match('/^\d{2}:\d{2}$/', $horaFim)) responder(false, 'Horário inválido.', [], 400);
if ($horaInicio >= $horaFim) responder(false, 'O horário final precisa ser maior que o inicial.', [], 400);

try {
    $salaStmt = $pdo->prepare('SELECT * FROM salas WHERE id = ?');
    $salaStmt->execute([$salaId]);
    $sala = $salaStmt->fetch();
    if (!$sala) responder(false, 'Sala não encontrada.', [], 404);
    if ($sala['status_sala'] !== 'ativa') responder(false, 'Sala indisponível para reserva.', [], 409);
    $salaInicio = substr($sala['hora_inicio'], 0, 5);
    $salaFim = substr($sala['hora_fim'], 0, 5);
    if ($horaInicio < $salaInicio || $horaFim > $salaFim) responder(false, 'Horário fora do funcionamento da sala.', [], 409);

    $usuarioStmt = $pdo->prepare('SELECT email FROM usuarios WHERE email = ?');
    $usuarioStmt->execute([$email]);
    if (!$usuarioStmt->fetch()) responder(false, 'Usuário não encontrado.', [], 404);

    $limiteStmt = $pdo->prepare('SELECT COUNT(*) AS total FROM reservas WHERE email_usuario = ?');
    $limiteStmt->execute([$email]);
    if (intval($limiteStmt->fetch()['total']) >= 2) responder(false, 'Cada usuário pode ter no máximo 2 reservas ativas.', [], 409);

    $conflitoSala = $pdo->prepare('SELECT id FROM reservas WHERE sala_id = ? AND data_reserva = ? AND hora_inicio < ? AND COALESCE(hora_fim, ADDTIME(hora_inicio, "01:00:00")) > ? LIMIT 1');
    $conflitoSala->execute([$salaId, $data, $horaFim, $horaInicio]);
    if ($conflitoSala->fetch()) responder(false, 'Essa sala já está reservada nesse horário.', [], 409);

    $conflitoUsuario = $pdo->prepare('SELECT id FROM reservas WHERE email_usuario = ? AND data_reserva = ? AND hora_inicio < ? AND COALESCE(hora_fim, ADDTIME(hora_inicio, "01:00:00")) > ? LIMIT 1');
    $conflitoUsuario->execute([$email, $data, $horaFim, $horaInicio]);
    if ($conflitoUsuario->fetch()) responder(false, 'Você já possui uma reserva nesse horário.', [], 409);

    $conflitoProfessor = $pdo->prepare('SELECT id FROM solicitacoes_professor WHERE sala_id = ? AND data_solicitacao = ? AND status_solicitacao = "confirmada" AND hora_inicio < ? AND hora_fim > ? LIMIT 1');
    $conflitoProfessor->execute([$salaId, $data, $horaFim, $horaInicio]);
    if ($conflitoProfessor->fetch()) responder(false, 'Sala reservada para aula nesse horário.', [], 409);

    $stmt = $pdo->prepare('INSERT INTO reservas (data_reserva, hora_inicio, hora_fim, sala_id, email_usuario) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$data, $horaInicio, $horaFim, $salaId, $email]);
    responder(true, 'Reserva criada com sucesso.', ['id' => $pdo->lastInsertId()]);
} catch (PDOException $erro) {
    responder(false, 'Erro ao criar reserva.', ['erro' => $erro->getMessage()], 500);
}
?>
