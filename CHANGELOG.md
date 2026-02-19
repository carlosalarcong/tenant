# Changelog

Todos los cambios notables de este proyecto serán documentados en este archivo.

El formato está basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.0.0/),
y este proyecto adhiere a [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.2.0] - 2026-02-19

### Agregado

#### Admisión Hospitalaria V1

- Asistente de admisión en 3 pasos con flujo completo de creación de ingreso.
- Vista previa de impresión en modal para mantener foco en la pantalla de trabajo.
- Nuevos catálogos y entidades para ciclo financiero de admisión:
  - `AccountStatus`
  - `PatientAccount`
  - `PaymentStatus`
  - `PaymentAccount`
- Endpoint API `GET /api/admission/tutor-search` para autocompletar tutor por documento.

### Modificado

- Formulario de admisión (paso 1) con rediseño visual profesional y layout responsive.
- Carga dinámica de selectores con Stimulus/Fetch para:
  - sucursal, profesional, especialidad, origen
  - tipo de atención, financiador, convenio
  - servicio, cama, plan previsional, paquete
- Persistencia extendida de datos clínicos/administrativos del paso 1 en `AdmissionRecord` y `Patient`.

### Corregido

- Bloqueo de re-admisión cuando la persona ya tiene un ingreso activo.
- Resolución y visualización consistente de estado de ingreso en búsqueda/listado.
- Preservación del contexto de búsqueda al volver desde “Detalle de Admisión”.
- Refactor de consultas desde servicios a repositorios en módulo de admisión.

## [2.1.0] - 2026-01-23

### Agregado

#### Sistema de Menú Dinámico Configurable (BD + Caché)

- **Nueva Entidad Tenant:**
  - `MenuItem`: Entidad jerárquica auto-referencial con soporte para estructura multi-nivel ilimitada
  - Campos: name, label, route, icon, module, parent, children, position, enabled, visibleInSidebar, requiresAuth, requiredRoles (JSON)
  - Relaciones: ManyToOne/OneToMany consigo misma para jerarquía, CASCADE delete en parent

- **MenuItemRepository:**
  - `getMenuStructure()`: Obtiene solo items raíz habilitados y visibles
  - `getMenuWithChildren()`: Eager loading de 3 niveles (m, c, cc) para optimizar queries
  - `getAllForAdmin()`: Obtiene todos los items incluyendo deshabilitados para interfaz administrativa
  - `getNextPosition()`: Calcula siguiente posición para ordenamiento
  - `reorderAfterDelete()`: Actualiza posiciones después de eliminar items

- **MenuDefinition Service (Refactorizado):**
  - Estrategia de 3 niveles: Cache → BD → Fallback hardcoded
  - `getMenuStructure($tenantId)`: Método principal con caché multi-tenant (TTL: 1 hora)
  - `invalidateCache($tenantId)`: Elimina caché después de modificaciones
  - `convertEntitiesToArray()`: Convierte entidades MenuItem a arrays compatibles
  - Cache keys por tenant: `menu_structure_tenant_{$tenantId}`
  - Logging opcional con nullsafe operator (`?->`)

- **MenuConfigController (Nuevo):**
  - CRUD completo para configuración de menús en `/admin/menu-config`
  - Acciones: index, new, edit, delete, toggle (enable/disable), clearCache
  - Invalidación automática de caché después de cada modificación
  - Procesamiento de formularios con mapeo de roles JSON

- **Plantillas Administrativas:**
  - `admin/menu_config/index.html.twig`: Vista de tabla con macro recursivo para jerarquía ilimitada
  - `admin/menu_config/new.html.twig`: Formulario completo con selector de parent, roles, y checkboxes
  - `admin/menu_config/edit.html.twig`: Reutiliza formulario de new.html.twig
  - Integración con Boxicons para selección de iconos

- **Migración Hakam:**
  - `Version20260123122407`: CREATE TABLE menu_items con FK auto-referencial
  - 22 items iniciales: 6 raíz, 3 subcategorías, 12 en mantenedores básico, 1 configuración
  - Estructura: Dashboard, Pacientes, Mantenedores (con subcategoría Básico), Configuración

- **Mejoras en Builders:**
  - `MenuBuilder`: Ahora obtiene tenantId de sesión y delega estructura a MenuDefinition
  - `PermissionAwareMenuBuilder` (renombrado): Agrega soporte multi-tenant
  - Ambos extraen tenant_id de RequestStack para cache por tenant

### Corregido

- **Error de Template en Sidebar:**
  - Línea 43 de `_sidebar.html.twig`: Agregado check `(item.route is defined and item.route)`
  - Previene RuntimeError al acceder a clave 'route' inexistente en items padre

