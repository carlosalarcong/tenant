# Prompt para GitHub Copilot: Renombrar "melisa" → genérico

## Contexto
El proyecto se llama `melisa_tenant` y debe ser renombrado a un sistema genérico
llamado simplemente `tenant`. La palabra "melisa" aparece en ~90 referencias PHP,
4 archivos de config, 2 templates Twig, 2 JS de Stimulus y múltiples directorios de assets/translations.

Este cambio debe hacerse en **fases ordenadas** porque algunas referencias tocan
infraestructura real (credenciales de BD, nombres de tenants activos en BD).

---

## INVENTARIO COMPLETO DE OCURRENCIAS

### Grupo A — UI / Presentación (sin impacto en runtime)
| Archivo | Línea | Valor actual | Reemplazar por |
|---------|-------|-------------|----------------|
| `templates/partials/title-meta.html.twig` | `'melisa.title'\|trans` | Clave de traducción | `'app.title'\|trans` |
| `src/Controller/ResetPasswordController.php` | ~129 | `noreply@melisa.com` | `noreply@tenant.local` (o variable de entorno) |
| `src/Controller/ResetPasswordController.php` | ~129 | `'Sistema Melisa'` | `'Sistema'` (o parámetro de config) |
| `templates/dashboard/default/index.html.twig` | texto | `.melisaupgrade.prod` | Variable de entorno `APP_DOMAIN` |

### Grupo B — Archivos de traducción y assets por tenant
Directorios que usan el prefijo `melisa` como nombre de tenant:

| Directorio actual | Propósito |
|-------------------|-----------|
| `translations/melisahospital/` | Traducciones tenant "hospital" |
| `translations/melisalacolina/` | Traducciones tenant "lacolina" |
| `assets/controllers/internal/melisahospital/` | Stimulus controllers tenant "hospital" |
| `assets/controllers/internal/melisalacolina/` | Stimulus controllers tenant "lacolina" |

Los archivos JS dentro de estos directorios tienen referencias internas adicionales:

**`assets/controllers/internal/melisahospital/patient_controller.js`**:
- `tenant: "melisahospital"` (objeto de datos)
- `console.log("📊 Tenant: melisahospital")`
- Comentarios sobre `BD melisahospital`

**`assets/controllers/internal/melisalacolina/patient_controller.js`**:
- `clinic: 'melisalacolina'` (objeto de datos)
- `'X-Clinic-Context': 'melisalacolina'` (header HTTP)
- `?clinic=melisalacolina` (query param en llamadas API)

> **Nota**: el prefijo `melisa` en estos directorios viene del subdomain del tenant
> almacenado en BD (`melisahospital`, `melisalacolina`). Renombrar los directorios
> **y el contenido de los JS** requiere sincronizar con la Fase 5 (renombrar subdomains en BD).

### Grupo C — Config YAML
| Archivo | Línea | Cambio |
|---------|-------|--------|
| `config/packages/translation.yaml` | paths `melisahospital`, `melisalacolina` | Depende de Fase 5 |
| `config/services.yaml` | comentario `melisa_central` | Solo actualizar comentario |
| `config/packages/hakam_multi_tenancy.yaml` | comentario `melisa_central` | Solo actualizar comentario |
| `.env` | `CORS_ALLOW_ORIGIN` → `melisaupgrade.prod` | Mover a variable |
| `.env.example` | mismo | Ídem |

### Grupo D — PHP: credenciales hardcodeadas (¡RIESGO ALTO!)
Estos archivos tienen credenciales de BD **hardcodeadas** que deben moverse a `.env`:

| Archivo | Valores hardcodeados |
|---------|---------------------|
| `src/Entity/Main/TenantDb.php:79` | `return 'melisa'` (user DB) |
| `src/Entity/Main/TenantDb.php:84` | `return 'melisamelisa'` (password DB) |
| `src/Controller/PasswordResetController.php:45-47` | `dbname: melisa_central`, user, password |
| `src/Command/MigrateTenantLegacyCommand.php:28-30` | Mismas credenciales |
| `src/Service/CustomTenantConfigProvider.php:59-60` | `user: 'melisa'`, `password: 'melisamelisa'` |

