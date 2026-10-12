/* Migração 008: Ligar bairros ao município (em vez de lista solta) */
ALTER TABLE bairros ADD COLUMN municipio_id INT NULL AFTER nome;

/* Bairros de teste existentes ficam temporariamente atribuídos a Ingombota
   (sede da província de Luanda), só para não quebrar a integridade
   referencial. Substitui por bairros reais quando fores popular dados
   de produção. */
UPDATE bairros
SET municipio_id = (SELECT id FROM municipios WHERE nome = 'Ingombota' LIMIT 1)
WHERE municipio_id IS NULL;

ALTER TABLE bairros MODIFY municipio_id INT NOT NULL;
ALTER TABLE bairros ADD FOREIGN KEY (municipio_id) REFERENCES municipios(id);
ALTER TABLE bairros ADD INDEX idx_bairros_municipio (municipio_id);
