#!/bin/bash

# Configuración de PostgreSQL
PGHOST="localhost"
PGUSER="melisa"
PGPASSWORD="melisamelisa"

echo "==========================================

"
echo "INSERTANDO DATOS DUMMY EN BASES DE DATOS"
echo "=========================================="
echo ""

echo "=== Limpiando e insertando en melisalacolina ==="
PGPASSWORD=$PGPASSWORD psql -h $PGHOST -U $PGUSER -d melisalacolina << 'EOF'
-- Truncar todas las tablas en orden correcto
DO $$
DECLARE
    r RECORD;
BEGIN
    FOR r IN (SELECT tablename FROM pg_tables WHERE schemaname = 'public') LOOP
        EXECUTE 'TRUNCATE TABLE ' || quote_ident(r.tablename) || ' RESTART IDENTITY CASCADE';
    END LOOP;
END $$;

-- Member Groups
INSERT INTO member_group (id, name, code, description, active, created_at) VALUES
(1, 'Administradores', 'ADMIN', 'Grupo con acceso total al sistema', true, NOW()),
(2, 'Médicos', 'DOCTOR', 'Profesionales médicos', true, NOW()),
(3, 'Enfermeras', 'ENFERMERA', 'Personal de enfermería', true, NOW()),
(4, 'Recepción', 'RECEPCION', 'Personal de recepción', true, NOW());

SELECT setval('member_group_id_seq', 4, true);

-- Members (password: 123456)
INSERT INTO member (id, username, roles, password, email, first_name, last_name, is_active, created_at, updated_at) VALUES
(1, 'admin', '["ROLE_ADMIN"]', '$2y$10$dQ0NzxDcieRB4sYryXRBoeBZvuIzpTFscIBm7WckLrDRe6R.PtuZG', 'admin@lacolina.cl', 'Administrador', 'Sistema', true, NOW(), NOW()),
(2, 'doctor1', '["ROLE_USER","ROLE_DOCTOR"]', '$2y$10$dQ0NzxDcieRB4sYryXRBoeBZvuIzpTFscIBm7WckLrDRe6R.PtuZG', 'doctor1@lacolina.cl', 'Juan', 'González', true, NOW(), NOW()),
(3, 'doctor2', '["ROLE_USER","ROLE_DOCTOR"]', '$2y$10$dQ0NzxDcieRB4sYryXRBoeBZvuIzpTFscIBm7WckLrDRe6R.PtuZG', 'doctor2@lacolina.cl', 'María', 'López', true, NOW(), NOW()),
(4, 'enfermera1', '["ROLE_USER","ROLE_ENFERMERA"]', '$2y$10$dQ0NzxDcieRB4sYryXRBoeBZvuIzpTFscIBm7WckLrDRe6R.PtuZG', 'enfermera1@lacolina.cl', 'Ana', 'Rodríguez', true, NOW(), NOW()),
(5, 'recepcion1', '["ROLE_USER","ROLE_RECEPCION"]', '$2y$10$dQ0NzxDcieRB4sYryXRBoeBZvuIzpTFscIBm7WckLrDRe6R.PtuZG', 'recepcion1@lacolina.cl', 'Pedro', 'Martínez', true, NOW(), NOW());

SELECT setval('member_id_seq', 5, true);

-- Member Group Membership
INSERT INTO member_group_membership (member_id, member_group_id) VALUES
(1, 1), (2, 2), (3, 2), (4, 3), (5, 4);

-- Country
INSERT INTO country (id, name, demonym, is_active) VALUES
(1, 'Chile', 'Chileno/a', true),
(2, 'Argentina', 'Argentino/a', true),
(3, 'Perú', 'Peruano/a', true);

SELECT setval('country_id_seq', 3, true);

-- Gender (tabla gender)
INSERT INTO gender (id, name, code, is_active, id_estado, created_at) VALUES
(1, 'Masculino', 'M', true, 1, NOW()),
(2, 'Femenino', 'F', true, 1, NOW());

SELECT setval('gender_id_seq', 2, true);

-- Marital Status
INSERT INTO marital_status (id, name, is_active, created_at) VALUES
(1, 'Soltero/a', true, NOW()),
(2, 'Casado/a', true, NOW()),
(3, 'Divorciado/a', true, NOW());

SELECT setval('marital_status_id_seq', 3, true);

-- Religion
INSERT INTO religion (id, name, is_active, created_at) VALUES
(1, 'Católica', true, NOW()),
(2, 'Evangélica', true, NOW()),
(3, 'Sin Religión', true, NOW());

