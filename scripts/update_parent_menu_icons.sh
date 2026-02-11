#!/bin/bash

# Script para actualizar iconos de categorías padre y sus hijos
# Actualiza los items padre (Liquidaciones, Hospitalaria, Presupuesto, Taller, Clinico)

PGPASSWORD=melisamelisa

for DB in melisalacolina melisa_template melisahospital; do
    echo "=== Actualizando iconos de categorías en $DB ==="
    
    psql -h localhost -U melisa -d $DB <<EOF
-- Actualizar categorías padre
UPDATE menu_items SET icon = 'fas fa-file-invoice-dollar' WHERE name = 'Liquidaciones';
UPDATE menu_items SET icon = 'fas fa-hospital' WHERE name = 'Hospitalaria';
UPDATE menu_items SET icon = 'fas fa-calculator' WHERE name = 'Presupuesto';
UPDATE menu_items SET icon = 'fas fa-tools' WHERE name = 'Taller';
UPDATE menu_items SET icon = 'fas fa-heartbeat' WHERE name = 'Clinico';

-- Verificar actualización de categorías
SELECT id, name, icon FROM menu_items WHERE name IN ('Liquidaciones', 'Hospitalaria', 'Presupuesto', 'Taller', 'Clinico') ORDER BY id;

-- Verificar que todos los hijos de estas categorías tengan iconos
SELECT m.id, m.name, m.icon, p.name as parent_name
FROM menu_items m
LEFT JOIN menu_items p ON m.parent_id = p.id
WHERE p.name IN ('Liquidaciones', 'Hospitalaria', 'Presupuesto', 'Taller', 'Clinico')
  AND (m.icon IS NULL OR m.icon = '')
ORDER BY p.name, m.id;

EOF
    
    echo ""
done

echo "=== Actualización completada ==="
