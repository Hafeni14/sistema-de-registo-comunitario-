/* Migração 009: Hierarquia de papéis (cidadão / responsável / admin nacional) */
ALTER TABLE utilizadores
    ADD COLUMN nivel_acesso ENUM('cidadao', 'responsavel', 'admin_nacional')
        NOT NULL DEFAULT 'cidadao',
    ADD COLUMN municipio_id INT NULL,
    ADD FOREIGN KEY (municipio_id) REFERENCES municipios(id);

/* Migrar quem já era is_admin para o novo nível 'admin_nacional' */
UPDATE utilizadores SET nivel_acesso = 'admin_nacional' WHERE is_admin = 1;
