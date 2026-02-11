#!/bin/bash

# Script para exportar datos de menu_items desde PostgreSQL melisalacolina
# Genera un archivo SQL con los INSERT statements
# 
# Uso: ./export_menu_items_from_postgres.sh [output_file]
# Si no se especifica output_file, usa: /tmp/menu_items_export_$(date +%Y%m%d_%H%M%S).sql

# Configuración de la base de datos
DB_HOST="localhost"
DB_USER="melisa"
DB_PASSWORD="melisamelisa"
DB_NAME="melisalacolina"
TABLE_NAME="menu_items"

# Archivo de salida (puede ser pasado como argumento)
OUTPUT_FILE="${1:-/tmp/menu_items_export_$(date +%Y%m%d_%H%M%S).sql}"

echo "=========================================="
echo "Exportando menu_items desde PostgreSQL"
echo "=========================================="
echo "Base de datos: $DB_NAME"
echo "Tabla: $TABLE_NAME"
echo "Archivo de salida: $OUTPUT_FILE"
echo "=========================================="

# Verificar si existe la tabla
echo "Verificando tabla..."
TABLE_EXISTS=$(PGPASSWORD=$DB_PASSWORD psql -h $DB_HOST -U $DB_USER -d $DB_NAME -tAc "SELECT COUNT(*) FROM information_schema.tables WHERE table_name='$TABLE_NAME';")

if [ "$TABLE_EXISTS" -eq "0" ]; then
    echo "ERROR: La tabla $TABLE_NAME no existe en la base de datos $DB_NAME"
    exit 1
fi

# Contar registros
RECORD_COUNT=$(PGPASSWORD=$DB_PASSWORD psql -h $DB_HOST -U $DB_USER -d $DB_NAME -tAc "SELECT COUNT(*) FROM $TABLE_NAME;")
echo "Registros encontrados: $RECORD_COUNT"

if [ "$RECORD_COUNT" -eq "0" ]; then
    echo "ADVERTENCIA: La tabla está vacía. No hay datos para exportar."
    exit 0
fi

# Generar el archivo SQL
echo ""
echo "Generando archivo SQL..."

# Encabezado del archivo
cat > "$OUTPUT_FILE" << 'EOF'
-- Exportación de menu_items desde PostgreSQL melisalacolina
-- Generado automáticamente
-- Fecha: $(date '+%Y-%m-%d %H:%M:%S')
-- 
-- Para aplicar este script en otra base de datos PostgreSQL:
-- PGPASSWORD=melisamelisa psql -h localhost -U melisa -d [nombre_db] -f [este_archivo.sql]

-- Limpiar tabla antes de insertar (opcional, comentar si no deseas truncar)
-- TRUNCATE TABLE menu_items RESTART IDENTITY CASCADE;

-- Insertar datos
EOF

# Reemplazar la fecha en el encabezado
sed -i "s/\$(date '+%Y-%m-%d %H:%M:%S')/$(date '+%Y-%m-%d %H:%M:%S')/g" "$OUTPUT_FILE"

# Generar los INSERT statements
PGPASSWORD=$DB_PASSWORD psql -h $DB_HOST -U $DB_USER -d $DB_NAME -tA << EOSQL >> "$OUTPUT_FILE"
SELECT 'INSERT INTO menu_items (id, parent_id, name, label, route, icon, module, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at, updated_at) VALUES (' ||
    COALESCE(id::text, 'NULL') || ', ' ||
    COALESCE(parent_id::text, 'NULL') || ', ' ||
    COALESCE('''' || REPLACE(name, '''', '''''') || '''', 'NULL') || ', ' ||
    COALESCE('''' || REPLACE(label, '''', '''''') || '''', 'NULL') || ', ' ||
    COALESCE('''' || REPLACE(route, '''', '''''') || '''', 'NULL') || ', ' ||
    COALESCE('''' || REPLACE(icon, '''', '''''') || '''', 'NULL') || ', ' ||
    COALESCE('''' || REPLACE(module, '''', '''''') || '''', 'NULL') || ', ' ||
    COALESCE(position::text, 'NULL') || ', ' ||
    enabled || ', ' ||
    visible_in_sidebar || ', ' ||
    requires_auth || ', ' ||
    COALESCE('''' || REPLACE(required_roles::text, '''', '''''') || '''', 'NULL') || ', ' ||
    COALESCE('''' || created_at::text || '''', 'NULL') || ', ' ||
    COALESCE('''' || updated_at::text || '''', 'NULL') || 
    ');'
FROM menu_items
ORDER BY id;
EOSQL

# Agregar comando para resetear la secuencia al final
cat >> "$OUTPUT_FILE" << 'EOF'

-- Resetear la secuencia del ID para que el próximo INSERT use el ID correcto
SELECT setval('menu_items_id_seq', (SELECT MAX(id) FROM menu_items));
EOF

# Verificar que se generó el archivo
if [ -f "$OUTPUT_FILE" ]; then
    FILE_SIZE=$(stat -f%z "$OUTPUT_FILE" 2>/dev/null || stat -c%s "$OUTPUT_FILE" 2>/dev/null)
    LINES=$(wc -l < "$OUTPUT_FILE")
    
    echo ""
    echo "=========================================="
    echo "✓ Exportación completada exitosamente"
    echo "=========================================="
    echo "Archivo generado: $OUTPUT_FILE"
    echo "Tamaño: $FILE_SIZE bytes"
    echo "Líneas: $LINES"
    echo "Registros exportados: $RECORD_COUNT"
    echo ""
    echo "Para aplicar en otra base de datos PostgreSQL:"
    echo "  PGPASSWORD=melisamelisa psql -h localhost -U melisa -d [nombre_db] -f $OUTPUT_FILE"
    echo ""
    echo "Para aplicar en todas las bases de datos tenant:"
    echo "  for db in melisalacolina melisahospital melisa_template; do"
    echo "    echo \"Aplicando en \$db...\""
    echo "    PGPASSWORD=melisamelisa psql -h localhost -U melisa -d \$db -f $OUTPUT_FILE"
    echo "  done"
    echo "=========================================="
else
    echo "ERROR: No se pudo generar el archivo $OUTPUT_FILE"
    exit 1
fi
