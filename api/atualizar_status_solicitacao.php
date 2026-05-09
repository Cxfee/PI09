<?php
require_once 'conexao.php';
$dados = ler_json();
$id = intval($dados['id'] ?? 0);
$status = trim($dados['status_solicitacao'] ?? '');
if ($id <= 0 || !in_array($status, ['confirmada','em-analise','recusada'], true)) responder(false, 'Dados inválidos.', [], 400);
try {
    if ($status === 'confirmada') {
        $stmtReq = $pdo->prepare('SELECT sp.*, s.status_sala, s.hora_inicio AS sala_hora_inicio, s.hora_fim AS sala_hora_fim FROM solicitacoes_professor sp JOIN salas s ON s.id = sp.sala_id WHERE sp.id = ?');
        $stmtReq->execute([$id]);
        $req = $stmtReq->fetch();
        if (!$req) responder(false, 'Solicitação não encontrada.', [], 404);
        if ($req['status_sala'] !== 'ativa') responder(false, 'A sala não está ativa.', [], 409);

        $salaInicio = substr($req['sala_hora_inicio'], 0, 5);
        $salaFim = substr($req['sala_hora_fim'], 0, 5);
        $horaInicio = substr($req['hora_inicio'], 0, 5);
        $horaFim = substr($req['hora_fim'], 0, 5);
        if ($horaInicio < $salaInicio || $horaFim > $salaFim) responder(false, 'Horário fora do funcionamento da sala.', [], 409);

        $conflitoAluno = $pdo->prepare('SELECT id FROM reservas WHERE sala_id = ? AND data_reserva = ? AND hora_inicio < ? AND COALESCE(hora_fim, ADDTIME(hora_inicio, "01:00:00")) > ? LIMIT 1');
        $conflitoAluno->execute([$req['sala_id'], $req['data_solicitacao'], $req['hora_fim'], $req['hora_inicio']]);
        if ($conflitoAluno->fetch()) responder(false, 'Existe reserva de aluno nesse horário.', [], 409);

        $conflitoProfessor = $pdo->prepare('SELECT id FROM solicitacoes_professor WHERE id <> ? AND sala_id = ? AND data_solicitacao = ? AND status_solicitacao = "confirmada" AND hora_inicio < ? AND hora_fim > ? LIMIT 1');
        $conflitoProfessor->execute([$id, $req['sala_id'], $req['data_solicitacao'], $req['hora_fim'], $req['hora_inicio']]);
        if ($conflitoProfessor->fetch()) responder(false, 'Existe outra solicitação confirmada nesse horário.', [], 409);
    }

    $stmt = $pdo->prepare('UPDATE solicitacoes_professor SET status_solicitacao = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
    responder(true, 'Status da solicitação atualizado.');
} catch (PDOException $erro) {
    responder(false, 'Erro ao atualizar solicitação.', ['erro' => $erro->getMessage()], 500);
}
?>
