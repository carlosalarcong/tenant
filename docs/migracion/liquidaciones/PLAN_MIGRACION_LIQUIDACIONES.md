# Plan de Migracion - Categoria Liquidaciones

## Resumen Ejecutivo

**Categoria:** MantenedorLiquidaciones
**Origen Legacy:** `melisa_prod/src/Rebsol/MantenedoresBundle/Controller/_Default/MantenedorMaestro/MantenedorEmpresa/MantenedorLiquidaciones/`
**Destino Nuevo:** `melisa_tenant/src/Controller/Maintainers/Settlements/`
**Total Entidades:** 5 mantenedores
**Complejidad Global:** Media

---

## Inventario Completo

### Entidades a Migrar

| # | Legacy (ES) | Nuevo (EN) | Tabla Nueva | Complejidad | Fase |
|---|-------------|------------|-------------|-------------|------|
| 1 | AsociaEmpresaUsuario | CompanyUserAssociation | company_user_association | Simple | 1 |
| 2 | BaseLiquidaciones | SettlementBase | settlement_base | Simple | 1 |
| 3 | CuentasBancarias | BankAccount | bank_account | Moderada | 2 |
| 4 | ParticipacionProfesional | ProfessionalParticipation | professional_participation | Simple+ | 1 |
| 5 | UFDiarias | DailyUF | daily_uf | Simple+ | 1 |

### Dependencias Externas

| Entidad | Ubicacion | Estado |
|---------|-----------|--------|
| Bank | src/Entity/Tenant/Bank.php | YA EXISTE (Tesoreria) |
| BankAccountType | src/Entity/Tenant/BankAccountType.php | YA EXISTE (Tesoreria) |

### Entidades Descartadas

| Legacy | Razon |
|--------|-------|
| *(ninguna)* | Todas las 5 entidades del inventario son migrables |

---

## Patron a Seguir (Referencia)

Todos los archivos nuevos deben seguir EXACTAMENTE el patron existente:

| Tipo | Ejemplo Referencia | Ubicacion |
|------|-------------------|-----------|
| Entity | `src/Entity/Tenant/Gender.php` | `src/Entity/Tenant/` |
| Repository | `src/Repository/Tenant/GenderRepository.php` | `src/Repository/Tenant/` |
| FormType | `src/Form/Maintainers/Personal/GenderType.php` | `src/Form/Maintainers/Settlements/` |
| Controller | `src/Controller/Maintainers/Basic/GenderController.php` | `src/Controller/Maintainers/Settlements/` |
| Template | `templates/maintainers/basic/gender/index.html.twig` | `templates/maintainers/settlements/` |

### Herencia de Controllers

```
AbstractController
  -> AbstractTenantAwareController
    -> AbstractMantenedorController (Template Method Pattern)
      -> [Tu nuevo controller]
```

### Convenciones de Nombres

- **Rutas:** `app_maintainers_settlements_{entity_snake}_{action}`
- **Ejemplo:** `app_maintainers_settlements_settlement_base_index`
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
'bank.name' => 'Banco',                     // Relacion ManyToOne
'bankAccountType.name' => 'Tipo Cuenta',    // Relacion ManyToOne
```

---

## FASE 1: Entidades Simples

Entidades con campos basicos sin relaciones FK complejas.

---

### 1.1 CompanyUserAssociation (AsociaEmpresaUsuario)

**Legacy:** `AsociaEmpresaUsuario.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea el archivo src/Entity/Tenant/CompanyUserAssociation.php siguiendo EXACTAMENTE el patron de src/Entity/Tenant/Gender.php.

Especificaciones:
- Tabla: company_user_association
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
- Repository: CompanyUserAssociationRepository

