-- Migration 003: Normalizar estado 'Resolvido' para 'Resolvida' (consistência)
UPDATE ocorrencias SET estado = 'Resolvida' WHERE estado = 'Resolvido';