### Grupo D2 — PHP: tenant por defecto hardcodeado (¡RIESGO CRÍTICO!)
`src/EventListener/TenantDatabaseSwitchListener.php:117` tiene el tenant de fallback hardcodeado:

```php
// ANTES — si el resolver falla, redirige silenciosamente a melisahospital
return 'melisahospital';

// DESPUÉS — leer de variable de entorno
return $_ENV['TENANT_DEFAULT_FALLBACK'] ?? throw new \RuntimeException('No tenant resolved');
```

> **⚠️ Este es el más crítico**: si el listener no resuelve el subdomain,
> usa `melisahospital` como tenant por defecto sin avisar. En producción esto
> puede mostrar datos del tenant equivocado.

### Grupo E — PHP: rutas absolutas hardcodeadas
`src/Command/MigrateTenantLegacyCommand.php` tiene múltiples referencias a:
```
'/var/www/html/melisa_tenant/migrations'
'/var/www/html/melisa_tenant/src/Entity'
```
Deben reemplazarse por `$this->projectDir` inyectado desde `%kernel.project_dir%`.

### Grupo F — BD: nombres de bases de datos
| BD actual | Propuesta | Impacto |
|-----------|-----------|---------|
| `melisa_central` | `tenant_central` | Renombrar base de datos real |
| `melisa_template` | `tenant_template` | Renombrar base de datos real |
| `melisahospital` | `hospital` o `lacolina_tenant` | Renombrar BD de cada tenant |
| `melisalacolina` | `lacolina` | Renombrar BD de cada tenant |

> **⚠️ Esta fase requiere coordinación con DBA y migración de infraestructura.**
> No ejecutar en producción sin respaldo.

---

## PLAN POR FASES

---

### FASE 0 — Renombrar repo GitHub y directorio del servidor (hacer PRIMERO)
**Objetivo**: Cambiar el nombre del proyecto a nivel de infraestructura antes de tocar código.

> Hacer esto primero evita que los pasos siguientes queden con referencias al nombre viejo
> en git history, commits y mensajes de PR.

#### 0.1 — Renombrar el repositorio en GitHub
1. Ir a `https://github.com/carlosalarcong/melisa_tenant/settings`
2. Sección **General** → campo **Repository name**
3. Cambiar `melisa_tenant` → `tenant`
4. Click **Rename**

GitHub crea una redirección automática desde la URL vieja, pero **no confiar en ella a largo plazo**.

#### 0.2 — Actualizar el remote en todos los clones locales
```bash
# Ejecutar en cada máquina que tenga el repo clonado
git remote set-url origin https://github.com/carlosalarcong/tenant.git

# Verificar
git remote -v
# Debe mostrar: origin  https://github.com/carlosalarcong/tenant.git
```

#### 0.3 — Renombrar el directorio físico en el servidor
> **⚠️ Requiere actualizar el virtual host de Apache/Nginx. Hacer en ventana de mantenimiento.**

```bash
# 1. Detener el servidor web
sudo systemctl stop apache2   # o nginx

# 2. Renombrar el directorio
sudo mv /var/www/html/melisa_tenant /var/www/html/tenant

# 3. Actualizar el virtual host
# En Apache: /etc/apache2/sites-available/melisa_tenant.conf
#   DocumentRoot /var/www/html/tenant
#   <Directory /var/www/html/tenant>

# 4. Reiniciar el servidor web
sudo systemctl start apache2

# 5. Verificar que la app responde
curl -I http://localhost
```

#### 0.4 — Actualizar el remote en el servidor (si el servidor tiene el repo clonado por git)
```bash
cd /var/www/html/tenant
git remote set-url origin https://github.com/carlosalarcong/tenant.git
git remote -v
```

