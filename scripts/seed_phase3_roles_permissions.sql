-- ========================================================================
-- SEED: Roles y Permisos Phase 3 - Granularidad por mantenedor específico
-- ========================================================================
-- Descripción: Agrega roles especializados con permisos a nivel de 
--              mantenedor individual (Disease, Specialty, etc.)
-- Fecha: 2026-02-09
-- Sprint: 3 - Phase 3
-- ========================================================================

-- ========================================================================
-- PARTE 1: ROLES DE PHASE 3
-- ========================================================================

-- Rol: Enfermera Clínica (solo lectura en Disease)
INSERT INTO role (id, code, name, description, position, is_active, is_system, created_at)
VALUES (
    nextval('role_id_seq'),
    'ROLE_CLINICAL_NURSE',
    'Enfermera Clínica',
    'Personal de enfermería con acceso de lectura a mantenedor Disease',
    10,
    true,
    false,
    NOW()
) ON CONFLICT (code) DO NOTHING;

-- Rol: Visor Clínico (lectura en toda la categoría clinical)
INSERT INTO role (id, code, name, description, position, is_active, is_system, created_at)
VALUES (
    nextval('role_id_seq'),
    'ROLE_CLINICAL_VIEWER',
    'Visor Clínico',
    'Acceso de solo lectura a todos los mantenedores clínicos',
    11,
    true,
    false,
    NOW()
) ON CONFLICT (code) DO NOTHING;

-- Rol: Editor de Enfermedades (CRUD solo en Disease)
INSERT INTO role (id, code, name, description, position, is_active, is_system, created_at)
VALUES (
    nextval('role_id_seq'),
    'ROLE_DISEASE_EDITOR',
    'Editor de Enfermedades',
    'CRUD completo sobre mantenedor Disease (Enfermedades)',
    12,
    true,
    false,
    NOW()
) ON CONFLICT (code) DO NOTHING;

-- Rol: Editor de Especialidades (READ+UPDATE solo en Specialty)
INSERT INTO role (id, code, name, description, position, is_active, is_system, created_at)
VALUES (
    nextval('role_id_seq'),
    'ROLE_SPECIALTY_EDITOR',
    'Editor de Especialidades',
    'Edición de mantenedor Specialty (Especialidades Médicas)',
    13,
    true,
    false,
    NOW()
) ON CONFLICT (code) DO NOTHING;

-- Rol: Gestor Comercial (categoría commercial completa)
INSERT INTO role (id, code, name, description, position, is_active, is_system, created_at)
VALUES (
    nextval('role_id_seq'),
    'ROLE_COMMERCIAL_MANAGER',
    'Gestor Comercial',
    'Gestión completa de módulos comerciales (facturas, contratos, etc.)',
    14,
    true,
    false,
    NOW()
) ON CONFLICT (code) DO NOTHING;

-- Rol: Gestor Hospitalario (categoría hospital completa)
INSERT INTO role (id, code, name, description, position, is_active, is_system, created_at)
VALUES (
    nextval('role_id_seq'),
    'ROLE_HOSPITAL_MANAGER',
    'Gestor Hospitalario',
    'Gestión de infraestructura hospitalaria (pabellones, camas, etc.)',
    15,
    true,
    false,
    NOW()
) ON CONFLICT (code) DO NOTHING;

-- ========================================================================
-- PARTE 2: PERMISOS PHASE 3 - GRANULARIDAD POR MANTENEDOR
-- ========================================================================
-- NOTA: Los INSERT usan subconsultas con NOT EXISTS para evitar duplicados
--       No podemos usar ON CONFLICT porque el constraint único actual es
--       (role, permission) pero necesitamos diferenciar por (category, maintainer)

-- ROLE_CLINICAL_NURSE: Solo READ en Disease
INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_CLINICAL_NURSE', 'READ', true, 'clinical', 'Disease', 'Enfermeras pueden consultar enfermedades', 30, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_CLINICAL_NURSE' 
    AND permission = 'READ' 
    AND category = 'clinical' 
    AND maintainer = 'Disease'
);

-- ROLE_CLINICAL_VIEWER: READ en toda la categoría clinical (maintainer NULL = todos)
INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_CLINICAL_VIEWER', 'READ', true, 'clinical', NULL, 'Lectura de todos los mantenedores clínicos', 25, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_CLINICAL_VIEWER' 
    AND permission = 'READ' 
    AND category = 'clinical' 
    AND maintainer IS NULL
);

-- ROLE_DISEASE_EDITOR: CRUD completo en Disease
INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_DISEASE_EDITOR', 'CREATE', true, 'clinical', 'Disease', 'Crear enfermedades', 40, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_DISEASE_EDITOR' 
    AND permission = 'CREATE' 
    AND category = 'clinical' 
    AND maintainer = 'Disease'
);

INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_DISEASE_EDITOR', 'READ', true, 'clinical', 'Disease', 'Leer enfermedades', 40, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_DISEASE_EDITOR' 
    AND permission = 'READ' 
    AND category = 'clinical' 
    AND maintainer = 'Disease'
);

INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_DISEASE_EDITOR', 'UPDATE', true, 'clinical', 'Disease', 'Editar enfermedades', 40, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_DISEASE_EDITOR' 
    AND permission = 'UPDATE' 
    AND category = 'clinical' 
    AND maintainer = 'Disease'
);

INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_DISEASE_EDITOR', 'DELETE', true, 'clinical', 'Disease', 'Eliminar enfermedades', 40, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_DISEASE_EDITOR' 
    AND permission = 'DELETE' 
    AND category = 'clinical' 
    AND maintainer = 'Disease'
);

