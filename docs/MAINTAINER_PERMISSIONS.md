# 🔐 Sistema de Permisos para Mantenedores

## 📋 Resumen Ejecutivo

Este documento describe el sistema de permisos implementado para los **132 mantenedores** del sistema Melisa, utilizando un enfoque híbrido basado en Symfony Voters con gestión dinámica desde UI.

**Estado:** ✅ **Sprint 2 - CRUD UI Completado** (Febrero 2026)  
**Arquitectura:** Voter Híbrido + Base de Datos + Administración Web  
**Próximas Fases:** Category-based granularity (Sprint 3) → Permisos específicos opcionales (Sprint 4+)

**Novedades Sprint 2:**
- 🎉 Administración de permisos desde interfaz web
- 🔄 Roles cargados dinámicamente desde tabla `role`
- ⚡ Invalidación automática de caché
- 🛡️ Protección de permisos críticos
- 📊 Logging de auditoría automático

---

## 🎯 Componentes Implementados

### 1. MaintainerVoter

**Ubicación:** `src/Security/Voter/MaintainerVoter.php`

Voter de Symfony que gestiona permisos de acceso a mantenedores mediante verificación de roles del usuario.

**Permisos soportados:**
- `MAINTAINER_CREATE` - Crear nuevos registros
- `MAINTAINER_READ` - Ver/listar registros (lectura)
- `MAINTAINER_UPDATE` - Editar registros existentes
- `MAINTAINER_DELETE` - Eliminar registros
- `MAINTAINER_EXPORT` - Exportar datos a CSV

**Características:**
- ✅ Sin dependencias de base de datos (performance óptimo)
- ✅ Cache automático de roles en sesión
- ✅ Secure by default (todo denegado si no hay rol explícito)
- ✅ Preparado para Fase 2 (granularidad por categoría)

---

### 2. Integración con AbstractMantenedorController

**Ubicación:** `src/Controller/AbstractMantenedorController.php`

Todos los 132 mantenedores que heredan de `AbstractMantenedorController` ahora tienen control de acceso automático.

**Métodos protegidos:**
```php
protected function handleCreate(Request $request): Response
{
    $this->denyAccessUnlessGranted(MaintainerVoter::CREATE, $this->getEntityClass());
    // ...
}

protected function handleEdit(Request $request, int $id): Response
{
    $entity = $this->findEntity($id);
    $this->denyAccessUnlessGranted(MaintainerVoter::UPDATE, $entity);
    // ...
}

protected function handleDelete(Request $request, int $id): Response
{
    $entity = $this->findEntity($id);
    $this->denyAccessUnlessGranted(MaintainerVoter::DELETE, $entity);
    // ...
}

protected function handleExport(Request $request, ...): Response
{
    $this->denyAccessUnlessGranted(MaintainerVoter::EXPORT, $this->getEntityClass());
    // ...
}
```

**Beneficio:** 132 mantenedores protegidos con 4 líneas de código (centralización).

---

### 3. Control de UI en Templates

**Ubicación:** `templates/maintainers/modern_index.html.twig`

Los botones de acción solo se muestran si el usuario tiene el permiso correspondiente.

**Botones protegidos:**
```twig
{# Botón Crear - Solo si tiene permiso CREATE #}
{% if is_granted(constant('App\\Security\\Voter\\MaintainerVoter::CREATE'), entity_class) %}
    <button data-bs-toggle="modal" ...>Crear</button>
{% endif %}

{# Botón Editar - Solo si tiene permiso UPDATE #}
{% if is_granted(constant('App\\Security\\Voter\\MaintainerVoter::UPDATE'), item) %}
    <button data-bs-toggle="modal" ...>Editar</button>
{% endif %}

{# Botón Eliminar - Solo si tiene permiso DELETE #}
{% if is_granted(constant('App\\Security\\Voter\\MaintainerVoter::DELETE'), item) %}
    <form method="post" ...><button>Eliminar</button></form>
{% endif %}

{# Botón Exportar - Solo si tiene permiso EXPORT #}
{% if is_granted(constant('App\\Security\\Voter\\MaintainerVoter::EXPORT'), entity_class) %}
    <a href="...">Exportar CSV</a>
{% endif %}
```

**Principio de seguridad:** Defense in depth
- Capa 1: UI oculta botones → Mejor UX
- Capa 2: Controller verifica permisos → Seguridad real

