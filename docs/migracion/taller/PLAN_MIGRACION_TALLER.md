# Plan de Migracion - Categoria Taller

## Resumen Ejecutivo

**Categoria:** MantenedorTaller
**Origen Legacy:** `melisa_prod/src/Rebsol/MantenedoresBundle/Controller/_Default/MantenedorMaestro/MantenedorEmpresa/MantenedorTaller/`
**Destino Nuevo:** `melisa_tenant/src/Controller/Maintainers/Workshop/`
**Total Entidades:** 1 mantenedor
**Complejidad Global:** Baja

---

## Inventario Completo

### Entidades a Migrar

| # | Legacy (ES) | Nuevo (EN) | Tabla Nueva | Complejidad | Fase |
|---|-------------|------------|-------------|-------------|------|
| 1 | Taller | Workshop | workshop | Simple | 1 |

### Dependencias Externas

| Entidad | Ubicacion | Estado |
|---------|-----------|--------|
| *(ninguna)* | - | - |

### Entidades Descartadas

| Legacy | Razon |
|--------|-------|
| *(ninguna)* | La unica entidad del inventario es migrable |

---

## Patron a Seguir (Referencia)

Todos los archivos nuevos deben seguir EXACTAMENTE el patron existente:

| Tipo | Ejemplo Referencia | Ubicacion |
|------|-------------------|-----------|
| Entity | `src/Entity/Tenant/Gender.php` | `src/Entity/Tenant/` |
| Repository | `src/Repository/Tenant/GenderRepository.php` | `src/Repository/Tenant/` |
| FormType | `src/Form/Maintainers/Personal/GenderType.php` | `src/Form/Maintainers/Workshop/` |
| Controller | `src/Controller/Maintainers/Basic/GenderController.php` | `src/Controller/Maintainers/Workshop/` |
| Template | `templates/maintainers/basic/gender/index.html.twig` | `templates/maintainers/workshop/` |

### Herencia de Controllers

```
AbstractController
  -> AbstractTenantAwareController
    -> AbstractMantenedorController (Template Method Pattern)
      -> [Tu nuevo controller]
```

### Convenciones de Nombres

- **Rutas:** `app_maintainers_workshop_{entity_snake}_{action}`
- **Ejemplo:** `app_maintainers_workshop_workshop_index`
- **Acciones:** `index`, `create`, `edit`, `delete`, `export`

### Nuevo Formato: getColumns() Asociativo

**IMPORTANTE:** A partir de febrero 2026, todos los controllers usan formato asociativo para columnas:

```php
// Formato NUEVO (usar siempre)
protected function getColumns(): array {
    return [
        'name' => 'Nombre',
        'code' => 'Codigo',
        'isActive' => 'Estado'
    ];
}
```

---

## FASE 1: Entidad Simple

---

### 1.1 Workshop (Taller)

**Legacy:** `Taller.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea el archivo src/Entity/Tenant/Workshop.php siguiendo EXACTAMENTE el patron de src/Entity/Tenant/Gender.php.

Especificaciones:
- Tabla: workshop
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable

- Incluir getters/setters para todos los campos
- Constructor inicializa createdAt = new \DateTime()
- Namespace: App\Entity\Tenant
- Repository: WorkshopRepository

RESTRICCIONES:
- NO agregar campo idEmpresa (multi-tenancy por Hakam)
- NO agregar relaciones adicionales
- Usar PHP 8.2 attributes (#[ORM\...])
- Usar Assert constraints de Symfony
```

#### Prompt Copilot - Repository

```
Crea el archivo src/Repository/Tenant/WorkshopRepository.php siguiendo EXACTAMENTE el patron de src/Repository/Tenant/GenderRepository.php.

- Extends ServiceEntityRepository
- Entity class: Workshop
- Metodos:
  - findAllActive(): array - orderBy name ASC, where isActive=true
- Namespace: App\Repository\Tenant
```

#### Prompt Copilot - FormType

```
Crea el archivo src/Form/Maintainers/Workshop/WorkshopType.php siguiendo EXACTAMENTE el patron de src/Form/Maintainers/Personal/GenderType.php.

Campos del formulario:
- name: TextType, label='Nombre', required, placeholder='Ingrese nombre del taller', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: Workshop
Namespace: App\Form\Maintainers\Workshop
```

#### Prompt Copilot - Controller

