# Plan de Renombrado: melisa_tenant → tenant

**Fecha:** 5 de Febrero, 2026  
**Objetivo:** Renombrar el proyecto de `melisa_tenant` a `tenant`  
**Impacto Total:** ~90 ocurrencias en código, documentación y configuraciones  
**Tiempo Estimado:** 2-4 horas  
**Riesgo:** Medio-Alto ⚠️

---

## 📋 Pre-requisitos

**ANTES DE COMENZAR:**

- [ ] ✅ Backup completo del proyecto
- [ ] ✅ Commit de todos los cambios pendientes
- [ ] ✅ Crear rama específica: `git checkout -b refactor/rename-to-tenant`
- [ ] ✅ Notificar al equipo (si aplica)
- [ ] ✅ Verificar que no hay procesos corriendo (servicios, migraciones, etc.)

```bash
# Backup completo
tar -czf backup_melisa_tenant_$(date +%Y%m%d_%H%M%S).tar.gz /var/www/html/melisa_tenant

# Crear rama de trabajo
cd /var/www/html/melisa_tenant
git checkout -b refactor/rename-to-tenant
git status
```

---

## 🎯 Fase 1: Actualizar Comandos PHP (CRÍTICO)

**Archivos:** 2  
**Prioridad:** 🔴 CRÍTICA (estos comandos se usan en producción)

### Archivos a Modificar:

1. **src/Command/MigrationsTenantLegacyCommand.php**
   - Línea 229: `$entityDir = '/var/www/html/melisa_tenant/src/Entity';`
   - Línea 264: `setWorkingDirectory('/var/www/html/melisa_tenant')`
   - Línea 293: `setWorkingDirectory('/var/www/html/melisa_tenant')`
   - Línea 352: `setWorkingDirectory('/var/www/html/melisa_tenant')`

2. **src/Command/MigrateTenantLegacyCommand.php**
   - Línea 238: `$migrationsDir = '/var/www/html/melisa_tenant/migrations';`
   - Línea 247: `setWorkingDirectory('/var/www/html/melisa_tenant')`
   - Línea 278: `$entityDir = '/var/www/html/melisa_tenant/src/Entity';`
   - Línea 320: `setWorkingDirectory('/var/www/html/melisa_tenant')`
   - Línea 368: `setWorkingDirectory('/var/www/html/melisa_tenant')`
   - Línea 547: `$migrationsDir = '/var/www/html/melisa_tenant/migrations';`
   - Línea 661: `$migrationsDir = '/var/www/html/melisa_tenant/migrations';`
   - Línea 812: `$migrationFile = '/var/www/html/melisa_tenant/migrations/' . $filename . '.php';`
   - Línea 1267: `$migrationsDir = '/var/www/html/melisa_tenant/migrations';`

### Instrucciones para Claude:

```
Reemplaza todas las ocurrencias de '/var/www/html/melisa_tenant' por '/var/www/html/tenant' 
en los archivos:
- src/Command/MigrationsTenantLegacyCommand.php
- src/Command/MigrateTenantLegacyCommand.php

Usa multi_replace_string_in_file para hacerlo eficientemente.
```

### Verificación:

```bash
# Verificar que no queden referencias
grep -n "melisa_tenant" src/Command/MigrationsTenantLegacyCommand.php
grep -n "melisa_tenant" src/Command/MigrateTenantLegacyCommand.php

# Debe retornar: no matches
```

---

## 🎯 Fase 2: Actualizar Scripts Bash

**Archivos:** ~8 scripts  
**Prioridad:** 🟠 ALTA

### Archivos a Modificar:

1. **scripts/deploy.sh**
   - Línea 20: `PROJECT_DIR="/var/www/html/melisa_tenant"`

2. **scripts/export_menu_items_from_postgres.sh** (verificar si existe)
3. **scripts/insert_*.sh** (varios scripts de inserción)

### Instrucciones para Claude:

```
En el archivo scripts/deploy.sh, reemplaza:
PROJECT_DIR="/var/www/html/melisa_tenant"
por:
PROJECT_DIR="/var/www/html/tenant"

Luego busca todos los scripts .sh en la carpeta scripts/ que contengan 
"melisa_tenant" y reemplázalos por "tenant".
```

### Verificación:

```bash
# Buscar referencias en scripts
grep -r "melisa_tenant" scripts/

# Debe retornar: no matches
```

---

## 🎯 Fase 3: Actualizar Archivos de Entorno

**Archivos:** .env, .env.dev.test, .env.local, etc.  
**Prioridad:** 🟡 MEDIA

