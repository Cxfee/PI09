<?php
header('Content-Type: application/json; charset=UTF-8');

$host = 'localhost';
$banco = 'projeto_senac';
$usuario = 'root';
$senha = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$banco;charset=utf8mb4", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $erro) {
    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro ao conectar ao banco de dados.',
        'erro' => $erro->getMessage()
    ]);
    exit;
}

function ler_json() {
    $dados = json_decode(file_get_contents('php://input'), true);
    return is_array($dados) ? $dados : [];
}

function responder($sucesso, $mensagem = '', $extra = [], $codigo = 200) {
    http_response_code($codigo);
    echo json_encode(array_merge([
        'sucesso' => $sucesso,
        'mensagem' => $mensagem
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function somente_digitos($valor) {
    return preg_replace('/\D/', '', $valor ?? '');
}
?>
