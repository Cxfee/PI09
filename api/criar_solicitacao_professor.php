<?php
require_once 'conexao.php';
$dados = ler_json();
$email = strtolower(trim($dados['email_professor'] ?? ''));
$salaId = trim($dados['sala_id'] ?? '');
$turma = trim($dados['turma_atividade'] ?? '');
$finalidade = trim($dados['finalidade'] ?? '');
$data = trim($dados['data_solicitacao'] ?? '');
$horaInicio = trim($dados['hora_inicio'] ?? '');
$horaFim = trim($dados['hora_fim'] ?? '');

if (!$email || !$salaId || !$turma || !$finalidade || !$data || !$horaInicio || !$horaFim) responder(false, 'Preencha todos os campos.', [], 400);
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) responder(false, 'Data inválida.', [], 400);
if (!preg_match('/^\d{2}:\d{2}$/', $horaInicio) || !preg_match('/^\d{2}:\d{2}$/', $horaFim)) responder(false, 'Horário inválido.', [], 400);
if ($horaInicio >= $horaFim) responder(false, 'O horário final precisa ser maior que o inicial.', [], 400);

try {
    $profStmt = $pdo->prepare('SELECT email, tipo_acesso FROM usuarios WHERE email = ?');
    $profStmt->execute([$email]);
    $prof = $profStmt->fetch();
    if (!$prof) responder(false, 'Professor não encontrado.', [], 404);

    $salaStmt = $pdo->prepare('SELECT * FROM salas WHERE id = ?');
    $salaStmt->execute([$salaId]);
    $sala = $salaStmt->fetch();
    if (!$sala) responder(false, 'Sala não encontrada.', [], 404);
    if ($sala['status_sala'] !== 'ativa') responder(false, 'Sala indisponível.', [], 409);
    $salaInicio = substr($sala['hora_inicio'], 0, 5);
    $salaFim = substr($sala['hora_fim'], 0, 5);
    if ($horaInicio < $salaInicio || $horaFim > $salaFim) responder(false, 'Horário fora do funcionamento da sala.', [], 409);

    $conflitoAluno = $pdo->prepare('SELECT id FROM reservas WHERE sala_id = ? AND data_reserva = ? AND hora_inicio < ? AND COALESCE(hora_fim, ADDTIME(hora_inicio, "01:00:00")) > ? LIMIT 1');
    $conflitoAluno->execute([$salaId, $data, $horaFim, $horaInicio]);

    $conflitoProfessor = $pdo->prepare('SELECT id FROM solicitacoes_professor WHERE sala_id = ? AND data_solicitacao = ? AND status_solicitacao = "confirmada" AND hora_inicio < ? AND hora_fim > ? LIMIT 1');
    $conflitoProfessor->execute([$salaId, $data, $horaFim, $horaInicio]);

    $status = ($conflitoAluno->fetch() || $conflitoProfessor->fetch()) ? 'em-analise' : 'confirmada';

    $stmt = $pdo->prepare('INSERT INTO solicitacoes_professor (email_professor, sala_id, turma_atividade, finalidade, data_solicitacao, hora_inicio, hora_fim, status_solicitacao) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$email, $salaId, $turma, $finalidade, $data, $horaInicio, $horaFim, $status]);
    responder(true, 'Solicitação criada com sucesso.', ['id' => $pdo->lastInsertId(), 'status_solicitacao' => $status]);
} catch (PDOException $erro) {
    responder(false, 'Erro ao criar solicitação.', ['erro' => $erro->getMessage()], 500);
}
?>
