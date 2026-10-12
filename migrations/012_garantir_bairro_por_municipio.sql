/* Migração 012: Garantir que todos os municípios têm pelo menos um bairro
   selecionável no formulário de ocorrências. Usa "<Município> (Sede)" como
   bairro por omissão para municípios que ainda não têm bairros reais
   cadastrados. Os bairros reais devem substituir estes à medida que forem
   confirmados a partir de fonte oficial — isto é só para o sistema não
   ficar "preso" a Ingombota. */
INSERT IGNORE INTO bairros (nome, municipio_id)
SELECT CONCAT(m.nome, ' (Sede)'), m.id
FROM municipios m
WHERE NOT EXISTS (
    SELECT 1 FROM bairros b WHERE b.municipio_id = m.id
);
