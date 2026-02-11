# SPEC: Mantenedores Budget

**Categoría**: Budget  
**Total Mantenedores**: 3  
**Estado**: ✅ Implementado  
**Última actualización**: 2026-02-09

---

## 📐 Arquitectura

Todos los mantenedores de presupuesto extienden `AbstractMantenedorController` que implementa:
- ✅ CRUD completo con Template Method Pattern
- ✅ Paginación automática vía QueryBuilder
- ✅ Turbo Frames para modales
- ✅ Multi-tenancy con TenantEntityManager
- ✅ Exportación CSV
- ✅ Relaciones con entidades relacionadas (JOINs)

**Ruta base**: `/maintainers/budget/{mantenedor}`

---

## 🗂️ Mantenedores Implementados

### 1. Budget Footer (Pies de Presupuesto)

**Controlador**: `App\Controller\Maintainers\Budget\BudgetFooterController`  
**Entidad**: `App\Entity\Tenant\BudgetFooter`  
**Form**: `App\Form\Maintainers\Budget\BudgetFooterType`  
**Template**: `templates/maintainers/budget/budget_footer/index.html.twig`

**Endpoints**:
- `GET /maintainers/budget/budget-footer` → `app_maintainers_budget_budget_footer_index`
- `GET /maintainers/budget/budget-footer/create` → `app_maintainers_budget_budget_footer_create`
- `GET /maintainers/budget/budget-footer/{id}/edit` → `app_maintainers_budget_budget_footer_edit`
- `POST /maintainers/budget/budget-footer/{id}/delete` → `app_maintainers_budget_budget_footer_delete`
- `GET /maintainers/budget/budget-footer/export` → `app_maintainers_budget_budget_footer_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV

**Export**:
- Columnas: `name`, `isActive`
- Headers: Traducciones de `name`, `is_active`
- Filename: `pies_presupuesto_YYYY-MM-DD.csv`

---

### 2. Budget Footer By Funder (Pies Presupuesto por Financiador)

**Controlador**: `App\Controller\Maintainers\Budget\BudgetFooterByFunderController`  
**Entidad**: `App\Entity\Tenant\BudgetFooterByFunder`  
**Form**: `App\Form\Maintainers\Budget\BudgetFooterByFunderType`  
**Template**: `templates/maintainers/budget/budget_footer_by_funder/index.html.twig`

**Endpoints**:
- `GET /maintainers/budget/budget-footer-by-funder` → `app_maintainers_budget_budget_footer_by_funder_index`
- `GET /maintainers/budget/budget-footer-by-funder/create` → `app_maintainers_budget_budget_footer_by_funder_create`
- `GET /maintainers/budget/budget-footer-by-funder/{id}/edit` → `app_maintainers_budget_budget_footer_by_funder_edit`
- `POST /maintainers/budget/budget-footer-by-funder/{id}/delete` → `app_maintainers_budget_budget_footer_by_funder_delete`
- `GET /maintainers/budget/budget-footer-by-funder/export` → `app_maintainers_budget_budget_footer_by_funder_export`

**Columnas**: name, budgetFooter.name, isActive  
**Relaciones**: 
- `ManyToOne` con `BudgetFooter` (LEFT JOIN con alias `bf`)

**Paginación**: ✅ QueryBuilder con JOIN (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV + Relaciones

**QueryBuilder**:
```php
$this->repository->createQueryBuilder('bfbf')
    ->leftJoin('bfbf.budgetFooter', 'bf')
    ->addSelect('bf')
    ->orderBy('bfbf.id', 'DESC');
```

**Export**:
- Columnas: `name`, `budgetFooter.name`, `isActive`
- Headers: Traducciones de `name`, `budget_footer`, `is_active`
- Filename: `pies_presupuesto_financiador_YYYY-MM-DD.csv`

---

### 3. Budget Funder Footer (Presupuesto Pie Financiador)

**Controlador**: `App\Controller\Maintainers\Budget\BudgetFunderFooterController`  
**Entidad**: `App\Entity\Tenant\BudgetFunderFooter`  
**Form**: `App\Form\Maintainers\Budget\BudgetFunderFooterType`  
**Template**: `templates/maintainers/budget/budget_funder_footer/index.html.twig`

**Endpoints**:
- `GET /maintainers/budget/budget-funder-footer` → `app_maintainers_budget_budget_funder_footer_index`
- `GET /maintainers/budget/budget-funder-footer/create` → `app_maintainers_budget_budget_funder_footer_create`
- `GET /maintainers/budget/budget-funder-footer/{id}/edit` → `app_maintainers_budget_budget_funder_footer_edit`
- `POST /maintainers/budget/budget-funder-footer/{id}/delete` → `app_maintainers_budget_budget_funder_footer_delete`
- `GET /maintainers/budget/budget-funder-footer/export` → `app_maintainers_budget_budget_funder_footer_export`

**Columnas**: name, budgetFooter.name, isActive  
**Relaciones**: 
- `ManyToOne` con `BudgetFooter` (LEFT JOIN con alias `bf`)

**Paginación**: ✅ QueryBuilder con JOIN (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV + Relaciones

**QueryBuilder**:
```php
$this->repository->createQueryBuilder('bff')
    ->leftJoin('bff.budgetFooter', 'bf')
    ->addSelect('bf')
    ->orderBy('bff.id', 'DESC');
```

**Export**:
- Columnas: `name`, `budgetFooter.name`, `isActive`
- Headers: Traducciones de `name`, `budget_footer`, `is_active`
- Filename: `presupuestos_pie_financiador_YYYY-MM-DD.csv`

---

## 🔄 Patrón de Implementación

### Mantenedor Simple (BudgetFooter)

```php
class BudgetFooterController extends AbstractMantenedorController
{
    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->budgetFooterRepository->createQueryBuilder('bf')
            ->orderBy('bf.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
            'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
            'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
        ];
    }
}
```

### Mantenedor con Relaciones

```php
class BudgetFooterByFunderController extends AbstractMantenedorController
{
    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->repository->createQueryBuilder('bfbf')
            ->leftJoin('bfbf.budgetFooter', 'bf')
            ->addSelect('bf')
            ->orderBy('bfbf.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
            'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
            'budgetFooter.name' => $this->translator->trans('maintainers.columns.budget_footer', [], 'maintainers'),
            'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
        ];
    }
}
```

---

## 📊 Resumen

| Mantenedor | Entidad | Columnas | Relaciones |
|------------|---------|----------|------------|
| Budget Footer | BudgetFooter | name, isActive | - |
| Budget Footer By Funder | BudgetFooterByFunder | name, budgetFooter, isActive | ManyToOne → BudgetFooter |
| Budget Funder Footer | BudgetFunderFooter | name, budgetFooter, isActive | ManyToOne → BudgetFooter |

**Características comunes**:
- ✅ Paginación automática
- ✅ Exportación CSV
- ✅ Turbo Frames
- ✅ Multi-tenancy
- ✅ Traducciones i18n
- ✅ Validación de formularios
- ✅ Relaciones con LEFT JOIN
- ✅ Soft deletes (isActive)

**Jerarquía de dependencias**:
```
BudgetFooter (entidad base)
    ├── BudgetFooterByFunder (depende de BudgetFooter)
    └── BudgetFunderFooter (depende de BudgetFooter)
```