---

### 4. Tests Unitarios

**Ubicación:** `tests/Unit/Security/Voter/MaintainerVoterTest.php`

Suite completa de **27 tests** que verifican todos los escenarios posibles.

**Cobertura:**
- ✅ ROLE_ADMIN → Acceso completo (5 tests)
- ✅ ROLE_MAINTAINER_MANAGER → CRUD + Export (5 tests)
- ✅ ROLE_MAINTAINER_USER → Solo READ (5 tests)
- ✅ Usuario sin rol → Sin acceso (2 tests)
- ✅ Usuario no autenticado → Sin acceso (2 tests)
- ✅ Atributos no soportados → Abstención (1 test)
- ✅ Jerarquía de roles (3 tests)
- ✅ Matrices completas de permisos (4 tests)

**Ejecutar tests:**
```bash
# Test específico del MaintainerVoter
php bin/phpunit tests/Unit/Security/Voter/MaintainerVoter Test.php

# Con cobertura de código
php bin/phpunit --coverage-html var/coverage tests/Unit/Security/Voter/MaintainerVoterTest.php
```

---

### 5. Administración desde UI (Sprint 2)

**Ubicación:** `/admin/maintainer-permissions`  
**Controller:** `src/Controller/MaintainerRolePermissionController.php`  
**Form:** `src/Form/MaintainerRolePermissionType.php`  
**Templates:** `templates/maintainers/maintainer_role_permission/`

🎉 **NUEVO:** Mantenedor CRUD completo para gestionar permisos dinámicamente desde la interfaz web.

**Características principales:**
- ✅ Roles cargados dinámicamente desde tabla `role`
- ✅ Gestión completa CRUD sin editar código
- ✅ Invalidación automática de caché (Redis + in-memory)
- ✅ Soporte para wildcards (*) que dan todos los permisos
- ✅ Granularidad opcional por categoría de mantenedor
- ✅ Protección contra eliminación de permisos críticos
- ✅ Logging automático de cambios para auditoría
- ✅ Solo accesible por ROLE_ADMIN

**Flujo de trabajo:**
1. Acceder a la UI en **Configuración → Permisos de Mantenedores**
2. Crear/editar permisos seleccionando rol, permiso, categoría
3. Los cambios se aplican **inmediatamente** (sin restart)
4. El caché se invalida automáticamente

**Campos del formulario:**
- **Rol**: Dropdown dinámico desde tabla `role` (solo activos)
- **Permiso**: CREATE, READ, UPDATE, DELETE, EXPORT, o wildcard (*)
- **Concedido**: Checkbox (OTORGA o DENIEGA el permiso)
- **Categoría**: Opcional - basic, clinical, commercial, hospital, etc.
- **Mantenedor**: Opcional - Clase PHP específica (Phase 3)
- **Descripción**: Texto libre para documentar el propósito
- **Prioridad**: 0-100 (mayor prioridad se evalúa primero)
- **Activo**: Solo permisos activos son evaluados

**Validaciones especiales:**
- ⚠️ Advierte si se crea permiso DENY (granted=false)
- ⚠️ Sugiere prioridad >= 5 para wildcards (*)
- 🛡️ Previene eliminación del permiso ROLE_ADMIN wildcard

**Caché y performance:**
```php
// La invalidación de caché se ejecuta automáticamente
// en afterSave() y afterDelete()
$this->repository->invalidateCache();

// Los cambios se reflejan en el siguiente request:
// 1. Redis cache cleared (1 hora TTL)
// 2. In-memory cache cleared (per-request)
// 3. Próxima verificación usa nuevos permisos
```

**Ejemplo: Dar permiso READ a ROLE_ENFERMERA en categoría 'clinical'**
1. Crear nuevo permiso
2. Rol: Enfermera
3. Permiso: READ
4. Concedido: ✓  
5. Categoría: clinical
6. Guardar → Caché invalidado automáticamente

**Tabla `role`:**
La tabla centraliza todos los roles del sistema:
```sql
SELECT id, code, name, position, is_active FROM role ORDER BY position;
-- Roles están ordenados por prioridad (admin primero)
-- Solo roles activos aparecen en dropdowns
-- Roles de sistema (is_system=true) no pueden eliminarse
```