- **Conflicto Turbo Drive en JavaScript:**
  - `app_layout.html.twig`: Encapsulado código sidebar en IIFE `(function() { ... })()`
  - Agregado `data-turbo-eval="false"` en etiqueta script
  - Agregado null checks: `if (!sidebar || !mainContent || !sidebarToggle) return;`
  - Previene error "Identifier 'sidebar' has already been declared" en navegación SPA

### Refactorizado

- **Renombrado NavbarBuilder → PermissionAwareMenuBuilder:**
  - Nombre más descriptivo que refleja su propósito real (filtrado por permisos)
  - Actualizado en `services.yaml` y `DefaultController`

- **Reorganización de Archivos:**
  - Movido `MenuBuilder.php` de `src/Service/` a `src/Service/Menu/`
  - Mayor cohesión: todos los servicios de menú en mismo directorio

- **MenuDefinition como Única Fuente de Verdad:**
  - Eliminada duplicación de estructura de 283 líneas entre builders
  - Ambos builders ahora consumen MenuDefinition
  - Estructura hardcoded se mantiene como fallback de seguridad

## [2.0.0] - 2026-01-21

### Agregado

#### Sistema de Permisos Híbrido Multi-Tenant

- **Nuevas Entidades:**
  - `TenantPermissionProfile`: Almacena el perfil de permisos del tenant (collaborative/restrictive/custom)
  - `TenantModulePermissionOverride`: Sobrescribe permisos a nivel de módulo con roles requeridos en JSON

- **Patrón Strategy para Permisos:**
  - `CollaborativePermissionStrategy`: Acceso multi-rol con permisos por defecto
  - `RestrictivePermissionStrategy`: Acceso solo para administradores
  - `CustomPermissionStrategy`: Permisos basados en base de datos con overrides
  - `PermissionStrategyFactory`: Factory para crear estrategias según perfil del tenant

- **NavbarBuilder Service:**
  - Sistema de construcción de menú de 3+ niveles jerárquicos
  - Filtrado dinámico basado en roles y permisos del tenant
  - Soporte para hasta 4 niveles de profundidad (Mantenedores → Básico → Antecedentes → Tipo)
  - 15 items en nivel Básico, estructura completa en Clínico y Geográficos
  - Integración con MenuItem value object para recursión

- **Sistema de Componentes Dashboard:**
  - `DashboardExtension`: Extensión Twig con función `include_tenant_component()`
  - Sistema de fallback automático: `tenants/{tenant}/{component}` → `components/{component}`
  - 6 componentes base reutilizables:
    * `_welcome_banner.html.twig`
    * `_stats_cards.html.twig`
    * `_appointments_table.html.twig`
    * `_quick_actions.html.twig`
    * `_daily_summary.html.twig`
    * `_dashboard_styles.html.twig`
  - 3 componentes personalizados para WiClinic:
    * `_welcome_banner.html.twig` (tema azul médico)
    * `_quick_actions.html.twig` (5 botones especializados)
    * `_appointments_table.html.twig` (estilos personalizados)
  - Dashboard modularizado de 470 líneas a 27 líneas

