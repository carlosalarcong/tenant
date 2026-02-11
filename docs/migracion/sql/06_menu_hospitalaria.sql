-- =====================================================
-- SCRIPT: Inserción de Menús - Categoría Hospitalaria
-- Base de Datos: melisalacolina
-- Tenant: Todas las empresas
-- Autor: Sistema MELISA
-- Fecha: 2026-02-04
-- =====================================================

-- DESCRIPCIÓN:
-- Este script inserta los menús de la categoría Hospitalaria que incluye
-- mantenedores relacionados con atención hospitalaria, nutrición, prescripciones
-- y exámenes físicos.

-- PREREQUISITOS:
-- 1. Debe existir el menú padre "Mantenedores" con nombre 'maintenance'
-- 2. La base de datos debe tener la tabla menu_items con la estructura correcta
-- 3. Los controladores y rutas correspondientes deben estar implementados

-- =====================================================
-- PASO 1: Verificar que existe el menú padre "Mantenedores"
-- =====================================================

DO $$
DECLARE
    v_parent_id INTEGER;
BEGIN
    -- Obtener el ID del menú padre "Mantenedores"
    SELECT id INTO v_parent_id
    FROM menu_items
    WHERE name = 'maintenance'
    LIMIT 1;

    IF v_parent_id IS NULL THEN
        RAISE EXCEPTION 'Error: No existe el menú padre "maintenance". Debe crearse primero.';
    END IF;

    RAISE NOTICE 'Menú padre encontrado con ID: %', v_parent_id;
END $$;

-- =====================================================
-- PASO 2: Crear ítem padre de la categoría Hospitalaria
-- =====================================================

INSERT INTO menu_items (name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at)
SELECT
    'maintainers.hospital',
    'Hospitalaria',
    NULL,
    'bx bx-plus-medical',
    'Mantenedores',
    (SELECT id FROM menu_items WHERE name = 'maintenance' LIMIT 1),
    6,
    true,
    true,
    true,
    '["ROLE_USER"]',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM menu_items WHERE name = 'maintainers.hospital'
);

-- =====================================================
-- PASO 3: Insertar los mantenedores hijos de Hospitalaria
-- =====================================================

-- Variables para el parent_id
DO $$
DECLARE
    v_hospital_id INTEGER;