**Acceso desde menú:**
- Ubicación: **Configuración → Permisos de Mantenedores**
- Icon: 🔐 (bi bi-shield-lock)
- Ruta: `app_maintainer_permission_index`
- Permiso requerido: ROLE_ADMIN

---

## 👥 Roles del Sistema

### 📊 Matriz de Permisos (Fase 1 - MVP)

| Rol                      | CREATE | READ | UPDATE | DELETE | EXPORT |
|--------------------------|--------|------|--------|--------|--------|
| **ROLE_ADMIN**               | ✅     | ✅   | ✅     | ✅     | ✅     |
| **ROLE_MAINTAINER_MANAGER**  | ✅     | ✅   | ✅     | ✅     | ✅     |
| **ROLE_MAINTAINER_USER**     | ❌     | ✅   | ❌     | ❌     | ❌     |
| *(Sin rol específico)*       | ❌     | ❌   | ❌     | ❌     | ❌     |

---

### 1️⃣ ROLE_ADMIN

**Descripción:** Rol de administrador del sistema (God mode)

**Permisos:**
- ✅ Acceso completo a TODOS los mantenedores
- ✅ Crear, leer, editar, eliminar y exportar sin restricciones
- ✅ Puede acceder a mantenedores de todas las categorías

**Casos de uso:**
- Administrador de sistema
- Super usuario con acceso total
- DevOps / IT Manager

**Ejemplo de asignación:**
```php
$user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
```

---

### 2️⃣ ROLE_MAINTAINER_MANAGER

**Descripción:** Gestor de datos maestros con acceso CRUD completo

**Permisos:**
- ✅ Crear nuevos registros en mantenedores
- ✅ Leer y listar todos los registros
- ✅ Editar registros existentes
- ✅ Eliminar registros
- ✅ Exportar datos a CSV

**Restricciones:**
- ❌ No tiene acceso a funciones de administración del sistema
- ❌ No puede gestionar usuarios ni permisos (solo datos)

**Casos de uso:**
- Jefe de finanzas (gestiona centros de costo, tipos de documento, etc.)
- Jefe de RRHH (gestiona cargos, profesiones, tipos de contrato, etc.)
- Administrador de datos clínicos (gestiona diagnósticos, procedimientos, etc.)

**Ejemplo de asignación:**
```php
$user->setRoles(['ROLE_USER', 'ROLE_MAINTAINER_MANAGER']);
```

---

### 3️⃣ ROLE_MAINTAINER_USER

**Descripción:** Usuario de solo lectura de mantenedores

**Permisos:**
- ✅ Leer y listar todos los registros
- ✅ Ver detalles de mantenedores

**Restricciones:**
- ❌ No puede crear nuevos registros
- ❌ No puede editar registros existentes
- ❌ No puede eliminar registros
- ❌ No puede exportar datos

**Casos de uso:**
- Usuarios operativos que solo consultan datos
- Personal que necesita ver catálogos pero no modificarlos
- Roles de apoyo o consulta

**Ejemplo de asignación:**
```php
$user->setRoles(['ROLE_USER', 'ROLE_MAINTAINER_USER']);
```

---

## 🔧 Guía de Uso

### Para Desarrolladores

#### 1. Proteger un nuevo mantenedor

Si creas un nuevo controlador de mantenedor, heredar de `AbstractMantenedorController` es suficiente:

```php
class NewMaintainerController extends AbstractMantenedorController
{
    // ¡Automáticamente protegido! No necesitas hacer nada más.
}
```

#### 2. Verificar permisos en código custom

```php
// En cualquier controller
if ($this->isGranted(MaintainerVoter::CREATE, Disease::class)) {
    // Usuario puede crear enfermedades
}

// Con excepción automática si no tiene permiso
$this->denyAccessUnlessGranted(MaintainerVoter::UPDATE, $disease);
```

#### 3. Verificar permisos en Twig

```twig
{% if is_granted(constant('App\\Security\\Voter\\MaintainerVoter::CREATE'), entity_class) %}
    <button>Crear</button>
{% endif %}

{# Para una entidad específica #}
{% if is_granted(constant('App\\Security\\Voter\\MaintainerVoter::UPDATE'), item) %}
    <button>Editar</button>
{% endif %}
```

---

### Para Administradores de Sistema

#### 1. Asignar roles a usuarios

**Opción A: Por código (scripts de migración)**
```php
$user = $entityManager->getRepository(Member::class)->find($userId);
$user->setRoles(['ROLE_USER', 'ROLE_MAINTAINER_MANAGER']);
$entityManager->flush();
```

