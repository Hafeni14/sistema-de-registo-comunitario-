-- Migration 001: Adicionar coluna is_admin à tabela utilizadores
ALTER TABLE utilizadores ADD COLUMN is_admin TINYINT(1) DEFAULT 0;
