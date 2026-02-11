#!/bin/bash

# Script para insertar los 24 mantenedores de Hospitalaria en el menú
# Categoría: Hospitalaria (hijo de Mantenedores)
# IDs: 97-121 (MAX actual es 96)

set -e

DB_USER="melisa"
DB_HOST="localhost"
export PGPASSWORD="melisamelisa"

TENANTS=("melisalacolina" "melisahospital" "melisa_template")

echo "🏥 Insertando mantenedores de Hospitalaria en el menú..."

for TENANT in "${TENANTS[@]}"; do
    echo ""
    echo "📦 Procesando tenant: $TENANT"
    
    # Obtener ID del padre "Mantenedores" (debería ser 4)
    MANTENEDORES_ID=$(psql -h $DB_HOST -U $DB_USER -d $TENANT -t -c "SELECT id FROM menu_items WHERE name = 'mantenedores' LIMIT 1;" | xargs)
    
    if [ -z "$MANTENEDORES_ID" ]; then
        echo "❌ Error: No se encontró el menú 'Mantenedores'"
        continue
    fi
    
    echo "  ℹ️  Parent ID (Mantenedores): $MANTENEDORES_ID"
    
    # Categoría Hospitalaria (ID 97)
    psql -h $DB_HOST -U $DB_USER -d $TENANT <<EOF
-- Categoría Hospitalaria
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at)
VALUES (97, 'mantenedores_hospitalaria', 'Hospitalaria', NULL, 'bx-clinic', 'maintainers', $MANTENEDORES_ID, 7, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW())
ON CONFLICT (id) DO NOTHING;

-- Fase 1: 12 mantenedores simples (IDs 98-109)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at) VALUES
(98, 'medical_device', 'Dispositivos Médicos', 'app_maintainers_hospital_medical_device_index', 'bx-devices', 'maintainers', 97, 1, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(99, 'intoxication_state', 'Estados de Ebriedad', 'app_maintainers_hospital_intoxication_state_index', 'bx-drink', 'maintainers', 97, 2, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(100, 'care_closure_destination', 'Destinos Cierre Atención', 'app_maintainers_hospital_care_closure_destination_index', 'bx-exit', 'maintainers', 97, 3, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(101, 'dosage_type', 'Tipos de Posología', 'app_maintainers_hospital_dosage_type_index', 'bx-time-five', 'maintainers', 97, 4, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(102, 'prescription_type', 'Tipos de Receta', 'app_maintainers_hospital_prescription_type_index', 'bx-file-blank', 'maintainers', 97, 5, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(103, 'care_category', 'Categorías de Cuidados', 'app_maintainers_hospital_care_category_index', 'bx-heart', 'maintainers', 97, 6, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(104, 'nutritionist_bmi_index', 'Índices IMC', 'app_maintainers_hospital_nutritionist_bmi_index_index', 'bx-body', 'maintainers', 97, 7, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(105, 'nutritionist_te_index', 'Índices Talla/Edad', 'app_maintainers_hospital_nutritionist_te_index_index', 'bx-ruler', 'maintainers', 97, 8, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(106, 'nutritionist_index_classification', 'Clasificaciones Índices', 'app_maintainers_hospital_nutritionist_index_classification_index', 'bx-category', 'maintainers', 97, 9, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(107, 'nutritional_diagnosis', 'Diagnósticos Nutricionales', 'app_maintainers_hospital_nutritional_diagnosis_index', 'bx-food-menu', 'maintainers', 97, 10, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(108, 'eating_disorder_history', 'Antecedentes TCA', 'app_maintainers_hospital_eating_disorder_history_index', 'bx-clinic', 'maintainers', 97, 11, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(109, 'clinical_action_category', 'Categorías Acción Clínica', 'app_maintainers_hospital_clinical_action_category_index', 'bx-health', 'maintainers', 97, 12, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW())
ON CONFLICT (id) DO NOTHING;

-- Fase 2: 6 mantenedores simple+ (IDs 110-115)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at) VALUES
(110, 'prescription_frequency', 'Frecuencias de Receta', 'app_maintainers_hospital_prescription_frequency_index', 'bx-time-five', 'maintainers', 97, 13, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(111, 'prescription_route', 'Vías de Administración', 'app_maintainers_hospital_prescription_route_index', 'bx-injection', 'maintainers', 97, 14, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(112, 'prescription_dosage', 'Dosis de Receta', 'app_maintainers_hospital_prescription_dosage_index', 'bx-calculator', 'maintainers', 97, 15, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(113, 'prescription_format', 'Formatos de Receta', 'app_maintainers_hospital_prescription_format_index', 'bx-capsule', 'maintainers', 97, 16, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(114, 'prescription_dispensation', 'Dispensaciones de Receta', 'app_maintainers_hospital_prescription_dispensation_index', 'bx-package', 'maintainers', 97, 17, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(115, 'physical_exam_grouping', 'Agrupaciones Examen Físico', 'app_maintainers_hospital_physical_exam_grouping_index', 'bx-list-ul', 'maintainers', 97, 18, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW())
ON CONFLICT (id) DO NOTHING;

-- Fase 3: 3 mantenedores moderados (IDs 116-118)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at) VALUES
(116, 'care_intervention', 'Cuidados Clínicos', 'app_maintainers_hospital_care_intervention_index', 'bx-heart', 'maintainers', 97, 19, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(117, 'clinical_action_question', 'Preguntas Acción Clínica', 'app_maintainers_hospital_clinical_action_question_index', 'bx-question-mark', 'maintainers', 97, 20, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(118, 'clinical_action_answer', 'Respuestas Acción Clínica', 'app_maintainers_hospital_clinical_action_answer_index', 'bx-check-circle', 'maintainers', 97, 21, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW())
ON CONFLICT (id) DO NOTHING;

-- Fase 4: 3 mantenedores complejos (IDs 119-121)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at) VALUES
(119, 'physical_exam_base_field', 'Campos Base Examen Físico', 'app_maintainers_hospital_physical_exam_base_field_index', 'bx-body', 'maintainers', 97, 22, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(120, 'physical_exam_field', 'Campos Examen Físico', 'app_maintainers_hospital_physical_exam_field_index', 'bx-pulse', 'maintainers', 97, 23, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW()),
(121, 'prescription_rule_detail', 'Reglas de Prescripción', 'app_maintainers_hospital_prescription_rule_detail_index', 'bx-list-ul', 'maintainers', 97, 24, true, true, true, '["ROLE_ADMIN","ROLE_MAINTAINER"]', NOW(), NOW())
ON CONFLICT (id) DO NOTHING;
EOF

    if [ $? -eq 0 ]; then
        echo "  ✅ Menú de Hospitalaria insertado exitosamente en $TENANT"
    else
        echo "  ❌ Error insertando menú en $TENANT"
    fi
done

echo ""
echo "✨ Proceso completado. 25 items de menú insertados (1 categoría + 24 mantenedores) en 3 tenants."
