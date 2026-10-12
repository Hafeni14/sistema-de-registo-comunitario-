/* Migração 013: Bairros reais do município de Ingombota (Luanda).
   Fontes cruzadas: Wikipédia (en.wikipedia.org/wiki/Ingombota) e UCCLA
   (uccla.pt/membro/membro-efetivo/ingombota), que descreve a divisão em
   comunas segundo a Lei n.º 14/24. Os restantes 15 municípios de Luanda
   (e os das outras 20 províncias) continuam só com o bairro "(Sede)"
   criado pela migração 012 — não inventar nomes sem fonte confirmada. */
INSERT IGNORE INTO bairros (nome, municipio_id)
SELECT b.nome, m.id
FROM (
    SELECT 'Ilha de Luanda' AS nome UNION ALL
    SELECT 'Baixa' UNION ALL
    SELECT 'Cidade Alta' UNION ALL
    SELECT 'Ingombota (Comuna Sede)' UNION ALL
    SELECT 'Maculusso' UNION ALL
    SELECT 'Kinanga' UNION ALL
    SELECT 'Patrice Lumumba'
) AS b
CROSS JOIN municipios m
WHERE m.nome = 'Ingombota';
