-- Script de inicializacao da Base de Dados
-- Sistema de Registo de Ocorrencias Comunitarias

-- Definir charset da conexao
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

USE ocorrencias_comunitarias;

-- Tabela de Utilizadores
CREATE TABLE IF NOT EXISTS utilizadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    data_registo TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de Bairros
CREATE TABLE IF NOT EXISTS bairros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de Ocorrencias
CREATE TABLE IF NOT EXISTS ocorrencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT NOT NULL,
    imagem LONGTEXT NULL,
    tipo ENUM('Água', 'Energia', 'Lixo', 'Segurança') NOT NULL,
    bairro_id INT NOT NULL,
    utilizador_id INT NOT NULL,
    estado VARCHAR(50) DEFAULT 'Pendente',
    data_registo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bairro_id) REFERENCES bairros(id),
    FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inserir Bairros de Exemplo
INSERT INTO bairros (nome) VALUES 
    ('Centro'),
    ('Bairro Norte'),
    ('Bairro Sul'),
    ('Bairro Leste'),
    ('Bairro Oeste'),
    ('Vila Nova'),
    ('Jardim das Flores'),
    ('Parque Industrial');

-- Inserir Utilizador de Teste (senha: 123456)
INSERT INTO utilizadores (nome, email, senha) VALUES 
    ('Utilizador Teste', 'teste@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Inserir algumas ocorrências de exemplo
INSERT INTO ocorrencias (titulo, descricao, tipo, bairro_id, utilizador_id, estado) VALUES 
    ('Falta de água na Rua Principal', 'Não há abastecimento de água há 3 dias na rua principal do bairro.', 'Água', 1, 1, 'Pendente'),
    ('Poste de luz danificado', 'Poste de iluminação pública caído após tempestade na Av. Central.', 'Energia', 2, 1, 'Pendente'),
    ('Acúmulo de lixo', 'Grande quantidade de lixo acumulado no terreno baldio próximo à escola.', 'Lixo', 3, 1, 'Resolvida');
