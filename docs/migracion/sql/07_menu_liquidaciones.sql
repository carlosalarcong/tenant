-- =====================================================
-- SCRIPT: Inserción de Menús - Categoría Liquidaciones
-- Base de Datos: melisalacolina
-- Tenant: Todas las empresas
-- Autor: Sistema MELISA
-- Fecha: 2026-02-04
-- =====================================================

-- DESCRIPCIÓN:
-- Este script inserta los menús de la categoría Liquidaciones que incluye
-- mantenedores relacionados con cuentas bancarias, asociaciones de usuarios,
-- participación profesional y bases de liquidaciones.

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
-- PASO 2: Crear ítem padre de la categoría Liquidaciones
-- =====================================================

INSERT INTO menu_items (name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at)
SELECT
    'maintainers.settlements',
    'Liquidaciones',
    NULL,
    'bx bx-calculator',
    'Mantenedores',
    (SELECT id FROM menu_items WHERE name = 'maintenance' LIMIT 1),
    7,
    true,
    true,
    true,
    '["ROLE_USER"]',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM menu_items WHERE name = 'maintainers.settlements'
);

-- =====================================================
-- PASO 3: Insertar los mantenedores hijos de Liquidaciones
-- =====================================================

-- Variables para el parent_id
DO $$
DECLARE
    v_settlements_id INTEGER;
BEGIN
    -- Obtener el ID del menú padre Liquidaciones
    SELECT id INTO v_settlements_id
    FROM menu_items
    WHERE name = 'maintainers.settlements'
    LIMIT 1;

    IF v_settlements_id IS NULL THEN
        RAISE EXCEPTION 'Error: No se pudo crear el menú padre "maintainers.settlements"';
    END IF;

    RAISE NOTICE 'Menú Liquidaciones creado con ID: %', v_settlements_id;

    -- =====================================================
    -- SECCIÓN: Mantenedores de Liquidaciones
    -- =====================================================

    INSERT INTO menu_items (name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at)
    VALUES
    -- Cuentas Bancarias
    ('maintainers.settlements.bank_account', 'Cuentas Bancarias', 'app_maintainers_settlements_bank_account_index', 'bx bx-wallet', 'maintainers.settlements', v_settlements_id, 1, true, true, true, '["ROLE_USER"]', NOW(), NOW()),

    -- Asociación Usuario-Empresa
    ('maintainers.settlements.company_user_association', 'Asociación Usuario-Empresa', 'app_maintainers_settlements_company_user_association_index', 'bx bx-link', 'maintainers.settlements', v_settlements_id, 2, true, true, true, '["ROLE_USER"]', NOW(), NOW()),

    -- UF Diaria
    ('maintainers.settlements.daily_uf', 'UF Diaria', 'app_maintainers_settlements_daily_uf_index', 'bx bx-calendar-event', 'maintainers.settlements', v_settlements_id, 3, true, true, true, '["ROLE_USER"]', NOW(), NOW()),

    -- Participación Profesional
    ('maintainers.settlements.professional_participation', 'Participación Profesional', 'app_maintainers_settlements_professional_participation_index', 'bx bx-user-check', 'maintainers.settlements', v_settlements_id, 4, true, true, true, '["ROLE_USER"]', NOW(), NOW()),

    -- Base de Liquidaciones
    ('maintainers.settlements.settlement_base', 'Base de Liquidaciones', 'app_maintainers_settlements_settlement_base_index', 'bx bx-calculator', 'maintainers.settlements', v_settlements_id, 5, true, true, true, '["ROLE_USER"]', NOW(), NOW())

    ON CONFLICT (name) DO NOTHING;

    RAISE NOTICE 'Se han insertado correctamente % mantenedores en la categoría Liquidaciones', 5;
END $$;

-- =====================================================
-- PASO 4: Verificación de la inserción
-- =====================================================

DO $$
DECLARE
    v_count INTEGER;
    v_settlements_id INTEGER;
BEGIN
    SELECT id INTO v_settlements_id FROM menu_items WHERE name = 'maintainers.settlements' LIMIT 1;
    SELECT COUNT(*) INTO v_count FROM menu_items WHERE parent_id = v_settlements_id;

    RAISE NOTICE '============================================';
    RAISE NOTICE 'RESUMEN DE INSERCIÓN - CATEGORÍA LIQUIDACIONES';
    RAISE NOTICE '============================================';
    RAISE NOTICE 'Menú padre ID: %', v_settlements_id;
    RAISE NOTICE 'Total de mantenedores insertados: %', v_count;
    RAISE NOTICE '============================================';

    IF v_count < 5 THEN
        RAISE WARNING 'ADVERTENCIA: Se esperaban 5 mantenedores pero solo se insertaron %', v_count;
    END IF;
END $$;

-- =====================================================
-- ROLLBACK (Ejecutar solo si se necesita deshacer)
-- =====================================================

/*
-- Para eliminar todos los menús de Liquidaciones:

DELETE FROM menu_items
WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintainers.settlements');

DELETE FROM menu_items
WHERE name = 'maintainers.settlements';

-- Verificar eliminación:
SELECT COUNT(*) FROM menu_items WHERE name LIKE 'maintainers.settlements%';
*/

-- =====================================================
-- NOTAS IMPORTANTES
-- =====================================================

-- 1. Este script es idempotente: se puede ejecutar múltiples veces
--    sin duplicar registros gracias a ON CONFLICT DO NOTHING
--
-- 2. Las rutas siguen el formato:
--    app_maintainers_settlements_{entity}_index
--
-- 3. Todos los menús requieren autenticación (requires_auth = true)
--    y están disponibles para ROLE_USER
--
-- 4. Los íconos utilizados son de la librería Boxicons (bx-)
--
-- 5. Después de ejecutar este script, es necesario:
--    - Limpiar la caché de Symfony: php bin/console cache:clear
--    - Verificar que las rutas existan: php bin/console debug:router | grep settlements
--
-- 6. Para verificar la inserción en la base de datos:
--    SELECT name, label, route, position
--    FROM menu_items
--    WHERE name LIKE 'maintainers.settlements%'
--    ORDER BY position;
--
-- 7. Relación con otras categorías:
--    - Liquidaciones está relacionada con Tesorería (ya migrada)
--    - Los datos de cuentas bancarias pueden compartir tipos con Tesorería
--    - La UF Diaria es crítica para cálculos financieros

-- =====================================================
-- DESCRIPCIÓN DE MANTENEDORES
-- =====================================================

-- 1. BANK_ACCOUNT (Cuentas Bancarias)
--    - Gestión de cuentas bancarias para liquidaciones
--    - Relacionado con entidades bancarias de Tesorería
--
-- 2. COMPANY_USER_ASSOCIATION (Asociación Usuario-Empresa)
--    - Vinculación de usuarios con empresas/sucursales
--    - Esencial para cálculo de liquidaciones multi-tenant
--
-- 3. DAILY_UF (UF Diaria)
--    - Valores diarios de la Unidad de Fomento
--    - Crítico para cálculos financieros en Chile
--    - Debe actualizarse periódicamente
--
-- 4. PROFESSIONAL_PARTICIPATION (Participación Profesional)
--    - Porcentajes de participación de profesionales en atenciones
--    - Base para distribución de ingresos
--
-- 5. SETTLEMENT_BASE (Base de Liquidaciones)
--    - Configuración base para cálculo de liquidaciones
--    - Parámetros generales del proceso de liquidación

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
