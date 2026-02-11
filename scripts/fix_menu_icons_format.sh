#!/bin/bash

# Script para corregir formato de iconos en menu_items
# Formato correcto: "bx bx-nombre" o "fas fa-nombre"

PGPASSWORD=melisamelisa

for DB in melisalacolina melisa_template melisahospital; do
    echo "=== Corrigiendo iconos en $DB ==="
    
    psql -h localhost -U melisa -d $DB <<EOF

-- Corregir iconos que empiezan con solo "bx-" (sin el "bx " al inicio)
UPDATE menu_items 
SET icon = 'bx ' || icon 
WHERE icon LIKE 'bx-%' AND icon NOT LIKE 'bx bx-%';

-- Verificar que todos los iconos estén en formato correcto
SELECT id, name, icon 
FROM menu_items 
WHERE icon IS NOT NULL 
ORDER BY id;

EOF
    
    echo ""
done

echo "=== Corrección de iconos completada ==="
