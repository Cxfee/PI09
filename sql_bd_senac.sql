CREATE DATABASE IF NOT EXISTS projeto_senac CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE projeto_senac;

DROP TABLE IF EXISTS solicitacoes_professor;
DROP TABLE IF EXISTS reservas;
DROP TABLE IF EXISTS salas;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    email VARCHAR(100) PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo_acesso ENUM('aluno', 'professor', 'admin') NOT NULL
);

CREATE TABLE salas (
    id VARCHAR(50) PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    capacidade INT NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    ilustracao VARCHAR(50) DEFAULT 'study',
    status_sala ENUM('ativa', 'revisao', 'bloqueada') DEFAULT 'ativa'
);

CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_reserva DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NULL,
    sala_id VARCHAR(50) NOT NULL,
    email_usuario VARCHAR(100) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sala_id) REFERENCES salas(id) ON DELETE CASCADE,
    FOREIGN KEY (email_usuario) REFERENCES usuarios(email) ON DELETE CASCADE,
    INDEX idx_reserva_sala_data_hora (sala_id, data_reserva, hora_inicio),
    INDEX idx_reserva_usuario (email_usuario)
);

CREATE TABLE solicitacoes_professor (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email_professor VARCHAR(100) NOT NULL,
    sala_id VARCHAR(50) NOT NULL,
    turma_atividade VARCHAR(100) NOT NULL,
    finalidade VARCHAR(150) NOT NULL,
    data_solicitacao DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    status_solicitacao ENUM('confirmada', 'em-analise', 'recusada') DEFAULT 'em-analise',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (email_professor) REFERENCES usuarios(email) ON DELETE CASCADE,
    FOREIGN KEY (sala_id) REFERENCES salas(id) ON DELETE CASCADE,
    INDEX idx_sol_sala_data_hora (sala_id, data_solicitacao, hora_inicio, hora_fim),
    INDEX idx_sol_professor (email_professor)
);
