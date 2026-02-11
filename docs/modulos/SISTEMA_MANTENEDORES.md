# 📋 Sistema de Mantenedores

Sistema completo y estandarizado para gestión de mantenedores (datos maestros) con arquitectura moderna basada en Patrón Template Method.

---

## 📐 Arquitectura General

```
┌──────────────────────────────────────────────────────────────┐
│                  ABSTRACTMANTENEDORCONTROLLER                 │
│                   (Template Method Pattern)                   │
│  ┌────────────────────────────────────────────────────────┐  │
│  │ • handleIndex() → Paginación automática con QueryBuilder│  │
│  │ • handleCreate() → Modal con Turbo Frame                │  │
│  │ • handleEdit() → Modal con Turbo Frame                  │  │
│  │ • handleDelete() → Confirmación y eliminación           │  │
│  │ • paginate() → Doctrine Paginator                       │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
                              │
                              │ extends
                              ▼
    ┌─────────────────────────────────────────────────────┐
    │        CONTROLLERS DE MANTENEDORES BÁSICOS          │
    │  • GenderController                                 │
    │  • ReligionController                               │
    │  • DoctorTypeController (20 registros)              │
    │  • MaritalStatusController                          │
    │  • EducationLevelController                         │
    │  • + 8 mantenedores más                             │
    │                                                     │
    │  Cada uno solo implementa:                          │
    │  ✓ getData(): array|QueryBuilder                   │
    │  ✓ getColumns(): array                             │
    │  ✓ getTemplatePath(): string                       │
    │  ✓ getFormType(): string                           │
    │  ✓ createNewEntity(): object                       │
    └─────────────────────────────────────────────────────┘
```

---

## 🎯 Componentes del Sistema

### 1. AbstractMantenedorController
**Ubicación**: `src/Controller/AbstractMantenedorController.php`

Controlador base que implementa el flujo CRUD completo con:
- ✅ Paginación automática con auto-detección
- ✅ Integración con Turbo Frames para modales
- ✅ Multi-tenancy con TenantEntityManager
- ✅ Hooks para personalización

**Métodos Principales**:

```php
protected function handleIndex(Request $request): Response
{
    $dataOrQuery = $this->getData($request);
    
    // 🎯 AUTO-DETECCIÓN: QueryBuilder → Paginar | Array → Sin paginar
    if ($dataOrQuery instanceof QueryBuilder) {
        $pagination = $this->paginate($dataOrQuery, $request);
        $data = $pagination['items'];
        $paginationData = [...];
    } else {
        $data = $dataOrQuery;
        $paginationData = null;
    }
    
    return $this->render($this->getTemplatePath(), [
        'data' => $data,
        'pagination' => $paginationData, // ← Para el componente
    ]);
}
```

---

## 🔄 Sistema de Paginación

### Auto-detección con instanceof

El sistema detecta automáticamente si aplicar paginación basándose en el tipo de retorno de `getData()`:

```php
// ✅ CON PAGINACIÓN (retorna QueryBuilder)
protected function getData(Request $request): array|QueryBuilder
{
    return $this->repository->createQueryBuilder('dt')
        ->orderBy('dt.id', 'DESC');
}

// ❌ SIN PAGINACIÓN (retorna Array)
protected function getData(Request $request): array|QueryBuilder
{
    return $this->repository->findAll(); // Array simple
}
```

### Flujo de Paginación

```
Usuario: GET /doctor-type?page=2
    ↓
DoctorTypeController::index($request)
    ↓ delega
AbstractMantenedorController::handleIndex($request)
    ↓ llama
DoctorTypeController::getData($request)
    ↓ retorna QueryBuilder
$repository->createQueryBuilder('dt')->orderBy('dt.id', 'DESC')
    ↓ instanceof detecta QueryBuilder
AbstractMantenedorController::paginate($queryBuilder, $request)
    ↓ modifica query
$queryBuilder->setFirstResult(10)->setMaxResults(10)
    ↓ ejecuta
new Paginator($queryBuilder)
    ↓ SQL generado
SELECT * FROM maintainer_doctor_type 
ORDER BY id DESC 
LIMIT 10 OFFSET 10
    ↓ retorna
[
    'items' => [obj1, obj2, ...],
    'current_page' => 2,
    'total_pages' => 2,
    'total_items' => 20,
    'has_previous' => true,
    'has_next' => false
]
```

