-- =====================================================
-- SCRIPT: Inserción de Menús - Categoría Taller
-- Base de Datos: melisalacolina
-- Tenant: Todas las empresas
-- Autor: Sistema MELISA
-- Fecha: 2026-02-04
-- =====================================================

-- DESCRIPCIÓN:
-- Este script inserta los menús de la categoría Taller que incluye
-- el mantenedor de talleres/workshops para actividades grupales,
-- capacitaciones o sesiones terapéuticas grupales.

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
-- PASO 2: Crear ítem padre de la categoría Taller
-- =====================================================

INSERT INTO menu_items (name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at)
SELECT
    'maintainers.workshop',
    'Taller',
    NULL,
    'bx bx-chalkboard',
    'Mantenedores',
    (SELECT id FROM menu_items WHERE name = 'maintenance' LIMIT 1),
    9,
    true,
    true,
    true,
    '["ROLE_USER"]',
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM menu_items WHERE name = 'maintainers.workshop'
);

-- =====================================================
-- PASO 3: Insertar los mantenedores hijos de Taller
-- =====================================================

-- Variables para el parent_id
DO $$
DECLARE
    v_workshop_id INTEGER;
BEGIN
    -- Obtener el ID del menú padre Taller
    SELECT id INTO v_workshop_id
    FROM menu_items
    WHERE name = 'maintainers.workshop'
    LIMIT 1;

    IF v_workshop_id IS NULL THEN
        RAISE EXCEPTION 'Error: No se pudo crear el menú padre "maintainers.workshop"';
    END IF;

    RAISE NOTICE 'Menú Taller creado con ID: %', v_workshop_id;

    -- =====================================================
    -- SECCIÓN: Mantenedores de Taller
    -- =====================================================

    INSERT INTO menu_items (name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at)
    VALUES
    -- Talleres
    ('maintainers.workshop.workshop', 'Talleres', 'app_maintainers_workshop_workshop_index', 'bx bx-group', 'maintainers.workshop', v_workshop_id, 1, true, true, true, '["ROLE_USER"]', NOW(), NOW())

    ON CONFLICT (name) DO NOTHING;

    RAISE NOTICE 'Se han insertado correctamente % mantenedores en la categoría Taller', 1;
END $$;

-- =====================================================
-- PASO 4: Verificación de la inserción
-- =====================================================

DO $$
DECLARE
    v_count INTEGER;
    v_workshop_id INTEGER;
BEGIN
    SELECT id INTO v_workshop_id FROM menu_items WHERE name = 'maintainers.workshop' LIMIT 1;
    SELECT COUNT(*) INTO v_count FROM menu_items WHERE parent_id = v_workshop_id;

    RAISE NOTICE '============================================';
    RAISE NOTICE 'RESUMEN DE INSERCIÓN - CATEGORÍA TALLER';
    RAISE NOTICE '============================================';
    RAISE NOTICE 'Menú padre ID: %', v_workshop_id;
    RAISE NOTICE 'Total de mantenedores insertados: %', v_count;
    RAISE NOTICE '============================================';

    IF v_count < 1 THEN
        RAISE WARNING 'ADVERTENCIA: Se esperaba 1 mantenedor pero solo se insertaron %', v_count;
    END IF;
END $$;

-- =====================================================
-- ROLLBACK (Ejecutar solo si se necesita deshacer)
-- =====================================================

/*
-- Para eliminar todos los menús de Taller:

DELETE FROM menu_items
WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintainers.workshop');

DELETE FROM menu_items
WHERE name = 'maintainers.workshop';

-- Verificar eliminación:
SELECT COUNT(*) FROM menu_items WHERE name LIKE 'maintainers.workshop%';
*/

-- =====================================================
-- NOTAS IMPORTANTES
-- =====================================================

-- 1. Este script es idempotente: se puede ejecutar múltiples veces
--    sin duplicar registros gracias a ON CONFLICT DO NOTHING
--
-- 2. Las rutas siguen el formato:
--    app_maintainers_workshop_{entity}_index
--
-- 3. Todos los menús requieren autenticación (requires_auth = true)
--    y están disponibles para ROLE_USER
--
-- 4. Los íconos utilizados son de la librería Boxicons (bx-)
--
-- 5. Después de ejecutar este script, es necesario:
--    - Limpiar la caché de Symfony: php bin/console cache:clear
--    - Verificar que las rutas existan: php bin/console debug:router | grep workshop
--
-- 6. Para verificar la inserción en la base de datos:
--    SELECT name, label, route, position
--    FROM menu_items
--    WHERE name LIKE 'maintainers.workshop%'
--    ORDER BY position;
--
-- 7. Características especiales:
--    - Categoría más simple: solo 1 mantenedor
--    - Útil para actividades grupales en contextos de salud mental
--    - Puede expandirse en el futuro con más mantenedores relacionados

-- =====================================================
-- DESCRIPCIÓN DEL MANTENEDOR
-- =====================================================

-- WORKSHOP (Talleres)
--    - Catálogo de talleres/workshops disponibles
--    - Incluye información como:
--      * Nombre del taller
--      * Descripción y objetivos
--      * Duración y frecuencia
--      * Capacidad máxima de participantes
--      * Profesionales responsables
--      * Material necesario
--      * Temáticas (salud mental, rehabilitación, etc.)
--
--    Casos de uso:
--    - Talleres terapéuticos grupales
--    - Actividades de rehabilitación
--    - Sesiones educativas para pacientes
--    - Grupos de apoyo
--    - Capacitaciones para profesionales
--
--    Relaciones con otros módulos:
--    - Puede relacionarse con Agenda (programación de sesiones)
--    - Vinculado con Profesionales (facilitadores)
--    - Conectado con Pacientes (participantes)
--    - Asociado a Prestaciones (para facturación)

-- =====================================================
-- EJEMPLOS DE TALLERES COMUNES EN SALUD
-- =====================================================

-- Salud Mental:
-- - Taller de Manejo de Ansiedad
-- - Grupo de Apoyo para Depresión
-- - Taller de Mindfulness
-- - Terapia Grupal de Trauma
--
-- Nutrición:
-- - Taller de Alimentación Saludable
-- - Grupo de Educación Nutricional
-- - Taller de Cocina Terapéutica
--
-- Rehabilitación:
-- - Taller de Ejercicios Terapéuticos
-- - Grupo de Estimulación Cognitiva
-- - Taller de Motricidad Fina
--
-- Preventivos:
-- - Taller de Prevención de Caídas
-- - Educación en Diabetes
-- - Taller de Hipertensión

-- =====================================================
-- POSIBLES EXPANSIONES FUTURAS
-- =====================================================

-- Si la categoría Taller crece, podrían agregarse:
--
-- 1. workshop_type (Tipos de Taller)
--    - Clasificación por temática
--
-- 2. workshop_session (Sesiones de Taller)
--    - Gestión de sesiones individuales dentro de un taller
--
-- 3. workshop_material (Material de Taller)
--    - Recursos y materiales necesarios
--
-- 4. workshop_evaluation (Evaluación de Taller)
--    - Criterios de evaluación de participantes
--
-- 5. workshop_attendance (Asistencia a Taller)
--    - Control de asistencia por sesión

-- =====================================================
-- FIN DEL SCRIPT
-- =====================================================
