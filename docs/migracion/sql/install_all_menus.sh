#!/bin/bash

# =====================================================
# SCRIPT: Instalador de Menús de Mantenedores
# Descripción: Ejecuta todos los scripts SQL de menús
# Autor: Sistema MELISA
# Fecha: 2026-02-04
# =====================================================

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuración de base de datos
DB_HOST="localhost"
DB_NAME="melisalacolina"
DB_USER="melisa"
DB_PASS="melisamelisa"

# Directorio base
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(cd "$SCRIPT_DIR/../../.." && pwd)"

# Banner
echo -e "${BLUE}"
echo "=================================================="
echo "  INSTALADOR DE MENÚS - SISTEMA MELISA"
echo "=================================================="
echo -e "${NC}"

# Función para mostrar mensajes
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[OK]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Función para ejecutar SQL
execute_sql() {
    local sql_file="$1"
    local category_name="$2"

    log_info "Ejecutando: $sql_file"

    PGPASSWORD="$DB_PASS" psql -h "$DB_HOST" -U "$DB_USER" -d "$DB_NAME" -f "$sql_file" > /tmp/sql_output_$$.log 2>&1

    if [ $? -eq 0 ]; then
        log_success "Menús de $category_name insertados correctamente"
        return 0
    else
        log_error "Error al insertar menús de $category_name"
        cat /tmp/sql_output_$$.log
        return 1
    fi
}

# Verificar que psql está instalado
if ! command -v psql &> /dev/null; then
    log_error "psql no está instalado. Por favor instalar PostgreSQL client."
    exit 1
fi

# Verificar conexión a base de datos
log_info "Verificando conexión a base de datos..."
PGPASSWORD="$DB_PASS" psql -h "$DB_HOST" -U "$DB_USER" -d "$DB_NAME" -c "SELECT 1;" > /dev/null 2>&1

if [ $? -ne 0 ]; then
    log_error "No se pudo conectar a la base de datos"
    log_error "Host: $DB_HOST, Database: $DB_NAME, User: $DB_USER"
    exit 1
fi

log_success "Conexión a base de datos establecida"

# Verificar que existe el menú padre "maintenance"
log_info "Verificando menú padre 'Mantenedores'..."
PARENT_EXISTS=$(PGPASSWORD="$DB_PASS" psql -h "$DB_HOST" -U "$DB_USER" -d "$DB_NAME" -t -c "SELECT COUNT(*) FROM menu_items WHERE name = 'maintenance';")

if [ "$PARENT_EXISTS" -eq 0 ]; then
    log_error "No existe el menú padre 'maintenance'. Debe crearse primero."
    log_info "Ejecutar: INSERT INTO menu_items (name, label, icon, route, parent_id, position, enabled, visible_in_sidebar, requires_auth, module, required_roles, created_at) VALUES ('maintenance', 'Mantenedores', 'bx bx-cog', NULL, NULL, 4, true, true, true, 'Mantenedores', '[\"ROLE_USER\"]', NOW());"
    exit 1
fi

log_success "Menú padre 'Mantenedores' encontrado"

# Array de scripts a ejecutar
declare -a SCRIPTS=(
    "06_menu_hospitalaria.sql:Hospitalaria"
    "07_menu_liquidaciones.sql:Liquidaciones"
    "08_menu_presupuesto.sql:Presupuesto"
    "09_menu_taller.sql:Taller"
)

# Contador de éxitos y errores
SUCCESS_COUNT=0
ERROR_COUNT=0

# Ejecutar cada script
echo ""
log_info "Iniciando instalación de menús..."
echo ""

for script_info in "${SCRIPTS[@]}"; do
    IFS=':' read -r script_file category_name <<< "$script_info"

    if [ -f "$SCRIPT_DIR/$script_file" ]; then
        if execute_sql "$SCRIPT_DIR/$script_file" "$category_name"; then
            ((SUCCESS_COUNT++))
        else
            ((ERROR_COUNT++))
        fi
    else
        log_error "Archivo no encontrado: $script_file"
        ((ERROR_COUNT++))
    fi

    echo ""
done

# Limpiar archivos temporales
rm -f /tmp/sql_output_$$.log

# Resumen de ejecución
echo -e "${BLUE}"
echo "=================================================="
echo "  RESUMEN DE INSTALACIÓN"
echo "=================================================="
echo -e "${NC}"

if [ $ERROR_COUNT -eq 0 ]; then
    log_success "Todas las categorías se instalaron correctamente ($SUCCESS_COUNT/$((SUCCESS_COUNT + ERROR_COUNT)))"
else
    log_warning "Se completaron $SUCCESS_COUNT categorías"
    log_error "Fallaron $ERROR_COUNT categorías"
fi

# Mostrar estadísticas
echo ""
log_info "Estadísticas de menús insertados:"

PGPASSWORD="$DB_PASS" psql -h "$DB_HOST" -U "$DB_USER" -d "$DB_NAME" -c "
SELECT
    CASE
        WHEN name = 'maintainers.hospital' THEN 'Hospitalaria'
        WHEN name = 'maintainers.settlements' THEN 'Liquidaciones'
        WHEN name = 'maintainers.budget' THEN 'Presupuesto'
        WHEN name = 'maintainers.workshop' THEN 'Taller'
    END as \"Categoría\",
    label as \"Nombre\",
    (SELECT COUNT(*) FROM menu_items mi WHERE mi.parent_id = menu_items.id) as \"Mantenedores\"
FROM menu_items
WHERE name IN ('maintainers.hospital', 'maintainers.settlements', 'maintainers.budget', 'maintainers.workshop')
ORDER BY position;
"

# Limpiar caché de Symfony
if [ -d "$PROJECT_DIR" ]; then
    echo ""
    log_info "Limpiando caché de Symfony..."

    cd "$PROJECT_DIR"

    if [ -f "bin/console" ]; then
        php bin/console cache:clear --no-warmup > /dev/null 2>&1

        if [ $? -eq 0 ]; then
            log_success "Caché limpiada correctamente"
        else
            log_warning "No se pudo limpiar la caché automáticamente"
            log_info "Ejecutar manualmente: php bin/console cache:clear"
        fi
    else
        log_warning "No se encontró bin/console"
        log_info "Limpiar caché manualmente: php bin/console cache:clear"
    fi
else
    log_warning "No se encontró el directorio del proyecto"
    log_info "Limpiar caché manualmente desde el directorio del proyecto"
fi

# Siguiente paso
echo ""
echo -e "${BLUE}"
echo "=================================================="
echo "  PRÓXIMOS PASOS"
echo "=================================================="
echo -e "${NC}"

echo "1. Verificar menús en la interfaz web"
echo "2. Comprobar que las rutas funcionan correctamente"
echo "3. Verificar permisos de usuario"
echo ""
echo "Para verificar rutas instaladas:"
echo "  php bin/console debug:router | grep -E '(hospital|settlements|budget|workshop)'"
echo ""
echo "Para ver menús en base de datos:"
echo "  psql -h $DB_HOST -U $DB_USER -d $DB_NAME -c \"SELECT name, label, route FROM menu_items WHERE name LIKE 'maintainers.%' ORDER BY parent_id, position;\""
echo ""

# Exit code
if [ $ERROR_COUNT -eq 0 ]; then
    exit 0
else
    exit 1
fi