#### 0.5 — Actualizar referencias al path del servidor en código
Después de renombrar el directorio, las referencias hardcodeadas a `/var/www/html/melisa_tenant/`
en `MigrateTenantLegacyCommand.php` quedarán rotas. Por eso la **Fase 3** (paths a `%kernel.project_dir%`)
debe ejecutarse **inmediatamente después** de esta fase.

**Verificación**: `php bin/console cache:clear` desde el nuevo directorio sin errores.

---

### FASE 1 — Texto de UI (5 min, sin riesgo)
**Objetivo**: Eliminar "Melisa" de lo que ve el usuario final.

**Archivos a modificar**:
```
templates/partials/title-meta.html.twig
src/Controller/ResetPasswordController.php
```

**Cambios**:
1. En `title-meta.html.twig`: cambiar `'melisa.title'|trans` → `'app.title'|trans`
2. En `ResetPasswordController.php`: extraer sender a parámetro `mailer_sender_address`
   y `mailer_sender_name` en `config/services.yaml` y `.env`
3. Actualizar archivos de traducción: renombrar clave `melisa.title` → `app.title`
   en todos los `.yaml` de `translations/`

**Verificación**: Cargar la app en browser, revisar título y correos de reset.

---

### FASE 2 — Comentarios y documentación (10 min, sin riesgo)
**Objetivo**: Limpiar referencias en comentarios PHP y docs sin tocar lógica.

**Archivos a modificar** (solo comentarios/docstrings, NO código):
```
src/Controller/AbstractTenantAwareController.php
src/Command/TenantPermissionProfileCommand.php
src/Command/TestTenantEntityManagerCommand.php
src/Command/MigrateTenantLegacyCommand.php (solo textos de ayuda, no código)
src/EventListener/TenantDatabaseSwitchListener.php
src/Service/DynamicControllerResolver.php
src/Service/CustomTenantConfigProvider.php
src/Service/LocalizationService.php
src/Service/TenantResolver.php
config/services.yaml (comentario)
config/packages/hakam_multi_tenancy.yaml (comentario)
```

**Cambios**: `sed -i` en comentarios:
- `melisahospital` → `hospital` (en ejemplos de comandos)
- `melisalacolina` → `lacolina` (en ejemplos de comandos)
- `melisa_central` → `tenant_central` (en comentarios)

**Verificación**: `php bin/console cache:clear` — la app debe seguir funcionando igual.

---

### FASE 3 — Rutas absolutas en Commands (15 min, bajo riesgo)
**Objetivo**: Eliminar `/var/www/html/melisa_tenant/` hardcodeado.

**Archivo**: `src/Command/MigrateTenantLegacyCommand.php`

**Patrón de corrección**:
```php
// ANTES
$migrationsDir = '/var/www/html/melisa_tenant/migrations';

// DESPUÉS
// Inyectar en constructor:
public function __construct(
    private string $projectDir,  // bind: '%kernel.project_dir%' en services.yaml
) { ... }

// Usar:
$migrationsDir = $this->projectDir . '/migrations';
```

**Archivo services.yaml** — agregar binding:
```yaml
App\Command\MigrateTenantLegacyCommand:
    arguments:
        $projectDir: '%kernel.project_dir%'
```

**Verificación**: `php bin/console app:migrate-tenant --help`

---

### FASE 4 — Credenciales a variables de entorno (30 min, medio riesgo)
**Objetivo**: Eliminar credenciales hardcodeadas de BD.

**Archivos**:
- `src/Entity/Main/TenantDb.php`
- `src/Controller/PasswordResetController.php`
- `src/Command/MigrateTenantLegacyCommand.php`
- `src/Service/CustomTenantConfigProvider.php`
- `src/EventListener/TenantDatabaseSwitchListener.php` ← **tenant fallback hardcodeado**