- **Navegación Mejorada:**
  - Navbar con dropdown de usuario (avatar, perfil, logout)
  - Sidebar con sistema recursivo de 3+ niveles usando macros Twig
  - Sistema de iconos por nivel con Font Awesome 6 y Boxicons
  - Colores distintivos por nivel: Azul (#5e72e4), Morado (#8b5cf6), Naranja (#f59e0b)
  - Tooltips CSS para textos largos con ellipsis
  - Animaciones suaves de collapse con Bootstrap 5

- **Herramientas CLI:**
  - `TenantPermissionProfileCommand`: Gestión de perfiles de permisos por CLI
  - Uso de DBAL para queries directas a base de datos de tenant
  - Comandos: `show-profile`, `set-profile {type}`

- **Documentación:**
  - `docs/SISTEMA_PERMISOS_MULTI_TENANT.md`: Documentación completa del sistema híbrido
  - Ejemplos de uso y configuración
  - Guía de estrategias y casos de uso

### Modificado

- **Dashboard Componentizado:**
  - Reducción de código de 470 líneas a 27 líneas en `dashboard/default.html.twig`
  - Componentes reutilizables sin necesidad de tablas en base de datos
  - Soporte para personalización por tenant sin modificar código base

- **Optimización Visual del Sidebar:**
  - Ancho optimizado de 260px → 350px para mejor legibilidad
  - Jerarquía visual ultra clara con sistema de colores por nivel
  - Tamaños de fuente graduales: 0.95rem → 0.87rem → 0.8rem → 0.75rem
  - Paddings incrementales: 1rem → 1.5rem → 2rem → 2.3rem
  - Bordes laterales distintivos: 5px → 4px → 3px según nivel
  - Sistema de ellipsis + tooltips para textos largos

- **Arquitectura de Permisos:**
  - Sistema de dos capas:
    * NavbarBuilder: Visibilidad de elementos en UI
    * PermissionVoter: Autorización a nivel de controlador (existente)
  - Separación de responsabilidades entre UI y lógica de negocio

### Corregido

- Rutas inexistentes configuradas como `null` con comentarios TODO:
  - `app_patients`
  - `app_appointments`
  - Rutas de mantenimiento sin implementar
- EntityManager apuntando a DB central en comandos (solucionado con DBAL)
- Problema de visibilidad en niveles 3 y 4 del sidebar (5 iteraciones de refinamiento)
- Truncamiento de texto en elementos de menú profundos (ellipsis + tooltips)

### Detalles Técnicos

- **43 archivos modificados**: +6,283 líneas, -874 líneas
- **Commits**: 14 commits en feature/dashboard
- **Merge commits**: 
  - b06b8e5 (develop)
  - 13993cf (master)
- **Migraciones**: `Version20260121124317.php` (tablas de permisos)
- **Branch eliminada**: feature/dashboard (local y remota)

## [1.1.0] - 2026-01-14

### Agregado

- Sistema de permisos a nivel de campo (FieldAccess)
- Voter personalizado (PermissionVoter) con lógica de cascada de permisos
- Extensión Twig para verificación de permisos en templates
- Tests unitarios: SecurityExtensionTest (9 tests) y FieldAccessTest (10 tests)
- Documentación completa de migraciones Hakam en `docs/MIGRACIONES_HAKAM.md`
- Cache in-memory para optimización de permisos
- Implementación de SecuredResourceInterface en entidades
- Controlador y vistas de testing para sistema de permisos

### Modificado

- Total de tests aumentado a 41 con 118 assertions

## [1.0.0] - 2026-01-14

### Agregado

- Script de deploy automatizado (`scripts/deploy.sh`) con 10 pasos
- Ejecución de tests unitarios en proceso de deploy
- Detección automática de entorno (dev/prod) para instalación de dependencias
- 12 tests unitarios para TenantResolver
- Documentación completa de Git Flow en `GIT_WORKFLOW.md`
- Documentación de proceso de migración en `SYMFONY_7.4_MIGRATION_PLAN.md`
- Sistema multi-tenancy con hakam/multi-tenancy-bundle v2.9.3
- Comando de prueba de multi-tenancy: `TestMultiTenancyCommand`
- Backups automáticos en cada deploy
- Configuración de CSRF y Property Info

### Modificado

- **BREAKING**: Migración de Symfony 6.4.29 a Symfony 7.4.3 LTS
- **BREAKING**: Requerimiento mínimo de PHP 8.2+
- Actualización de todas las dependencias de Symfony a versión 7.4.*
- Refactorización de entidades: `Pais` → `Country`, `Sexo` → `Gender`
- Actualización de repositorios para compatibilidad con Symfony 7.4
- Mejora en README.md con instrucciones actualizadas
- Optimización de composer.json eliminando scripts inexistentes

### Corregido

- Eliminación de animación particles.js que causaba error en página de login
- Corrección de comandos symfony-cmd inexistentes en composer auto-scripts
- Ajuste de clases CSS en template de login para evitar errores JavaScript
- Corrección de formato Markdown en toda la documentación

### Removed

- Entidades obsoletas: `Pais.php`, `Sexo.php`
- Repositorios obsoletos: `PaisRepository.php`, `SexoRepository.php`
- Scripts particles.js y particles.app.js del template de login
- Dependencias de desarrollo en builds de producción (--no-dev)

### Security

- Actualización a Symfony 7.4.3 LTS con soporte hasta 2029
- Mejoras de seguridad incluidas en nueva versión de framework

---

## [Unreleased]

### Planeado

- Deploy a servidor de staging
- Monitoreo de logs post-deploy
- Optimización de performance
- Documentación de API endpoints

---

**Formato de versiones:**

- **MAJOR** (X.0.0): Cambios incompatibles con versiones anteriores
- **MINOR** (0.X.0): Nueva funcionalidad compatible con versión anterior
- **PATCH** (0.0.X): Correcciones de bugs compatibles

[1.0.0]: https://github.com/carlosalarcong/melisa_tenant/releases/tag/v1.0.0
