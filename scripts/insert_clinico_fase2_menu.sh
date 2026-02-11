#!/bin/bash

# Script para insertar menu items de Clinico Fase 2 (Exámenes)
# Parent: ID 18 (Clínico)
# IDs: 148-153

PGPASSWORD=melisamelisa

for DB in melisalacolina melisa_template melisahospital; do
    echo "=== Insertando menu items Fase 2 en $DB ==="
    
    psql -h localhost -U melisa -d $DB <<EOF
-- Verificar si ya existen
SELECT 'Existentes:', COUNT(*) FROM menu_items WHERE id BETWEEN 148 AND 153;

-- Insertar solo si no existen
INSERT INTO menu_items (id, name, icon, route, parent_id, "order", is_active, created_at, updated_at)
SELECT 148, 'Agrupaciones Examen', 'fas fa-layer-group', 'app_maintainers_clinical_exam_group_index', 18, 7, true, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE id = 148);

INSERT INTO menu_items (id, name, icon, route, parent_id, "order", is_active, created_at, updated_at)
SELECT 149, 'Agrupaciones Examen Físico', 'fas fa-object-group', 'app_maintainers_clinical_physical_exam_group_index', 18, 8, true, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE id = 149);

INSERT INTO menu_items (id, name, icon, route, parent_id, "order", is_active, created_at, updated_at)
SELECT 150, 'Campos Examen Físico', 'fas fa-list', 'app_maintainers_clinical_physical_exam_field_index', 18, 9, true, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE id = 150);

INSERT INTO menu_items (id, name, icon, route, parent_id, "order", is_active, created_at, updated_at)
SELECT 151, 'Exámenes Prestación', 'fas fa-file-medical', 'app_maintainers_clinical_exam_service_index', 18, 10, true, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE id = 151);

INSERT INTO menu_items (id, name, icon, route, parent_id, "order", is_active, created_at, updated_at)
SELECT 152, 'Tipos Examen Físico', 'fas fa-tags', 'app_maintainers_clinical_physical_exam_type_index', 18, 11, true, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE id = 152);

INSERT INTO menu_items (id, name, icon, route, parent_id, "order", is_active, created_at, updated_at)
SELECT 153, 'Tipos Prestación Examen', 'fas fa-folder-open', 'app_maintainers_clinical_exam_service_type_index', 18, 12, true, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE id = 153);

-- Verificar inserción
SELECT 'Insertados:', COUNT(*) FROM menu_items WHERE id BETWEEN 148 AND 153;
SELECT id, name, route FROM menu_items WHERE id BETWEEN 148 AND 153 ORDER BY id;
EOF
    
    echo ""
done

echo "=== Inserción Fase 2 completada ==="