**Patrón de corrección para `TenantDb.php`**:
```php
// ANTES
public function getDefaultDbUser(): string { return 'melisa'; }
public function getDefaultDbPassword(): string { return 'melisamelisa'; }

// DESPUÉS (leer de parámetro inyectado o env)
public function getDefaultDbUser(): string
{
    return $_ENV['TENANT_DB_DEFAULT_USER'] ?? 'tenant';
}
public function getDefaultDbPassword(): string
{
    return $_ENV['TENANT_DB_DEFAULT_PASSWORD'] ?? '';
}
```

**Patrón de corrección para `CustomTenantConfigProvider.php`**:
```php
// ANTES
$connection = [
    'user'     => 'melisa',
    'password' => 'melisamelisa',
    'dbname'   => 'melisa_central',
];

// DESPUÉS
$connection = [
    'user'     => $_ENV['CENTRAL_DB_USER'],
    'password' => $_ENV['CENTRAL_DB_PASSWORD'],
    'dbname'   => $_ENV['CENTRAL_DB_NAME'],
];
```

**Patrón de corrección para `TenantDatabaseSwitchListener.php:117`**:
```php
// ANTES — fallback silencioso al tenant equivocado
return 'melisahospital';

// OPCIÓN A — lanzar excepción (recomendado en producción)
throw new \RuntimeException('No se pudo resolver el tenant desde el request.');

// OPCIÓN B — variable de entorno (si se necesita fallback en dev)
return $_ENV['TENANT_DEFAULT_FALLBACK'] ?? throw new \RuntimeException('No tenant resolved');
```

**Agregar a `.env`**:
```dotenv
TENANT_DB_DEFAULT_USER=tenant
TENANT_DB_DEFAULT_PASSWORD=changeme
CENTRAL_DB_NAME=tenant_central
CENTRAL_DB_USER=tenant
CENTRAL_DB_PASSWORD=changeme
APP_DOMAIN=yourdomain.com
MAILER_SENDER_ADDRESS=noreply@yourdomain.com
MAILER_SENDER_NAME="Sistema"
# Solo para desarrollo local, NO poner en producción:
# TENANT_DEFAULT_FALLBACK=hospital
```

**Verificación**: Probar login, reset de contraseña y `php bin/console app:migrate-tenant --help`.

---

### FASE 5 — Renombrar subdomains de tenant en BD (coordinado con DBA)
**Objetivo**: Cambiar los nombres de subdomain de `melisahospital` → `hospital`, etc.

> **⚠️ Esta fase MODIFICA DATOS EN BD. Hacer backup primero.**
> Coordinar con todos los usuarios del sistema.

**SQL a ejecutar en `tenant_central`**:
```sql
-- Verificar tenants actuales
SELECT id, subdomain, db_name FROM tenant;

-- Renombrar subdomains (ajustar según los nombres reales en BD)
UPDATE tenant SET subdomain = 'hospital' WHERE subdomain = 'melisahospital';
UPDATE tenant SET subdomain = 'lacolina' WHERE subdomain = 'melisalacolina';
```

**Renombrar directorios de assets y translations DESPUÉS de la BD**:
```bash
# Translations
mv translations/melisahospital translations/hospital
mv translations/melisalacolina translations/lacolina

# Assets
mv assets/controllers/internal/melisahospital assets/controllers/internal/hospital
mv assets/controllers/internal/melisalacolina assets/controllers/internal/lacolina
```

**Actualizar `config/packages/translation.yaml`**:
```yaml
# ANTES
- '%kernel.project_dir%/translations/melisahospital'
- '%kernel.project_dir%/translations/melisalacolina'

# DESPUÉS
- '%kernel.project_dir%/translations/hospital'
- '%kernel.project_dir%/translations/lacolina'
```

**Actualizar `src/Service/LocalizationService.php`** y cualquier archivo que resuelva
la ruta de traducción por subdomain (arrays con claves `melisahospital`, `melisalacolina`, `melisawiclinic`).

