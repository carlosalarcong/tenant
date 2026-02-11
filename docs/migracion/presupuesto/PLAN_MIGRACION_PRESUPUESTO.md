# Plan de Migracion - Categoria Presupuesto

## Resumen Ejecutivo

**Categoria:** MantenedorPresupuesto
**Origen Legacy:** `melisa_prod/src/Rebsol/MantenedoresBundle/Controller/_Default/MantenedorMaestro/MantenedorEmpresa/MantenedorPresupuesto/`
**Destino Nuevo:** `melisa_tenant/src/Controller/Maintainers/Budget/`
**Total Entidades:** 3 mantenedores
**Complejidad Global:** Media

---

## Inventario Completo

### Entidades a Migrar

| # | Legacy (ES) | Nuevo (EN) | Tabla Nueva | Complejidad | Fase |
|---|-------------|------------|-------------|-------------|------|
| 1 | PiePresupuesto | BudgetFooter | budget_footer | Simple | 1 |
| 2 | PiePresupuestoPorFinanciador | BudgetFooterByFunder | budget_footer_by_funder | Moderada | 2 |
| 3 | PresupuestoPieFinanciador | BudgetFunderFooter | budget_funder_footer | Moderada | 2 |

### Dependencias Externas

| Entidad | Ubicacion | Estado |
|---------|-----------|--------|
| BudgetItem | src/Entity/Tenant/BudgetItem.php | YA EXISTE |

### Dependencias Internas

| Entidad | Depende De | Fase |
|---------|------------|------|
| BudgetFooterByFunder | BudgetFooter | 2 (crear BudgetFooter antes) |
| BudgetFunderFooter | BudgetFooter | 2 (crear BudgetFooter antes) |

### Entidades Descartadas

| Legacy | Razon |
|--------|-------|
| *(ninguna)* | Todas las 3 entidades del inventario son migrables |

---

## Patron a Seguir (Referencia)

Todos los archivos nuevos deben seguir EXACTAMENTE el patron existente:

| Tipo | Ejemplo Referencia | Ubicacion |
|------|-------------------|-----------|
| Entity | `src/Entity/Tenant/Gender.php` | `src/Entity/Tenant/` |
| Repository | `src/Repository/Tenant/GenderRepository.php` | `src/Repository/Tenant/` |
| FormType | `src/Form/Maintainers/Personal/GenderType.php` | `src/Form/Maintainers/Budget/` |
| Controller | `src/Controller/Maintainers/Basic/GenderController.php` | `src/Controller/Maintainers/Budget/` |
| Template | `templates/maintainers/basic/gender/index.html.twig` | `templates/maintainers/budget/` |

### Herencia de Controllers

```
AbstractController
  -> AbstractTenantAwareController
    -> AbstractMantenedorController (Template Method Pattern)
      -> [Tu nuevo controller]
```

### Convenciones de Nombres

- **Rutas:** `app_maintainers_budget_{entity_snake}_{action}`
- **Ejemplo:** `app_maintainers_budget_budget_footer_index`
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

**Relaciones:**
```php
'budgetFooter.name' => 'Pie Presupuesto',   // Relacion ManyToOne
```

---

## FASE 1: Entidad Simple

---

### 1.1 BudgetFooter (PiePresupuesto)

**Legacy:** `PiePresupuesto.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea el archivo src/Entity/Tenant/BudgetFooter.php siguiendo EXACTAMENTE el patron de src/Entity/Tenant/Gender.php.

Especificaciones:
- Tabla: budget_footer
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
- Repository: BudgetFooterRepository

RESTRICCIONES:
- NO agregar campo idEmpresa (multi-tenancy por Hakam)
- NO agregar relaciones adicionales
- Usar PHP 8.2 attributes (#[ORM\...])
- Usar Assert constraints de Symfony
```

#### Prompt Copilot - Repository

```
Crea el archivo src/Repository/Tenant/BudgetFooterRepository.php siguiendo EXACTAMENTE el patron de src/Repository/Tenant/GenderRepository.php.

- Extends ServiceEntityRepository
- Entity class: BudgetFooter
- Metodos:
  - findAllActive(): array - orderBy name ASC, where isActive=true
- Namespace: App\Repository\Tenant
```

