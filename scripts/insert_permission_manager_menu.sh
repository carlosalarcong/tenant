#!/bin/bash

# Script para insertar menu item de Permisos de Mantenedores
# Parent: ID 21 (Configuración)
# ID: 148
# Solo accesible por ROLE_ADMIN

PGPASSWORD=melisamelisa

for DB in melisalacolina melisa_template melisahospital; do
    echo "=== Insertando menu item de Permisos de Mantenedores en $DB ==="
    
    psql -h localhost -U melisa -d $DB <<EOF
-- Verificar si ya existe
SELECT 'Existente:', COUNT(*) FROM menu_items WHERE id = 148;

-- Insertar solo si no existe
INSERT INTO menu_items (
    id, 
    parent_id, 
    name, 
    label, 
    route, 
    icon, 
    module, 
    position, 
    enabled, 
    visible_in_sidebar, 
    requires_auth, 
    required_roles, 
    created_at, 
    updated_at
)
SELECT 
    148,
    21,
    'maintainer_permissions',
    'Permisos de Mantenedores',
    'app_maintainer_permission_index',
    'bi bi-shield-lock',
    'admin',
    99,
    true,
    true,
    true,
    '["ROLE_ADMIN"]'::json,
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE id = 148);

-- Verificar inserción
SELECT 'Estado:', CASE WHEN COUNT(*) > 0 THEN 'INSERTADO' ELSE 'YA EXISTÍA' END 
FROM menu_items WHERE id = 148;

SELECT id, label, route, parent_id, position, enabled, required_roles::text 
FROM menu_items WHERE id = 148;
EOF
    
    echo ""
done

echo "=== Inserción completada ==="
echo ""
echo "Para verificar en todos los tenants:"
echo "  psql -h localhost -U melisa -d melisalacolina -c \"SELECT id, label, route FROM menu_items WHERE id = 148;\""
echo "  psql -h localhost -U melisa -d melisa_template -c \"SELECT id, label, route FROM menu_items WHERE id = 148;\""
echo "  psql -h localhost -U melisa -d melisahospital -c \"SELECT id, label, route FROM menu_items WHERE id = 148;\""
echo ""
echo "El menú 'Permisos de Mantenedores' ahora aparecerá en Configuración solo para ROLE_ADMIN"
