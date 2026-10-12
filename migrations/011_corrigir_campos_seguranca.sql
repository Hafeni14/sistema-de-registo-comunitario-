/* Migração 011: Repor colunas da migração 004, que nunca chegaram a ser
   criadas devido ao bug do comentário "--" no runner (ver migrate.php).
   ADD COLUMN tolera coluna já existente (erro 1060 é ignorado pelo runner). */
ALTER TABLE utilizadores
  ADD COLUMN telefone VARCHAR(20) NULL,
  ADD COLUMN numero_bi VARCHAR(20) NULL UNIQUE,
  ADD COLUMN data_nascimento DATE NULL;