### Método paginate()

```php
protected function paginate(QueryBuilder $queryBuilder, Request $request): array
{
    $page = max(1, (int) $request->query->get('page', 1));
    $itemsPerPage = $this->getItemsPerPage(); // Default: 10
    
    $queryBuilder
        ->setFirstResult(($page - 1) * $itemsPerPage)
        ->setMaxResults($itemsPerPage);
    
    $paginator = new Paginator($queryBuilder, fetchJoinCollection: true);
    $totalItems = count($paginator);
    $totalPages = max(1, (int) ceil($totalItems / $itemsPerPage));
    
    return [
        'items' => iterator_to_array($paginator),
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_items' => $totalItems,
        'items_per_page' => $itemsPerPage,
        'has_previous' => $page > 1,
        'has_next' => $page < $totalPages,
    ];
}

// Personalizable por controller
protected function getItemsPerPage(): int
{
    return 10; // Puede ser sobrescrito
}
```

---

## 🎨 Componente de Paginación

**Ubicación**: `templates/components/_pagination.html.twig`

Componente visual reutilizable con:
- ✅ Navegación completa: « ‹ [1] [2] [3] › »
- ✅ Integración Turbo Frame (sin page refresh)
- ✅ Info contextual: "Mostrando 1-10 de 45 registros"
- ✅ Diseño Bootstrap 5 moderno
- ✅ Lógica inteligente para "..."

### Uso en Templates

```twig
{# modern_index.html.twig #}
<div class="card-footer">
    {% if pagination %}
        {% include 'components/_pagination.html.twig' with {
            pagination: pagination,
            route_name: app.request.attributes.get('_route'),
            turbo_frame: 'maintainer-content'
        } %}
    {% else %}
        <p>Mostrando {{ data|length }} registros</p>
    {% endif %}
</div>
```

### Variables del Componente

```twig
{# Requeridas #}
- pagination: objeto con current_page, total_pages, has_previous, has_next, total_items
- route_name: nombre de la ruta para generar URLs

{# Opcionales #}
- route_params: parámetros adicionales (default: {})
- turbo_frame: nombre del frame (default: 'maintainer-content')
- show_info: mostrar "Mostrando X de Y" (default: true)
- max_links: máximo de páginas visibles (default: 5)
```

---

## 🎭 Sistema de Modales

### Integración con Turbo Frames

Los formularios de crear/editar se cargan en modales sin page refresh:

```twig
{# Botón que abre modal #}
<button type="button"
        class="btn btn-primary"
        data-bs-toggle="modal"
        data-bs-target="#maintainerModal"
        data-modal-url="{{ path('app_maintainers_gender_create') }}"
        data-modal-title="<i class='bx bx-plus'></i>Nuevo Género">
    Nuevo Registro
</button>

{# Modal base universal #}
{% include 'components/_modal_base.html.twig' with {
    'modal_id': 'maintainerModal',
    'frame_id': 'maintainer-form',
    'modal_size': 'lg'
} %}
```

### Flujo del Modal

```
1. Usuario hace clic en "Nuevo Registro"
2. Stimulus ModalController intercepta el clic
3. Actualiza el título del modal
4. Carga el formulario vía Turbo Frame
5. Modal se abre automáticamente
6. Usuario llena el form y envía
7. Turbo Frame detecta redirect
8. Modal se cierra y tabla se actualiza
```

Ver documentación completa en: `docs/MODAL_SYSTEM.md`

---

## 📝 Mantenedores Implementados

### ✅ 14 Mantenedores Básicos (Todos con Paginación)

