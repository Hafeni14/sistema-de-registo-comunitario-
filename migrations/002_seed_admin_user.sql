-- Migration 002: Inserir utilizador administrador padrão (senha: 123456)
INSERT IGNORE INTO utilizadores (nome, email, senha, is_admin) VALUES
    ('Administrador', 'admin@sistema.com', '$2y$10$wVvo0dZPwNrAlQCPIgSfJetwPVfDGVQKBYfuwlusAoD/lpT./JMy.', 1);