**Opción B: Por SQL directo**
```sql
-- Ver roles actuales de un usuario
SELECT id, email, roles FROM member WHERE email = 'user@example.com';

-- Asignar ROLE_MAINTAINER_MANAGER
UPDATE member 
SET roles = '["ROLE_USER", "ROLE_MAINTAINER_MANAGER"]'
WHERE email = 'user@example.com';
```

#### 2. Jerarquía de roles

Symfony maneja la jerarquía automáticamente. Si un usuario tiene múltiples roles, **el más permisivo gana**:

```php
// Usuario con estos roles:
['ROLE_USER', 'ROLE_MAINTAINER_USER', 'ROLE_ADMIN']

// ROLE_ADMIN gana → Usuario tiene acceso completo
```

#### 3. Auditoría de accesos

**Recomendación:** Configurar logger de seguridad en `config/packages/monolog.yaml`:

```yaml
monolog:
    handlers:
        security:
            type: stream
            path: "%kernel.logs_dir%/security.log"
            level: info
            channels: ["security"]
```

---

## 🚀 Roadmap de Implementación

### ✅ Sprint 1 - MVP (Completado)

**Duración:** 1 semana  
**Estado:** ✅ **COMPLETADO** (Febrero 2026)

**Entregables:**
- ✅ MaintainerVoter básico (role-based simple)
- ✅ Integración en AbstractMantenedorController
- ✅ Protección de UI en templates
- ✅ 27 tests unitarios con 100% cobertura
- ✅ Documentación completa

**Impacto:** 132 mantenedores protegidos con permisos role-based.

---

### 📋 Sprint 2 - Granularidad por Categoría (Planificado)

**Duración:** 2 semanas  
**Estado:** ⏳ **Pendiente** (Marzo 2026)

**Objetivo:** Permisos diferenciados por categoría de mantenedor.

**Matriz de permisos propuesta:**

| Rol                      | Basic    | Clinical | Commercial | Hospital | Human    |
|--------------------------|----------|----------|------------|----------|----------|
| ROLE_ADMIN               | ✅ CRUD  | ✅ CRUD  | ✅ CRUD    | ✅ CRUD  | ✅ CRUD  |
| ROLE_MAINTAINER_MANAGER  | ✅ CRUD  | ✅ CRUD  | ✅ CRUD    | ✅ CRUD  | ✅ CRUD  |
| ROLE_CLINICAL_MANAGER    | ❌       | ✅ CRUD  | ❌         | ❌       | ❌       |
| ROLE_FINANCE_MANAGER     | ❌       | ❌       | ✅ CRUD    | ❌       | ❌       |
| ROLE_HR_MANAGER          | ❌       | ❌       | ❌         | ❌       | ✅ CRUD  |
| ROLE_MAINTAINER_USER     | ✅ READ  | ✅ READ  | ✅ READ    | ✅ READ  | ✅ READ  |

**Entregables planificados:**
- Método `getMaintenedorCategory()` activo
- Matriz `$categoryRoleMatrix` en MaintainerVoter
- Nuevos roles específicos por categoría
- Tests de permisos por categoría
- Documentación actualizada

---

### 🔮 Sprint 3+ - Permisos Específicos Opcionales (Futuro)

**Duración:** 4+ semanas  
**Estado:** 💡 **Idea** (Abril+ 2026)

**Objetivo:** Integración con PermissionVoter existente para casos edge.

**Casos de uso:**
- Usuario puede editar solo SUS propios registros
- Permiso específico a nivel de registro individual
- Bloqueo temporal de ciertas entidades
- Permisos delegados temporalmente

**Entregables potenciales:**
- Integración con PermissionVoter
- UI de administración de permisos granulares
- Logs de auditoría de accesos
- Dashboard de permisos activos
- Sistema de aprobación de cambios críticos

---

## 📚 Ejemplos de Uso

### Ejemplo 1: Jefe de Finanzas

**Rol asignado:** `ROLE_MAINTAINER_MANAGER` + `ROLE_FINANCE_MANAGER` (Fase 2)

**Puede hacer:**
- ✅ Gestionar centros de costo
- ✅ Crear/editar tipos de documento (DTE)
- ✅ Administrar bancos y cuentas bancarias
- ✅ Exportar datos financieros a CSV