**Actualizar referencias internas en archivos JS**:
```bash
# Reemplazar referencias al nombre del tenant dentro de los JS
sed -i 's/melisahospital/hospital/g' assets/controllers/internal/hospital/patient_controller.js
sed -i 's/melisalacolina/lacolina/g' assets/controllers/internal/lacolina/patient_controller.js
```

Los valores específicos a reemplazar en cada archivo:
- `tenant: "melisahospital"` → `tenant: "hospital"`
- `clinic: 'melisalacolina'` → `clinic: 'lacolina'`
- `'X-Clinic-Context': 'melisalacolina'` → `'X-Clinic-Context': 'lacolina'`
- `?clinic=melisalacolina` → `?clinic=lacolina`

**Verificación**: Login con usuario del tenant renombrado, verificar que traducciones cargan
y que las llamadas API desde el JS llevan el header/param correcto (DevTools → Network).

---

### FASE 6 — Renombrar bases de datos (requiere DBA + downtime)
**Objetivo**: Cambiar nombre de `melisa_central` → `tenant_central`.

> **⚠️ Requiere downtime. Coordinar ventana de mantenimiento.**

**Pasos**:
```bash
# PostgreSQL
ALTER DATABASE melisa_central RENAME TO tenant_central;

# Actualizar .env
DATABASE_URL="postgresql://tenant:changeme@localhost:5432/tenant_central?serverVersion=16&charset=utf8"
```

**Archivos PHP a actualizar después**:
- `src/Command/TestTenantEntityManagerCommand.php` (referencia directa a `melisa_central`)
- Cualquier otro archivo con el string literal `melisa_central`

**Verificación**: `php bin/console doctrine:schema:validate`

---

## ORDEN DE EJECUCIÓN RECOMENDADO

```
Fase 0 (GitHub + directorio servidor)
    ↓
Fase 3 (paths a %kernel.project_dir%)  ← inmediatamente después del rename del directorio
    ↓
Fase 1 (UI) → Fase 2 (comentarios)
    ↓
Fase 4 (credenciales a .env)
    ↓
Fase 5 (BD: subdomains + dirs assets/translations)  ← requiere backup
    ↓
Fase 6 (BD: rename central)  ← requiere downtime
```

| Fases | Riesgo | Requiere |
|-------|--------|---------|
| 0 | Medio (downtime corto) | Ventana de mantenimiento |
| 1, 2 | Sin riesgo | Nada especial |
| 3 | Bajo | Después de Fase 0 |
| 4 | Medio | Revisar con DBA las credenciales nuevas |
| 5 | Alto | Backup previo + coordinar usuarios |
| 6 | Alto | Downtime + backup previo |

---

## CHECKLIST DE VERIFICACIÓN FINAL

**Fase 0**:
- [ ] Repo GitHub renombrado a `tenant`
- [ ] `git remote -v` en todos los clones muestra la nueva URL
- [ ] Virtual host actualizado y servidor web reiniciado
- [ ] `php bin/console cache:clear` desde `/var/www/html/tenant` sin errores

- [ ] `php bin/console cache:clear && php bin/console cache:warmup`
- [ ] Login funciona en todos los tenants
- [ ] Reset de contraseña envía email correcto
- [ ] `php bin/console doctrine:schema:validate` pasa
- [ ] `php bin/console app:migrate-tenant --help` muestra ayuda correcta
- [ ] Assets Webpack compilan: `npm run build`
- [ ] CORS funciona para el nuevo dominio en `.env`
- [ ] Sidebar carga el menú correctamente
- [ ] Buscar `melisa` en todo el proyecto: `grep -r "melisa" src/ config/ templates/ assets/ .env*` → debe retornar 0 resultados

---

## ARCHIVOS QUE COPILOT NO DEBE TOCAR

- `docs/` — documentación histórica, no es código runtime
- `var/` — cache generado
- `vendor/` — dependencias de terceros
- Migraciones en `migrations/` — históricamente ya ejecutadas