| Controller | Entidad | Items por página | Alias QueryBuilder |
|-----------|---------|------------------|-------------------|
| DoctorTypeController | DoctorType | 10 | `dt` |
| EducationLevelController | EducationLevel | 10 | `el` |
| EducationLevelDetailController | EducationLevelDetail | 10 | `eld` |
| EthnicGroupController | EthnicGroup | 10 | `eg` |
| GenderController | Gender | 10 | `g` |
| InsuranceAdministratorController | InsuranceAdministrator | 10 | `ia` |
| JobPositionController | JobPosition | 10 | `jp` |
| LocationController | Location | 10 | `l` |
| MaritalStatusController | MaritalStatus | 10 | `ms` |
| MedicalBoxController | MedicalBox | 10 | `mb` |
| OccupationController | Occupation | 10 | `o` |
| OriginController | Origin | 10 | `ori` |
| OriginTypeController | OriginType | 10 | `ot` |
| ReligionController | Religion | 10 | `r` |

**Todos retornan QueryBuilder** → Paginación automática activada ✅

---

## 🛠️ Crear Nuevo Mantenedor

### Paso 1: Crear Controller

```php
<?php

namespace App\Controller\Maintainers\Basic;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\MiEntidad;
use App\Form\Maintainers\MiEntidadType;
use App\Repository\Tenant\MiEntidadRepository;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/maintainers/basic/mi-entidad')]
class MiEntidadController extends AbstractMantenedorController
{
    public function __construct(
        private MiEntidadRepository $repository,
        TenantEntityManager $tenantEntityManager
    ) {
        parent::__construct($tenantEntityManager);
    }

    // Rutas CRUD estándar
    #[Route('', name: 'app_maintainers_mi_entidad_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_mi_entidad_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_mi_entidad_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_mi_entidad_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }

    // Métodos abstractos requeridos
    protected function getData(Request $request): array|QueryBuilder
    {
        // Con paginación
        return $this->repository->createQueryBuilder('me')
            ->orderBy('me.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return ['name', 'code', 'isActive'];
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/basic/mi_entidad/index.html.twig';
    }

    protected function getFormType(): string
    {
        return MiEntidadType::class;
    }

    protected function createNewEntity(): object
    {
        return new MiEntidad();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_mi_entidad_index';
    }

    protected function getPageTitle(string $action = 'index'): string
    {
        return match($action) {
            'create' => 'Crear Mi Entidad',
            'edit' => 'Editar Mi Entidad',
            default => 'Mi Entidad'
        };
    }

    // Opcional: Sobrescribir items por página
    protected function getItemsPerPage(): int
    {
        return 15; // Default es 10
    }
}
```

### Paso 2: Crear Template Index

```twig
{# templates/maintainers/basic/mi_entidad/index.html.twig #}
{% extends 'maintainers/modern_index.html.twig' %}

{% block title %}Mi Entidad - Melisa{% endblock %}

{% set page_title = 'Mi Entidad' %}
{% set icon = 'bx-star' %}
{% set breadcrumb_section = 'Básico' %}
{% set description = 'Gestiona las entidades del sistema' %}
{% set create_route = 'app_maintainers_mi_entidad_create' %}
{% set edit_route = 'app_maintainers_mi_entidad_edit' %}
{% set delete_route = 'app_maintainers_mi_entidad_delete' %}
```

¡Eso es todo! 🎉 El sistema se encarga de:
- ✅ Listado con paginación automática
- ✅ Crear en modal
- ✅ Editar en modal
- ✅ Eliminar con confirmación
- ✅ Flash messages
- ✅ Validación de formularios
- ✅ Multi-tenancy

---

## 🎨 Templates

### modern_index.html.twig
**Ubicación**: `templates/maintainers/modern_index.html.twig`

Template base para índices de mantenedores con:
- ✅ Diseño moderno con stats cards
- ✅ Búsqueda y filtros (UI preparada)
- ✅ Tabla responsive con acciones
- ✅ Paginación integrada
- ✅ Modal universal incluido