#### Prompt Copilot - FormType

```
Crea el archivo src/Form/Maintainers/Budget/BudgetFooterType.php siguiendo EXACTAMENTE el patron de src/Form/Maintainers/Personal/GenderType.php.

Campos del formulario:
- name: TextType, label='Nombre', required, placeholder='Ingrese nombre del pie de presupuesto', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: BudgetFooter
Namespace: App\Form\Maintainers\Budget
```

#### Prompt Copilot - Controller

```
Crea el archivo src/Controller/Maintainers/Budget/BudgetFooterController.php siguiendo EXACTAMENTE el patron de src/Controller/Maintainers/Basic/GenderController.php.

Especificaciones:
- Route base: /maintainers/budget/budget-footer
- Rutas:
  - GET '' -> index (app_maintainers_budget_budget_footer_index)
  - GET/POST '/create' -> create (app_maintainers_budget_budget_footer_create)
  - GET/POST '/{id}/edit' -> edit (app_maintainers_budget_budget_footer_edit)
  - POST '/{id}/delete' -> delete (app_maintainers_budget_budget_footer_delete)
  - GET '/export' -> export (app_maintainers_budget_budget_footer_export)

- Inyectar: BudgetFooterRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('bf')->orderBy('bf.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): BudgetFooterType::class
- createNewEntity(): new BudgetFooter()
- getTemplatePath(): 'maintainers/budget/budget_footer/index.html.twig'
- getPageTitle(): 'create'=>'Crear Pie Presupuesto', 'edit'=>'Editar Pie Presupuesto', default=>'Pies de Presupuesto'

- Export columns: ['name', 'isActive']
- Export headers: ['Nombre', 'Activo']
- Export filename: 'pies_presupuesto_'.date('Y-m-d').'.csv'

RESTRICCIONES:
- NO cambiar el patron de AbstractMantenedorController
- NO agregar logica de negocio extra
- Mantener multi-tenant (AbstractTenantAwareController lo maneja)
```

#### Prompt Copilot - Template

```
Crea el archivo templates/maintainers/budget/budget_footer/index.html.twig siguiendo EXACTAMENTE el patron de templates/maintainers/basic/gender/index.html.twig.

Variables a configurar:
- page_title: 'Pies de Presupuesto'
- icon: 'bx-note'
- breadcrumb_section: 'Presupuesto'
- description: 'Gestiona los pies de presupuesto del sistema'
- create_route: 'app_maintainers_budget_budget_footer_create'
- edit_route: 'app_maintainers_budget_budget_footer_edit'
- delete_route: 'app_maintainers_budget_budget_footer_delete'
- export_route: 'app_maintainers_budget_budget_footer_export'

Extends: maintainers/modern_index.html.twig
```

---

## FASE 2: Entidades con Relaciones

Entidades que tienen FK a BudgetFooter (creada en Fase 1).

### DEPENDENCIAS PREVIAS

Antes de esta fase, asegurar que exista:
- **BudgetFooter** -> Creado en Fase 1

---

### 2.1 BudgetFooterByFunder (PiePresupuestoPorFinanciador)

**Legacy:** `PiePresupuestoPorFinanciador.php` - Campos: id, nombre(255), idPiePresupuesto(FK->BudgetFooter), idEstado, idEmpresa

**DEPENDENCIA:** Crear BudgetFooter ANTES (Fase 1)

#### Prompt Copilot - Entity

```
Crea el archivo src/Entity/Tenant/BudgetFooterByFunder.php siguiendo el patron de Gender.php pero CON relacion ManyToOne.

Especificaciones:
- Tabla: budget_footer_by_funder
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - budgetFooter: ManyToOne -> BudgetFooter, nullable, JoinColumn(name='budget_footer_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable

- Namespace: App\Entity\Tenant
- Repository: BudgetFooterByFunderRepository

IMPORTANTE: La relacion ManyToOne a BudgetFooter debe usar:
#[ORM\ManyToOne(targetEntity: BudgetFooter::class)]
#[ORM\JoinColumn(name: 'budget_footer_id', nullable: true)]

RESTRICCIONES:
- NO agregar campo idEmpresa
- Usar PHP 8.2 attributes
```

