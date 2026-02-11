# Plan de Migracion - Categoria Tesoreria

## Resumen Ejecutivo

**Categoria:** MantenedorTesoreria
**Origen Legacy:** `melisa_prod/src/Rebsol/MantenedoresBundle/Controller/_Default/MantenedorMaestro/MantenedorEmpresa/MantenedorTesoreria/`
**Destino Nuevo:** `melisa_tenant/src/Controller/Maintainers/Treasury/`
**Total Entidades:** 16 mantenedores + 2 dependencias
**Complejidad Global:** Media-Alta

---

## Inventario Completo

### Entidades a Migrar

| # | Legacy (ES) | Nuevo (EN) | Tabla Nueva | Complejidad | Fase |
|---|-------------|------------|-------------|-------------|------|
| 1 | Giro | BusinessTurn | business_turn | Simple | 1 |
| 2 | TipoCuentaBanco | BankAccountType | bank_account_type | Simple | 1 |
| 3 | TarjetaCreditoTipo | CreditCardType | credit_card_type | Simple | 1 |
| 4 | TipoGratuidad | GratuityType | gratuity_type | Simple | 1 |
| 5 | TipoMoneda | CurrencyType | currency_type | Simple+ | 2 |
| 6 | IndicadorTraslado | TransferIndicator | transfer_indicator | Simple+ | 2 |
| 7 | FormaPagoFacturacion | BillingPaymentMethod | billing_payment_method | Moderada | 2 |
| 8 | CondicionPago | PaymentCondition | payment_condition | Moderada | 2 |
| 9 | Banco | Bank | bank | Moderada | 3 |
| 10 | TipoDiferencia | DifferenceType | difference_type | Moderada | 3 |
| 11 | MotivoDiferencia | DifferenceReason | difference_reason | Moderada | 3 |
| 12 | TarjetaCredito | CreditCard | credit_card | Moderada | 3 |
| 13 | MotivoGratuidad | GratuityReason | gratuity_reason | Moderada+ | 3 |
| 14 | TipoDocumento | DocumentType | document_type | Moderada+ | 3 |
| 15 | UbicacionCaja | CashRegisterLocation | cash_register_location | Moderada+ | 3 |
| 16 | FormaPago | PaymentMethod | payment_method | Compleja | 4 |

### Dependencias Externas (crear ANTES)

| Legacy (ES) | Nuevo (EN) | Tabla | Requerido Por |
|-------------|------------|-------|---------------|
| FormaPagoTipo | PaymentMethodType | payment_method_type | PaymentMethod |
| TipoSentidoDiferencia | DifferenceDirection | difference_direction | DifferenceType, DifferenceReason |

### ALERTA: Posible Duplicado

> **BusinessActivity** ya existe como entidad en el proyecto nuevo.
> Verificar si corresponde a **Giro** del legacy antes de crear BusinessTurn.
> Si son equivalentes, OMITIR la migracion de Giro.

### Entidades Descartadas

| Legacy | Razon |
|--------|-------|
| Empresa | Es la entidad tenant, no es un mantenedor |
| UbicacionCajero | No existe la entidad en el legacy (solo controller vacio) |

---

## Patron a Seguir (Referencia)

Todos los archivos nuevos deben seguir EXACTAMENTE el patron existente:

| Tipo | Ejemplo Referencia | Ubicacion |
|------|-------------------|-----------|
| Entity | `src/Entity/Tenant/Gender.php` | `src/Entity/Tenant/` |
| Repository | `src/Repository/Tenant/GenderRepository.php` | `src/Repository/Tenant/` |
| FormType | `src/Form/Maintainers/Personal/GenderType.php` | `src/Form/Maintainers/Treasury/` |
| Controller | `src/Controller/Maintainers/Basic/GenderController.php` | `src/Controller/Maintainers/Treasury/` |
| Template | `templates/maintainers/basic/gender/index.html.twig` | `templates/maintainers/treasury/` |

### Herencia de Controllers

```
AbstractController
  -> AbstractTenantAwareController
    -> AbstractMantenedorController (Template Method Pattern)
      -> [Tu nuevo controller]
```

### Convenciones de Nombres

- **Rutas:** `app_maintainers_treasury_{entity_snake}_{action}`
- **Ejemplo:** `app_maintainers_treasury_bank_account_type_index`
- **Acciones:** `index`, `create`, `edit`, `delete`, `export`

### ⚡ Nuevo Formato: getColumns() Asociativo

**IMPORTANTE:** A partir de febrero 2026, todos los controllers usan formato asociativo para columnas:

```php
// ❌ Formato ANTIGUO (deprecated)
protected function getColumns(): array {
    return ['name', 'code', 'isActive'];
}

// ✅ Formato NUEVO (usar siempre)
protected function getColumns(): array {
    return [
        'name' => 'Nombre',
        'code' => 'Código',
        'isActive' => 'Estado'
    ];
}
```

**Beneficios:**
- ✅ Columnas y labels juntos (cohesión)
- ✅ Auto-documentación del código
- ✅ Sin duplicación de identificadores
- ✅ Template más limpio (sin if/elseif masivos)
- ✅ Escalable para metadata futura

**Relaciones:**
```php
'branch.name' => 'Sucursal',        // Relación ManyToOne
'parent.name' => 'Padre',           // Auto-referencia
'paymentMethodType.name' => 'Tipo' // Relación
```

---

## FASE 1: Entidades Simples

Entidades con solo campo `name` + `isActive`. CRUD basico sin relaciones.

---

### 1.1 BusinessTurn (Giro)

> VERIFICAR si BusinessActivity existente ya cubre esta entidad. Si es asi, OMITIR.

**Legacy:** `Giro.php` - Campos: id, nombre, idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea el archivo src/Entity/Tenant/BusinessTurn.php siguiendo EXACTAMENTE el patron de src/Entity/Tenant/Gender.php.

Especificaciones:
- Tabla: business_turn
- Campos:
  - id: integer, PK, auto-increment
  - name: string(100), NOT NULL, Assert\NotBlank, Assert\Length(max=100)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable

- Incluir getters/setters para todos los campos
- Constructor inicializa createdAt = new \DateTime()
- Namespace: App\Entity\Tenant
- Repository: BusinessTurnRepository