### Archivos a Modificar:

1. **.env.dev.test**
   - Línea 21: `APP_SECRET=test_secret_melisa_tenant_2025`

### Instrucciones para Claude:

```
En .env.dev.test, reemplaza:
APP_SECRET=test_secret_melisa_tenant_2025
por:
APP_SECRET=test_secret_tenant_2025

Busca en todos los archivos .env* si hay otras referencias a "melisa_tenant".
```

### Verificación:

```bash
# Verificar archivos .env
grep -n "melisa_tenant" .env*

# Debe retornar: no matches
```

---

## 🎯 Fase 4: Actualizar Documentación de Arquitectura

**Archivos:** 4 archivos principales  
**Prioridad:** 🟡 MEDIA

### Archivos a Modificar:

1. **ARCHITECTURE.md**
   - Referencias conceptuales a "melisa_tenant"
   - Diagramas de estructura

2. **MIGRATION_PLAN.md**
   - Referencias al proyecto tenant

3. **MULTITENANCY.md**
   - Estructura de proyectos

4. **CONEXION_DINAMICA_MULTI_TENANT.md**
   - Rutas de archivos y ejemplos

### Instrucciones para Claude:

```
En los archivos de arquitectura (ARCHITECTURE.md, MIGRATION_PLAN.md, 
MULTITENANCY.md, CONEXION_DINAMICA_MULTI_TENANT.md):

Reemplaza todas las referencias de "melisa_tenant" por "tenant".
Ten cuidado de mantener el contexto donde se explica el cambio de 
"melisa_base" (legacy) a "melisa_tenant" (actual) - en esos casos históricos,
mantén "melisa_tenant" pero aclara que ahora se llama "tenant".
```

### Verificación:

```bash
# Verificar archivos de arquitectura
grep -n "melisa_tenant" ARCHITECTURE.md MIGRATION_PLAN.md MULTITENANCY.md CONEXION_DINAMICA_MULTI_TENANT.md
```

---

## 🎯 Fase 5: Actualizar Documentación de Módulos

**Archivos:** ~20 archivos en docs/  
**Prioridad:** 🟢 BAJA

### Archivos a Modificar:

1. **docs/migracion/*/PLAN_MIGRACION_*.md** (todos los módulos)
   - logistica/PLAN_MIGRACION_LOGISTICA.md
   - liquidaciones/PLAN_MIGRACION_LIQUIDACIONES.md
   - taller/PLAN_MIGRACION_TALLER.md
   - clinico/PLAN_MIGRACION_CLINICO.md
   - admision/PLAN_MIGRACION_ADMISION.md
   - facturacion/PLAN_MIGRACION_FACTURACION.md
   - tesoreria/PLAN_MIGRACION_TESORERIA.md
   - pabellon/PLAN_MIGRACION_PABELLON.md
   - apoyo_clinico/PLAN_MIGRACION_APOYO_CLINICO.md
   - presupuesto/PLAN_MIGRACION_PRESUPUESTO.md
   - hospitalaria/PLAN_MIGRACION_HOSPITALARIA.md

2. **docs/migracion/sql/*.md**
   - INDEX.md
   - QUICK_START.md
   - README.md
   - RESUMEN_EJECUTIVO.md
   - 00_install_all_menus.sql

3. **docs/menu-tree-view/*.md**
   - QUICK_START.md
   - COPILOT_PROMPTS_INDEX.md

4. **docs/modulos/*.md**
   - SISTEMA_MANTENEDORES.md
   - MANTENEDOR_PAISES_DOCUMENTACION_COMPLETA.md

5. **docs/migracion/DATOS_DUMMY_POSTGRESQL.md**

### Instrucciones para Claude:

```
Reemplaza todas las ocurrencias de "melisa_tenant" por "tenant" en todos los 
archivos dentro de la carpeta docs/.

Usa grep_search primero para confirmar todos los archivos, luego procesa
los reemplazos en lotes usando multi_replace_string_in_file.
```

### Verificación:

```bash
# Verificar documentación
grep -r "melisa_tenant" docs/

# Debe retornar: no matches
```

---

## 🎯 Fase 6: Actualizar Configuraciones de Herramientas

**Archivos:** 1 archivo  
**Prioridad:** 🟢 BAJA

### Archivos a Modificar:

1. **.claude/settings.local.json**
   - Múltiples referencias en comandos históricos

### Instrucciones para Claude:

```
En .claude/settings.local.json, reemplaza todas las referencias de 
"melisa_tenant" por "tenant" en los comandos git y rutas.
```

### Verificación:

```bash
# Verificar configuración de Claude
grep -n "melisa_tenant" .claude/settings.local.json

# Debe retornar: no matches
```

---

## 🎯 Fase 7: Actualizar README y CHANGELOG

**Archivos:** 2 archivos  
**Prioridad:** 🟡 MEDIA

### Archivos a Modificar:

1. **README.md**
   - Línea 26-27: Instrucciones de clonado
   - Línea 327: Referencias al proyecto

2. **CHANGELOG.md**
   - Línea 261: URL de releases en GitHub

### Instrucciones para Claude:

```
En README.md:
- Reemplaza "git clone [URL_TFS] melisa_tenant" por "git clone [URL_TFS] tenant"
- Reemplaza "cd melisa_tenant" por "cd tenant"
- Actualiza referencias de "melisa_tenant" a "tenant"

En CHANGELOG.md:
- Actualiza la URL de GitHub releases si aplica
- Considera agregar una entrada en el changelog sobre este cambio
```

### Verificación:

```bash
# Verificar archivos principales
grep -n "melisa_tenant" README.md CHANGELOG.md
```

---

## 🎯 Fase 8: Actualizar Otros Archivos de Documentación

**Archivos:** Archivos variados  
**Prioridad:** 🟢 BAJA

### Archivos a Modificar:

1. **SYMFONY_7.4_MIGRATION_PLAN.md**
   - Línea 370: Ruta de backup

### Instrucciones para Claude:

```
Busca con grep_search cualquier otro archivo que contenga "melisa_tenant"
que no hayamos cubierto en las fases anteriores y actualízalo.
```

### Verificación:

```bash
# Búsqueda final global
grep -r "melisa_tenant" . --exclude-dir={vendor,var,node_modules,.git} --exclude="*.lock"

# Debe retornar: no matches (excepto este archivo de planificación)
```

---

## 🎯 Fase 9: Renombrar Directorio Físico

**⚠️ CRÍTICO: Esta es la fase final e irreversible**  
**Prioridad:** 🔴 CRÍTICA

### Pasos:

1. **Asegurarse que todos los cambios anteriores están comprometidos**

```bash
cd /var/www/html/melisa_tenant
git add .
git commit -m "refactor: actualizar todas las referencias de melisa_tenant a tenant"
git push origin refactor/rename-to-tenant
```

2. **Cerrar todos los editores y servicios**

```bash
# Detener servicios si están corriendo
docker-compose down  # si usas Docker
# o
symfony server:stop  # si usas Symfony CLI
```

3. **Renombrar el directorio**

```bash
cd /var/www/html
mv melisa_tenant tenant
cd tenant
```

4. **Actualizar remote de git si aplica**

```bash
# Si el repositorio remoto también cambia
git remote set-url origin [NUEVA_URL]
git remote -v  # Verificar
```

5. **Reinstalar dependencias (por si acaso)**

```bash
composer install
npm install  # si usas npm
```

### Verificación:

```bash
# Verificar que el proyecto funciona desde la nueva ubicación
pwd  # Debe mostrar: /var/www/html/tenant

# Probar comandos críticos
php bin/console list
php bin/console doctrine:migrations:status

# Verificar git
git status
git log --oneline -3
```

---

## 🎯 Fase 10: Testing y Validación Final

**Prioridad:** 🔴 CRÍTICA

### Checklist de Testing:

#### Testing Básico
- [ ] El proyecto carga sin errores: `php bin/console about`
- [ ] Las migraciones se listan correctamente: `php bin/console doctrine:migrations:list`
- [ ] Los comandos custom funcionan: `php bin/console app:migrate-tenant-legacy --help`
- [ ] Las rutas están correctas: `php bin/console debug:router`

#### Testing de Servicios
- [ ] Los servicios se cargan: `php bin/console debug:container`
- [ ] No hay errores en logs: `tail -f var/log/dev.log`

#### Testing de Base de Datos
- [ ] Conexión a base de datos funciona
- [ ] Las migraciones pendientes se pueden aplicar
- [ ] Los comandos de tenant funcionan

#### Testing de Scripts
- [ ] `scripts/deploy.sh` funciona correctamente
- [ ] Scripts de inserción de datos funcionan

#### Testing de Documentación
- [ ] Los enlaces en README.md funcionan
- [ ] Las rutas en documentación son correctas

### Comandos de Validación:

```bash
# Test 1: Verificar instalación
php bin/console about

# Test 2: Verificar migraciones
php bin/console doctrine:migrations:status --em=default
php bin/console doctrine:migrations:status --em=tenant

# Test 3: Verificar comandos custom
php bin/console app:migrate-tenant-legacy --help

# Test 4: Verificar que no quedan referencias
grep -r "melisa_tenant" . --exclude-dir={vendor,var,node_modules,.git} --exclude="*.lock" --exclude="RENAME_PROJECT_PLAN.md"

# Test 5: Verificar scripts
bash scripts/deploy.sh --dry-run  # si tiene esta opción

# Test 6: Probar en navegador
symfony server:start -d
# Visitar: http://localhost:8000
```

---

## 🚨 Plan de Rollback

**Si algo sale mal, seguir estos pasos:**

### Opción A: Rollback con Git (si no se renombró el directorio)

```bash
cd /var/www/html/melisa_tenant
git reset --hard origin/main  # o la rama principal
git clean -fd
```

### Opción B: Rollback completo (si se renombró el directorio)

```bash
# 1. Renombrar de vuelta
cd /var/www/html
mv tenant melisa_tenant

# 2. Restaurar desde backup
cd /var/www/html
rm -rf melisa_tenant  # ¡CUIDADO!
tar -xzf backup_melisa_tenant_YYYYMMDD_HHMMSS.tar.gz

# 3. Verificar
cd /var/www/html/melisa_tenant
git status
```

---

## 📊 Resumen de Impacto

| Fase | Archivos | Prioridad | Tiempo Est. |
|------|----------|-----------|-------------|
| 1. Comandos PHP | 2 | 🔴 CRÍTICA | 15 min |
| 2. Scripts Bash | ~8 | 🟠 ALTA | 20 min |
| 3. Archivos .env | ~3 | 🟡 MEDIA | 10 min |
| 4. Docs Arquitectura | 4 | 🟡 MEDIA | 20 min |
| 5. Docs Módulos | ~20 | 🟢 BAJA | 30 min |
| 6. Configs Herramientas | 1 | 🟢 BAJA | 5 min |
| 7. README/CHANGELOG | 2 | 🟡 MEDIA | 10 min |
| 8. Otros | Variable | 🟢 BAJA | 10 min |
| 9. Renombrar Directorio | - | 🔴 CRÍTICA | 20 min |
| 10. Testing | - | 🔴 CRÍTICA | 40 min |
| **TOTAL** | **~90** | - | **3 horas** |

---

## ✅ Checklist Final

Antes de considerar el trabajo completo:

- [ ] Todas las fases completadas
- [ ] Todos los tests pasados
- [ ] No quedan referencias a "melisa_tenant" (excepto en contextos históricos)
- [ ] Backup guardado y verificado
- [ ] Código funcionando desde `/var/www/html/tenant`
- [ ] Git remotes actualizados (si aplica)
- [ ] Equipo notificado del cambio
- [ ] Documentación actualizada en wiki/confluence (si aplica)
- [ ] CI/CD actualizado con nuevas rutas (si aplica)

---

## 📝 Notas Importantes

1. **Orden de Ejecución:** Las fases 1-8 pueden hacerse con el directorio aún llamado "melisa_tenant". Solo la fase 9 requiere el cambio físico.

2. **Git History:** El historial de git se mantiene intacto. Solo cambian las referencias de texto.

3. **Contextos Históricos:** En algunos documentos puede ser apropiado mantener "melisa_tenant" cuando se refiere al contexto histórico de migración de "melisa_base".

4. **Testing Incremental:** Es recomendable hacer commit y testing después de cada fase importante.

5. **Ambiente de Desarrollo:** Considera probar primero en un ambiente de desarrollo/staging antes de aplicar en producción.

---

## 🤖 Prompt para Claude

```
Necesito renombrar el proyecto de "melisa_tenant" a "tenant". 
Tengo un plan detallado en RENAME_PROJECT_PLAN.md.

Por favor, ejecuta las fases 1-8 en orden, usando multi_replace_string_in_file 
para eficiencia cuando sea posible.

IMPORTANTE: 
- NO renombres el directorio físico (Fase 9) - lo haré manualmente
- Después de cada fase, verifica con grep que no quedan referencias
- Usa los comandos de verificación especificados en el plan
- Si encuentras algún problema, repórtalo antes de continuar

Comienza con la Fase 1 (Comandos PHP) que es CRÍTICA.
```

---

**Creado:** 5 de Febrero, 2026  
**Última actualización:** 5 de Febrero, 2026  
**Versión:** 1.0