#### Prompt Copilot - Repository

```
Crea el archivo src/Repository/Tenant/BudgetFooterByFunderRepository.php siguiendo el patron de GenderRepository.php.

- Entity: BudgetFooterByFunder
- Metodos:
  - findAllActive(): orderBy name ASC
  - findByBudgetFooter(int $budgetFooterId): array
- Namespace: App\Repository\Tenant
```

#### Prompt Copilot - FormType

```
Crea el archivo src/Form/Maintainers/Budget/BudgetFooterByFunderType.php.

Seguir el patron de MedicalServiceType.php para campos con EntityType (relaciones).

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese nombre', class='form-control'
- budgetFooter: EntityType, class=BudgetFooter::class, label='Pie Presupuesto',
  choice_label='name', placeholder='Seleccione pie presupuesto...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: BudgetFooterByFunder
Namespace: App\Form\Maintainers\Budget
```

#### Prompt Copilot - Controller

```
Crea el archivo src/Controller/Maintainers/Budget/BudgetFooterByFunderController.php siguiendo el patron de GenderController.php.

- Route base: /maintainers/budget/budget-footer-by-funder
- Prefijo rutas: app_maintainers_budget_budget_footer_by_funder_
- Inyectar: BudgetFooterByFunderRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('bfbf')
    ->leftJoin('bfbf.budgetFooter', 'bf')
    ->addSelect('bf')
    ->orderBy('bfbf.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'budgetFooter.name' => 'Pie Presupuesto', 'isActive' => 'Estado']
- getFormType(): BudgetFooterByFunderType::class
- createNewEntity(): new BudgetFooterByFunder()
- getTemplatePath(): 'maintainers/budget/budget_footer_by_funder/index.html.twig'
- getPageTitle(): default=>'Pies Presupuesto por Financiador'
- Export columns: ['name', 'budgetFooter.name', 'isActive'], headers: ['Nombre', 'Pie Presupuesto', 'Activo'], filename: 'pies_presupuesto_financiador_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/budget/budget_footer_by_funder/index.html.twig siguiendo el patron de gender/index.html.twig.

- page_title: 'Pies Presupuesto por Financiador'
- icon: 'bx-money'
- breadcrumb_section: 'Presupuesto'
- description: 'Gestiona los pies de presupuesto por financiador'
- Rutas: app_maintainers_budget_budget_footer_by_funder_{create,edit,delete,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 2.2 BudgetFunderFooter (PresupuestoPieFinanciador)

**Legacy:** `PresupuestoPieFinanciador.php` - Campos: id, nombre(255), idPiePresupuesto(FK->BudgetFooter), idEstado, idEmpresa

**DEPENDENCIA:** Crear BudgetFooter ANTES (Fase 1)

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/BudgetFunderFooter.php siguiendo el patron de Gender.php pero CON relacion ManyToOne.

- Tabla: budget_funder_footer
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - budgetFooter: ManyToOne -> BudgetFooter, nullable, JoinColumn(name='budget_footer_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: BudgetFunderFooterRepository
- Namespace: App\Entity\Tenant

IMPORTANTE: La relacion ManyToOne a BudgetFooter debe usar:
#[ORM\ManyToOne(targetEntity: BudgetFooter::class)]
#[ORM\JoinColumn(name: 'budget_footer_id', nullable: true)]
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/BudgetFunderFooterRepository.php siguiendo el patron de GenderRepository.

- Entity: BudgetFunderFooter
- Metodos:
  - findAllActive(): orderBy name ASC
  - findByBudgetFooter(int $budgetFooterId): array
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Budget/BudgetFunderFooterType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese nombre', class='form-control'
- budgetFooter: EntityType, class=BudgetFooter::class, label='Pie Presupuesto',
  choice_label='name', placeholder='Seleccione pie presupuesto...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: BudgetFunderFooter
Namespace: App\Form\Maintainers\Budget
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Budget/BudgetFunderFooterController.php siguiendo el patron de GenderController.

- Route base: /maintainers/budget/budget-funder-footer
- Prefijo rutas: app_maintainers_budget_budget_funder_footer_
- Inyectar: BudgetFunderFooterRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('bff')
    ->leftJoin('bff.budgetFooter', 'bf')
    ->addSelect('bf')
    ->orderBy('bff.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'budgetFooter.name' => 'Pie Presupuesto', 'isActive' => 'Estado']
- getFormType(): BudgetFunderFooterType::class
- createNewEntity(): new BudgetFunderFooter()
- getTemplatePath(): 'maintainers/budget/budget_funder_footer/index.html.twig'
- getPageTitle(): default=>'Presupuestos Pie Financiador'
- Export columns: ['name', 'budgetFooter.name', 'isActive'], headers: ['Nombre', 'Pie Presupuesto', 'Activo'], filename: 'presupuestos_pie_financiador_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/budget/budget_funder_footer/index.html.twig.

- page_title: 'Presupuestos Pie Financiador'
- icon: 'bx-dollar-circle'
- breadcrumb_section: 'Presupuesto'
- description: 'Gestiona los presupuestos pie de financiador'
- Rutas: app_maintainers_budget_budget_funder_footer_{create,edit,delete,export}
```

