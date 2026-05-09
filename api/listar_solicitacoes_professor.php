<?php
require_once 'conexao.php';
$email = strtolower(trim($_GET['email'] ?? ''));
try {
    $sql = 'SELECT sp.id, sp.email_professor, u.nome AS professor_nome, sp.sala_id, s.nome AS sala_nome, sp.turma_atividade, sp.finalidade, sp.data_solicitacao, TIME_FORMAT(sp.hora_inicio, "%H:%i") AS hora_inicio, TIME_FORMAT(sp.hora_fim, "%H:%i") AS hora_fim, sp.status_solicitacao, sp.criado_em
            FROM solicitacoes_professor sp
            JOIN usuarios u ON u.email = sp.email_professor
            JOIN salas s ON s.id = sp.sala_id';
    $params = [];
    if ($email) {
        $sql .= ' WHERE sp.email_professor = ?';
        $params[] = $email;
    }
    $sql .= ' ORDER BY sp.criado_em DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    responder(true, 'Solicitações carregadas.', ['solicitacoes' => $stmt->fetchAll()]);
} catch (PDOException $erro) {
    responder(false, 'Erro ao listar solicitações.', ['erro' => $erro->getMessage()], 500);
}
?>
