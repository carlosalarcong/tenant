# SPEC: Mantenedores de Liquidaciones (Settlements)

**Categoría**: Settlements  
**Total Mantenedores**: 5  
**Estado**: ✅ Implementado  
**Última actualización**: 2026-02-09

---

## 📐 Arquitectura

Todos los mantenedores de liquidaciones extienden `AbstractMantenedorController` que implementa:
- ✅ CRUD completo con Template Method Pattern
- ✅ Paginación automática vía QueryBuilder
- ✅ Turbo Frames para modales
- ✅ Multi-tenancy con TenantEntityManager
- ✅ Exportación CSV
- ✅ Hook `beforeSave()` para actualizar timestamps

**Ruta base**: `/maintainers/settlements/{mantenedor}`

---

## 🗂️ Mantenedores Implementados

### 1. Bank Account (Cuentas Bancarias)

**Controlador**: `App\Controller\Maintainers\Settlements\BankAccountController`  
**Entidad**: `App\Entity\Tenant\BankAccount`  
**Form**: `App\Form\Maintainers\Settlements\BankAccountFormType`  
**Template**: `templates/maintainers/settlements/bank_account/index.html.twig`

**Endpoints**:
- `GET /maintainers/settlements/bank-account` → `app_maintainers_settlements_bank_account_index`
- `GET /maintainers/settlements/bank-account/create` → `app_maintainers_settlements_bank_account_create`
- `GET /maintainers/settlements/bank-account/{id}/edit` → `app_maintainers_settlements_bank_account_edit`
- `POST /maintainers/settlements/bank-account/{id}/delete` → `app_maintainers_settlements_bank_account_delete`
- `GET /maintainers/settlements/bank-account/export` → `app_maintainers_settlements_bank_account_export`

**Columnas**: 
- accountNumber (Numero de Cuenta)
- bank.name (Banco)
- bankAccountType.name (Tipo de Cuenta)
- isActive (Estado)

**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Export**: `cuentas_bancarias_YYYY-MM-DD.csv`

**Form Fields**:
```php
- accountNumber: TextType (required, max 255)
- bank: EntityType(Bank) (optional, select con filtro activos)
- bankAccountType: EntityType(BankAccountType) (optional, select con filtro activos)
- isActive: CheckboxType
```

**Relaciones**:
- `ManyToOne` → Bank (nullable)
- `ManyToOne` → BankAccountType (nullable)

**Features**: CRUD completo + Export + Join queries optimizado

---

### 2. Company User Association (Asociación Empresa Usuario)

**Controlador**: `App\Controller\Maintainers\Settlements\CompanyUserAssociationController`  
**Entidad**: `App\Entity\Tenant\CompanyUserAssociation`  
**Form**: `App\Form\Maintainers\Settlements\CompanyUserAssociationType`  
**Template**: `templates/maintainers/settlements/company_user_association/index.html.twig`

**Endpoints**:
- `GET /maintainers/settlements/company-user-association` → `app_maintainers_settlements_company_user_association_index`
- `GET /maintainers/settlements/company-user-association/create` → `app_maintainers_settlements_company_user_association_create`
- `GET /maintainers/settlements/company-user-association/{id}/edit` → `app_maintainers_settlements_company_user_association_edit`
- `POST /maintainers/settlements/company-user-association/{id}/delete` → `app_maintainers_settlements_company_user_association_delete`
- `GET /maintainers/settlements/company-user-association/export` → `app_maintainers_settlements_company_user_association_export`

**Columnas**: 
- name (Nombre)
- isActive (Estado)

**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Export**: `asociaciones_empresa_usuario_YYYY-MM-DD.csv`

**Page Titles**:
- Index: "Asociaciones Empresa Usuario"
- Create: "Crear Asociacion Empresa Usuario"
- Edit: "Editar Asociacion Empresa Usuario"

**Features**: CRUD completo + Export

---

### 3. Daily UF (UF Diaria)

**Controlador**: `App\Controller\Maintainers\Settlements\DailyUFController`  
**Entidad**: `App\Entity\Tenant\DailyUF`  
**Form**: `App\Form\Maintainers\Settlements\DailyUFType`  
**Template**: `templates/maintainers/settlements/daily_uf/index.html.twig`

**Endpoints**:
- `GET /maintainers/settlements/daily-uf` → `app_maintainers_settlements_daily_uf_index`
- `GET /maintainers/settlements/daily-uf/create` → `app_maintainers_settlements_daily_uf_create`
- `GET /maintainers/settlements/daily-uf/{id}/edit` → `app_maintainers_settlements_daily_uf_edit`
- `POST /maintainers/settlements/daily-uf/{id}/delete` → `app_maintainers_settlements_daily_uf_delete`
- `GET /maintainers/settlements/daily-uf/export` → `app_maintainers_settlements_daily_uf_export`