```
Crea el archivo src/Controller/Maintainers/Workshop/WorkshopController.php siguiendo EXACTAMENTE el patron de src/Controller/Maintainers/Basic/GenderController.php.

Especificaciones:
- Route base: /maintainers/workshop/workshop
- Rutas:
  - GET '' -> index (app_maintainers_workshop_workshop_index)
  - GET/POST '/create' -> create (app_maintainers_workshop_workshop_create)
  - GET/POST '/{id}/edit' -> edit (app_maintainers_workshop_workshop_edit)
  - POST '/{id}/delete' -> delete (app_maintainers_workshop_workshop_delete)
  - GET '/export' -> export (app_maintainers_workshop_workshop_export)

- Inyectar: WorkshopRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('w')->orderBy('w.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): WorkshopType::class
- createNewEntity(): new Workshop()
- getTemplatePath(): 'maintainers/workshop/workshop/index.html.twig'
- getPageTitle(): 'create'=>'Crear Taller', 'edit'=>'Editar Taller', default=>'Talleres'

- Export columns: ['name', 'isActive']
- Export headers: ['Nombre', 'Activo']
- Export filename: 'talleres_'.date('Y-m-d').'.csv'

RESTRICCIONES:
- NO cambiar el patron de AbstractMantenedorController
- NO agregar logica de negocio extra
- Mantener multi-tenant (AbstractTenantAwareController lo maneja)
```

#### Prompt Copilot - Template

```
Crea el archivo templates/maintainers/workshop/workshop/index.html.twig siguiendo EXACTAMENTE el patron de templates/maintainers/basic/gender/index.html.twig.

Variables a configurar:
- page_title: 'Talleres'
- icon: 'bx-wrench'
- breadcrumb_section: 'Taller'
- description: 'Gestiona los talleres del sistema'
- create_route: 'app_maintainers_workshop_workshop_create'
- edit_route: 'app_maintainers_workshop_workshop_edit'
- delete_route: 'app_maintainers_workshop_workshop_delete'
- export_route: 'app_maintainers_workshop_workshop_export'

Extends: maintainers/modern_index.html.twig
```

---

## Migracion de Base de Datos

Despues de crear la entidad, ejecutar:

```bash
# Generar migracion para tenant
php bin/console tenant:migrations:diff <tenant_id>

# Revisar el archivo generado en migrations/Tenant/
# Verificar que las tablas son correctas

# Ejecutar migracion
php bin/console tenant:migrations:migrate <tenant_id>
```

---

## Orden de Ejecucion Recomendado

```
1. Fase 1: Workshop
2. Migracion BD
3. Registro en Menu (MenuItem)
4. Validacion Multi-Tenant
```

---

## Registro en Menu

Despues de crear el controller, registrar en el sistema de menus.

**Tabla:** `menu_items`
**IMPORTANTE:** La columna `id` NO es auto-increment. Hay que asignarlo manualmente.

### Paso 1: Obtener ID maximo actual y del padre

```sql
SELECT MAX(id) FROM menu_items;
-- Usar el siguiente numero como base

SELECT id FROM menu_items WHERE name = 'mantenedores';
-- Anotar como {MANTENEDORES_ID} (actualmente = 4)
```

### Paso 2: Insertar categoria + 1 mantenedor

Reemplazar los IDs segun corresponda. En este ejemplo:
- `{MANTENEDORES_ID}` = 4 (padre Mantenedores)
- ID base = siguiente disponible

```sql
-- Categoria Taller (hijo de Mantenedores)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES ({BASE_ID}, 'maintenance_workshop', 'Taller', NULL, 'bx bx-wrench', NULL, 4, 15, true, true, true, '["ROLE_USER"]', NOW());

-- 1 mantenedor (hijo de Taller, parent_id = {BASE_ID})
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES
({BASE_ID+1}, 'workshop', 'Talleres', 'app_maintainers_workshop_workshop_index', 'bx bx-wrench', 'maintenance_workshop', {BASE_ID}, 1, true, true, true, '["ROLE_USER"]', NOW());
```

### Paso 3: Limpiar cache de menu

```bash
php bin/console cache:clear
```

### Rollback (en caso de necesitar borrar)

```sql
DELETE FROM menu_items WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintenance_workshop');
DELETE FROM menu_items WHERE name = 'maintenance_workshop';
```

---

## Checklist de Validacion (DoD)

Por cada mantenedor migrado:

```
[ ] Entity creada con campos correctos
[ ] Repository creado con findAllActive()
[ ] FormType creado con campos correctos
[ ] Controller creado con 5 rutas (index, create, edit, delete, export)
[ ] Template creado extiende modern_index.html.twig
[ ] Migracion BD ejecutada sin errores
[ ] CRUD funciona: crear, listar, editar, eliminar
[ ] Modal abre y cierra correctamente
[ ] Paginacion funciona
[ ] Export CSV funciona
[ ] Multi-tenant: Tenant A no ve datos de Tenant B
[ ] Multi-tenant: Cross-tenant access da 404
[ ] Sin errores en consola del navegador
[ ] Registrado en menu
```

---

## Archivos Totales a Crear

| Tipo | Cantidad | Ubicacion |
|------|----------|-----------|
| Entities | 1 | src/Entity/Tenant/ |
| Repositories | 1 | src/Repository/Tenant/ |
| FormTypes | 1 | src/Form/Maintainers/Workshop/ |
| Controllers | 1 | src/Controller/Maintainers/Workshop/ |
| Templates | 1 | templates/maintainers/workshop/ |
| **TOTAL** | **5 archivos** | |

(1 mantenedor = 1 entidad x 5 archivos)