BEGIN
    -- Obtener el ID del menú padre Hospitalaria
    SELECT id INTO v_hospital_id
    FROM menu_items
    WHERE name = 'maintainers.hospital'
    LIMIT 1;

    IF v_hospital_id IS NULL THEN
        RAISE EXCEPTION 'Error: No se pudo crear el menú padre "maintainers.hospital"';
    END IF;

    RAISE NOTICE 'Menú Hospitalaria creado con ID: %', v_hospital_id;

    -- =====================================================
    -- SECCIÓN: Atención y Cuidado (Care)
    -- =====================================================

    INSERT INTO menu_items (name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at)
    VALUES
    ('maintainers.hospital.care_category', 'Categorías de Atención', 'app_maintainers_hospital_care_category_index', 'bx bx-category-alt', 'maintainers.hospital', v_hospital_id, 1, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.care_closure_destination', 'Destinos de Cierre de Atención', 'app_maintainers_hospital_care_closure_destination_index', 'bx bx-exit', 'maintainers.hospital', v_hospital_id, 2, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.care_intervention', 'Intervenciones de Atención', 'app_maintainers_hospital_care_intervention_index', 'bx bx-first-aid', 'maintainers.hospital', v_hospital_id, 3, true, true, true, '["ROLE_USER"]', NOW(), NOW())
    ON CONFLICT (name) DO NOTHING;

    -- =====================================================
    -- SECCIÓN: Acciones Clínicas (Clinical Actions)
    -- =====================================================

    INSERT INTO menu_items (name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at)
    VALUES
    ('maintainers.hospital.clinical_action_category', 'Categorías de Acciones Clínicas', 'app_maintainers_hospital_clinical_action_category_index', 'bx bx-list-ul', 'maintainers.hospital', v_hospital_id, 4, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.clinical_action_question', 'Preguntas de Acciones Clínicas', 'app_maintainers_hospital_clinical_action_question_index', 'bx bx-help-circle', 'maintainers.hospital', v_hospital_id, 5, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.clinical_action_answer', 'Respuestas de Acciones Clínicas', 'app_maintainers_hospital_clinical_action_answer_index', 'bx bx-message-square-check', 'maintainers.hospital', v_hospital_id, 6, true, true, true, '["ROLE_USER"]', NOW(), NOW())
    ON CONFLICT (name) DO NOTHING;

    -- =====================================================
    -- SECCIÓN: Tipos Generales
    -- =====================================================

    INSERT INTO menu_items (name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at)
    VALUES
    ('maintainers.hospital.dosage_type', 'Tipos de Dosificación', 'app_maintainers_hospital_dosage_type_index', 'bx bx-dots-horizontal-rounded', 'maintainers.hospital', v_hospital_id, 7, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.eating_disorder_history', 'Historial de Trastornos Alimentarios', 'app_maintainers_hospital_eating_disorder_history_index', 'bx bx-restaurant', 'maintainers.hospital', v_hospital_id, 8, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.intoxication_state', 'Estados de Intoxicación', 'app_maintainers_hospital_intoxication_state_index', 'bx bx-error-circle', 'maintainers.hospital', v_hospital_id, 9, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.medical_device', 'Dispositivos Médicos', 'app_maintainers_hospital_medical_device_index', 'bx bx-devices', 'maintainers.hospital', v_hospital_id, 10, true, true, true, '["ROLE_USER"]', NOW(), NOW())
    ON CONFLICT (name) DO NOTHING;

    -- =====================================================
    -- SECCIÓN: Nutrición (Nutritionist)
    -- =====================================================

    INSERT INTO menu_items (name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at)
    VALUES
    ('maintainers.hospital.nutritional_diagnosis', 'Diagnósticos Nutricionales', 'app_maintainers_hospital_nutritional_diagnosis_index', 'bx bx-food-menu', 'maintainers.hospital', v_hospital_id, 11, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.nutritionist_bmi_index', 'Índices IMC Nutricionista', 'app_maintainers_hospital_nutritionist_bmi_index_index', 'bx bx-body', 'maintainers.hospital', v_hospital_id, 12, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.nutritionist_index_classification', 'Clasificación Índices Nutricionista', 'app_maintainers_hospital_nutritionist_index_classification_index', 'bx bx-category', 'maintainers.hospital', v_hospital_id, 13, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.nutritionist_te_index', 'Índices TE Nutricionista', 'app_maintainers_hospital_nutritionist_te_index_index', 'bx bx-bar-chart-alt-2', 'maintainers.hospital', v_hospital_id, 14, true, true, true, '["ROLE_USER"]', NOW(), NOW())
    ON CONFLICT (name) DO NOTHING;

    -- =====================================================
    -- SECCIÓN: Examen Físico (Physical Exam)
    -- =====================================================

    INSERT INTO menu_items (name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at)
    VALUES
    ('maintainers.hospital.physical_exam_grouping', 'Agrupaciones de Examen Físico', 'app_maintainers_hospital_physical_exam_grouping_index', 'bx bx-group', 'maintainers.hospital', v_hospital_id, 15, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.physical_exam_field', 'Campos de Examen Físico', 'app_maintainers_hospital_physical_exam_field_index', 'bx bx-list-check', 'maintainers.hospital', v_hospital_id, 16, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.physical_exam_base_field', 'Campos Base de Examen Físico', 'app_maintainers_hospital_physical_exam_base_field_index', 'bx bx-checkbox-square', 'maintainers.hospital', v_hospital_id, 17, true, true, true, '["ROLE_USER"]', NOW(), NOW())
    ON CONFLICT (name) DO NOTHING;

    -- =====================================================
    -- SECCIÓN: Prescripciones (Prescription)
    -- =====================================================

    INSERT INTO menu_items (name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at)
    VALUES
    ('maintainers.hospital.prescription_type', 'Tipos de Prescripción', 'app_maintainers_hospital_prescription_type_index', 'bx bx-file-blank', 'maintainers.hospital', v_hospital_id, 18, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.prescription_dispensation', 'Dispensaciones de Prescripción', 'app_maintainers_hospital_prescription_dispensation_index', 'bx bx-package', 'maintainers.hospital', v_hospital_id, 19, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.prescription_dosage', 'Dosificaciones de Prescripción', 'app_maintainers_hospital_prescription_dosage_index', 'bx bx-injection', 'maintainers.hospital', v_hospital_id, 20, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.prescription_format', 'Formatos de Prescripción', 'app_maintainers_hospital_prescription_format_index', 'bx bx-layout', 'maintainers.hospital', v_hospital_id, 21, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.prescription_frequency', 'Frecuencias de Prescripción', 'app_maintainers_hospital_prescription_frequency_index', 'bx bx-time', 'maintainers.hospital', v_hospital_id, 22, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.prescription_route', 'Vías de Prescripción', 'app_maintainers_hospital_prescription_route_index', 'bx bx-right-arrow-alt', 'maintainers.hospital', v_hospital_id, 23, true, true, true, '["ROLE_USER"]', NOW(), NOW()),
    ('maintainers.hospital.prescription_rule_detail', 'Detalles de Reglas de Prescripción', 'app_maintainers_hospital_prescription_rule_detail_index', 'bx bx-detail', 'maintainers.hospital', v_hospital_id, 24, true, true, true, '["ROLE_USER"]', NOW(), NOW())
    ON CONFLICT (name) DO NOTHING;

    RAISE NOTICE 'Se han insertado correctamente % mantenedores en la categoría Hospitalaria', 24;
END $$;

-- =====================================================
-- PASO 4: Verificación de la inserción
-- =====================================================

DO $$
DECLARE
    v_count INTEGER;
    v_hospital_id INTEGER;
BEGIN
    SELECT id INTO v_hospital_id FROM menu_items WHERE name = 'maintainers.hospital' LIMIT 1;
    SELECT COUNT(*) INTO v_count FROM menu_items WHERE parent_id = v_hospital_id;

    RAISE NOTICE '============================================';
    RAISE NOTICE 'RESUMEN DE INSERCIÓN - CATEGORÍA HOSPITALARIA';
    RAISE NOTICE '============================================';
    RAISE NOTICE 'Menú padre ID: %', v_hospital_id;
    RAISE NOTICE 'Total de mantenedores insertados: %', v_count;
    RAISE NOTICE '============================================';

    IF v_count < 24 THEN
        RAISE WARNING 'ADVERTENCIA: Se esperaban 24 mantenedores pero solo se insertaron %', v_count;
    END IF;
END $$;

-- =====================================================
-- ROLLBACK (Ejecutar solo si se necesita deshacer)
-- =====================================================

/*
-- Para eliminar todos los menús de Hospitalaria:

DELETE FROM menu_items
WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintainers.hospital');

DELETE FROM menu_items
WHERE name = 'maintainers.hospital';

-- Verificar eliminación:
SELECT COUNT(*) FROM menu_items WHERE name LIKE 'maintainers.hospital%';
*/

-- =====================================================
-- NOTAS IMPORTANTES
-- =====================================================

-- 1. Este script es idempotente: se puede ejecutar múltiples veces
--    sin duplicar registros gracias a ON CONFLICT DO NOTHING
--
-- 2. Las rutas siguen el formato:
--    app_maintainers_hospital_{entity}_index
--
-- 3. Todos los menús requieren autenticación (requires_auth = true)
--    y están disponibles para ROLE_USER
--
-- 4. Los íconos utilizados son de la librería Boxicons (bx-)
--
-- 5. Después de ejecutar este script, es necesario:
--    - Limpiar la caché de Symfony: php bin/console cache:clear
--    - Verificar que las rutas existan: php bin/console debug:router | grep hospital
--
-- 6. Para verificar la inserción en la base de datos:
--    SELECT name, label, route, position
--    FROM menu_items
--    WHERE name LIKE 'maintainers.hospital%'
--    ORDER BY position;

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