**Columnas**: 
- ufDate (Fecha)
- ufValue (Valor UF)
- isActive (Estado)

**Paginación**: ✅ QueryBuilder (DESC por ufDate)  
**Turbo Frame**: ✅ Modal para create/edit  
**Export**: `uf_diaria_YYYY-MM-DD.csv`

**Form Fields**:
```php
- date: DateType (widget: single_text)
- value: NumberType (scale: 2, min: 0, step: 0.01)
- isActive: CheckboxType
```

**Page Titles**:
- Index: "UF Diaria"
- Create: "Crear UF Diaria"
- Edit: "Editar UF Diaria"

**Ordenamiento Especial**: Por fecha descendente (`ORDER BY ufDate DESC`)

**Features**: CRUD completo + Export + Formato numérico decimal

---

### 4. Professional Participation (Participación Profesional)

**Controlador**: `App\Controller\Maintainers\Settlements\ProfessionalParticipationController`  
**Entidad**: `App\Entity\Tenant\ProfessionalParticipation`  
**Form**: `App\Form\Maintainers\Settlements\ProfessionalParticipationType`  
**Template**: `templates/maintainers/settlements/professional_participation/index.html.twig`

**Endpoints**:
- `GET /maintainers/settlements/professional-participation` → `app_maintainers_settlements_professional_participation_index`
- `GET /maintainers/settlements/professional-participation/create` → `app_maintainers_settlements_professional_participation_create`
- `GET /maintainers/settlements/professional-participation/{id}/edit` → `app_maintainers_settlements_professional_participation_edit`
- `POST /maintainers/settlements/professional-participation/{id}/delete` → `app_maintainers_settlements_professional_participation_delete`
- `GET /maintainers/settlements/professional-participation/export` → `app_maintainers_settlements_professional_participation_export`

**Columnas**: 
- name (Nombre)
- isActive (Estado)

**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Export**: `participaciones_profesionales_YYYY-MM-DD.csv`

**Page Titles**:
- Index: "Participaciones Profesionales"
- Create: "Crear Participacion Profesional"
- Edit: "Editar Participacion Profesional"

**Features**: CRUD completo + Export

---

### 5. Settlement Base (Base de Liquidación)

**Controlador**: `App\Controller\Maintainers\Settlements\SettlementBaseController`  
**Entidad**: `App\Entity\Tenant\SettlementBase`  
**Form**: `App\Form\Maintainers\Settlements\SettlementBaseType`  
**Template**: `templates/maintainers/settlements/settlement_base/index.html.twig`

**Endpoints**:
- `GET /maintainers/settlements/settlement-base` → `app_maintainers_settlements_settlement_base_index`
- `GET /maintainers/settlements/settlement-base/create` → `app_maintainers_settlements_settlement_base_create`
- `GET /maintainers/settlements/settlement-base/{id}/edit` → `app_maintainers_settlements_settlement_base_edit`
- `POST /maintainers/settlements/settlement-base/{id}/delete` → `app_maintainers_settlements_settlement_base_delete`
- `GET /maintainers/settlements/settlement-base/export` → `app_maintainers_settlements_settlement_base_export`

**Columnas**: 
- name (Nombre)
- isActive (Estado)

**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Export**: `bases_liquidacion_YYYY-MM-DD.csv`

**Page Titles**:
- Index: "Bases Liquidacion"
- Create: "Crear Base Liquidacion"
- Edit: "Editar Base Liquidacion"

**Features**: CRUD completo + Export

---

## 🔧 Componentes Compartidos

### Templates Base
- `templates/maintainers/modern_index.html.twig` - Template maestro
- `templates/maintainers/_modal_form.html.twig` - Modal para forms
- Templates específicos en `templates/maintainers/settlements/{mantenedor}/`

### AbstractMantenedorController
**Ubicación**: `src/Controller/AbstractMantenedorController.php`

**Métodos implementados por TODOS los Settlements**:
```php
protected function getData(Request $request): QueryBuilder;
protected function getColumns(): array;
protected function getTemplatePath(): string;
protected function getFormType(): string;
protected function createNewEntity(): object;
protected function getIndexRoute(): string;
protected function getPageTitle(?string $action = null): string;
protected function beforeSave(object $entity, Request $request): void;
protected function canDelete(object $entity): bool;
```

### Hook beforeSave()
Todos los mantenedores implementan actualización automática de `updatedAt`:
```php
protected function beforeSave(object $entity, Request $request): void
{
    if ($entity instanceof {EntityClass}) {
        $entity->setUpdatedAt(new \DateTime());
    }
}
```

### Paginación
- **Tipo**: QueryBuilder → Paginación automática activada ✅
- **Parámetros URL**: `?page=1&limit=10`
- **Default**: 10 items por página
- **Ordenamiento**: 
  - Por ID DESC (4 mantenedores)
  - Por ufDate DESC (DailyUF)

