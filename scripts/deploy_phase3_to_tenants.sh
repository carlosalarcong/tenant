#!/bin/bash

# ========================================================================
# Script de deployment de Phase 3 a todos los tenants
# ========================================================================
# Descripción: Ejecuta seed de roles y permisos Phase 3 en todos los tenants
# Fecha: 2026-02-09
# Uso: bash scripts/deploy_phase3_to_tenants.sh
# ========================================================================

set -e  # Exit on error

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuración de conexión PostgreSQL
DB_USER="melisa"
DB_PASS="melisamelisa"
DB_HOST="localhost"
DB_PORT="5432"

# Lista de tenants (bases de datos)
TENANTS=(
    "melisalacolina"
    "melisa_template"
    "melisahospital"
)

# Ruta del archivo SQL
SQL_FILE="$(dirname "$0")/seed_phase3_roles_permissions.sql"

# Verificar que el archivo SQL existe
if [ ! -f "$SQL_FILE" ]; then
    echo -e "${RED}❌ Error: Archivo SQL no encontrado: $SQL_FILE${NC}"
    exit 1
fi

echo -e "${BLUE}========================================================================${NC}"
echo -e "${BLUE}   DEPLOYMENT PHASE 3: Roles y Permisos por Mantenedor Específico${NC}"
echo -e "${BLUE}========================================================================${NC}"
echo ""
echo -e "${YELLOW}📋 Archivo SQL: $SQL_FILE${NC}"
echo -e "${YELLOW}🎯 Tenants objetivo: ${#TENANTS[@]} bases de datos${NC}"
echo ""

# Contador de éxitos y fallos
SUCCESS_COUNT=0
FAIL_COUNT=0

# Iterar sobre cada tenant
for TENANT in "${TENANTS[@]}"; do
    echo -e "${BLUE}------------------------------------------------------------------------${NC}"
    echo -e "${YELLOW}🔄 Procesando tenant: ${TENANT}${NC}"
    echo -e "${BLUE}------------------------------------------------------------------------${NC}"
    
    # Construir connection string
    CONN_STRING="postgresql://${DB_USER}:${DB_PASS}@${DB_HOST}:${DB_PORT}/${TENANT}"
    
    # Ejecutar SQL
    if PGPASSWORD="${DB_PASS}" psql -h "${DB_HOST}" -p "${DB_PORT}" -U "${DB_USER}" -d "${TENANT}" -f "$SQL_FILE" 2>&1; then
        echo -e "${GREEN}✅ SUCCESS: SQL ejecutado correctamente en ${TENANT}${NC}"
        ((SUCCESS_COUNT++))
    else
        echo -e "${RED}❌ FAILED: Error al ejecutar SQL en ${TENANT}${NC}"
        ((FAIL_COUNT++))
    fi
    
    echo ""
done

# Resumen final
echo -e "${BLUE}========================================================================${NC}"
echo -e "${BLUE}   RESUMEN DE DEPLOYMENT${NC}"
echo -e "${BLUE}========================================================================${NC}"
echo -e "${GREEN}✅ Exitosos: ${SUCCESS_COUNT}/${#TENANTS[@]}${NC}"
if [ $FAIL_COUNT -gt 0 ]; then
    echo -e "${RED}❌ Fallidos: ${FAIL_COUNT}/${#TENANTS[@]}${NC}"
fi
echo ""

if [ $FAIL_COUNT -eq 0 ]; then
    echo -e "${GREEN}🎉 ¡Deployment completado exitosamente en todos los tenants!${NC}"
    echo ""
    echo -e "${YELLOW}📦 Recursos creados:${NC}"
    echo -e "   • 6 roles nuevos (ROLE_CLINICAL_NURSE, ROLE_CLINICAL_VIEWER, etc.)"
    echo -e "   • 18 permisos con granularidad por category + maintainer"
    echo ""
    echo -e "${YELLOW}🧪 Verificación:${NC}"
    echo -e "   1. Conectarse a DB: psql -h localhost -U melisa -d melisalacolina"
    echo -e "   2. Consultar roles: SELECT code, name FROM role WHERE position >= 10;"
    echo -e "   3. Consultar permisos: SELECT role, permission, category, maintainer FROM maintainer_role_permission WHERE role LIKE 'ROLE_CLINICAL%';"
    echo ""
    exit 0
else
    echo -e "${RED}⚠️  Deployment completado con errores. Revisa los mensajes anteriores.${NC}"
    exit 1
fi