---

## Migracion de Base de Datos

Despues de crear todas las entidades, ejecutar:

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
1. Fase 1: BudgetFooter
2. Fase 2: BudgetFooterByFunder, BudgetFunderFooter
3. Migracion BD
4. Registro en Menu (MenuItem)
5. Validacion Multi-Tenant
```

---

## Registro en Menu

Despues de crear todos los controllers, registrar en el sistema de menus.

**Tabla:** `menu_items`
**IMPORTANTE:** La columna `id` NO es auto-increment. Hay que asignarlo manualmente.

### Paso 1: Obtener ID maximo actual y del padre

```sql
SELECT MAX(id) FROM menu_items;
-- Usar el siguiente numero como base

SELECT id FROM menu_items WHERE name = 'mantenedores';
-- Anotar como {MANTENEDORES_ID} (actualmente = 4)
```

### Paso 2: Insertar categoria + 3 mantenedores

Reemplazar los IDs segun corresponda. En este ejemplo:
- `{MANTENEDORES_ID}` = 4 (padre Mantenedores)
- ID base = siguiente disponible

```sql
-- Categoria Presupuesto (hijo de Mantenedores)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES ({BASE_ID}, 'maintenance_budget', 'Presupuesto', NULL, 'bx bx-bar-chart-alt-2', NULL, 4, 14, true, true, true, '["ROLE_USER"]', NOW());

-- 3 mantenedores (hijos de Presupuesto, parent_id = {BASE_ID})
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES
({BASE_ID+1}, 'budget_footer', 'Pies de Presupuesto', 'app_maintainers_budget_budget_footer_index', 'bx bx-note', 'maintenance_budget', {BASE_ID}, 1, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+2}, 'budget_footer_by_funder', 'Pies Presupuesto por Financiador', 'app_maintainers_budget_budget_footer_by_funder_index', 'bx bx-money', 'maintenance_budget', {BASE_ID}, 2, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+3}, 'budget_funder_footer', 'Presupuestos Pie Financiador', 'app_maintainers_budget_budget_funder_footer_index', 'bx bx-dollar-circle', 'maintenance_budget', {BASE_ID}, 3, true, true, true, '["ROLE_USER"]', NOW());
```

### Paso 3: Limpiar cache de menu

```bash
php bin/console cache:clear
```

### Rollback (en caso de necesitar borrar)

```sql
DELETE FROM menu_items WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintenance_budget');
DELETE FROM menu_items WHERE name = 'maintenance_budget';
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
| Entities | 3 | src/Entity/Tenant/ |
| Repositories | 3 | src/Repository/Tenant/ |
| FormTypes | 3 | src/Form/Maintainers/Budget/ |
| Controllers | 3 | src/Controller/Maintainers/Budget/ |
| Templates | 3 | templates/maintainers/budget/ |
| **TOTAL** | **15 archivos** | |

(3 mantenedores = 3 entidades x 5 archivos cada uno)
