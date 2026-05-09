<?php
require_once 'conexao.php';
$email = strtolower(trim($_GET['email'] ?? ''));
try {
    $sql = 'SELECT r.id, r.data_reserva, TIME_FORMAT(r.hora_inicio, "%H:%i") AS hora_inicio, TIME_FORMAT(r.hora_fim, "%H:%i") AS hora_fim, r.sala_id, r.email_usuario, s.nome AS sala_nome, s.tipo, s.capacidade, s.status_sala
            FROM reservas r
            JOIN salas s ON s.id = r.sala_id';
    $params = [];
    if ($email) {
        $sql .= ' WHERE r.email_usuario = ?';
        $params[] = $email;
    }
    $sql .= ' ORDER BY r.data_reserva DESC, r.hora_inicio DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    responder(true, 'Reservas carregadas.', ['reservas' => $stmt->fetchAll()]);
} catch (PDOException $erro) {
    responder(false, 'Erro ao listar reservas.', ['erro' => $erro->getMessage()], 500);
}
?>