**No puede hacer:**
- ❌ Gestionar datos clínicos (enfermedades, procedimientos)
- ❌ Gestionar usuarios del sistema
- ❌ Modificar permisos de otros usuarios

---

### Ejemplo 2: Doctor con acceso limitado

**Rol asignado:** `ROLE_MAINTAINER_USER`

**Puede hacer:**
- ✅ Ver listado de enfermedades (diagnósticos)
- ✅ Ver catálogo de procedimientos médicos
- ✅ Consultar tipos de exámenes

**No puede hacer:**
- ❌ Crear nuevas enfermedades
- ❌ Editar códigos de diagnósticos
- ❌ Eliminar procedimientos médicos
- ❌ Exportar datos

---

### Ejemplo 3: Desarrollador en ambiente de pruebas

**Rol asignado:** `ROLE_ADMIN`

**Puede hacer:**
- ✅ Acceso completo a todos los mantenedores
- ✅ Crear datos de prueba
- ✅ Limpiar y resetear datos
- ✅ Exportar configuraciones
- ✅ Probar escenarios edge

---

## 🛡️ Consideraciones de Seguridad

### Principios Aplicados

1. **Secure by Default**
   - Todo denegado por defecto
   - Permisos explícitos requeridos
   
2. **Defense in Depth**
   - Capa 1: UI oculta botones
   - Capa 2: Controller verifica permisos
   - Capa 3: Voter evalúa acceso

3. **Least Privilege**
   - Usuarios reciben mínimo necesario
   - Escalación explícita de privilegios

4. **Auditabilidad**
   - Logs de seguridad configurables
   - Trazabilidad de cambios

---

## 🔍 Troubleshooting

### Problema: Usuario con ROLE_ADMIN no tiene acceso

**Causa:** Rol mal configurado en base de datos.

**Solución:**
```sql
SELECT id, email, roles FROM member WHERE email = 'admin@example.com';
-- Si roles está NULL o vacío:
UPDATE member SET roles = '["ROLE_USER", "ROLE_ADMIN"]' WHERE email = 'admin@example.com';
```

---

### Problema: Botones no se ocultan en UI

**Causa:** Template no incluye la variable `entity_class`.

**Solución:** Verificar que el controlador pase `entity_class` al renderizar:
```php
return $this->render($this->getTemplatePath(), [
    'entity_class' => $this->getEntityClass(), // ← Debe estar presente
    // ...
]);
```

---

### Problema: Tests fallan con "Class not found"

**Causa:** Entidad Gender no existe en el tenant configurado.

**Solución:** El test usa mocks, no necesita la entidad real. Verificar autoload:
```bash
composer dump-autoload
php bin/phpunit tests/Unit/Security/Voter/MaintainerVoterTest.php
```

---

## 📞 Contacto y Soporte

**Equipo de Desarrollo:** Melisa Development Team  
**Documentación creada:** Febrero 2026  
**Versión:** 1.0.0 - Sprint 1 MVP

**Para preguntas o mejoras:**
- Revisar issues en el repositorio
- Consultar con el arquitecto del proyecto
- Verificar tests existentes como referencia

---

## 📝 Changelog

### [2.0.0] - 2026-02-09 - Sprint 2 CRUD UI
- ✅ Tabla `role` para gestión dinámica de roles
- ✅ MaintainerRolePermissionController con CRUD completo
- ✅ MaintainerRolePermissionType con EntityType para roles
- ✅ Templates para administración desde UI
- ✅ Invalidación automática de caché en operaciones
- ✅ Menú en Configuración (solo ROLE_ADMIN)
- ✅ Tests funcionales básicos
- ✅ Documentación actualizada con guía de uso UI

### [1.0.0] - 2026-02-09 - Sprint 1 MVP
- ✅ Implementación inicial de MaintainerVoter
- ✅ Integración con AbstractMantenedorController
- ✅ Protección de UI en templates
- ✅ Suite de 27 tests unitarios
- ✅ Documentación completa de Sprint 1 MVP

---

**🎉 Sprint 2 - CRUD UI Completado con Éxito**

Los administradores ahora pueden gestionar permisos de mantenedores dinámicamente desde la interfaz web, sin necesidad de modificar código o ejecutar scripts SQL. Los cambios se aplican instantáneamente gracias al sistema de caché optimizado.