SELECT setval('religion_id_seq', 3, true);

-- Ethnic Group
INSERT INTO ethnic_group (id, name, is_active, created_at) VALUES
(1, 'Mapuche', true, NOW()),
(2, 'No pertenece', true, NOW());

SELECT setval('ethnic_group_id_seq', 2, true);

COMMIT;
EOF

echo "✅ Datos insertados en melisalacolina"
echo ""

echo "=== Limpiando e insertando en melisahospital ==="
PGPASSWORD=$PGPASSWORD psql -h $PGHOST -U $PGUSER -d melisahospital << 'EOF'
-- Truncar todas las tablas en orden correcto
DO $$
DECLARE
    r RECORD;
BEGIN
    FOR r IN (SELECT tablename FROM pg_tables WHERE schemaname = 'public') LOOP
        EXECUTE 'TRUNCATE TABLE ' || quote_ident(r.tablename) || ' RESTART IDENTITY CASCADE';
    END LOOP;
END $$;

-- Member Groups
INSERT INTO member_group (id, name, code, description, active, created_at) VALUES
(1, 'Administradores', 'ADMIN', 'Grupo con acceso total al sistema', true, NOW()),
(2, 'Médicos', 'DOCTOR', 'Profesionales médicos', true, NOW()),
(3, 'Enfermeras', 'ENFERMERA', 'Personal de enfermería', true, NOW());

SELECT setval('member_group_id_seq', 3, true);

-- Members (password: 123456)
INSERT INTO member (id, username, roles, password, email, first_name, last_name, is_active, created_at, updated_at) VALUES
(1, 'admin.hospital', '["ROLE_ADMIN"]', '$2y$10$dQ0NzxDcieRB4sYryXRBoeBZvuIzpTFscIBm7WckLrDRe6R.PtuZG', 'admin@hospital.cl', 'Administrador', 'Hospital', true, NOW(), NOW()),
(2, 'dr.vargas', '["ROLE_USER","ROLE_DOCTOR"]', '$2y$10$dQ0NzxDcieRB4sYryXRBoeBZvuIzpTFscIBm7WckLrDRe6R.PtuZG', 'dr.vargas@hospital.cl', 'Carlos', 'Vargas', true, NOW(), NOW()),
(3, 'enf.martinez', '["ROLE_USER","ROLE_ENFERMERA"]', '$2y$10$dQ0NzxDcieRB4sYryXRBoeBZvuIzpTFscIBm7WckLrDRe6R.PtuZG', 'enf.martinez@hospital.cl', 'Rosa', 'Martínez', true, NOW(), NOW());

SELECT setval('member_id_seq', 3, true);

-- Member Group Membership
INSERT INTO member_group_membership (member_id, member_group_id) VALUES
(1, 1), (2, 2), (3, 3);

-- Country
INSERT INTO country (id, name, demonym, is_active) VALUES
(1, 'Chile', 'Chileno/a', true),
(2, 'Bolivia', 'Boliviano/a', true),
(3, 'Brasil', 'Brasileño/a', true);

SELECT setval('country_id_seq', 3, true);

-- Gender (tabla gender)
INSERT INTO gender (id, name, code, is_active, id_estado, created_at) VALUES
(1, 'Masculino', 'M', true, 1, NOW()),
(2, 'Femenino', 'F', true, 1, NOW());

SELECT setval('gender_id_seq', 2, true);

-- Marital Status
INSERT INTO marital_status (id, name, is_active, created_at) VALUES
(1, 'Soltero/a', true, NOW()),
(2, 'Casado/a', true, NOW());

SELECT setval('marital_status_id_seq', 2, true);

-- Religion
INSERT INTO religion (id, name, is_active, created_at) VALUES
(1, 'Católica', true, NOW()),
(2, 'Judía', true, NOW());

SELECT setval('religion_id_seq', 2, true);

-- Ethnic Group
INSERT INTO ethnic_group (id, name, is_active, created_at) VALUES
(1, 'Aymara', true, NOW()),
(2, 'No pertenece', true, NOW());

SELECT setval('ethnic_group_id_seq', 2, true);

COMMIT;
EOF

echo "✅ Datos insertados en melisahospital"
echo ""

echo "==========================================
"
echo "✅ PROCESO COMPLETADO"
echo "==========================================" 
echo "Datos dummy insertados con datos diferentes por base de datos"
echo "Password de usuarios: 123456"
