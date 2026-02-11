#!/bin/bash

# Script para renombrar tabla 'sexo' a 'gender'
# Uso: ./scripts/rename_sexo_to_gender.sh

set -e

PGPASSWORD="melisamelisa"
PGUSER="melisa"
PGHOST="localhost"

echo "=== Renombrando tabla 'sexo' a 'gender' en melisalacolina ==="
PGPASSWORD=$PGPASSWORD psql -h $PGHOST -U $PGUSER -d melisalacolina << 'EOF'
ALTER TABLE sexo RENAME TO gender;
ALTER SEQUENCE sexo_id_seq RENAME TO gender_id_seq;
EOF

echo "✅ Tabla renombrada en melisalacolina"
echo ""

echo "=== Renombrando tabla 'sexo' a 'gender' en melisahospital ==="
PGPASSWORD=$PGPASSWORD psql -h $PGHOST -U $PGUSER -d melisahospital << 'EOF'
ALTER TABLE sexo RENAME TO gender;
ALTER SEQUENCE sexo_id_seq RENAME TO gender_id_seq;
EOF

echo "✅ Tabla renombrada en melisahospital"
echo ""

echo "=== Renombrando tabla 'sexo' a 'gender' en melisa_template ==="
PGPASSWORD=$PGPASSWORD psql -h $PGHOST -U $PGUSER -d melisa_template << 'EOF'
ALTER TABLE sexo RENAME TO gender;
ALTER SEQUENCE sexo_id_seq RENAME TO gender_id_seq;
EOF

echo "✅ Tabla renombrada en melisa_template"
echo ""

echo "=========================================="
echo "✅ PROCESO COMPLETADO"
echo "=========================================="
echo "Tabla 'sexo' renombrada a 'gender' en:"
echo "  - melisalacolina"
echo "  - melisahospital"
echo "  - melisa_template"
