/* Migração 007: Popular os 16 municípios da província de Luanda (Lei n.º 14/24).
   Os municípios das restantes 20 províncias (326 no total) devem ser
   adicionados em migrações futuras, confirmados a partir da fonte oficial
   (Diário da República, 1.ª Série, n.º 171), em vez de inventados. */
INSERT IGNORE INTO municipios (nome, provincia_id)
SELECT m.nome, p.id
FROM (
    SELECT 'Belas' AS nome UNION ALL
    SELECT 'Cacuaco' UNION ALL
    SELECT 'Camama' UNION ALL
    SELECT 'Cazenga' UNION ALL
    SELECT 'Hoji-ya-Henda' UNION ALL
    SELECT 'Ingombota' UNION ALL
    SELECT 'Kilamba' UNION ALL
    SELECT 'Kilamba Kiaxi' UNION ALL
    SELECT 'Maianga' UNION ALL
    SELECT 'Mulenvos' UNION ALL
    SELECT 'Mussulo' UNION ALL
    SELECT 'Rangel' UNION ALL
    SELECT 'Samba' UNION ALL
    SELECT 'Sambizanga' UNION ALL
    SELECT 'Talatona' UNION ALL
    SELECT 'Viana'
) AS m
CROSS JOIN provincias p
WHERE p.nome = 'Luanda';
