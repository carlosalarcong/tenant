#!/bin/bash

# Script para actualizar iconos de menu items de Liquidaciones, Presupuesto y Taller
# Liquidaciones: IDs 130-135
# Presupuesto: IDs 136-139
# Taller: IDs 140-141

PGPASSWORD=melisamelisa

for DB in melisalacolina melisa_template melisahospital; do
    echo "=== Actualizando iconos en $DB ==="
    
    psql -h localhost -U melisa -d $DB <<EOF
-- LIQUIDACIONES (130-135)
UPDATE menu_items SET icon = 'fas fa-building' WHERE id = 130; -- Asociación Empresa-Usuario
UPDATE menu_items SET icon = 'fas fa-file-invoice-dollar' WHERE id = 131; -- Base Liquidación
UPDATE menu_items SET icon = 'fas fa-user-md' WHERE id = 132; -- Participación Profesional
UPDATE menu_items SET icon = 'fas fa-chart-line' WHERE id = 133; -- UF Diaria
UPDATE menu_items SET icon = 'fas fa-university' WHERE id = 134; -- Cuenta Bancaria

-- PRESUPUESTO (136-139)
UPDATE menu_items SET icon = 'fas fa-receipt' WHERE id = 136; -- Pie Presupuesto
UPDATE menu_items SET icon = 'fas fa-file-invoice' WHERE id = 137; -- Pie Presupuesto por Financiador
UPDATE menu_items SET icon = 'fas fa-money-check-alt' WHERE id = 138; -- Pie Financiador Presupuesto

-- TALLER (140-141)
UPDATE menu_items SET icon = 'fas fa-wrench' WHERE id = 140; -- Taller

-- Verificar actualización
SELECT id, name, icon FROM menu_items WHERE id BETWEEN 130 AND 141 ORDER BY id;
EOF
    
    echo ""
done

echo "=== Actualización de iconos completada ==="
