/* Migração 010: Routing geográfico das ocorrências (denormalizado para performance) */
ALTER TABLE ocorrencias
    ADD COLUMN municipio_id INT NULL AFTER bairro_id,
    ADD COLUMN provincia_id INT NULL AFTER municipio_id,
    ADD COLUMN responsavel_id INT NULL AFTER utilizador_id;

/* Preencher município/província das ocorrências já existentes, a partir do bairro */
UPDATE ocorrencias o
JOIN bairros b ON o.bairro_id = b.id
SET o.municipio_id = b.municipio_id;

UPDATE ocorrencias o
JOIN municipios m ON o.municipio_id = m.id
SET o.provincia_id = m.provincia_id;

ALTER TABLE ocorrencias
    MODIFY municipio_id INT NOT NULL,
    MODIFY provincia_id INT NOT NULL,
    ADD FOREIGN KEY (municipio_id) REFERENCES municipios(id),
    ADD FOREIGN KEY (provincia_id) REFERENCES provincias(id),
    ADD FOREIGN KEY (responsavel_id) REFERENCES utilizadores(id),
    ADD INDEX idx_ocorrencias_estado (estado),
    ADD INDEX idx_ocorrencias_tipo (tipo),
    ADD INDEX idx_ocorrencias_provincia (provincia_id),
    ADD INDEX idx_ocorrencias_municipio (municipio_id);