-- ROLE_SPECIALTY_EDITOR: READ + UPDATE en Specialty
INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_SPECIALTY_EDITOR', 'READ', true, 'clinical', 'Specialty', 'Leer especialidades', 35, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_SPECIALTY_EDITOR' 
    AND permission = 'READ' 
    AND category = 'clinical' 
    AND maintainer = 'Specialty'
);

INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_SPECIALTY_EDITOR', 'UPDATE', true, 'clinical', 'Specialty', 'Editar especialidades', 35, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_SPECIALTY_EDITOR' 
    AND permission = 'UPDATE' 
    AND category = 'clinical' 
    AND maintainer = 'Specialty'
);

-- ROLE_COMMERCIAL_MANAGER: CRUD + EXPORT en category='commercial', maintainer=NULL (todos)
INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_COMMERCIAL_MANAGER', 'CREATE', true, 'commercial', NULL, 'Crear mantenedores comerciales', 20, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_COMMERCIAL_MANAGER' 
    AND permission = 'CREATE' 
    AND category = 'commercial' 
    AND maintainer IS NULL
);

INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_COMMERCIAL_MANAGER', 'READ', true, 'commercial', NULL, 'Leer mantenedores comerciales', 20, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_COMMERCIAL_MANAGER' 
    AND permission = 'READ' 
    AND category = 'commercial' 
    AND maintainer IS NULL
);

INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_COMMERCIAL_MANAGER', 'UPDATE', true, 'commercial', NULL, 'Editar mantenedores comerciales', 20, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_COMMERCIAL_MANAGER' 
    AND permission = 'UPDATE' 
    AND category = 'commercial' 
    AND maintainer IS NULL
);

INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_COMMERCIAL_MANAGER', 'DELETE', true, 'commercial', NULL, 'Eliminar mantenedores comerciales', 20, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_COMMERCIAL_MANAGER' 
    AND permission = 'DELETE' 
    AND category = 'commercial' 
    AND maintainer IS NULL
);

INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_COMMERCIAL_MANAGER', 'EXPORT', true, 'commercial', NULL, 'Exportar mantenedores comerciales', 20, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_COMMERCIAL_MANAGER' 
    AND permission = 'EXPORT' 
    AND category = 'commercial' 
    AND maintainer IS NULL
);

-- ROLE_HOSPITAL_MANAGER: CRUD + EXPORT en category='hospital', maintainer=NULL (todos)
INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_HOSPITAL_MANAGER', 'CREATE', true, 'hospital', NULL, 'Crear mantenedores hospitalarios', 20, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_HOSPITAL_MANAGER' 
    AND permission = 'CREATE' 
    AND category = 'hospital' 
    AND maintainer IS NULL
);

INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_HOSPITAL_MANAGER', 'READ', true, 'hospital', NULL, 'Leer mantenedores hospitalarios', 20, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_HOSPITAL_MANAGER' 
    AND permission = 'READ' 
    AND category = 'hospital' 
    AND maintainer IS NULL
);

INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_HOSPITAL_MANAGER', 'UPDATE', true, 'hospital', NULL, 'Editar mantenedores hospitalarios', 20, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_HOSPITAL_MANAGER' 
    AND permission = 'UPDATE' 
    AND category = 'hospital' 
    AND maintainer IS NULL
);

INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_HOSPITAL_MANAGER', 'DELETE', true, 'hospital', NULL, 'Eliminar mantenedores hospitalarios', 20, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_HOSPITAL_MANAGER' 
    AND permission = 'DELETE' 
    AND category = 'hospital' 
    AND maintainer IS NULL
);

INSERT INTO maintainer_role_permission (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at)
SELECT nextval('maintainer_role_permission_id_seq'), 'ROLE_HOSPITAL_MANAGER', 'EXPORT', true, 'hospital', NULL, 'Exportar mantenedores hospitalarios', 20, true, NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM maintainer_role_permission 
    WHERE role = 'ROLE_HOSPITAL_MANAGER' 
    AND permission = 'EXPORT' 
    AND category = 'hospital' 
    AND maintainer IS NULL
);

-- ========================================================================
-- RESUMEN DE ROLES Y PERMISOS CREADOS
-- ========================================================================
-- Roles Phase 3:
--   1. ROLE_CLINICAL_NURSE      - READ solo en Disease
--   2. ROLE_CLINICAL_VIEWER     - READ en toda categoría clinical
--   3. ROLE_DISEASE_EDITOR      - CRUD en Disease
--   4. ROLE_SPECIALTY_EDITOR    - READ+UPDATE en Specialty
--   5. ROLE_COMMERCIAL_MANAGER  - CRUD+EXPORT en categoría commercial
--   6. ROLE_HOSPITAL_MANAGER    - CRUD+EXPORT en categoría hospital
--
-- Permisos Phase 3:
--   - 1 permiso para ROLE_CLINICAL_NURSE (READ Disease)
--   - 1 permiso para ROLE_CLINICAL_VIEWER (READ clinical/*)
--   - 4 permisos para ROLE_DISEASE_EDITOR (CRUD Disease)
--   - 2 permisos para ROLE_SPECIALTY_EDITOR (READ+UPDATE Specialty)
--   - 5 permisos para ROLE_COMMERCIAL_MANAGER (CRUD+EXPORT commercial/*)
--   - 5 permisos para ROLE_HOSPITAL_MANAGER (CRUD+EXPORT hospital/*)
--
-- Total: 6 roles nuevos, 18 permisos nuevos
-- ========================================================================