---

## 🎨 UI/UX

**Framework**: Bootstrap 5.3.0  
**Iconos**: BoxIcons (`bx-*`)  
**Modales**: Turbo Frames  
**Confirmación delete**: SweetAlert2  
**Responsive**: ✅ Mobile-first

**Breadcrumb típico**:
```
Dashboard > Mantenedores > Liquidaciones > {Mantenedor}
```

**Estructura Table**:
- Header con botones de acción (Crear, Exportar)
- Table responsive con columnas dinámicas
- Actions column (Edit, Delete) por registro
- Paginación inferior

---

## 🔐 Seguridad

- ✅ CSRF Protection en formularios
- ✅ Multi-tenancy aislamiento por TenantContext
- ✅ Validación Doctrine constraints
- ✅ Method-level security con `#[Route]` methods
- ✅ Cascading soft-delete preparado (entity property `idEstado`)

---

## 📊 Estado de Implementación

| Característica | Estado |
|---------------|--------|
| CRUD Completo | ✅ 5/5 |
| Paginación | ✅ 5/5 |
| Exportación CSV | ✅ 5/5 |
| Turbo Frames | ✅ 5/5 |
| Forms validados | ✅ 5/5 documentados |
| Join queries | ✅ 1/5 (BankAccount) |
| beforeSave hook | ✅ 5/5 |
| canDelete hook | ✅ 5/5 |
| Tests unitarios | ❌ No implementado |
| Permisos/Roles | ❌ No implementado |

---

## 🔗 Relaciones Entre Entidades

```
BankAccount
├── ManyToOne → Bank
└── ManyToOne → BankAccountType

DailyUF (standalone)
CompanyUserAssociation (standalone)
ProfessionalParticipation (standalone)
SettlementBase (standalone)
```

**Nota**: BankAccount es el único con relaciones externas documentadas.

---

## 📝 Notas Técnicas

1. **Multi-tenancy**: Todos los mantenedores usan `TenantEntityManager` vía inyección de dependencias
2. **Soft Delete**: Preparado con campo `idEstado` pero delete es físico actualmente
3. **Audit Trail**: Timestamps `createdAt` y `updatedAt` implementados con hook `beforeSave()`
4. **Relaciones**: Solo BankAccount tiene JOINs optimizados con `leftJoin` + `addSelect`
5. **Ordenamiento**: 
   - Default: `ORDER BY id DESC`
   - Excepción: DailyUF usa `ORDER BY ufDate DESC`
6. **Búsqueda/Filtros**: No implementado en ningún mantenedor
7. **Import masivo**: No implementado
8. **Traducciones**: Uso de `TranslatorInterface` para columnas (`maintainers` domain)

---

## 🚀 Próximos Pasos

### Prioridad Alta
1. ✅ Completar Forms pendientes → **DONE (5/5)**
2. ⚠️ Implementar tests unitarios para controllers
3. ⚠️ Agregar tests de integración para repositorios

### Prioridad Media
4. Implementar búsqueda/filtros en listados
5. Agregar sistema de permisos por rol
6. Optimizar queries con JOINs en mantenedores sin relaciones
7. Implementar soft delete real (usar `idEstado`)

### Prioridad Baja
8. Implementar import CSV masivo
9. Agregar validaciones customizadas en Forms
10. Implementar ordenamiento dinámico por columnas
11. Agregar paginación configurable por usuario

---

## 📦 Dependencias

**Bundles requeridos**:
- `hakam/multi-tenancy-bundle` - Multi-tenancy
- `symfony/form` - Form components
- `symfony/validator` - Validaciones
- `doctrine/orm` - ORM y QueryBuilder
- `symfony/translation` - Traducciones

**Services**:
- `TenantEntityManager` - Gestión multi-tenant
- `ExportService` - Exportación CSV
- `TranslatorInterface` - Traducciones i18n

---

## 🧪 Cobertura de Testing

| Componente | Unit Tests | Integration Tests | E2E Tests |
|-----------|-----------|------------------|-----------|
| Controllers | ❌ 0/5 | ❌ 0/5 | ❌ 0/5 |
| Entities | ❌ 0/5 | ❌ 0/5 | - |
| Forms | ❌ 0/5 | ❌ 0/5 | - |
| Repositories | ❌ 0/5 | ❌ 0/5 | - |

**Total Coverage**: 0% (Pendiente implementación)

---

## 📚 Referencias

- [AbstractMantenedorController](../../src/Controller/AbstractMantenedorController.php)
- [SPEC Mantenedores Básicos](SPEC_MANTENEDORES_BASIC.md)
- [Arquitectura General](../arquitectura/ARCHITECTURE.md)
- [Multi-tenancy Documentation](../../MULTITENANCY.md)

---

**Generado**: 2026-02-09  
**Autor**: GitHub Copilot  
**Versión**: 1.0.0
