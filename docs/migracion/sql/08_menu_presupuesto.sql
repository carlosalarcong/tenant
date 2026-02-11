-- =====================================================
-- SCRIPT: Inserción de Menús - Categoría Presupuesto
-- Base de Datos: melisalacolina
-- Tenant: Todas las empresas
-- Autor: Sistema MELISA
-- Fecha: 2026-02-04
-- =====================================================

-- DESCRIPCIÓN:
-- Este script inserta los menús de la categoría Presupuesto que incluye
-- mantenedores relacionados con pies de página de presupuestos y
-- configuraciones por financiador.

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
-- PASO 2: Crear ítem padre de la categoría Presupuesto
-- =====================================================

INSERT INTO menu_items (name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at)
SELECT
    'maintainers.budget',
    'Presupuesto',
    NULL,
    'bx bx-receipt',
    'Mantenedores',
    (SELECT id FROM menu_items WHERE name = 'maintenance' LIMIT 1),
    8,
    true,
    true,
    true,
    '["ROLE_USER"]',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM menu_items WHERE name = 'maintainers.budget'
);

-- =====================================================
-- PASO 3: Insertar los mantenedores hijos de Presupuesto
-- =====================================================

-- Variables para el parent_id
DO $$
DECLARE
    v_budget_id INTEGER;
BEGIN
    -- Obtener el ID del menú padre Presupuesto
    SELECT id INTO v_budget_id
    FROM menu_items
    WHERE name = 'maintainers.budget'
    LIMIT 1;

    IF v_budget_id IS NULL THEN
        RAISE EXCEPTION 'Error: No se pudo crear el menú padre "maintainers.budget"';
    END IF;

    RAISE NOTICE 'Menú Presupuesto creado con ID: %', v_budget_id;

    -- =====================================================
    -- SECCIÓN: Mantenedores de Presupuesto
    -- =====================================================

    INSERT INTO menu_items (name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at)
    VALUES
    -- Pie de Presupuesto General
    ('maintainers.budget.budget_footer', 'Pie de Presupuesto', 'app_maintainers_budget_budget_footer_index', 'bx bx-note', 'maintainers.budget', v_budget_id, 1, true, true, true, '["ROLE_USER"]', NOW(), NOW()),

    -- Pie de Presupuesto por Financiador
    ('maintainers.budget.budget_footer_by_funder', 'Pie de Presupuesto por Financiador', 'app_maintainers_budget_budget_footer_by_funder_index', 'bx bx-user-pin', 'maintainers.budget', v_budget_id, 2, true, true, true, '["ROLE_USER"]', NOW(), NOW()),

    -- Pie del Financiador
    ('maintainers.budget.budget_funder_footer', 'Pie del Financiador', 'app_maintainers_budget_budget_funder_footer_index', 'bx bx-file-blank', 'maintainers.budget', v_budget_id, 3, true, true, true, '["ROLE_USER"]', NOW(), NOW())

    ON CONFLICT (name) DO NOTHING;

    RAISE NOTICE 'Se han insertado correctamente % mantenedores en la categoría Presupuesto', 3;
END $$;

-- =====================================================
-- PASO 4: Verificación de la inserción
-- =====================================================

DO $$
DECLARE
    v_count INTEGER;
    v_budget_id INTEGER;
BEGIN
    SELECT id INTO v_budget_id FROM menu_items WHERE name = 'maintainers.budget' LIMIT 1;
    SELECT COUNT(*) INTO v_count FROM menu_items WHERE parent_id = v_budget_id;

    RAISE NOTICE '============================================';
    RAISE NOTICE 'RESUMEN DE INSERCIÓN - CATEGORÍA PRESUPUESTO';
    RAISE NOTICE '============================================';
    RAISE NOTICE 'Menú padre ID: %', v_budget_id;
    RAISE NOTICE 'Total de mantenedores insertados: %', v_count;
    RAISE NOTICE '============================================';

    IF v_count < 3 THEN
        RAISE WARNING 'ADVERTENCIA: Se esperaban 3 mantenedores pero solo se insertaron %', v_count;
    END IF;
END $$;

-- =====================================================
-- ROLLBACK (Ejecutar solo si se necesita deshacer)
-- =====================================================

/*
-- Para eliminar todos los menús de Presupuesto:

DELETE FROM menu_items
WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintainers.budget');

DELETE FROM menu_items
WHERE name = 'maintainers.budget';

-- Verificar eliminación:
SELECT COUNT(*) FROM menu_items WHERE name LIKE 'maintainers.budget%';
*/

-- =====================================================
-- NOTAS IMPORTANTES
-- =====================================================

-- 1. Este script es idempotente: se puede ejecutar múltiples veces
--    sin duplicar registros gracias a ON CONFLICT DO NOTHING
--
-- 2. Las rutas siguen el formato:
--    app_maintainers_budget_{entity}_index
--
-- 3. Todos los menús requieren autenticación (requires_auth = true)
--    y están disponibles para ROLE_USER
--
-- 4. Los íconos utilizados son de la librería Boxicons (bx-)
--
-- 5. Después de ejecutar este script, es necesario:
--    - Limpiar la caché de Symfony: php bin/console cache:clear
--    - Verificar que las rutas existan: php bin/console debug:router | grep budget
--
-- 6. Para verificar la inserción en la base de datos:
--    SELECT name, label, route, position
--    FROM menu_items
--    WHERE name LIKE 'maintainers.budget%'
--    ORDER BY position;
--
-- 7. Relación con otras categorías:
--    - Presupuesto está relacionado con Comercial (clientes/financiadores)
--    - Los pies de presupuesto son plantillas para documentos comerciales

-- =====================================================
-- DESCRIPCIÓN DE MANTENEDORES
-- =====================================================

-- 1. BUDGET_FOOTER (Pie de Presupuesto)
--    - Plantillas de texto para el pie de página de presupuestos
--    - Información legal, términos y condiciones generales
--    - Aplicable a todos los presupuestos de la empresa
--
-- 2. BUDGET_FOOTER_BY_FUNDER (Pie de Presupuesto por Financiador)
--    - Asociación entre financiadores y pies de presupuesto específicos
--    - Permite personalizar el pie según el tipo de financiador
--    - Ejemplo: FONASA, ISAPRE, PARTICULAR requieren textos diferentes
--
-- 3. BUDGET_FUNDER_FOOTER (Pie del Financiador)
--    - Configuración específica del pie de página por financiador
--    - Incluye términos específicos del convenio con cada financiador
--    - Información de contacto y procedimientos específicos

-- =====================================================
-- CASOS DE USO COMUNES
-- =====================================================

-- Ejemplo de jerarquía:
-- 1. Se crea un BUDGET_FOOTER genérico con términos generales
-- 2. Se crea un BUDGET_FUNDER_FOOTER específico para FONASA
-- 3. Se asocia mediante BUDGET_FOOTER_BY_FUNDER
-- 4. Al generar presupuesto para paciente FONASA, se usa el pie específico

-- Flujo de trabajo:
-- Mantenedor Comercial → Presupuesto → Asocia Financiador → Aplica Pie

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