### _modal_form.html.twig
**Ubicación**: `templates/maintainers/_modal_form.html.twig`

Formulario genérico para modales:
```twig
<turbo-frame id="maintainer-form">
    <form method="post" action="{{ action_url }}" data-turbo-frame="_top">
        {{ form_start(form, {'attr': {'data-turbo-frame': '_top'}}) }}
        
        <div class="modal-body">
            {{ form_widget(form) }}
        </div>
        
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                Cancelar
            </button>
            <button type="submit" class="btn btn-primary">
                Guardar
            </button>
        </div>
        
        {{ form_end(form) }}
    </form>
</turbo-frame>
```

---

## 🔧 Hooks y Personalización

### Hooks Disponibles

```php
// Antes/después de acciones
protected function beforeIndex(Request $request): void {}
protected function afterIndex(Request $request): void {}
protected function beforeCreate(Request $request): void {}
protected function beforeEdit(object $entity, Request $request): void {}
protected function beforeSave(object $entity, Request $request): void {}
protected function afterSave(object $entity, Request $request): void {}
protected function beforeDelete(object $entity, Request $request): void {}
protected function afterDelete(object $entity, Request $request): void {}
```

### Ejemplo de Uso

```php
protected function beforeSave(object $entity, Request $request): void
{
    // Asignar tenant automáticamente
    $entity->setTenant($this->getTenant());
    
    // Auditoria
    $entity->setUpdatedBy($this->getUser());
}

protected function canDelete(object $entity): bool
{
    // Validar si tiene relaciones
    return $entity->getRelatedRecords()->isEmpty();
}
```

---

## 📊 Estadísticas del Sistema

```
✅ 14 Mantenedores implementados
✅ 100% con paginación automática
✅ 10 items por página (configurable)
✅ ~30% menos código duplicado
✅ Modal system con Turbo Frame
✅ Multi-tenancy integrado
✅ Template Method Pattern
```

---

## 🚀 Ventajas del Sistema

### 1. Legibilidad
- `instanceof QueryBuilder` auto-explica el comportamiento
- Sin flags booleanos ocultos
- Código autodocumentado

### 2. Moderno
- Union Types PHP 8.3: `array|QueryBuilder`
- Doctrine Paginator con lazy loading
- Turbo Frames para SPA-like UX

### 3. Performance
- `instanceof` es operación nativa O(1)
- Paginación lazy solo cuando necesario
- QueryBuilder optimizado por Doctrine

### 4. Flexible
- Cada controller decide si paginar o no
- Items por página configurable
- Hooks para customización

### 5. Mantenible
- Lógica centralizada en clase base
- Cambio en un lugar → Aplica a todos
- Sin refactor: Controllers legacy siguen funcionando

---

## 📚 Documentación Relacionada

- **Sistema de Modales**: `docs/MODAL_SYSTEM.md`
- **Multi-Tenancy**: `docs/TENANT_SYSTEM.md`
- **Stimulus Controllers**: `docs/STIMULUS_GUIDE.md`
- **Arquitectura General**: `docs/ARQUITECTURA_MELISA_TENANT.md`

---

## 🐛 Troubleshooting

### Error: "syntax error, unexpected token '|'"
**Causa**: VSCode/Intelephense configurado para PHP < 8.0  
**Solución**: Ignorar (falso positivo). Validar con `php -l archivo.php`

### Paginación no aparece
**Causa**: `getData()` retorna array en lugar de QueryBuilder  
**Solución**: Cambiar a `return $this->repository->createQueryBuilder('alias')`

### Modal no se cierra después de guardar
**Causa**: Falta `data-turbo-frame="_top"` en el form  
**Solución**: Verificar `_modal_form.html.twig` tiene el atributo

### Items por página incorrectos
**Causa**: QueryBuilder ejecutándose antes de paginate()  
**Solución**: NO usar `->getQuery()->getResult()`, retornar el QueryBuilder sin ejecutar

---

**Última actualización**: Enero 2026  
**Versión**: 2.0 (Con Paginación Automática)
