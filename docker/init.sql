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
    is_admin TINYINT(1) DEFAULT 0,
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
INSERT INTO utilizadores (nome, email, senha, is_admin) VALUES
    ('Utilizador Teste', 'teste@teste.com', '$2y$10$wVvo0dZPwNrAlQCPIgSfJetwPVfDGVQKBYfuwlusAoD/lpT./JMy.', 0);

-- Inserir Utilizador Administrador (senha: 123456)
INSERT INTO utilizadores (nome, email, senha, is_admin) VALUES
    ('Administrador', 'admin@sistema.com', '$2y$10$wVvo0dZPwNrAlQCPIgSfJetwPVfDGVQKBYfuwlusAoD/lpT./JMy.', 1);

-- Inserir algumas ocorrências de exemplo
INSERT INTO ocorrencias (titulo, descricao, tipo, bairro_id, utilizador_id, estado) VALUES
    ('Falta de água na Rua Principal', 'Não há abastecimento de água há 3 dias na rua principal do bairro.', 'Água', 1, 1, 'Pendente'),
    ('Poste de luz danificado', 'Poste de iluminação pública caído após tempestade na Av. Central.', 'Energia', 2, 1, 'Em Análise'),
    ('Acúmulo de lixo', 'Grande quantidade de lixo acumulado no terreno baldio próximo à escola.', 'Lixo', 3, 1, 'Resolvida'),
    ('Buraco na calçada Av. das Flores', 'Buraco de grande dimensão na calçada, risco para peões.', 'Segurança', 4, 1, 'Em Progresso'),
    ('Iluminação pública apagada', 'Vários postes de luz sem funcionar no bairro há semanas.', 'Energia', 5, 1, 'Aguardando Recursos'),
    ('Lixo não recolhido há semanas', 'Recolha de lixo não realizada há 2 semanas nesta zona.', 'Lixo', 6, 1, 'Rejeitada');

-- Tabela de Likes
CREATE TABLE IF NOT EXISTS likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ocorrencia_id INT NOT NULL,
    utilizador_id INT NOT NULL,
    data_registo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ocorrencia_id) REFERENCES ocorrencias(id) ON DELETE CASCADE,
    FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (ocorrencia_id, utilizador_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabela de Comentários
CREATE TABLE IF NOT EXISTS comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ocorrencia_id INT NOT NULL,
    utilizador_id INT NOT NULL,
    texto TEXT NOT NULL,
    data_registo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ocorrencia_id) REFERENCES ocorrencias(id) ON DELETE CASCADE,
    FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inserir alguns comentários de exemplo
INSERT INTO comentarios (ocorrencia_id, utilizador_id, texto) VALUES 
    (1, 1, 'Também estou com este problema há dias!'),
    (1, 1, 'Já liguei para a companhia de águas mas ninguém resolve.'),
    (2, 1, 'Muito perigoso, especialmente à noite.'),
    (3, 1, 'Finalmente resolveram! Obrigado.');

-- Inserir alguns likes de exemplo
INSERT INTO likes (ocorrencia_id, utilizador_id) VALUES 
    (1, 1),
    (2, 1);
