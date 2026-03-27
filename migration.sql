-- =============================================================
-- MIGRATION: Adicionar suporte a Admin e novos estados
-- Execute este script se a base de dados já existia antes destas alterações
-- =============================================================

USE ocorrencias_comunitarias;

-- 1. Adicionar coluna is_admin à tabela utilizadores (ignorar se já existir)
ALTER TABLE utilizadores ADD COLUMN is_admin TINYINT(1) DEFAULT 0;

-- 2. Inserir utilizador administrador (senha: 123456)
INSERT IGNORE INTO utilizadores (nome, email, senha, is_admin) VALUES
    ('Administrador', 'admin@sistema.com', '$2y$10$wVvo0dZPwNrAlQCPIgSfJetwPVfDGVQKBYfuwlusAoD/lpT./JMy.', 1);

-- 3. Promover utilizador existente a admin (opcional - ajuste o email conforme necessário)
-- UPDATE utilizadores SET is_admin = 1 WHERE email = 'seu@email.com';

-- 4. O campo 'estado' já é VARCHAR(50) e suporta os novos valores:
--    'Pendente', 'Em Análise', 'Em Progresso', 'Aguardando Recursos', 'Resolvida', 'Rejeitada'
--    Nenhuma alteração de esquema necessária para os estados.

-- 5. Normalizar estados antigos 'Resolvido' para 'Resolvida' (consistência)
UPDATE ocorrencias SET estado = 'Resolvida' WHERE estado = 'Resolvido';