RESTRICCIONES:
- NO agregar campo idEmpresa (multi-tenancy por Hakam)
- NO agregar relaciones adicionales
- Usar PHP 8.2 attributes (#[ORM\...])
- Usar Assert constraints de Symfony
```

#### Prompt Copilot - Repository

```
Crea el archivo src/Repository/Tenant/CompanyUserAssociationRepository.php siguiendo EXACTAMENTE el patron de src/Repository/Tenant/GenderRepository.php.

- Extends ServiceEntityRepository
- Entity class: CompanyUserAssociation
- Metodos:
  - findAllActive(): array - orderBy name ASC, where isActive=true
- Namespace: App\Repository\Tenant
```

#### Prompt Copilot - FormType

```
Crea el archivo src/Form/Maintainers/Settlements/CompanyUserAssociationType.php siguiendo EXACTAMENTE el patron de src/Form/Maintainers/Personal/GenderType.php.

Campos del formulario:
- name: TextType, label='Nombre', required, placeholder='Ingrese nombre de la asociacion', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: CompanyUserAssociation
Namespace: App\Form\Maintainers\Settlements
```

#### Prompt Copilot - Controller

```
Crea el archivo src/Controller/Maintainers/Settlements/CompanyUserAssociationController.php siguiendo EXACTAMENTE el patron de src/Controller/Maintainers/Basic/GenderController.php.

Especificaciones:
- Route base: /maintainers/settlements/company-user-association
- Rutas:
  - GET '' -> index (app_maintainers_settlements_company_user_association_index)
  - GET/POST '/create' -> create (app_maintainers_settlements_company_user_association_create)
  - GET/POST '/{id}/edit' -> edit (app_maintainers_settlements_company_user_association_edit)
  - POST '/{id}/delete' -> delete (app_maintainers_settlements_company_user_association_delete)
  - GET '/export' -> export (app_maintainers_settlements_company_user_association_export)

- Inyectar: CompanyUserAssociationRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('cua')->orderBy('cua.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): CompanyUserAssociationType::class
- createNewEntity(): new CompanyUserAssociation()
- getTemplatePath(): 'maintainers/settlements/company_user_association/index.html.twig'
- getPageTitle(): 'create'=>'Crear Asociacion Empresa Usuario', 'edit'=>'Editar Asociacion Empresa Usuario', default=>'Asociaciones Empresa Usuario'

- Export columns: ['name', 'isActive']
- Export headers: ['Nombre', 'Activo']
- Export filename: 'asociaciones_empresa_usuario_'.date('Y-m-d').'.csv'

RESTRICCIONES:
- NO cambiar el patron de AbstractMantenedorController
- NO agregar logica de negocio extra
- Mantener multi-tenant (AbstractTenantAwareController lo maneja)
```

#### Prompt Copilot - Template

```
Crea el archivo templates/maintainers/settlements/company_user_association/index.html.twig siguiendo EXACTAMENTE el patron de templates/maintainers/basic/gender/index.html.twig.

Variables a configurar:
- page_title: 'Asociaciones Empresa Usuario'
- icon: 'bx-link'
- breadcrumb_section: 'Liquidaciones'
- description: 'Gestiona las asociaciones entre empresas y usuarios'
- create_route: 'app_maintainers_settlements_company_user_association_create'
- edit_route: 'app_maintainers_settlements_company_user_association_edit'
- delete_route: 'app_maintainers_settlements_company_user_association_delete'
- export_route: 'app_maintainers_settlements_company_user_association_export'

Extends: maintainers/modern_index.html.twig
```

---

### 1.2 SettlementBase (BaseLiquidaciones)

**Legacy:** `BaseLiquidaciones.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea el archivo src/Entity/Tenant/SettlementBase.php siguiendo EXACTAMENTE el patron de src/Entity/Tenant/Gender.php.

Especificaciones:
- Tabla: settlement_base
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable

- Namespace: App\Entity\Tenant
- Repository: SettlementBaseRepository

RESTRICCIONES:
- NO agregar campo idEmpresa
- Usar PHP 8.2 attributes
```

#### Prompt Copilot - Repository

```
Crea el archivo src/Repository/Tenant/SettlementBaseRepository.php siguiendo el patron de GenderRepository.php.

- Entity: SettlementBase
- Metodo findAllActive(): orderBy name ASC
- Namespace: App\Repository\Tenant
```

#### Prompt Copilot - FormType

```
Crea el archivo src/Form/Maintainers/Settlements/SettlementBaseType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese base de liquidacion', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: SettlementBase
Namespace: App\Form\Maintainers\Settlements
```

#### Prompt Copilot - Controller

```
Crea el archivo src/Controller/Maintainers/Settlements/SettlementBaseController.php siguiendo EXACTAMENTE el patron de GenderController.php.

- Route base: /maintainers/settlements/settlement-base
- Prefijo rutas: app_maintainers_settlements_settlement_base_
- Inyectar: SettlementBaseRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('sb')->orderBy('sb.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): SettlementBaseType::class
- createNewEntity(): new SettlementBase()
- getTemplatePath(): 'maintainers/settlements/settlement_base/index.html.twig'
- getPageTitle(): 'create'=>'Crear Base Liquidacion', 'edit'=>'Editar Base Liquidacion', default=>'Bases de Liquidacion'
- Export columns: ['name', 'isActive'], headers: ['Nombre', 'Activo'], filename: 'bases_liquidacion_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/settlements/settlement_base/index.html.twig siguiendo el patron de gender/index.html.twig.

- page_title: 'Bases de Liquidacion'
- icon: 'bx-calculator'
- breadcrumb_section: 'Liquidaciones'
- description: 'Gestiona las bases de liquidacion'
- Rutas: app_maintainers_settlements_settlement_base_{create,edit,delete,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 1.3 ProfessionalParticipation (ParticipacionProfesional)

**Legacy:** `ParticipacionProfesional.php` - Campos: id, nombre(255), descripcion(text), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/ProfessionalParticipation.php siguiendo el patron de Gender.php.

- Tabla: professional_participation
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - description: text, nullable
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: ProfessionalParticipationRepository
- Namespace: App\Entity\Tenant
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/ProfessionalParticipationRepository.php siguiendo el patron de GenderRepository.

- Entity: ProfessionalParticipation
- Metodo findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Settlements/ProfessionalParticipationType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese tipo de participacion', class='form-control'
- description: TextareaType, label='Descripcion', required=false, class='form-control', attr=['rows'=>3]
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: ProfessionalParticipation
Namespace: App\Form\Maintainers\Settlements
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Settlements/ProfessionalParticipationController.php siguiendo el patron de GenderController.

- Route base: /maintainers/settlements/professional-participation
- Prefijo rutas: app_maintainers_settlements_professional_participation_
- Inyectar: ProfessionalParticipationRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('pp')->orderBy('pp.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'description' => 'Descripcion', 'isActive' => 'Estado']
- getFormType(): ProfessionalParticipationType::class
- createNewEntity(): new ProfessionalParticipation()
- getTemplatePath(): 'maintainers/settlements/professional_participation/index.html.twig'
- getPageTitle(): default=>'Participaciones Profesionales'
- Export columns: ['name', 'description', 'isActive'], headers: ['Nombre', 'Descripcion', 'Activo'], filename: 'participaciones_profesionales_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/settlements/professional_participation/index.html.twig.

- page_title: 'Participaciones Profesionales'
- icon: 'bx-user-voice'
- breadcrumb_section: 'Liquidaciones'
- description: 'Gestiona las participaciones profesionales en liquidaciones'
- Rutas: app_maintainers_settlements_professional_participation_{create,edit,delete,export}
```

---

### 1.4 DailyUF (UFDiarias)

**Legacy:** `UFDiarias.php` - Campos: id, fecha(date), valor(decimal10,2), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/DailyUF.php siguiendo el patron de Gender.php.

- Tabla: daily_uf
- Campos:
  - id: integer, PK, auto-increment
  - date: date, NOT NULL, Assert\NotNull (valor de UF en una fecha especifica)
  - value: decimal(10,2), NOT NULL, Assert\NotNull, Assert\PositiveOrZero
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: DailyUFRepository
- Namespace: App\Entity\Tenant

NOTA: Esta entidad NO tiene campo 'name'. El campo 'date' es la clave descriptiva.
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/DailyUFRepository.php siguiendo el patron de GenderRepository.

- Entity: DailyUF
- Metodos:
  - findAllActive(): orderBy date DESC
  - findByDate(\DateTime $date): ?DailyUF
  - findByDateRange(\DateTime $from, \DateTime $to): array
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Settlements/DailyUFType.php siguiendo el patron de GenderType.php.

Campos:
- date: DateType, label='Fecha', required, widget='single_text', class='form-control'
- value: NumberType, label='Valor UF', required, class='form-control', scale=2, attr=['min'=>0, 'step'=>'0.01']
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: DailyUF
Namespace: App\Form\Maintainers\Settlements
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Settlements/DailyUFController.php siguiendo el patron de GenderController.

- Route base: /maintainers/settlements/daily-uf
- Prefijo rutas: app_maintainers_settlements_daily_uf_
- Inyectar: DailyUFRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('duf')->orderBy('duf.date', 'DESC')
- getColumns(): ['date' => 'Fecha', 'value' => 'Valor UF', 'isActive' => 'Estado']
- getFormType(): DailyUFType::class
- createNewEntity(): new DailyUF()
- getTemplatePath(): 'maintainers/settlements/daily_uf/index.html.twig'
- getPageTitle(): default=>'UF Diarias'
- Export columns: ['date', 'value', 'isActive'], headers: ['Fecha', 'Valor', 'Activo'], filename: 'uf_diarias_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/settlements/daily_uf/index.html.twig.

- page_title: 'UF Diarias'
- icon: 'bx-calendar-event'
- breadcrumb_section: 'Liquidaciones'
- description: 'Gestiona los valores de UF diarios'
- Rutas: app_maintainers_settlements_daily_uf_{create,edit,delete,export}
```

---

## FASE 2: Entidad con Relaciones

Entidad que tiene FK a otras entidades existentes (Bank, BankAccountType).

---

### 2.1 BankAccount (CuentasBancarias)

**Legacy:** `CuentasBancarias.php` - Campos: id, numeroCuenta(255), idBanco(FK->Bank), idTipoCuenta(FK->BankAccountType), idEstado, idEmpresa

**DEPENDENCIA:** Bank y BankAccountType ya existen en el proyecto (categoria Tesoreria)

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/BankAccount.php siguiendo el patron de Gender.php pero CON relaciones ManyToOne.

- Tabla: bank_account
- Campos:
  - id: integer, PK, auto-increment
  - accountNumber: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255), column: account_number (legacy: numeroCuenta)
  - bank: ManyToOne -> Bank, nullable, JoinColumn(name='bank_id')
  - bankAccountType: ManyToOne -> BankAccountType, nullable, JoinColumn(name='bank_account_type_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: BankAccountRepository
- Namespace: App\Entity\Tenant

IMPORTANTE: Las relaciones ManyToOne deben usar:
#[ORM\ManyToOne(targetEntity: Bank::class)]
#[ORM\JoinColumn(name: 'bank_id', nullable: true)]

#[ORM\ManyToOne(targetEntity: BankAccountType::class)]
#[ORM\JoinColumn(name: 'bank_account_type_id', nullable: true)]

Mapeo columnas:
- accountNumber -> column: account_number
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/BankAccountRepository.php siguiendo el patron de GenderRepository.

- Entity: BankAccount
- Metodos:
  - findAllActive(): orderBy accountNumber ASC
  - findByBank(int $bankId): array
  - findByAccountType(int $typeId): array
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Settlements/BankAccountFormType.php.

Seguir el patron de MedicalServiceType.php para campos con EntityType (relaciones).

Campos:
- accountNumber: TextType, label='Numero de Cuenta', required, placeholder='Ingrese numero de cuenta', class='form-control'
- bank: EntityType, class=Bank::class, label='Banco',
  choice_label='name', placeholder='Seleccione banco...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- bankAccountType: EntityType, class=BankAccountType::class, label='Tipo de Cuenta',
  choice_label='name', placeholder='Seleccione tipo...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: BankAccount
Namespace: App\Form\Maintainers\Settlements
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Settlements/BankAccountController.php siguiendo el patron de GenderController.

- Route base: /maintainers/settlements/bank-account
- Prefijo rutas: app_maintainers_settlements_bank_account_
- Inyectar: BankAccountRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('ba')
    ->leftJoin('ba.bank', 'b')
    ->leftJoin('ba.bankAccountType', 'bat')
    ->addSelect('b', 'bat')
    ->orderBy('ba.id', 'DESC')
- getColumns(): ['accountNumber' => 'Numero Cuenta', 'bank.name' => 'Banco', 'bankAccountType.name' => 'Tipo Cuenta', 'isActive' => 'Estado']
- getFormType(): BankAccountFormType::class
- createNewEntity(): new BankAccount()
- getTemplatePath(): 'maintainers/settlements/bank_account/index.html.twig'
- getPageTitle(): default=>'Cuentas Bancarias'
- Export columns: ['accountNumber', 'bank.name', 'bankAccountType.name', 'isActive']
- Export headers: ['Numero Cuenta', 'Banco', 'Tipo Cuenta', 'Activo']
- filename: 'cuentas_bancarias_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/settlements/bank_account/index.html.twig.

- page_title: 'Cuentas Bancarias'
- icon: 'bx-credit-card'
- breadcrumb_section: 'Liquidaciones'
- description: 'Gestiona las cuentas bancarias para liquidaciones'
- Rutas: app_maintainers_settlements_bank_account_{create,edit,delete,export}
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
1. Fase 1: CompanyUserAssociation, SettlementBase, ProfessionalParticipation, DailyUF
2. Fase 2: BankAccount (requiere Bank y BankAccountType de Tesoreria)
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

### Paso 2: Insertar categoria + 5 mantenedores

Reemplazar los IDs segun corresponda. En este ejemplo:
- `{MANTENEDORES_ID}` = 4 (padre Mantenedores)
- ID base = siguiente disponible

```sql
-- Categoria Liquidaciones (hijo de Mantenedores)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES ({BASE_ID}, 'maintenance_settlements', 'Liquidaciones', NULL, 'bx bx-calculator', NULL, 4, 13, true, true, true, '["ROLE_USER"]', NOW());

-- 5 mantenedores (hijos de Liquidaciones, parent_id = {BASE_ID})
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES
({BASE_ID+1}, 'company_user_association', 'Asociaciones Empresa Usuario', 'app_maintainers_settlements_company_user_association_index', 'bx bx-link', 'maintenance_settlements', {BASE_ID}, 1, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+2}, 'settlement_base', 'Bases de Liquidacion', 'app_maintainers_settlements_settlement_base_index', 'bx bx-calculator', 'maintenance_settlements', {BASE_ID}, 2, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+3}, 'bank_account', 'Cuentas Bancarias', 'app_maintainers_settlements_bank_account_index', 'bx bx-credit-card', 'maintenance_settlements', {BASE_ID}, 3, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+4}, 'professional_participation', 'Participaciones Profesionales', 'app_maintainers_settlements_professional_participation_index', 'bx bx-user-voice', 'maintenance_settlements', {BASE_ID}, 4, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+5}, 'daily_uf', 'UF Diarias', 'app_maintainers_settlements_daily_uf_index', 'bx bx-calendar-event', 'maintenance_settlements', {BASE_ID}, 5, true, true, true, '["ROLE_USER"]', NOW());
```

### Paso 3: Limpiar cache de menu

```bash
php bin/console cache:clear
```

### Rollback (en caso de necesitar borrar)

```sql
DELETE FROM menu_items WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintenance_settlements');
DELETE FROM menu_items WHERE name = 'maintenance_settlements';
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
| Entities | 5 | src/Entity/Tenant/ |
| Repositories | 5 | src/Repository/Tenant/ |
| FormTypes | 5 | src/Form/Maintainers/Settlements/ |
| Controllers | 5 | src/Controller/Maintainers/Settlements/ |
| Templates | 5 | templates/maintainers/settlements/ |
| **TOTAL** | **25 archivos** | |

(5 mantenedores = 5 entidades x 5 archivos cada uno)