RESTRICCIONES:
- NO agregar campo idEmpresa (multi-tenancy por Hakam)
- NO agregar relaciones adicionales
- Usar PHP 8.2 attributes (#[ORM\...])
- Usar Assert constraints de Symfony
```

#### Prompt Copilot - Repository

```
Crea el archivo src/Repository/Tenant/BusinessTurnRepository.php siguiendo EXACTAMENTE el patron de src/Repository/Tenant/GenderRepository.php.

- Extends ServiceEntityRepository
- Entity class: BusinessTurn
- Metodos:
  - findAllActive(): array - orderBy name ASC, where isActive=true
- Namespace: App\Repository\Tenant
```

#### Prompt Copilot - FormType

```
Crea el archivo src/Form/Maintainers/Treasury/BusinessTurnType.php siguiendo EXACTAMENTE el patron de src/Form/Maintainers/Personal/GenderType.php.

Campos del formulario:
- name: TextType, label='Nombre', required, placeholder='Ingrese nombre del giro', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: BusinessTurn
Namespace: App\Form\Maintainers\Treasury
```

#### Prompt Copilot - Controller

```
Crea el archivo src/Controller/Maintainers/Treasury/BusinessTurnController.php siguiendo EXACTAMENTE el patron de src/Controller/Maintainers/Basic/GenderController.php.

Especificaciones:
- Route base: /maintainers/treasury/business-turn
- Rutas:
  - GET '' -> index (app_maintainers_treasury_business_turn_index)
  - GET/POST '/create' -> create (app_maintainers_treasury_business_turn_create)
  - GET/POST '/{id}/edit' -> edit (app_maintainers_treasury_business_turn_edit)
  - POST '/{id}/delete' -> delete (app_maintainers_treasury_business_turn_delete)
  - GET '/export' -> export (app_maintainers_treasury_business_turn_export)

- Inyectar: BusinessTurnRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('bt')->orderBy('bt.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): BusinessTurnType::class
- createNewEntity(): new BusinessTurn()
- getTemplatePath(): 'maintainers/treasury/business_turn/index.html.twig'
- getPageTitle(): 'create'=>'Crear Giro', 'edit'=>'Editar Giro', default=>'Giros'

- Export columns: ['name', 'isActive']
- Export headers: ['Nombre', 'Activo']
- Export filename: 'giros_'.date('Y-m-d').'.csv'

RESTRICCIONES:
- NO cambiar el patron de AbstractMantenedorController
- NO agregar logica de negocio extra
- Mantener multi-tenant (AbstractTenantAwareController lo maneja)
```

#### Prompt Copilot - Template

```
Crea el archivo templates/maintainers/treasury/business_turn/index.html.twig siguiendo EXACTAMENTE el patron de templates/maintainers/basic/gender/index.html.twig.

Variables a configurar:
- page_title: 'Giros'
- icon: 'bx-briefcase'
- breadcrumb_section: 'Tesoreria'
- description: 'Gestiona los giros comerciales del sistema'
- create_route: 'app_maintainers_treasury_business_turn_create'
- edit_route: 'app_maintainers_treasury_business_turn_edit'
- delete_route: 'app_maintainers_treasury_business_turn_delete'
- export_route: 'app_maintainers_treasury_business_turn_export'

Extends: maintainers/modern_index.html.twig
```

---

### 1.2 BankAccountType (TipoCuentaBanco)

**Legacy:** `TipoCuentaBanco.php` - Campos: id, nombre, idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea el archivo src/Entity/Tenant/BankAccountType.php siguiendo EXACTAMENTE el patron de src/Entity/Tenant/Gender.php.

Especificaciones:
- Tabla: bank_account_type
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable

- Namespace: App\Entity\Tenant
- Repository: BankAccountTypeRepository

RESTRICCIONES:
- NO agregar campo idEmpresa
- Usar PHP 8.2 attributes
```

#### Prompt Copilot - Repository

```
Crea el archivo src/Repository/Tenant/BankAccountTypeRepository.php siguiendo el patron de GenderRepository.php.

- Entity: BankAccountType
- Metodo findAllActive(): orderBy name ASC
- Namespace: App\Repository\Tenant
```

#### Prompt Copilot - FormType

```
Crea el archivo src/Form/Maintainers/Treasury/BankAccountTypeType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese tipo de cuenta', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: BankAccountType
Namespace: App\Form\Maintainers\Treasury
```

#### Prompt Copilot - Controller

```
Crea el archivo src/Controller/Maintainers/Treasury/BankAccountTypeController.php siguiendo EXACTAMENTE el patron de GenderController.php.

- Route base: /maintainers/treasury/bank-account-type
- Prefijo rutas: app_maintainers_treasury_bank_account_type_
- Inyectar: BankAccountTypeRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('bat')->orderBy('bat.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): BankAccountTypeType::class
- createNewEntity(): new BankAccountType()
- getTemplatePath(): 'maintainers/treasury/bank_account_type/index.html.twig'
- getPageTitle(): 'create'=>'Crear Tipo Cuenta Banco', 'edit'=>'Editar Tipo Cuenta Banco', default=>'Tipos de Cuenta Bancaria'
- Export columns: ['name', 'isActive'], headers: ['Nombre', 'Activo'], filename: 'tipos_cuenta_banco_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/treasury/bank_account_type/index.html.twig siguiendo el patron de gender/index.html.twig.

- page_title: 'Tipos de Cuenta Bancaria'
- icon: 'bx-credit-card-front'
- breadcrumb_section: 'Tesoreria'
- description: 'Gestiona los tipos de cuenta bancaria'
- Rutas: app_maintainers_treasury_bank_account_type_{create,edit,delete,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 1.3 CreditCardType (TarjetaCreditoTipo)

**Legacy:** `TarjetaCreditoTipo.php` - Campos: id, nombre, idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/CreditCardType.php siguiendo el patron de Gender.php.

- Tabla: credit_card_type
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: CreditCardTypeRepository
- Namespace: App\Entity\Tenant
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/CreditCardTypeRepository.php siguiendo el patron de GenderRepository.

- Entity: CreditCardType
- Metodo findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Treasury/CreditCardTypeType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese tipo de tarjeta', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: CreditCardType
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Treasury/CreditCardTypeController.php siguiendo el patron de GenderController.

- Route base: /maintainers/treasury/credit-card-type
- Prefijo rutas: app_maintainers_treasury_credit_card_type_
- Inyectar: CreditCardTypeRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('cct')->orderBy('cct.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): CreditCardTypeType::class
- createNewEntity(): new CreditCardType()
- getTemplatePath(): 'maintainers/treasury/credit_card_type/index.html.twig'
- getPageTitle(): default=>'Tipos de Tarjeta de Credito'
- Export columns: ['name', 'isActive'], headers: ['Nombre', 'Activo'], filename: 'tipos_tarjeta_credito_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/treasury/credit_card_type/index.html.twig siguiendo el patron de gender/index.html.twig.

- page_title: 'Tipos de Tarjeta de Credito'
- icon: 'bx-credit-card'
- breadcrumb_section: 'Tesoreria'
- description: 'Gestiona los tipos de tarjeta de credito'
- Rutas: app_maintainers_treasury_credit_card_type_{create,edit,delete,export}
```

---

### 1.4 GratuityType (TipoGratuidad)

**Legacy:** `TipoGratuidad.php` - Campos: id, nombre, idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/GratuityType.php siguiendo el patron de Gender.php.

- Tabla: gratuity_type
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: GratuityTypeRepository
- Namespace: App\Entity\Tenant
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/GratuityTypeRepository.php siguiendo el patron de GenderRepository.

- Entity: GratuityType
- Metodo findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Treasury/GratuityTypeType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese tipo de gratuidad', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: GratuityType
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Treasury/GratuityTypeController.php siguiendo el patron de GenderController.

- Route base: /maintainers/treasury/gratuity-type
- Prefijo rutas: app_maintainers_treasury_gratuity_type_
- Inyectar: GratuityTypeRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('gt')->orderBy('gt.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): GratuityTypeType::class
- createNewEntity(): new GratuityType()
- getTemplatePath(): 'maintainers/treasury/gratuity_type/index.html.twig'
- getPageTitle(): default=>'Tipos de Gratuidad'
- Export columns: ['name', 'isActive'], headers: ['Nombre', 'Activo'], filename: 'tipos_gratuidad_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/treasury/gratuity_type/index.html.twig siguiendo el patron de gender/index.html.twig.

- page_title: 'Tipos de Gratuidad'
- icon: 'bx-gift'
- breadcrumb_section: 'Tesoreria'
- description: 'Gestiona los tipos de gratuidad'
- Rutas: app_maintainers_treasury_gratuity_type_{create,edit,delete,export}
```

---

## FASE 2: Entidades con Campos Extra

Entidades con campos adicionales (codigo, checkboxes) pero sin relaciones FK complejas.

---

### 2.1 CurrencyType (TipoMoneda)

**Legacy:** `TipoMoneda.php` - Campos: id, nombre, esClp, idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/CurrencyType.php siguiendo el patron de Gender.php.

- Tabla: currency_type
- Campos:
  - id: integer, PK, auto-increment
  - name: string(100), NOT NULL, Assert\NotBlank, Assert\Length(max=100)
  - isClp: boolean, default false (indica si es Peso Chileno)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: CurrencyTypeRepository
- Namespace: App\Entity\Tenant

RESTRICCIONES:
- NO agregar campo idEmpresa
- El campo isClp es un boolean simple
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/CurrencyTypeRepository.php siguiendo el patron de GenderRepository.

- Entity: CurrencyType
- Metodos:
  - findAllActive(): orderBy name ASC
  - findClp(): ?CurrencyType - busca el registro donde isClp=true
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Treasury/CurrencyTypeType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ej: Peso Chileno, Dolar, UF', class='form-control'
- isClp: CheckboxType, label='Es CLP (Peso Chileno)', required=false, class='form-check-input'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: CurrencyType
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Treasury/CurrencyTypeController.php siguiendo el patron de GenderController.

- Route base: /maintainers/treasury/currency-type
- Prefijo rutas: app_maintainers_treasury_currency_type_
- Inyectar: CurrencyTypeRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('ct')->orderBy('ct.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isClp' => 'CLP', 'isActive' => 'Estado']
- getFormType(): CurrencyTypeType::class
- createNewEntity(): new CurrencyType()
- getTemplatePath(): 'maintainers/treasury/currency_type/index.html.twig'
- getPageTitle(): default=>'Tipos de Moneda'
- Export columns: ['name', 'isClp', 'isActive'], headers: ['Nombre', 'Es CLP', 'Activo'], filename: 'tipos_moneda_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/treasury/currency_type/index.html.twig siguiendo el patron de gender/index.html.twig.

- page_title: 'Tipos de Moneda'
- icon: 'bx-money'
- breadcrumb_section: 'Tesoreria'
- description: 'Gestiona los tipos de moneda del sistema'
- Rutas: app_maintainers_treasury_currency_type_{create,edit,delete,export}
```

---

### 2.2 TransferIndicator (IndicadorTraslado)

**Legacy:** `IndicadorTraslado.php` - Campos: id, codigo, nombre, idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/TransferIndicator.php siguiendo el patron de Gender.php.

- Tabla: transfer_indicator
- Campos:
  - id: integer, PK, auto-increment
  - code: integer, NOT NULL, Assert\NotNull
  - name: string(100), NOT NULL, Assert\NotBlank, Assert\Length(max=100)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: TransferIndicatorRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/TransferIndicatorRepository.php siguiendo el patron de GenderRepository.

- Entity: TransferIndicator
- Metodos:
  - findAllActive(): orderBy name ASC
  - findByCode(int $code): ?TransferIndicator
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Treasury/TransferIndicatorType.php siguiendo el patron de GenderType.php.

Campos:
- code: IntegerType, label='Codigo', required, placeholder='Ingrese codigo', class='form-control'
- name: TextType, label='Nombre', required, placeholder='Ingrese nombre', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: TransferIndicator
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Treasury/TransferIndicatorController.php siguiendo el patron de GenderController.

- Route base: /maintainers/treasury/transfer-indicator
- Prefijo rutas: app_maintainers_treasury_transfer_indicator_
- Inyectar: TransferIndicatorRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('ti')->orderBy('ti.id', 'DESC')
- getColumns(): ['code' => 'Código', 'name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): TransferIndicatorType::class
- createNewEntity(): new TransferIndicator()
- getTemplatePath(): 'maintainers/treasury/transfer_indicator/index.html.twig'
- getPageTitle(): default=>'Indicadores de Traslado'
- Export columns: ['code', 'name', 'isActive'], headers: ['Codigo', 'Nombre', 'Activo'], filename: 'indicadores_traslado_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/treasury/transfer_indicator/index.html.twig.

- page_title: 'Indicadores de Traslado'
- icon: 'bx-transfer'
- breadcrumb_section: 'Tesoreria'
- description: 'Gestiona los indicadores de traslado'
- Rutas: app_maintainers_treasury_transfer_indicator_{create,edit,delete,export}
```

---

### 2.3 BillingPaymentMethod (FormaPagoFacturacion)

**Legacy:** `FormaPagoFacturacion.php` - Campos: id, codigo, nombre, esEfectivo, idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/BillingPaymentMethod.php siguiendo el patron de Gender.php.

- Tabla: billing_payment_method
- Campos:
  - id: integer, PK, auto-increment
  - code: string(3), NOT NULL, Assert\NotBlank, Assert\Length(max=3)
  - name: string(100), NOT NULL, Assert\NotBlank, Assert\Length(max=100)
  - isCash: boolean, default false (legacy: esEfectivo)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: BillingPaymentMethodRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/BillingPaymentMethodRepository.php siguiendo el patron de GenderRepository.

- Entity: BillingPaymentMethod
- Metodos:
  - findAllActive(): orderBy name ASC
  - findByCode(string $code): ?BillingPaymentMethod
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Treasury/BillingPaymentMethodType.php siguiendo el patron de GenderType.php.

Campos:
- code: TextType, label='Codigo', required, placeholder='Ej: EFE', class='form-control', maxlength=3
- name: TextType, label='Nombre', required, placeholder='Ingrese nombre', class='form-control'
- isCash: CheckboxType, label='Es Efectivo', required=false, class='form-check-input'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: BillingPaymentMethod
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Treasury/BillingPaymentMethodController.php siguiendo el patron de GenderController.

- Route base: /maintainers/treasury/billing-payment-method
- Prefijo rutas: app_maintainers_treasury_billing_payment_method_
- Inyectar: BillingPaymentMethodRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('bpm')->orderBy('bpm.id', 'DESC')
- getColumns(): ['code' => 'Código', 'name' => 'Nombre', 'isCash' => 'Efectivo', 'isActive' => 'Estado']
- getFormType(): BillingPaymentMethodType::class
- createNewEntity(): new BillingPaymentMethod()
- getTemplatePath(): 'maintainers/treasury/billing_payment_method/index.html.twig'
- getPageTitle(): default=>'Formas de Pago Facturacion'
- Export columns: ['code', 'name', 'isCash', 'isActive'], headers: ['Codigo', 'Nombre', 'Es Efectivo', 'Activo']
- filename: 'formas_pago_facturacion_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/treasury/billing_payment_method/index.html.twig.

- page_title: 'Formas de Pago Facturacion'
- icon: 'bx-receipt'
- breadcrumb_section: 'Tesoreria'
- description: 'Gestiona las formas de pago para facturacion'
- Rutas: app_maintainers_treasury_billing_payment_method_{create,edit,delete,export}
```

---

### 2.4 PaymentCondition (CondicionPago)

**Legacy:** `CondicionPago.php` - Campos: id, nombre, codigoInterfaz, plazoMaximo, esAlDia, idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/PaymentCondition.php siguiendo el patron de Gender.php.

- Tabla: payment_condition
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank
  - interfaceCode: string(10), NOT NULL, Assert\NotBlank, Assert\Length(max=10)
  - maxTerm: integer, NOT NULL, default 0 (legacy: plazoMaximo)
  - isUpToDate: boolean, default false (legacy: esAlDia)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: PaymentConditionRepository

Mapeo columnas:
- interfaceCode -> column: interface_code
- maxTerm -> column: max_term
- isUpToDate -> column: is_up_to_date
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/PaymentConditionRepository.php siguiendo el patron de GenderRepository.

- Entity: PaymentCondition
- Metodo findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Treasury/PaymentConditionType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese condicion de pago', class='form-control'
- interfaceCode: TextType, label='Codigo Interfaz', required, placeholder='Codigo', class='form-control', maxlength=10
- maxTerm: IntegerType, label='Plazo Maximo (dias)', required, class='form-control', attr=['min'=>0]
- isUpToDate: CheckboxType, label='Es Al Dia', required=false, class='form-check-input'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: PaymentCondition
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Treasury/PaymentConditionController.php siguiendo el patron de GenderController.

- Route base: /maintainers/treasury/payment-condition
- Prefijo rutas: app_maintainers_treasury_payment_condition_
- Inyectar: PaymentConditionRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('pc')->orderBy('pc.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'interfaceCode' => 'Cód. Interfaz', 'maxTerm' => 'Plazo Máx.', 'isUpToDate' => 'Al Día', 'isActive' => 'Estado']
- getFormType(): PaymentConditionType::class
- createNewEntity(): new PaymentCondition()
- getTemplatePath(): 'maintainers/treasury/payment_condition/index.html.twig'
- getPageTitle(): default=>'Condiciones de Pago'
- Export columns: ['name', 'interfaceCode', 'maxTerm', 'isUpToDate', 'isActive']
- Export headers: ['Nombre', 'Codigo Interfaz', 'Plazo Maximo', 'Es Al Dia', 'Activo']
- filename: 'condiciones_pago_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/treasury/payment_condition/index.html.twig.

- page_title: 'Condiciones de Pago'
- icon: 'bx-calendar-check'
- breadcrumb_section: 'Tesoreria'
- description: 'Gestiona las condiciones de pago'
- Rutas: app_maintainers_treasury_payment_condition_{create,edit,delete,export}
```

---

## FASE 3: Entidades con Relaciones

Entidades que tienen FK a otras entidades. Crear las dependencias ANTES.

### DEPENDENCIAS PREVIAS

Antes de esta fase, asegurar que existan:
- **Branch** (Sucursal) -> YA EXISTE en el proyecto
- **DifferenceDirection** (TipoSentidoDiferencia) -> CREAR como dependencia
- **CreditCardType** -> Creado en Fase 1
- **GratuityType** -> Creado en Fase 1

#### Prompt Copilot - Dependencia: DifferenceDirection

```
Crea src/Entity/Tenant/DifferenceDirection.php siguiendo el patron de Gender.php.

- Tabla: difference_direction
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set
  - updatedAt: datetime, nullable
- Repository: DifferenceDirectionRepository

Crear tambien:
- src/Repository/Tenant/DifferenceDirectionRepository.php (findAllActive)
- src/Form/Maintainers/Treasury/DifferenceDirectionType.php (name + isActive)
- src/Controller/Maintainers/Treasury/DifferenceDirectionController.php
  - Route: /maintainers/treasury/difference-direction
  - Prefijo: app_maintainers_treasury_difference_direction_
  - Titulo: 'Sentidos de Diferencia'
- templates/maintainers/treasury/difference_direction/index.html.twig
  - icon: 'bx-sort-alt-2', breadcrumb: 'Tesoreria'
```

---

### 3.1 Bank (Banco)

**Legacy:** `Banco.php` - Campos: id, rut, nombre, cuentaCorriente, idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/Bank.php siguiendo el patron de Gender.php.

- Tabla: bank
- Campos:
  - id: integer, PK, auto-increment
  - rut: string(50), nullable (RUT del banco)
  - name: string(255), NOT NULL, Assert\NotBlank
  - currentAccount: bigint, nullable (legacy: cuentaCorriente, column: current_account)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set
  - updatedAt: datetime, nullable
- Repository: BankRepository

Mapeo columnas:
- currentAccount -> column: current_account, type: bigint
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/BankRepository.php siguiendo el patron de GenderRepository.

- Entity: Bank
- Metodos:
  - findAllActive(): orderBy name ASC
  - findByRut(string $rut): ?Bank
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Treasury/BankType.php siguiendo el patron de GenderType.php.

Campos:
- rut: TextType, label='RUT', required=false, placeholder='Ej: 12345678-9', class='form-control'
- name: TextType, label='Nombre', required, placeholder='Nombre del banco', class='form-control'
- currentAccount: IntegerType, label='Cuenta Corriente', required=false, class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: Bank
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Treasury/BankController.php siguiendo el patron de GenderController.

- Route base: /maintainers/treasury/bank
- Prefijo rutas: app_maintainers_treasury_bank_
- Inyectar: BankRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('b')->orderBy('b.id', 'DESC')
- getColumns(): ['rut' => 'RUT', 'name' => 'Nombre', 'currentAccount' => 'Cuenta Corriente', 'isActive' => 'Estado']
- getFormType(): BankType::class
- createNewEntity(): new Bank()
- getTemplatePath(): 'maintainers/treasury/bank/index.html.twig'
- getPageTitle(): default=>'Bancos'
- Export columns: ['rut', 'name', 'currentAccount', 'isActive']
- Export headers: ['RUT', 'Nombre', 'Cuenta Corriente', 'Activo']
- filename: 'bancos_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/treasury/bank/index.html.twig.

- page_title: 'Bancos'
- icon: 'bx-building-house'
- breadcrumb_section: 'Tesoreria'
- description: 'Gestiona los bancos del sistema'
- Rutas: app_maintainers_treasury_bank_{create,edit,delete,export}
```

---

### 3.2 DifferenceType (TipoDiferencia)

**Legacy:** `TipoDiferencia.php` - Campos: id, nombre, descripcion, idTipoSentidoDiferencia, idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/DifferenceType.php siguiendo el patron de Gender.php pero CON relacion ManyToOne.

- Tabla: difference_type
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank
  - description: text, nullable
  - differenceDirection: ManyToOne -> DifferenceDirection, nullable, JoinColumn(name='difference_direction_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set
  - updatedAt: datetime, nullable
- Repository: DifferenceTypeRepository

IMPORTANTE: La relacion ManyToOne a DifferenceDirection debe usar:
#[ORM\ManyToOne(targetEntity: DifferenceDirection::class)]
#[ORM\JoinColumn(name: 'difference_direction_id', nullable: true)]
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Treasury/DifferenceTypeType.php.

Seguir el patron de MedicalServiceType.php para campos con EntityType (relaciones).

Campos:
- name: TextType, label='Nombre', required, class='form-control'
- description: TextareaType, label='Descripcion', required=false, class='form-control', attr=['rows'=>3]
- differenceDirection: EntityType, class=DifferenceDirection::class, label='Sentido Diferencia',
  choice_label='name', placeholder='Seleccione...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: DifferenceType
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Treasury/DifferenceTypeController.php siguiendo el patron de GenderController.

- Route base: /maintainers/treasury/difference-type
- Prefijo rutas: app_maintainers_treasury_difference_type_
- Inyectar: DifferenceTypeRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('dt')->leftJoin('dt.differenceDirection', 'dd')->orderBy('dt.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'description' => 'Descripción', 'differenceDirection.name' => 'Sentido', 'isActive' => 'Estado']
- getFormType(): DifferenceTypeType::class
- createNewEntity(): new DifferenceType()
- getTemplatePath(): 'maintainers/treasury/difference_type/index.html.twig'
- getPageTitle(): default=>'Tipos de Diferencia'
- Export columns: ['name', 'description', 'differenceDirection.name', 'isActive']
- Export headers: ['Nombre', 'Descripcion', 'Sentido', 'Activo']
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/treasury/difference_type/index.html.twig.

- page_title: 'Tipos de Diferencia'
- icon: 'bx-error-alt'
- breadcrumb_section: 'Tesoreria'
- description: 'Gestiona los tipos de diferencia'
- Rutas: app_maintainers_treasury_difference_type_{create,edit,delete,export}
```

---

### 3.3 DifferenceReason (MotivoDiferencia)

**Legacy:** `MotivoDiferencia.php` - Campos: id, nombre, idTipoSentidoDiferencia, idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/DifferenceReason.php.

- Tabla: difference_reason
- Campos:
  - id: integer, PK, auto-increment
  - name: string(50), NOT NULL, Assert\NotBlank, Assert\Length(max=50)
  - differenceDirection: ManyToOne -> DifferenceDirection, nullable, JoinColumn(name='difference_direction_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set
  - updatedAt: datetime, nullable
- Repository: DifferenceReasonRepository
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Treasury/DifferenceReasonType.php.

Campos:
- name: TextType, label='Nombre', required, class='form-control'
- differenceDirection: EntityType, class=DifferenceDirection::class, label='Sentido Diferencia',
  choice_label='name', placeholder='Seleccione...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: DifferenceReason
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Treasury/DifferenceReasonController.php siguiendo el patron de GenderController.

- Route base: /maintainers/treasury/difference-reason
- Prefijo rutas: app_maintainers_treasury_difference_reason_
- getData(): createQueryBuilder('dr')->leftJoin('dr.differenceDirection', 'dd')->orderBy('dr.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'differenceDirection.name' => 'Sentido', 'isActive' => 'Estado']
- getFormType(): DifferenceReasonType::class
- createNewEntity(): new DifferenceReason()
- getTemplatePath(): 'maintainers/treasury/difference_reason/index.html.twig'
- getPageTitle(): default=>'Motivos de Diferencia'
- Export: ['name', 'differenceDirection.name', 'isActive'] headers: ['Nombre', 'Sentido', 'Activo']
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/treasury/difference_reason/index.html.twig.

- page_title: 'Motivos de Diferencia'
- icon: 'bx-comment-error'
- breadcrumb_section: 'Tesoreria'
- description: 'Gestiona los motivos de diferencia'
- Rutas: app_maintainers_treasury_difference_reason_{create,edit,delete,export}
```

---

### 3.4 CreditCard (TarjetaCredito)

**Legacy:** `TarjetaCredito.php` - Campos: id, nombre, abreviacion, idTarjetaCreditoTipo, idEstado

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/CreditCard.php.

- Tabla: credit_card
- Campos:
  - id: integer, PK, auto-increment
  - name: string(50), NOT NULL, Assert\NotBlank, Assert\Length(max=50)
  - abbreviation: string(50), nullable (legacy: abreviacion)
  - creditCardType: ManyToOne -> CreditCardType, nullable, JoinColumn(name='credit_card_type_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set
  - updatedAt: datetime, nullable
- Repository: CreditCardRepository
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Treasury/CreditCardFormType.php.

NOTA: Nombrar CreditCardFormType (no CreditCardType) para evitar conflicto con la entity CreditCardType.

Campos:
- name: TextType, label='Nombre', required, class='form-control'
- abbreviation: TextType, label='Abreviacion', required=false, placeholder='Ej: VISA, MC', class='form-control'
- creditCardType: EntityType, class=CreditCardType::class, label='Tipo de Tarjeta',
  choice_label='name', placeholder='Seleccione tipo...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: CreditCard
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Treasury/CreditCardController.php.

- Route base: /maintainers/treasury/credit-card
- Prefijo rutas: app_maintainers_treasury_credit_card_
- getData(): createQueryBuilder('cc')->leftJoin('cc.creditCardType', 'cct')->orderBy('cc.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'abbreviation' => 'Abreviatura', 'creditCardType.name' => 'Tipo Tarjeta', 'isActive' => 'Estado']
- getFormType(): CreditCardFormType::class
- createNewEntity(): new CreditCard()
- getTemplatePath(): 'maintainers/treasury/credit_card/index.html.twig'
- getPageTitle(): default=>'Tarjetas de Credito'
- Export: ['name', 'abbreviation', 'creditCardType.name', 'isActive']
- headers: ['Nombre', 'Abreviacion', 'Tipo', 'Activo']
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/treasury/credit_card/index.html.twig.

- page_title: 'Tarjetas de Credito'
- icon: 'bx-credit-card-alt'
- breadcrumb_section: 'Tesoreria'
- description: 'Gestiona las tarjetas de credito'
- Rutas: app_maintainers_treasury_credit_card_{create,edit,delete,export}
```

---

### 3.5 GratuityReason (MotivoGratuidad)

**Legacy:** `MotivoGratuidad.php` - Campos: id, nombre, idEstado, idSucursal, idTipoGratuidad

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/GratuityReason.php.

- Tabla: gratuity_reason
- Campos:
  - id: integer, PK, auto-increment
  - name: string(50), NOT NULL, Assert\NotBlank, Assert\Length(max=50)
  - branch: ManyToOne -> Branch, nullable, JoinColumn(name='branch_id')
  - gratuityType: ManyToOne -> GratuityType, nullable, JoinColumn(name='gratuity_type_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set
  - updatedAt: datetime, nullable
- Repository: GratuityReasonRepository
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Treasury/GratuityReasonType.php.

Campos:
- name: TextType, label='Nombre', required, class='form-control'
- gratuityType: EntityType, class=GratuityType::class, label='Tipo de Gratuidad',
  choice_label='name', placeholder='Seleccione...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- branch: EntityType, class=Branch::class, label='Sucursal',
  choice_label='name', placeholder='Seleccione...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: GratuityReason
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Treasury/GratuityReasonController.php.

- Route base: /maintainers/treasury/gratuity-reason
- Prefijo rutas: app_maintainers_treasury_gratuity_reason_
- getData(): createQueryBuilder('gr')->leftJoin('gr.gratuityType','gt')->leftJoin('gr.branch','b')->orderBy('gr.id','DESC')
- getColumns(): ['name' => 'Nombre', 'gratuityType.name' => 'Tipo Gratuidad', 'branch.name' => 'Sucursal', 'isActive' => 'Estado']
- getFormType(): GratuityReasonType::class
- createNewEntity(): new GratuityReason()
- getTemplatePath(): 'maintainers/treasury/gratuity_reason/index.html.twig'
- getPageTitle(): default=>'Motivos de Gratuidad'
- Export: ['name', 'gratuityType.name', 'branch.name', 'isActive']
- headers: ['Nombre', 'Tipo Gratuidad', 'Sucursal', 'Activo']
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/treasury/gratuity_reason/index.html.twig.

- page_title: 'Motivos de Gratuidad'
- icon: 'bx-gift'
- breadcrumb_section: 'Tesoreria'
- description: 'Gestiona los motivos de gratuidad por sucursal'
- Rutas: app_maintainers_treasury_gratuity_reason_{create,edit,delete,export}
```

---

### 3.6 DocumentType (TipoDocumento)

**Legacy:** `TipoDocumento.php` - Campos: id, codigoSii, nombre, esDte, esLogistica, idEstado, idEmpresa

**NOTA:** En legacy el ID no es auto-increment. En la migracion lo haremos auto-increment.

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/DocumentType.php.

- Tabla: document_type
- Campos:
  - id: integer, PK, auto-increment
  - siiCode: string(3), nullable, column: sii_code (legacy: codigoSii)
  - name: string(70), NOT NULL, Assert\NotBlank, Assert\Length(max=70)
  - isDte: boolean, default false, column: is_dte (Documento Tributario Electronico)
  - isLogistics: boolean, default false, column: is_logistics (legacy: esLogistica)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set
  - updatedAt: datetime, nullable
- Repository: DocumentTypeRepository
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Treasury/DocumentTypeType.php.

Campos:
- siiCode: TextType, label='Codigo SII', required=false, placeholder='Ej: 033', class='form-control', maxlength=3
- name: TextType, label='Nombre', required, class='form-control'
- isDte: CheckboxType, label='Es DTE', required=false, class='form-check-input'
- isLogistics: CheckboxType, label='Es Logistica', required=false, class='form-check-input'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: DocumentType
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Treasury/DocumentTypeController.php.

- Route base: /maintainers/treasury/document-type
- Prefijo rutas: app_maintainers_treasury_document_type_
- getData(): createQueryBuilder('dt')->orderBy('dt.id', 'DESC')
- getColumns(): ['siiCode' => 'Código SII', 'name' => 'Nombre', 'isDte' => 'DTE', 'isLogistics' => 'Logística', 'isActive' => 'Estado']
- getFormType(): DocumentTypeType::class
- createNewEntity(): new DocumentType()
- getTemplatePath(): 'maintainers/treasury/document_type/index.html.twig'
- getPageTitle(): default=>'Tipos de Documento'
- Export: ['siiCode', 'name', 'isDte', 'isLogistics', 'isActive']
- headers: ['Codigo SII', 'Nombre', 'Es DTE', 'Es Logistica', 'Activo']
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/treasury/document_type/index.html.twig.

- page_title: 'Tipos de Documento'
- icon: 'bx-file'
- breadcrumb_section: 'Tesoreria'
- description: 'Gestiona los tipos de documento tributario'
- Rutas: app_maintainers_treasury_document_type_{create,edit,delete,export}
```

---

### 3.7 CashRegisterLocation (UbicacionCaja)

**Legacy:** `UbicacionCaja.php` - Campos: id, nombre, descripcion, idEstado, idSucursal

**NOTA:** En legacy los controllers de CRUD estaban incompletos (solo index). Aqui se crea completo.

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/CashRegisterLocation.php.

- Tabla: cash_register_location
- Campos:
  - id: integer, PK, auto-increment
  - name: string(50), NOT NULL, Assert\NotBlank, Assert\Length(max=50)
  - description: text, nullable
  - branch: ManyToOne -> Branch, nullable, JoinColumn(name='branch_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set
  - updatedAt: datetime, nullable
- Repository: CashRegisterLocationRepository
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Treasury/CashRegisterLocationType.php.

Campos:
- name: TextType, label='Nombre', required, class='form-control'
- description: TextareaType, label='Descripcion', required=false, class='form-control', attr=['rows'=>3]
- branch: EntityType, class=Branch::class, label='Sucursal',
  choice_label='name', placeholder='Seleccione sucursal...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: CashRegisterLocation
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Treasury/CashRegisterLocationController.php.

- Route base: /maintainers/treasury/cash-register-location
- Prefijo rutas: app_maintainers_treasury_cash_register_location_
- getData(): createQueryBuilder('crl')->leftJoin('crl.branch','b')->orderBy('crl.id','DESC')
- getColumns(): ['name', 'description', 'branch.name', 'isActive']
- getFormType(): CashRegisterLocationType::class
- createNewEntity(): new CashRegisterLocation()
- getTemplatePath(): 'maintainers/treasury/cash_register_location/index.html.twig'
- getPageTitle(): default=>'Ubicaciones de Caja'
- Export: ['name', 'description', 'branch.name', 'isActive']
- headers: ['Nombre', 'Descripcion', 'Sucursal', 'Activo']
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/treasury/cash_register_location/index.html.twig.

- page_title: 'Ubicaciones de Caja'
- icon: 'bx-map-pin'
- breadcrumb_section: 'Tesoreria'
- description: 'Gestiona las ubicaciones de caja por sucursal'
- Rutas: app_maintainers_treasury_cash_register_location_{create,edit,delete,export}
```

---

## FASE 4: Entidad Compleja

### 4.1 PaymentMethod (FormaPago)

**Legacy:** `FormaPago.php` - 14 campos incluyendo auto-referencia, 5 checkboxes, validaciones de negocio

**DEPENDENCIA:** Crear primero PaymentMethodType (FormaPagoTipo)

#### Prompt Copilot - Dependencia: PaymentMethodType

```
Crea src/Entity/Tenant/PaymentMethodType.php siguiendo el patron de Gender.php.

- Tabla: payment_method_type
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set
  - updatedAt: datetime, nullable
- Repository: PaymentMethodTypeRepository

Crear tambien:
- src/Repository/Tenant/PaymentMethodTypeRepository.php (findAllActive)
- src/Form/Maintainers/Treasury/PaymentMethodTypeType.php (name + isActive)
- src/Controller/Maintainers/Treasury/PaymentMethodTypeController.php
  - Route: /maintainers/treasury/payment-method-type
  - Prefijo: app_maintainers_treasury_payment_method_type_
  - Titulo: 'Tipos de Forma de Pago'
- templates/maintainers/treasury/payment_method_type/index.html.twig
  - icon: 'bx-category', breadcrumb: 'Tesoreria'
```

#### Prompt Copilot - Entity PaymentMethod

```
Crea src/Entity/Tenant/PaymentMethod.php. Esta es la entidad MAS COMPLEJA de Tesoreria.

- Tabla: payment_method
- Campos:
  - id: integer, PK, auto-increment
  - code: integer, nullable (legacy: codigo)
  - name: string(40), NOT NULL, Assert\NotBlank, Assert\Length(max=40)
  - issuesReceipt: boolean, default false, column: issues_receipt (legacy: emiteBoleta)
  - isGuarantee: boolean, default false, column: is_guarantee (legacy: garantia)
  - isProfessionalPayment: boolean, default false, column: is_professional_payment (legacy: pagoProfesional)
  - isWebPayment: boolean, default false, column: is_web_payment (legacy: pagoWeb)
  - documentTypeCode: string(4), nullable, column: document_type_code (legacy: tipoDocumento)
  - accountingCode: integer, nullable, column: accounting_code (legacy: cuentaContable)
  - visibleInCashRegister: boolean, default false, column: visible_in_cash_register (legacy: verEnCaja)
  - parent: ManyToOne -> PaymentMethod (SELF), nullable, JoinColumn(name='parent_id')
  - paymentMethodType: ManyToOne -> PaymentMethodType, nullable, JoinColumn(name='payment_method_type_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set
  - updatedAt: datetime, nullable
  - children: OneToMany -> PaymentMethod, mappedBy='parent' (NO cascade, solo lectura)
- Repository: PaymentMethodRepository

IMPORTANTE:
- La relacion parent es AUTO-REFERENCIA (ManyToOne a si mismo)
- children es la inversa de parent (OneToMany, mappedBy='parent')
- NO agregar campo idEmpresa (multi-tenancy por Hakam)
```

#### Prompt Copilot - Repository PaymentMethod

```
Crea src/Repository/Tenant/PaymentMethodRepository.php.

- Entity: PaymentMethod
- Metodos:
  - findAllActive(): orderBy name ASC, where isActive=true
  - findRootMethods(): array - donde parent IS NULL y isActive=true
  - findByPaymentMethodType(int $typeId): array
```

#### Prompt Copilot - FormType PaymentMethod

```
Crea src/Form/Maintainers/Treasury/PaymentMethodFormType.php.

NOTA: Nombrar PaymentMethodFormType (no PaymentMethodType) para evitar conflicto con entity PaymentMethodType.

Campos:
- name: TextType, label='Nombre', required, class='form-control'
- code: IntegerType, label='Codigo', required=false, class='form-control'
- paymentMethodType: EntityType, class=PaymentMethodType::class, label='Tipo Forma Pago',
  choice_label='name', placeholder='Seleccione...', required=false, class='form-select',
  query_builder: filtrar isActive=true, orderBy name ASC
- documentTypeCode: TextType, label='Tipo Documento', required=false, class='form-control', maxlength=4
- accountingCode: IntegerType, label='Cuenta Contable', required=false, class='form-control'
- parent: EntityType, class=PaymentMethod::class, label='Forma Pago Padre',
  choice_label='name', placeholder='Sin padre (raiz)', required=false, class='form-select',
  query_builder: filtrar isActive=true, orderBy name ASC
- issuesReceipt: CheckboxType, label='Emite Boleta', required=false, class='form-check-input'
- isGuarantee: CheckboxType, label='Garantia', required=false, class='form-check-input'
- isProfessionalPayment: CheckboxType, label='Pago Profesional', required=false, class='form-check-input'
- isWebPayment: CheckboxType, label='Pago Web', required=false, class='form-check-input'
- visibleInCashRegister: CheckboxType, label='Ver en Caja', required=false, class='form-check-input'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: PaymentMethod
```

#### Prompt Copilot - Controller PaymentMethod

```
Crea src/Controller/Maintainers/Treasury/PaymentMethodController.php.

- Route base: /maintainers/treasury/payment-method
- Prefijo rutas: app_maintainers_treasury_payment_method_
- Inyectar: PaymentMethodRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('pm')
    ->leftJoin('pm.paymentMethodType', 'pmt')
    ->leftJoin('pm.parent', 'p')
    ->orderBy('pm.id', 'DESC')
- getColumns(): ['code' => 'Código', 'name' => 'Nombre', 'paymentMethodType.name' => 'Tipo', 'parent.name' => 'Padre', 'visibleInCashRegister' => 'Visible en Caja', 'isActive' => 'Estado']
- getFormType(): PaymentMethodFormType::class
- createNewEntity(): new PaymentMethod()
- getTemplatePath(): 'maintainers/treasury/payment_method/index.html.twig'
- getPageTitle(): default=>'Formas de Pago'
- Export columns: ['code', 'name', 'paymentMethodType.name', 'parent.name', 'issuesReceipt', 'isGuarantee', 'isProfessionalPayment', 'isWebPayment', 'visibleInCashRegister', 'isActive']
- Export headers: ['Codigo', 'Nombre', 'Tipo', 'Padre', 'Emite Boleta', 'Garantia', 'Pago Profesional', 'Pago Web', 'Ver en Caja', 'Activo']
- filename: 'formas_pago_'.date('Y-m-d').'.csv'

NOTA: Las validaciones de negocio del legacy (validarBonoElectronicoUnico, validarGarantiaUnico)
se implementaran como segundo paso si se requieren. Por ahora solo el CRUD basico.
```

#### Prompt Copilot - Template PaymentMethod

```
Crea templates/maintainers/treasury/payment_method/index.html.twig.

- page_title: 'Formas de Pago'
- icon: 'bx-wallet'
- breadcrumb_section: 'Tesoreria'
- description: 'Gestiona las formas de pago del sistema'
- Rutas: app_maintainers_treasury_payment_method_{create,edit,delete,export}
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
1. Dependencias (DifferenceDirection, PaymentMethodType)
2. Fase 1: BusinessTurn, BankAccountType, CreditCardType, GratuityType
3. Fase 2: CurrencyType, TransferIndicator, BillingPaymentMethod, PaymentCondition
4. Fase 3: Bank, DifferenceType, DifferenceReason, CreditCard, GratuityReason, DocumentType, CashRegisterLocation
5. Fase 4: PaymentMethod
6. Migracion BD
7. Registro en Menu (MenuItem)
8. Validacion Multi-Tenant
```

---

## Registro en Menu

Despues de crear todos los controllers, registrar en el sistema de menus.

**Tabla:** `menu_items`
**IMPORTANTE:** La columna `id` NO es auto-increment. Hay que asignarlo manualmente.

### Paso 1: Obtener ID maximo actual y del padre

```sql
SELECT MAX(id) FROM menu_items;
-- Usar el siguiente numero como base (ej: si MAX=53, empezar en 54)

SELECT id FROM menu_items WHERE name = 'mantenedores';
-- Anotar como {MANTENEDORES_ID} (actualmente = 4)
```

### Paso 2: Insertar categoria + 17 mantenedores

Reemplazar los IDs segun corresponda. En este ejemplo:
- `{MANTENEDORES_ID}` = 4 (padre Mantenedores)
- ID base = 54 (siguiente disponible)

```sql
-- Categoria Tesoreria (hijo de Mantenedores)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES (54, 'maintenance_treasury', 'Tesoreria', NULL, 'bx bx-dollar-circle', NULL, 4, 5, true, true, true, '["ROLE_USER"]', NOW());

-- 17 mantenedores (hijos de Tesoreria, parent_id = 54)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES
-- Tipos y catalogos simples
(55, 'bank_account_type', 'Tipos Cuenta Bancaria', 'app_maintainers_treasury_bank_account_type_index', 'bx bx-list-ul', 'maintenance_treasury', 54, 1, true, true, true, '["ROLE_USER"]', NOW()),
(56, 'credit_card_type', 'Tipos Tarjeta Credito', 'app_maintainers_treasury_credit_card_type_index', 'bx bx-list-ul', 'maintenance_treasury', 54, 2, true, true, true, '["ROLE_USER"]', NOW()),
(57, 'gratuity_type', 'Tipos de Gratuidad', 'app_maintainers_treasury_gratuity_type_index', 'bx bx-gift', 'maintenance_treasury', 54, 3, true, true, true, '["ROLE_USER"]', NOW()),
(58, 'currency_type', 'Tipos de Moneda', 'app_maintainers_treasury_currency_type_index', 'bx bx-dollar-circle', 'maintenance_treasury', 54, 4, true, true, true, '["ROLE_USER"]', NOW()),
(59, 'document_type', 'Tipos de Documento', 'app_maintainers_treasury_document_type_index', 'bx bx-file', 'maintenance_treasury', 54, 5, true, true, true, '["ROLE_USER"]', NOW()),
(60, 'payment_method_type', 'Tipos Forma de Pago', 'app_maintainers_treasury_payment_method_type_index', 'bx bx-category', 'maintenance_treasury', 54, 6, true, true, true, '["ROLE_USER"]', NOW()),
(61, 'difference_direction', 'Sentidos de Diferencia', 'app_maintainers_treasury_difference_direction_index', 'bx bx-sort-alt-2', 'maintenance_treasury', 54, 7, true, true, true, '["ROLE_USER"]', NOW()),
-- Entidades principales
(62, 'bank', 'Bancos', 'app_maintainers_treasury_bank_index', 'bx bx-building-house', 'maintenance_treasury', 54, 8, true, true, true, '["ROLE_USER"]', NOW()),
(63, 'credit_card', 'Tarjetas de Credito', 'app_maintainers_treasury_credit_card_index', 'bx bx-credit-card', 'maintenance_treasury', 54, 9, true, true, true, '["ROLE_USER"]', NOW()),
(64, 'transfer_indicator', 'Indicadores de Traslado', 'app_maintainers_treasury_transfer_indicator_index', 'bx bx-transfer', 'maintenance_treasury', 54, 10, true, true, true, '["ROLE_USER"]', NOW()),
(65, 'payment_condition', 'Condiciones de Pago', 'app_maintainers_treasury_payment_condition_index', 'bx bx-calendar-check', 'maintenance_treasury', 54, 11, true, true, true, '["ROLE_USER"]', NOW()),
(66, 'billing_payment_method', 'Formas Pago Facturacion', 'app_maintainers_treasury_billing_payment_method_index', 'bx bx-receipt', 'maintenance_treasury', 54, 12, true, true, true, '["ROLE_USER"]', NOW()),
(67, 'payment_method', 'Formas de Pago', 'app_maintainers_treasury_payment_method_index', 'bx bx-wallet', 'maintenance_treasury', 54, 13, true, true, true, '["ROLE_USER"]', NOW()),
-- Diferencias
(68, 'difference_type', 'Tipos de Diferencia', 'app_maintainers_treasury_difference_type_index', 'bx bx-error-alt', 'maintenance_treasury', 54, 14, true, true, true, '["ROLE_USER"]', NOW()),
(69, 'difference_reason', 'Motivos de Diferencia', 'app_maintainers_treasury_difference_reason_index', 'bx bx-comment-error', 'maintenance_treasury', 54, 15, true, true, true, '["ROLE_USER"]', NOW()),
-- Gratuidad y ubicaciones
(70, 'gratuity_reason', 'Motivos de Gratuidad', 'app_maintainers_treasury_gratuity_reason_index', 'bx bx-gift', 'maintenance_treasury', 54, 16, true, true, true, '["ROLE_USER"]', NOW()),
(71, 'cash_register_location', 'Ubicaciones de Caja', 'app_maintainers_treasury_cash_register_location_index', 'bx bx-map-pin', 'maintenance_treasury', 54, 17, true, true, true, '["ROLE_USER"]', NOW());
```

### Paso 3: Limpiar cache de menu

```bash
php bin/console cache:clear
```

### Rollback (en caso de necesitar borrar)

```sql
DELETE FROM menu_items WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintenance_treasury');
DELETE FROM menu_items WHERE name = 'maintenance_treasury';
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
| Entities | 18 | src/Entity/Tenant/ |
| Repositories | 18 | src/Repository/Tenant/ |
| FormTypes | 18 | src/Form/Maintainers/Treasury/ |
| Controllers | 18 | src/Controller/Maintainers/Treasury/ |
| Templates | 18 | templates/maintainers/treasury/ |
| **TOTAL** | **90 archivos** | |

(16 mantenedores + 2 dependencias = 18 entidades)
