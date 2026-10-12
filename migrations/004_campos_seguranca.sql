/* Migração 004: Adicionar campos de segurança */
ALTER TABLE utilizadores
  ADD COLUMN telefone VARCHAR(20) NULL,
  ADD COLUMN numero_bi VARCHAR(20) NULL UNIQUE,
  ADD COLUMN data_nascimento DATE NULL;
