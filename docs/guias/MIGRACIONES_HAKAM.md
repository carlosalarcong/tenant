# Comandos de Migraciones con Hakam Multi-Tenancy

Guía rápida de comandos para gestionar migraciones en arquitectura multi-tenant con `hakam/multi-tenancy-bundle`.

---

## Comandos Principales

### 1. Generar Nueva Migración (Tenant)

Genera un archivo de migración basado en diferencias entre entidades y base de datos.

```bash
php bin/console tenant:migrations:diff <tenant_id>
```

**Ejemplo:**
```bash
php bin/console tenant:migrations:diff 5
```

**Salida:**
- Crea archivo en `migrations/Tenant/VersionYYYYMMDDHHMMSS.php`
- Compara entidades con esquema actual
- Detecta cambios automáticamente

---

### 2. Ejecutar Migraciones (Tenant)

Aplica migraciones pendientes a una base de datos tenant.

```bash
php bin/console tenant:migrations:migrate migrate <tenant_id> [version] [opciones]
```

**Ejemplos:**

```bash
# Migrar a última versión
php bin/console tenant:migrations:migrate migrate 5 --no-interaction

# Migrar a versión específica
php bin/console tenant:migrations:migrate migrate 5 DoctrineMigrations\\Version20260114185009

# Dry run (simular sin ejecutar)
php bin/console tenant:migrations:migrate migrate 5 --dry-run
```

**Opciones:**
- `--no-interaction`: No pedir confirmación
- `--dry-run`: Simular sin ejecutar
- `--query-time`: Medir tiempo de cada query
- `--allow-no-migration`: No fallar si no hay migraciones

---

### 3. Migrar Todos los Tenants

Migra todos los tenants activos automáticamente.

```bash
php bin/console app:tenant:migrate-all
```

---

### 4. Inicializar Nueva Base de Datos Tenant

Crea y configura una nueva base de datos para un tenant.

```bash
php bin/console tenant:migrations:migrate init <tenant_id>
```

**Ejemplo:**
```bash
php bin/console tenant:migrations:migrate init 6
```

---

### 5. Crear Base de Datos Tenant

Crea físicamente la base de datos del tenant.

```bash
php bin/console tenant:database:create <tenant_id>
```

---

### 6. Actualizar Esquema (Desarrollo)

⚠️ **Solo para desarrollo** - Actualiza esquema directamente sin migración.

```bash
php bin/console tenant:schema:update --force <tenant_id>

# Ver SQL sin ejecutar
php bin/console tenant:schema:update --dump-sql <tenant_id>
```

---

### 7. Cargar Fixtures (Datos de Prueba)

Carga datos de prueba en base de datos tenant.

```bash
php bin/console tenant:fixtures:load <tenant_id>
```

---

## Comandos de Base de Datos Principal (Main)

Para la base de datos central (no tenants):

```bash
# Generar migración
php bin/console doctrine:migrations:diff --em=main

# Ejecutar migraciones
php bin/console doctrine:migrations:migrate --em=main --no-interaction

# Ver estado
php bin/console doctrine:migrations:status --em=main
```

---

## Flujo de Trabajo Típico

### Nuevo Feature con Cambios en Entidades

```bash
# 1. Modificar entidades en src/Entity/Tenant/

# 2. Generar migración para un tenant
php bin/console tenant:migrations:diff 5

# 3. Revisar archivo generado en migrations/Tenant/

# 4. Probar en un tenant
php bin/console tenant:migrations:migrate migrate 5 --dry-run

# 5. Aplicar migración
php bin/console tenant:migrations:migrate migrate 5 --no-interaction

# 6. Si todo OK, aplicar a todos los tenants
php bin/console app:tenant:migrate-all
```

---

## IDs de Tenants Disponibles

| ID | Nombre | Base de Datos | Estado |
|----|--------|---------------|--------|
| 5 | melisalacolina | melisalacolina | Activo |
| 6 | melisahospital | melisahospital | Activo |
| 7 | melisawiclinic | melisawiclinic | Activo |

---

## Troubleshooting

### Error: "Can't DROP 'FK_XXX'; check that column/key exists"

**Causa:** Migración intentando eliminar FK que no existe.

**Solución:**
```bash
# Eliminar migración problemática
rm migrations/Tenant/VersionXXXXXXXXXXXXXX.php

# Regenerar desde cero
php bin/console tenant:migrations:diff 5
```

---

### Migraciones no registradas en base de datos

**Mensaje:** "You have X previously executed migrations in the database that are not registered migrations."

**Causa:** Migraciones ejecutadas pero archivos eliminados del proyecto.

**Solución:** Normal si limpiaste histórico. Puedes continuar sin problema.

---

### Ver estado de migraciones

```bash
# Ver qué migraciones están pendientes
php bin/console doctrine:migrations:status --em=tenant

# Listar todas las migraciones
php bin/console doctrine:migrations:list --em=tenant
```

---

## Comandos Legacy (Deprecados)

⚠️ **No usar en proyectos nuevos:**

```bash
# Deprecado - usar tenant:migrations:diff
php bin/console app:migrations-tenant-legacy

# Deprecado - usar tenant:migrations:migrate
php bin/console app:migrate-tenant-legacy
```

---

## Referencias

- Bundle: `hakam/multi-tenancy-bundle` v2.9.3
- Documentación: [GitHub - hakam/multi-tenancy-bundle](https://github.com/klipperdev/multi-tenancy-bundle)
- Symfony Migrations: [doctrine/doctrine-migrations-bundle](https://symfony.com/bundles/DoctrineMigrationsBundle/current/index.html)

---

**Última actualización:** 2026-01-14
