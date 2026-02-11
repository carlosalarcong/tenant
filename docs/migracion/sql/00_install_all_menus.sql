-- =====================================================
-- SCRIPT MAESTRO: Instalación Completa de Menús
-- Base de Datos: melisalacolina
-- Tenant: Todas las empresas
-- Autor: Sistema MELISA
-- Fecha: 2026-02-04
-- =====================================================

-- DESCRIPCIÓN:
-- Este script ejecuta la instalación completa de todos los menús de las
-- categorías: Hospitalaria, Liquidaciones, Presupuesto y Taller.
--
-- TOTAL DE MANTENEDORES A INSERTAR: 33
-- - Hospitalaria: 24 mantenedores
-- - Liquidaciones: 5 mantenedores
-- - Presupuesto: 3 mantenedores
-- - Taller: 1 mantenedor

-- PREREQUISITOS:
-- 1. Debe existir el menú padre "Mantenedores" con nombre 'maintenance'
-- 2. Los controladores y rutas deben estar implementados

-- =====================================================
-- INSTRUCCIONES DE USO:
-- =====================================================
-- Opción 1 (desde psql):
--   \i /var/www/html/melisa_tenant/docs/migracion/sql/00_install_all_menus.sql
--
-- Opción 2 (desde terminal):
--   cat docs/migracion/sql/00_install_all_menus.sql | psql -h localhost -U melisa -d melisalacolina
--
-- Opción 3 (individual):
--   Ejecutar cada archivo numerado (06, 07, 08, 09) por separado
-- =====================================================

\echo '=================================================='
\echo '  INSTALACIÓN DE MENÚS - SISTEMA MELISA'
\echo '=================================================='
\echo ''

-- Verificar prerequisitos
\echo 'Verificando prerequisitos...'

DO $$
DECLARE
    v_parent_id INTEGER;
BEGIN
    SELECT id INTO v_parent_id FROM menu_items WHERE name = 'maintenance' LIMIT 1;

    IF v_parent_id IS NULL THEN
        RAISE EXCEPTION 'Error: No existe el menú padre "maintenance". Debe crearse primero.';
    END IF;

    RAISE NOTICE 'Prerequisitos verificados. Menú padre encontrado con ID: %', v_parent_id;
END $$;

\echo ''
\echo '=================================================='
\echo '  CATEGORÍA 1: HOSPITALARIA (24 mantenedores)'
\echo '=================================================='
\echo ''

\i 06_menu_hospitalaria.sql

\echo ''
\echo '=================================================='
\echo '  CATEGORÍA 2: LIQUIDACIONES (5 mantenedores)'
\echo '=================================================='
\echo ''

\i 07_menu_liquidaciones.sql

\echo ''
\echo '=================================================='
\echo '  CATEGORÍA 3: PRESUPUESTO (3 mantenedores)'
\echo '=================================================='
\echo ''

\i 08_menu_presupuesto.sql

\echo ''
\echo '=================================================='
\echo '  CATEGORÍA 4: TALLER (1 mantenedor)'
\echo '=================================================='
\echo ''

\i 09_menu_taller.sql

\echo ''
\echo '=================================================='
\echo '  RESUMEN FINAL'
\echo '=================================================='
\echo ''

-- Resumen de instalación
DO $$
DECLARE
    v_hospital_count INTEGER;
    v_settlements_count INTEGER;
    v_budget_count INTEGER;
    v_workshop_count INTEGER;
    v_total_count INTEGER;
BEGIN
    -- Contar mantenedores por categoría
    SELECT COUNT(*) INTO v_hospital_count
    FROM menu_items
    WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintainers.hospital' LIMIT 1);

    SELECT COUNT(*) INTO v_settlements_count
    FROM menu_items
    WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintainers.settlements' LIMIT 1);

    SELECT COUNT(*) INTO v_budget_count
    FROM menu_items
    WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintainers.budget' LIMIT 1);

    SELECT COUNT(*) INTO v_workshop_count
    FROM menu_items
    WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintainers.workshop' LIMIT 1);

    v_total_count := v_hospital_count + v_settlements_count + v_budget_count + v_workshop_count;

    RAISE NOTICE '================================================';
    RAISE NOTICE 'INSTALACIÓN COMPLETADA';
    RAISE NOTICE '================================================';
    RAISE NOTICE '';
    RAISE NOTICE 'Mantenedores insertados por categoría:';
    RAISE NOTICE '  - Hospitalaria:   % mantenedores (esperados: 24)', v_hospital_count;
    RAISE NOTICE '  - Liquidaciones:  % mantenedores (esperados: 5)', v_settlements_count;
    RAISE NOTICE '  - Presupuesto:    % mantenedores (esperados: 3)', v_budget_count;
    RAISE NOTICE '  - Taller:         % mantenedores (esperados: 1)', v_workshop_count;
    RAISE NOTICE '';
    RAISE NOTICE 'TOTAL: % mantenedores (esperados: 33)', v_total_count;
    RAISE NOTICE '================================================';

    -- Advertencias si hay diferencias
    IF v_hospital_count < 24 THEN
        RAISE WARNING 'HOSPITALARIA: Se insertaron menos mantenedores de lo esperado';
    END IF;

    IF v_settlements_count < 5 THEN
        RAISE WARNING 'LIQUIDACIONES: Se insertaron menos mantenedores de lo esperado';
    END IF;

    IF v_budget_count < 3 THEN
        RAISE WARNING 'PRESUPUESTO: Se insertaron menos mantenedores de lo esperado';
    END IF;

    IF v_workshop_count < 1 THEN
        RAISE WARNING 'TALLER: Se insertaron menos mantenedores de lo esperado';
    END IF;

    IF v_total_count = 33 THEN
        RAISE NOTICE '';
        RAISE NOTICE 'Instalación completada exitosamente!';
    ELSE
        RAISE WARNING '';
        RAISE WARNING 'Instalación completada con advertencias. Revisar logs.';
    END IF;
END $$;

\echo ''
\echo '=================================================='
\echo '  PRÓXIMOS PASOS'
\echo '=================================================='
\echo ''
\echo '1. Limpiar caché de Symfony:'
\echo '   php bin/console cache:clear'
\echo ''
\echo '2. Verificar rutas:'
\echo '   php bin/console debug:router | grep hospital'
\echo '   php bin/console debug:router | grep settlements'
\echo '   php bin/console debug:router | grep budget'
\echo '   php bin/console debug:router | grep workshop'
\echo ''
\echo '3. Verificar en la interfaz web que los menús aparecen'
\echo ''
\echo '=================================================='
\echo ''

-- Mostrar tabla resumen
\echo 'Tabla de menús instalados:'
\echo ''

SELECT
    CASE
        WHEN name = 'maintainers.hospital' THEN 'Hospitalaria'
        WHEN name = 'maintainers.settlements' THEN 'Liquidaciones'
        WHEN name = 'maintainers.budget' THEN 'Presupuesto'
        WHEN name = 'maintainers.workshop' THEN 'Taller'
    END as "Categoría",
    label as "Nombre en Menú",
    position as "Posición",
    (SELECT COUNT(*) FROM menu_items mi WHERE mi.parent_id = menu_items.id) as "Mantenedores"
FROM menu_items
WHERE name IN ('maintainers.hospital', 'maintainers.settlements', 'maintainers.budget', 'maintainers.workshop')
ORDER BY position;

\echo ''
\echo '=================================================='
\echo '  FIN DE LA INSTALACIÓN'
\echo '=================================================='
