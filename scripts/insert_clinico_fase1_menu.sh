#!/bin/bash

# Script para insertar menu items de Clinico Fase 1
# Parent: ID 18 (Clínico)
# IDs: 142-147

PGPASSWORD=melisamelisa

for DB in melisalacolina melisa_template melisahospital; do
    echo "=== Insertando menu items en $DB ==="
    
    psql -h localhost -U melisa -d $DB <<EOF
-- Verificar si ya existen
SELECT 'Existentes:', COUNT(*) FROM menu_items WHERE id BETWEEN 142 AND 147;

-- Insertar solo si no existen
INSERT INTO menu_items (id, name, icon, route, parent_id, "order", is_active, created_at, updated_at)
SELECT 142, 'Antecedentes Médicos', 'fas fa-notes-medical', 'app_maintainers_clinical_medical_history_index', 18, 1, true, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE id = 142);

INSERT INTO menu_items (id, name, icon, route, parent_id, "order", is_active, created_at, updated_at)
SELECT 143, 'Diagnósticos', 'fas fa-stethoscope', 'app_maintainers_clinical_diagnosis_index', 18, 2, true, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE id = 143);

INSERT INTO menu_items (id, name, icon, route, parent_id, "order", is_active, created_at, updated_at)
SELECT 144, 'Diagnósticos Inmunoterapia', 'fas fa-syringe', 'app_maintainers_clinical_immunotherapy_diagnosis_index', 18, 3, true, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE id = 144);

INSERT INTO menu_items (id, name, icon, route, parent_id, "order", is_active, created_at, updated_at)
SELECT 145, 'Diagnósticos por Patología', 'fas fa-microscope', 'app_maintainers_clinical_diagnosis_by_pathology_index', 18, 4, true, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE id = 145);

INSERT INTO menu_items (id, name, icon, route, parent_id, "order", is_active, created_at, updated_at)
SELECT 146, 'Estados de Diagnóstico', 'fas fa-clipboard-check', 'app_maintainers_clinical_diagnosis_status_index', 18, 5, true, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE id = 146);

INSERT INTO menu_items (id, name, icon, route, parent_id, "order", is_active, created_at, updated_at)
SELECT 147, 'Tipos de Antecedentes', 'fas fa-list-alt', 'app_maintainers_clinical_medical_history_type_index', 18, 6, true, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE id = 147);

-- Verificar inserción
SELECT 'Insertados:', COUNT(*) FROM menu_items WHERE id BETWEEN 142 AND 147;
SELECT id, name, route FROM menu_items WHERE id BETWEEN 142 AND 147 ORDER BY id;
EOF
    
    echo ""
done

echo "=== Inserción completada ==="
