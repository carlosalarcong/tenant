# Plan de Migracion - Categoria Logistica

## Resumen Ejecutivo

**Categoria:** MantenedorLogistica
**Origen Legacy:** `melisa_prod/src/Rebsol/MantenedoresBundle/Controller/_Default/MantenedorMaestro/MantenedorEmpresa/MantenedorLogistica/`
**Destino Nuevo:** `melisa_tenant/src/Controller/Maintainers/Logistics/`
**Total Entidades:** 10 mantenedores nuevos (3 ya existentes descartadas)
**Complejidad Global:** Alta (entidad Article con 25+ campos y multiples FK)

---

## Inventario Completo

### Entidades a Migrar

| # | Legacy (ES) | Nuevo (EN) | Tabla Nueva | Complejidad | Fase |
|---|-------------|------------|-------------|-------------|------|
| 1 | TipoCondicionProducto | ProductConditionType | product_condition_type | Simple | 1 |
| 2 | TipoEgreso | ArticleOutflowType | article_outflow_type | Simple | 1 |
| 3 | MotivoAjusteInventario | InventoryAdjustmentReason | inventory_adjustment_reason | Simple | 1 |
| 4 | TipoDespacho | DispatchType | dispatch_type | Simple+ | 2 |
| 5 | TipoArticulo | ArticleType | article_type | Simple+ | 2 |
| 6 | PieFirma | SignatureFooter | signature_footer | Moderada | 2 |
| 7 | RelEspecialidadBodega | WarehouseSpecialty | warehouse_specialty | Moderada | 3 |
| 8 | RelArticuloProveedor | ArticleSupplier | article_supplier | Moderada | 3 |
| 9 | RelArticuloCentroCosto | ArticleWarehouse | article_warehouse | Moderada+ | 3 |
| 10 | Articulo | Article | article | Compleja | 4 |

### Dependencias Externas (ya existen en el proyecto)

| Entidad Existente (EN) | Ubicacion | Requerido Por |
|-------------------------|-----------|---------------|
| Warehouse | src/Entity/Tenant/Warehouse.php | ArticleType, WarehouseSpecialty, ArticleWarehouse |
| WarehouseMedicalService | src/Entity/Tenant/WarehouseMedicalService.php | (relacion ya migrada) |
| Branch | src/Entity/Tenant/Branch.php | SignatureFooter |
| Specialty | src/Entity/Tenant/Specialty.php | WarehouseSpecialty |
| SubCompany | src/Entity/Tenant/SubCompany.php | Article |
| BudgetItem | src/Entity/Tenant/BudgetItem.php | Article |

### Entidades Descartadas

| Legacy | Razon |
|--------|-------|
| Bodega | Ya existe como Warehouse en src/Entity/Tenant/Warehouse.php |
| SubBodega | Manejado mediante auto-referencia parentWarehouse en Warehouse |
| RelBodegaAccionClinica | Ya existe como WarehouseMedicalService en src/Entity/Tenant/WarehouseMedicalService.php |
| PrestacionCentroCosto | Relacion compleja de prestaciones, no es un mantenedor simple |
| UsuarioCentroCosto | Relacion de usuarios con centros de costo, no es un mantenedor simple |
| ArticuloUnidadAdministracion | Relacion compleja con unidades de administracion, fuera de alcance de mantenedor |

---

## Patron a Seguir (Referencia)

Todos los archivos nuevos deben seguir EXACTAMENTE el patron existente:

| Tipo | Ejemplo Referencia | Ubicacion |
|------|-------------------|-----------|
| Entity | `src/Entity/Tenant/Gender.php` | `src/Entity/Tenant/` |
| Repository | `src/Repository/Tenant/GenderRepository.php` | `src/Repository/Tenant/` |
| FormType | `src/Form/Maintainers/Personal/GenderType.php` | `src/Form/Maintainers/Logistics/` |
| Controller | `src/Controller/Maintainers/Basic/GenderController.php` | `src/Controller/Maintainers/Logistics/` |
| Template | `templates/maintainers/basic/gender/index.html.twig` | `templates/maintainers/logistics/` |

### Herencia de Controllers

```
AbstractController
  -> AbstractTenantAwareController
    -> AbstractMantenedorController (Template Method Pattern)
      -> [Tu nuevo controller]
```

### Convenciones de Nombres

- **Rutas:** `app_maintainers_logistics_{entity_snake}_{action}`
- **Ejemplo:** `app_maintainers_logistics_article_type_index`
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

**Beneficios:**
- Columnas y labels juntos (cohesion)
- Auto-documentacion del codigo
- Sin duplicacion de identificadores
- Template mas limpio (sin if/elseif masivos)
- Escalable para metadata futura

**Relaciones:**
```php
'warehouse.name' => 'Bodega',          // Relacion ManyToOne
'branch.name' => 'Sucursal',           // Relacion ManyToOne
'articleType.name' => 'Tipo Articulo',  // Relacion ManyToOne
'specialty.name' => 'Especialidad',     // Relacion ManyToOne
```

---

## FASE 1: Entidades Simples

Entidades con solo campo `name` + `isActive`. CRUD basico sin relaciones.

---

### 1.1 ProductConditionType (TipoCondicionProducto)

**Legacy:** `TipoCondicionProducto.php` - Campos: id, nombre(100), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea el archivo src/Entity/Tenant/ProductConditionType.php siguiendo EXACTAMENTE el patron de src/Entity/Tenant/Gender.php.

Especificaciones:
- Tabla: product_condition_type
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
- Repository: ProductConditionTypeRepository

RESTRICCIONES:
- NO agregar campo idEmpresa (multi-tenancy por Hakam)
- NO agregar relaciones adicionales
- Usar PHP 8.2 attributes (#[ORM\...])
- Usar Assert constraints de Symfony
```

#### Prompt Copilot - Repository

```
Crea el archivo src/Repository/Tenant/ProductConditionTypeRepository.php siguiendo EXACTAMENTE el patron de src/Repository/Tenant/GenderRepository.php.

- Extends ServiceEntityRepository
- Entity class: ProductConditionType
- Metodos:
  - findAllActive(): array - orderBy name ASC, where isActive=true
- Namespace: App\Repository\Tenant
```

#### Prompt Copilot - FormType

```
Crea el archivo src/Form/Maintainers/Logistics/ProductConditionTypeType.php siguiendo EXACTAMENTE el patron de src/Form/Maintainers/Personal/GenderType.php.

Campos del formulario:
- name: TextType, label='Nombre', required, placeholder='Ingrese condicion de producto', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: ProductConditionType
Namespace: App\Form\Maintainers\Logistics
```

#### Prompt Copilot - Controller

```
Crea el archivo src/Controller/Maintainers/Logistics/ProductConditionTypeController.php siguiendo EXACTAMENTE el patron de src/Controller/Maintainers/Basic/GenderController.php.

Especificaciones:
- Route base: /maintainers/logistics/product-condition-type
- Rutas:
  - GET '' -> index (app_maintainers_logistics_product_condition_type_index)
  - GET/POST '/create' -> create (app_maintainers_logistics_product_condition_type_create)
  - GET/POST '/{id}/edit' -> edit (app_maintainers_logistics_product_condition_type_edit)
  - POST '/{id}/delete' -> delete (app_maintainers_logistics_product_condition_type_delete)
  - GET '/export' -> export (app_maintainers_logistics_product_condition_type_export)

- Inyectar: ProductConditionTypeRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('pct')->orderBy('pct.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): ProductConditionTypeType::class
- createNewEntity(): new ProductConditionType()
- getTemplatePath(): 'maintainers/logistics/product_condition_type/index.html.twig'
- getPageTitle(): 'create'=>'Crear Condicion Producto', 'edit'=>'Editar Condicion Producto', default=>'Condiciones de Producto'

- Export columns: ['name', 'isActive']
- Export headers: ['Nombre', 'Activo']
- Export filename: 'condiciones_producto_'.date('Y-m-d').'.csv'

RESTRICCIONES:
- NO cambiar el patron de AbstractMantenedorController
- NO agregar logica de negocio extra
- Mantener multi-tenant (AbstractTenantAwareController lo maneja)
```

#### Prompt Copilot - Template

```
Crea el archivo templates/maintainers/logistics/product_condition_type/index.html.twig siguiendo EXACTAMENTE el patron de templates/maintainers/basic/gender/index.html.twig.

Variables a configurar:
- page_title: 'Condiciones de Producto'
- icon: 'bx-check-shield'
- breadcrumb_section: 'Logistica'
- description: 'Gestiona las condiciones de producto del sistema'
- create_route: 'app_maintainers_logistics_product_condition_type_create'
- edit_route: 'app_maintainers_logistics_product_condition_type_edit'
- delete_route: 'app_maintainers_logistics_product_condition_type_delete'
- export_route: 'app_maintainers_logistics_product_condition_type_export'

Extends: maintainers/modern_index.html.twig
```

---

### 1.2 ArticleOutflowType (TipoEgreso)

**Legacy:** `TipoEgreso.php` - Campos: id, nombre(100), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea el archivo src/Entity/Tenant/ArticleOutflowType.php siguiendo EXACTAMENTE el patron de src/Entity/Tenant/Gender.php.

Especificaciones:
- Tabla: article_outflow_type
- Campos:
  - id: integer, PK, auto-increment
  - name: string(100), NOT NULL, Assert\NotBlank, Assert\Length(max=100)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable

- Namespace: App\Entity\Tenant
- Repository: ArticleOutflowTypeRepository

RESTRICCIONES:
- NO agregar campo idEmpresa
- Usar PHP 8.2 attributes
```

#### Prompt Copilot - Repository

```
Crea el archivo src/Repository/Tenant/ArticleOutflowTypeRepository.php siguiendo el patron de GenderRepository.php.

- Entity: ArticleOutflowType
- Metodo findAllActive(): orderBy name ASC
- Namespace: App\Repository\Tenant
```

#### Prompt Copilot - FormType

```
Crea el archivo src/Form/Maintainers/Logistics/ArticleOutflowTypeType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese tipo de egreso', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: ArticleOutflowType
Namespace: App\Form\Maintainers\Logistics
```

#### Prompt Copilot - Controller

```
Crea el archivo src/Controller/Maintainers/Logistics/ArticleOutflowTypeController.php siguiendo EXACTAMENTE el patron de GenderController.php.

- Route base: /maintainers/logistics/article-outflow-type
- Prefijo rutas: app_maintainers_logistics_article_outflow_type_
- Inyectar: ArticleOutflowTypeRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('aot')->orderBy('aot.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): ArticleOutflowTypeType::class
- createNewEntity(): new ArticleOutflowType()
- getTemplatePath(): 'maintainers/logistics/article_outflow_type/index.html.twig'
- getPageTitle(): 'create'=>'Crear Tipo Egreso', 'edit'=>'Editar Tipo Egreso', default=>'Tipos de Egreso'
- Export columns: ['name', 'isActive'], headers: ['Nombre', 'Activo'], filename: 'tipos_egreso_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/logistics/article_outflow_type/index.html.twig siguiendo el patron de gender/index.html.twig.

- page_title: 'Tipos de Egreso'
- icon: 'bx-export'
- breadcrumb_section: 'Logistica'
- description: 'Gestiona los tipos de egreso de articulos'
- Rutas: app_maintainers_logistics_article_outflow_type_{create,edit,delete,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 1.3 InventoryAdjustmentReason (MotivoAjusteInventario)

**Legacy:** `MotivoAjusteInventario.php` - Campos: id, nombre(50), idTipoAjuste, idEstado, idEmpresa

**NOTA:** El campo idTipoAjuste del legacy no tiene entidad FK clara. Se migra como campo simple `adjustmentType` (string) para no crear dependencia innecesaria.

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/InventoryAdjustmentReason.php siguiendo el patron de Gender.php.

- Tabla: inventory_adjustment_reason
- Campos:
  - id: integer, PK, auto-increment
  - name: string(50), NOT NULL, Assert\NotBlank, Assert\Length(max=50)
  - adjustmentType: string(50), nullable, column: adjustment_type (legacy: idTipoAjuste, se migra como texto descriptivo)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: InventoryAdjustmentReasonRepository
- Namespace: App\Entity\Tenant
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/InventoryAdjustmentReasonRepository.php siguiendo el patron de GenderRepository.

- Entity: InventoryAdjustmentReason
- Metodo findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Logistics/InventoryAdjustmentReasonType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese motivo de ajuste', class='form-control'
- adjustmentType: TextType, label='Tipo de Ajuste', required=false, placeholder='Ej: Ingreso, Egreso', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: InventoryAdjustmentReason
Namespace: App\Form\Maintainers\Logistics
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Logistics/InventoryAdjustmentReasonController.php siguiendo el patron de GenderController.

- Route base: /maintainers/logistics/inventory-adjustment-reason
- Prefijo rutas: app_maintainers_logistics_inventory_adjustment_reason_
- Inyectar: InventoryAdjustmentReasonRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('iar')->orderBy('iar.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'adjustmentType' => 'Tipo Ajuste', 'isActive' => 'Estado']
- getFormType(): InventoryAdjustmentReasonType::class
- createNewEntity(): new InventoryAdjustmentReason()
- getTemplatePath(): 'maintainers/logistics/inventory_adjustment_reason/index.html.twig'
- getPageTitle(): default=>'Motivos de Ajuste de Inventario'
- Export columns: ['name', 'adjustmentType', 'isActive'], headers: ['Nombre', 'Tipo Ajuste', 'Activo'], filename: 'motivos_ajuste_inventario_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/logistics/inventory_adjustment_reason/index.html.twig siguiendo el patron de gender/index.html.twig.

- page_title: 'Motivos de Ajuste de Inventario'
- icon: 'bx-revision'
- breadcrumb_section: 'Logistica'
- description: 'Gestiona los motivos de ajuste de inventario'
- Rutas: app_maintainers_logistics_inventory_adjustment_reason_{create,edit,delete,export}
```

---

## FASE 2: Entidades con Campos Extra y Relaciones Simples

Entidades con campos adicionales (codigo, checkboxes) y/o una relacion FK simple.

---

### 2.1 DispatchType (TipoDespacho)

**Legacy:** `TipoDespacho.php` - Campos: id, codigo(int), nombre(100), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/DispatchType.php siguiendo el patron de Gender.php.

- Tabla: dispatch_type
- Campos:
  - id: integer, PK, auto-increment
  - code: integer, NOT NULL, Assert\NotNull
  - name: string(100), NOT NULL, Assert\NotBlank, Assert\Length(max=100)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: DispatchTypeRepository
- Namespace: App\Entity\Tenant
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/DispatchTypeRepository.php siguiendo el patron de GenderRepository.

- Entity: DispatchType
- Metodos:
  - findAllActive(): orderBy name ASC
  - findByCode(int $code): ?DispatchType
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Logistics/DispatchTypeType.php siguiendo el patron de GenderType.php.

Campos:
- code: IntegerType, label='Codigo', required, placeholder='Ingrese codigo', class='form-control'
- name: TextType, label='Nombre', required, placeholder='Ingrese tipo de despacho', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: DispatchType
Namespace: App\Form\Maintainers\Logistics
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Logistics/DispatchTypeController.php siguiendo el patron de GenderController.

- Route base: /maintainers/logistics/dispatch-type
- Prefijo rutas: app_maintainers_logistics_dispatch_type_
- Inyectar: DispatchTypeRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('dt')->orderBy('dt.id', 'DESC')
- getColumns(): ['code' => 'Codigo', 'name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): DispatchTypeType::class
- createNewEntity(): new DispatchType()
- getTemplatePath(): 'maintainers/logistics/dispatch_type/index.html.twig'
- getPageTitle(): 'create'=>'Crear Tipo Despacho', 'edit'=>'Editar Tipo Despacho', default=>'Tipos de Despacho'
- Export columns: ['code', 'name', 'isActive'], headers: ['Codigo', 'Nombre', 'Activo'], filename: 'tipos_despacho_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/logistics/dispatch_type/index.html.twig.

- page_title: 'Tipos de Despacho'
- icon: 'bx-package'
- breadcrumb_section: 'Logistica'
- description: 'Gestiona los tipos de despacho'
- Rutas: app_maintainers_logistics_dispatch_type_{create,edit,delete,export}
```

---

### 2.2 ArticleType (TipoArticulo)

**Legacy:** `TipoArticulo.php` - Campos: id, nombre(255), codigo(255), esFarmaco(bool,default 0), idEstado, idBodega(FK->Warehouse)

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/ArticleType.php siguiendo el patron de Gender.php pero CON relacion ManyToOne.

- Tabla: article_type
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - code: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - isPharmaceutical: boolean, default false, column: is_pharmaceutical (legacy: esFarmaco)
  - warehouse: ManyToOne -> Warehouse, nullable, JoinColumn(name='warehouse_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: ArticleTypeRepository
- Namespace: App\Entity\Tenant

IMPORTANTE: La relacion ManyToOne a Warehouse debe usar:
#[ORM\ManyToOne(targetEntity: Warehouse::class)]
#[ORM\JoinColumn(name: 'warehouse_id', nullable: true)]
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/ArticleTypeRepository.php siguiendo el patron de GenderRepository.

- Entity: ArticleType
- Metodos:
  - findAllActive(): orderBy name ASC
  - findByWarehouse(int $warehouseId): array - filtra por warehouse
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Logistics/ArticleTypeType.php.

Seguir el patron de MedicalServiceType.php para campos con EntityType (relaciones).

Campos:
- code: TextType, label='Codigo', required, placeholder='Ingrese codigo', class='form-control'
- name: TextType, label='Nombre', required, placeholder='Ingrese tipo de articulo', class='form-control'
- isPharmaceutical: CheckboxType, label='Es Farmaco', required=false, class='form-check-input'
- warehouse: EntityType, class=Warehouse::class, label='Bodega',
  choice_label='name', placeholder='Seleccione bodega...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: ArticleType
Namespace: App\Form\Maintainers\Logistics
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Logistics/ArticleTypeController.php siguiendo el patron de GenderController.

- Route base: /maintainers/logistics/article-type
- Prefijo rutas: app_maintainers_logistics_article_type_
- Inyectar: ArticleTypeRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('at')->leftJoin('at.warehouse', 'w')->orderBy('at.id', 'DESC')
- getColumns(): ['code' => 'Codigo', 'name' => 'Nombre', 'isPharmaceutical' => 'Farmaco', 'warehouse.name' => 'Bodega', 'isActive' => 'Estado']
- getFormType(): ArticleTypeType::class
- createNewEntity(): new ArticleType()
- getTemplatePath(): 'maintainers/logistics/article_type/index.html.twig'
- getPageTitle(): 'create'=>'Crear Tipo Articulo', 'edit'=>'Editar Tipo Articulo', default=>'Tipos de Articulo'
- Export columns: ['code', 'name', 'isPharmaceutical', 'warehouse.name', 'isActive']
- Export headers: ['Codigo', 'Nombre', 'Es Farmaco', 'Bodega', 'Activo']
- filename: 'tipos_articulo_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/logistics/article_type/index.html.twig.

- page_title: 'Tipos de Articulo'
- icon: 'bx-category-alt'
- breadcrumb_section: 'Logistica'
- description: 'Gestiona los tipos de articulo del inventario'
- Rutas: app_maintainers_logistics_article_type_{create,edit,delete,export}
```

---

### 2.3 SignatureFooter (PieFirma)

**Legacy:** `PieFirma.php` - Campos: id, codigo(int), nombre(100), cargo(100), idEstado, idSucursal(FK->Branch)

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/SignatureFooter.php siguiendo el patron de Gender.php pero CON relacion ManyToOne.

- Tabla: signature_footer
- Campos:
  - id: integer, PK, auto-increment
  - code: integer, NOT NULL, Assert\NotNull
  - name: string(100), NOT NULL, Assert\NotBlank, Assert\Length(max=100)
  - position: string(100), NOT NULL, Assert\NotBlank, Assert\Length(max=100), column: position (legacy: cargo)
  - branch: ManyToOne -> Branch, nullable, JoinColumn(name='branch_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: SignatureFooterRepository
- Namespace: App\Entity\Tenant

IMPORTANTE: La relacion ManyToOne a Branch debe usar:
#[ORM\ManyToOne(targetEntity: Branch::class)]
#[ORM\JoinColumn(name: 'branch_id', nullable: true)]
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/SignatureFooterRepository.php siguiendo el patron de GenderRepository.

- Entity: SignatureFooter
- Metodos:
  - findAllActive(): orderBy name ASC
  - findByBranch(int $branchId): array - filtra por branch
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Logistics/SignatureFooterType.php.

Campos:
- code: IntegerType, label='Codigo', required, placeholder='Ingrese codigo', class='form-control'
- name: TextType, label='Nombre', required, placeholder='Nombre del firmante', class='form-control'
- position: TextType, label='Cargo', required, placeholder='Cargo del firmante', class='form-control'
- branch: EntityType, class=Branch::class, label='Sucursal',
  choice_label='name', placeholder='Seleccione sucursal...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: SignatureFooter
Namespace: App\Form\Maintainers\Logistics
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Logistics/SignatureFooterController.php siguiendo el patron de GenderController.

- Route base: /maintainers/logistics/signature-footer
- Prefijo rutas: app_maintainers_logistics_signature_footer_
- Inyectar: SignatureFooterRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('sf')->leftJoin('sf.branch', 'b')->orderBy('sf.id', 'DESC')
- getColumns(): ['code' => 'Codigo', 'name' => 'Nombre', 'position' => 'Cargo', 'branch.name' => 'Sucursal', 'isActive' => 'Estado']
- getFormType(): SignatureFooterType::class
- createNewEntity(): new SignatureFooter()
- getTemplatePath(): 'maintainers/logistics/signature_footer/index.html.twig'
- getPageTitle(): 'create'=>'Crear Pie de Firma', 'edit'=>'Editar Pie de Firma', default=>'Pies de Firma'
- Export columns: ['code', 'name', 'position', 'branch.name', 'isActive']
- Export headers: ['Codigo', 'Nombre', 'Cargo', 'Sucursal', 'Activo']
- filename: 'pies_firma_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/logistics/signature_footer/index.html.twig.

- page_title: 'Pies de Firma'
- icon: 'bx-pen'
- breadcrumb_section: 'Logistica'
- description: 'Gestiona los pies de firma para documentos logisticos'
- Rutas: app_maintainers_logistics_signature_footer_{create,edit,delete,export}
```

---

## FASE 3: Entidades con Relaciones Multiples

Entidades que tienen FK a otras entidades ya existentes o creadas en fases anteriores.

### DEPENDENCIAS PREVIAS

Antes de esta fase, asegurar que existan:
- **Warehouse** (Bodega) -> YA EXISTE en el proyecto
- **Specialty** (Especialidad) -> YA EXISTE en el proyecto
- **ArticleType** -> Creado en Fase 2

---

### 3.1 WarehouseSpecialty (RelEspecialidadBodega)

**Legacy:** `RelEspecialidadBodega.php` - Campos: id, fechaCreacion, idBodega(FK->Warehouse), idEspecialidadMedica(FK->Specialty), idEstado

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/WarehouseSpecialty.php siguiendo el patron de Gender.php pero CON relaciones ManyToOne.

- Tabla: warehouse_specialty
- Campos:
  - id: integer, PK, auto-increment
  - warehouse: ManyToOne -> Warehouse, NOT NULL, JoinColumn(name='warehouse_id')
  - specialty: ManyToOne -> Specialty, NOT NULL, JoinColumn(name='specialty_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: WarehouseSpecialtyRepository
- Namespace: App\Entity\Tenant

IMPORTANTE: Las relaciones ManyToOne deben usar:
#[ORM\ManyToOne(targetEntity: Warehouse::class)]
#[ORM\JoinColumn(name: 'warehouse_id', nullable: false)]

#[ORM\ManyToOne(targetEntity: Specialty::class)]
#[ORM\JoinColumn(name: 'specialty_id', nullable: false)]

NOTA: Esta entidad NO tiene campo name propio. El "nombre" se muestra como combinacion de warehouse + specialty.
Agregar metodo __toString(): return ($this->warehouse ? $this->warehouse->getName() : '') . ' - ' . ($this->specialty ? $this->specialty->getName() : '');
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/WarehouseSpecialtyRepository.php siguiendo el patron de GenderRepository.

- Entity: WarehouseSpecialty
- Metodos:
  - findAllActive(): orderBy createdAt DESC
  - findByWarehouse(int $warehouseId): array
  - findBySpecialty(int $specialtyId): array
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Logistics/WarehouseSpecialtyType.php.

Campos:
- warehouse: EntityType, class=Warehouse::class, label='Bodega',
  choice_label='name', placeholder='Seleccione bodega...', required=true, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- specialty: EntityType, class=Specialty::class, label='Especialidad',
  choice_label='name', placeholder='Seleccione especialidad...', required=true, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: WarehouseSpecialty
Namespace: App\Form\Maintainers\Logistics
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Logistics/WarehouseSpecialtyController.php siguiendo el patron de GenderController.

- Route base: /maintainers/logistics/warehouse-specialty
- Prefijo rutas: app_maintainers_logistics_warehouse_specialty_
- Inyectar: WarehouseSpecialtyRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('ws')
    ->leftJoin('ws.warehouse', 'w')
    ->leftJoin('ws.specialty', 's')
    ->addSelect('w', 's')
    ->orderBy('ws.id', 'DESC')
- getColumns(): ['warehouse.name' => 'Bodega', 'specialty.name' => 'Especialidad', 'isActive' => 'Estado']
- getFormType(): WarehouseSpecialtyType::class
- createNewEntity(): new WarehouseSpecialty()
- getTemplatePath(): 'maintainers/logistics/warehouse_specialty/index.html.twig'
- getPageTitle(): 'create'=>'Asignar Especialidad a Bodega', 'edit'=>'Editar Asignacion', default=>'Especialidades por Bodega'
- Export columns: ['warehouse.name', 'specialty.name', 'isActive']
- Export headers: ['Bodega', 'Especialidad', 'Activo']
- filename: 'especialidades_bodega_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/logistics/warehouse_specialty/index.html.twig.

- page_title: 'Especialidades por Bodega'
- icon: 'bx-sitemap'
- breadcrumb_section: 'Logistica'
- description: 'Gestiona la asignacion de especialidades medicas a bodegas'
- Rutas: app_maintainers_logistics_warehouse_specialty_{create,edit,delete,export}
```

---

### 3.2 ArticleSupplier (RelArticuloProveedor)

**Legacy:** `RelArticuloProveedor.php` - Campos: id, precio(decimal10.2), idArticulo(FK->Article), idProveedor(FK->???), idEstado

**NOTA:** La FK idProveedor apunta a una entidad Proveedor que no existe aun en el proyecto nuevo. Se crea el campo como string temporal (supplierName) hasta que se migre el modulo de proveedores. Alternativamente, si la entidad Article aun no existe cuando se cree esta, se puede postergar a Fase 4.

**DECISION:** Esta entidad depende de Article (Fase 4). Se crea DESPUES de Article. El orden de ejecucion real sera: Fase 4 (Article) primero, luego ArticleSupplier.

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/ArticleSupplier.php siguiendo el patron de Gender.php pero CON relaciones ManyToOne.

- Tabla: article_supplier
- Campos:
  - id: integer, PK, auto-increment
  - price: decimal(10,2), NOT NULL, default 0, Assert\PositiveOrZero
  - article: ManyToOne -> Article, NOT NULL, JoinColumn(name='article_id')
  - supplierName: string(255), nullable, column: supplier_name (temporal hasta migrar modulo Proveedores)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: ArticleSupplierRepository
- Namespace: App\Entity\Tenant

IMPORTANTE: La relacion ManyToOne a Article debe usar:
#[ORM\ManyToOne(targetEntity: Article::class)]
#[ORM\JoinColumn(name: 'article_id', nullable: false)]

NOTA: Cuando se migre el modulo de Proveedores, reemplazar supplierName por:
#[ORM\ManyToOne(targetEntity: Supplier::class)]
#[ORM\JoinColumn(name: 'supplier_id', nullable: false)]
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/ArticleSupplierRepository.php siguiendo el patron de GenderRepository.

- Entity: ArticleSupplier
- Metodos:
  - findAllActive(): orderBy id DESC
  - findByArticle(int $articleId): array
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Logistics/ArticleSupplierType.php.

Campos:
- article: EntityType, class=Article::class, label='Articulo',
  choice_label='name', placeholder='Seleccione articulo...', required=true, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- supplierName: TextType, label='Proveedor', required=false, placeholder='Nombre del proveedor', class='form-control'
- price: NumberType, label='Precio', required=true, class='form-control', scale=2, attr=['min'=>0, 'step'=>'0.01']
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: ArticleSupplier
Namespace: App\Form\Maintainers\Logistics
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Logistics/ArticleSupplierController.php siguiendo el patron de GenderController.

- Route base: /maintainers/logistics/article-supplier
- Prefijo rutas: app_maintainers_logistics_article_supplier_
- Inyectar: ArticleSupplierRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('as2')
    ->leftJoin('as2.article', 'a')
    ->addSelect('a')
    ->orderBy('as2.id', 'DESC')
- getColumns(): ['article.name' => 'Articulo', 'supplierName' => 'Proveedor', 'price' => 'Precio', 'isActive' => 'Estado']
- getFormType(): ArticleSupplierType::class
- createNewEntity(): new ArticleSupplier()
- getTemplatePath(): 'maintainers/logistics/article_supplier/index.html.twig'
- getPageTitle(): 'create'=>'Asignar Proveedor a Articulo', 'edit'=>'Editar Proveedor Articulo', default=>'Proveedores por Articulo'
- Export columns: ['article.name', 'supplierName', 'price', 'isActive']
- Export headers: ['Articulo', 'Proveedor', 'Precio', 'Activo']
- filename: 'proveedores_articulo_'.date('Y-m-d').'.csv'

NOTA: El alias 'as2' se usa en lugar de 'as' porque 'as' es palabra reservada de SQL.
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/logistics/article_supplier/index.html.twig.

- page_title: 'Proveedores por Articulo'
- icon: 'bx-store'
- breadcrumb_section: 'Logistica'
- description: 'Gestiona la relacion entre articulos y proveedores con precios'
- Rutas: app_maintainers_logistics_article_supplier_{create,edit,delete,export}
```

---

### 3.3 ArticleWarehouse (RelArticuloCentroCosto)

**Legacy:** `RelArticuloCentroCosto.php` - Campos: id, stockMinimo(decimal10.2), stockCritico(decimal10.2), stockOptimo(decimal10.2), esCritico(bool), idArticulo(FK->Article), idCentroCosto(FK->Warehouse), idEstado, idTipoFuncionamientoArticulo(FK->???)

**NOTA:** idTipoFuncionamientoArticulo no tiene entidad FK clara en el legacy. Se omite por ahora. Esta entidad depende de Article (Fase 4). Se crea DESPUES de Article.

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/ArticleWarehouse.php siguiendo el patron de Gender.php pero CON relaciones ManyToOne.

- Tabla: article_warehouse
- Campos:
  - id: integer, PK, auto-increment
  - article: ManyToOne -> Article, NOT NULL, JoinColumn(name='article_id')
  - warehouse: ManyToOne -> Warehouse, NOT NULL, JoinColumn(name='warehouse_id')
  - minStock: decimal(10,2), nullable, column: min_stock (legacy: stockMinimo)
  - criticalStock: decimal(10,2), nullable, column: critical_stock (legacy: stockCritico)
  - optimalStock: decimal(10,2), nullable, column: optimal_stock (legacy: stockOptimo)
  - isCritical: boolean, default false, column: is_critical (legacy: esCritico)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: ArticleWarehouseRepository
- Namespace: App\Entity\Tenant

IMPORTANTE: Las relaciones ManyToOne deben usar:
#[ORM\ManyToOne(targetEntity: Article::class)]
#[ORM\JoinColumn(name: 'article_id', nullable: false)]

#[ORM\ManyToOne(targetEntity: Warehouse::class)]
#[ORM\JoinColumn(name: 'warehouse_id', nullable: false)]
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/ArticleWarehouseRepository.php siguiendo el patron de GenderRepository.

- Entity: ArticleWarehouse
- Metodos:
  - findAllActive(): orderBy id DESC
  - findByArticle(int $articleId): array
  - findByWarehouse(int $warehouseId): array
  - findCritical(): array - donde isCritical=true y isActive=true
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Logistics/ArticleWarehouseType.php.

Campos:
- article: EntityType, class=Article::class, label='Articulo',
  choice_label='name', placeholder='Seleccione articulo...', required=true, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- warehouse: EntityType, class=Warehouse::class, label='Bodega',
  choice_label='name', placeholder='Seleccione bodega...', required=true, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- minStock: NumberType, label='Stock Minimo', required=false, class='form-control', scale=2, attr=['min'=>0, 'step'=>'0.01']
- criticalStock: NumberType, label='Stock Critico', required=false, class='form-control', scale=2, attr=['min'=>0, 'step'=>'0.01']
- optimalStock: NumberType, label='Stock Optimo', required=false, class='form-control', scale=2, attr=['min'=>0, 'step'=>'0.01']
- isCritical: CheckboxType, label='Es Critico', required=false, class='form-check-input'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: ArticleWarehouse
Namespace: App\Form\Maintainers\Logistics
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Logistics/ArticleWarehouseController.php siguiendo el patron de GenderController.

- Route base: /maintainers/logistics/article-warehouse
- Prefijo rutas: app_maintainers_logistics_article_warehouse_
- Inyectar: ArticleWarehouseRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('aw')
    ->leftJoin('aw.article', 'a')
    ->leftJoin('aw.warehouse', 'w')
    ->addSelect('a', 'w')
    ->orderBy('aw.id', 'DESC')
- getColumns(): ['article.name' => 'Articulo', 'warehouse.name' => 'Bodega', 'minStock' => 'Stock Min.', 'criticalStock' => 'Stock Crit.', 'optimalStock' => 'Stock Opt.', 'isCritical' => 'Critico', 'isActive' => 'Estado']
- getFormType(): ArticleWarehouseType::class
- createNewEntity(): new ArticleWarehouse()
- getTemplatePath(): 'maintainers/logistics/article_warehouse/index.html.twig'
- getPageTitle(): 'create'=>'Asignar Articulo a Bodega', 'edit'=>'Editar Articulo en Bodega', default=>'Articulos por Bodega'
- Export columns: ['article.name', 'warehouse.name', 'minStock', 'criticalStock', 'optimalStock', 'isCritical', 'isActive']
- Export headers: ['Articulo', 'Bodega', 'Stock Minimo', 'Stock Critico', 'Stock Optimo', 'Es Critico', 'Activo']
- filename: 'articulos_bodega_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/logistics/article_warehouse/index.html.twig.

- page_title: 'Articulos por Bodega'
- icon: 'bx-cabinet'
- breadcrumb_section: 'Logistica'
- description: 'Gestiona la asignacion de articulos a bodegas con niveles de stock'
- Rutas: app_maintainers_logistics_article_warehouse_{create,edit,delete,export}
```

---

## FASE 4: Entidad Compleja

### 4.1 Article (Articulo)

**Legacy:** `Articulo.php` - 25+ campos incluyendo multiples FK, campos decimales, multiples checkboxes. Es la entidad central de logistica.

**DEPENDENCIAS:** Crear primero ArticleType (Fase 2). Las entidades SubCompany y BudgetItem YA EXISTEN.

**NOTA SOBRE FK PENDIENTES:**
- `idUnidadMedida` -> No existe entidad MeasurementUnit aun. Se omite por ahora.
- `idSubEmpresaFacturadora` -> FK a SubCompany (ya existe)
- `idSubEmpresa` -> FK a SubCompany (ya existe)
- `idItemPresupuestario` -> FK a BudgetItem (ya existe)

#### Prompt Copilot - Entity Article

```
Crea src/Entity/Tenant/Article.php. Esta es la entidad MAS COMPLEJA de Logistica.

- Tabla: article
- Campos:
  - id: integer, PK, auto-increment
  - code: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - name: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - description: string(2000), nullable, Assert\Length(max=2000)
  - accountGroupCode: string(255), nullable, column: account_group_code (legacy: codigoAgrupacionCuenta)
  - isConsignment: boolean, default false, column: is_consignment (legacy: esConsignacion)
  - isControlled: boolean, default false, column: is_controlled (legacy: esControlado)
  - hasExpirationDate: boolean, default false, column: has_expiration_date (legacy: tieneFechaVencimientoLote)
  - photoName: string(255), nullable, column: photo_name (legacy: nombreFoto)
  - minStock: decimal(10,2), nullable, column: min_stock (legacy: stockMinimo)
  - criticalStock: decimal(10,2), nullable, column: critical_stock (legacy: stockCritico)
  - optimalStock: decimal(10,2), nullable, column: optimal_stock (legacy: stockOptimo)
  - maxStock: decimal(10,2), nullable, column: max_stock (legacy: stockMaximo)
  - isCritical: boolean, default false, column: is_critical (legacy: esCritico)
  - isGeneric: boolean, default false, column: is_generic (legacy: esGenerico)
  - isResterilizable: boolean, default false, column: is_resterilizable (legacy: esReesterilizable)
  - isForSale: boolean, default false, column: is_for_sale (legacy: esVenta)
  - isBillable: boolean, default false, column: is_billable (legacy: esFacturable)
  - isFirstAidDeduction: boolean, default false, column: is_first_aid_deduction (legacy: esRebajaBotiquin)
  - genericName: string(100), nullable, column: generic_name (legacy: nombreGenerico)
  - shortName: string(100), nullable, column: short_name (legacy: nombreAbreviado)
  - margin: decimal(10,2), nullable (legacy: margen)
  - iconCode: string(255), nullable, column: icon_code (legacy: codigoIcon)
  - cenabastCode: string(255), nullable, column: cenabast_code (legacy: codigoCenabast)
  - articleType: ManyToOne -> ArticleType, nullable, JoinColumn(name='article_type_id')
  - billingSubCompany: ManyToOne -> SubCompany, nullable, JoinColumn(name='billing_sub_company_id') (legacy: idSubEmpresaFacturadora)
  - subCompany: ManyToOne -> SubCompany, nullable, JoinColumn(name='sub_company_id') (legacy: idSubEmpresa)
  - budgetItem: ManyToOne -> BudgetItem, nullable, JoinColumn(name='budget_item_id') (legacy: idItemPresupuestario)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: ArticleRepository
- Namespace: App\Entity\Tenant

IMPORTANTE:
- NO agregar campo idEmpresa (multi-tenancy por Hakam)
- El campo idUnidadMedida se OMITE hasta que exista la entidad MeasurementUnit
- Todas las relaciones ManyToOne son nullable
- Usar PHP 8.2 attributes (#[ORM\...])
- Usar Assert constraints de Symfony para validaciones
- Constructor debe inicializar createdAt = new \DateTime()
- Implementar __toString() retornando $this->name
```

#### Prompt Copilot - Repository Article

```
Crea src/Repository/Tenant/ArticleRepository.php.

- Entity: Article
- Metodos:
  - findAllActive(): orderBy name ASC, where isActive=true
  - findByCode(string $code): ?Article
  - findByArticleType(int $articleTypeId): array
  - findControlled(): array - donde isControlled=true y isActive=true
  - findCritical(): array - donde isCritical=true y isActive=true
```

#### Prompt Copilot - FormType Article

```
Crea src/Form/Maintainers/Logistics/ArticleFormType.php.

NOTA: Nombrar ArticleFormType (no ArticleType) para evitar conflicto con la entity ArticleType.

Campos (organizados en grupos logicos):

Grupo "Identificacion":
- code: TextType, label='Codigo', required, placeholder='Codigo del articulo', class='form-control'
- name: TextType, label='Nombre', required, placeholder='Nombre del articulo', class='form-control'
- shortName: TextType, label='Nombre Abreviado', required=false, placeholder='Nombre corto', class='form-control'
- genericName: TextType, label='Nombre Generico', required=false, placeholder='Nombre generico', class='form-control'
- description: TextareaType, label='Descripcion', required=false, class='form-control', attr=['rows'=>3]

Grupo "Clasificacion":
- articleType: EntityType, class=ArticleType::class, label='Tipo de Articulo',
  choice_label='name', placeholder='Seleccione tipo...', required=false, class='form-select',
  query_builder: filtrar isActive=true, orderBy name ASC
- subCompany: EntityType, class=SubCompany::class, label='Sub Empresa',
  choice_label='name', placeholder='Seleccione...', required=false, class='form-select',
  query_builder: filtrar isActive=true, orderBy name ASC
- billingSubCompany: EntityType, class=SubCompany::class, label='Sub Empresa Facturadora',
  choice_label='name', placeholder='Seleccione...', required=false, class='form-select',
  query_builder: filtrar isActive=true, orderBy name ASC
- budgetItem: EntityType, class=BudgetItem::class, label='Item Presupuestario',
  choice_label='name', placeholder='Seleccione...', required=false, class='form-select',
  query_builder: filtrar isActive=true, orderBy name ASC

Grupo "Codigos":
- accountGroupCode: TextType, label='Cod. Agrupacion Cuenta', required=false, class='form-control'
- iconCode: TextType, label='Codigo Icono', required=false, class='form-control'
- cenabastCode: TextType, label='Codigo Cenabast', required=false, class='form-control'

Grupo "Stock":
- minStock: NumberType, label='Stock Minimo', required=false, class='form-control', scale=2
- criticalStock: NumberType, label='Stock Critico', required=false, class='form-control', scale=2
- optimalStock: NumberType, label='Stock Optimo', required=false, class='form-control', scale=2
- maxStock: NumberType, label='Stock Maximo', required=false, class='form-control', scale=2
- margin: NumberType, label='Margen', required=false, class='form-control', scale=2

Grupo "Propiedades" (checkboxes):
- isConsignment: CheckboxType, label='Es Consignacion', required=false, class='form-check-input'
- isControlled: CheckboxType, label='Es Controlado', required=false, class='form-check-input'
- hasExpirationDate: CheckboxType, label='Tiene Fecha Vencimiento', required=false, class='form-check-input'
- isCritical: CheckboxType, label='Es Critico', required=false, class='form-check-input'
- isGeneric: CheckboxType, label='Es Generico', required=false, class='form-check-input'
- isResterilizable: CheckboxType, label='Es Reesterilizable', required=false, class='form-check-input'
- isForSale: CheckboxType, label='Es Venta', required=false, class='form-check-input'
- isBillable: CheckboxType, label='Es Facturable', required=false, class='form-check-input'
- isFirstAidDeduction: CheckboxType, label='Rebaja Botiquin', required=false, class='form-check-input'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

Grupo "Imagen":
- photoName: TextType, label='Nombre Foto', required=false, class='form-control'

data_class: Article
Namespace: App\Form\Maintainers\Logistics
```

#### Prompt Copilot - Controller Article

```
Crea src/Controller/Maintainers/Logistics/ArticleController.php.

- Route base: /maintainers/logistics/article
- Prefijo rutas: app_maintainers_logistics_article_
- Inyectar: ArticleRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('a')
    ->leftJoin('a.articleType', 'at')
    ->leftJoin('a.subCompany', 'sc')
    ->addSelect('at', 'sc')
    ->orderBy('a.id', 'DESC')
- getColumns(): ['code' => 'Codigo', 'name' => 'Nombre', 'articleType.name' => 'Tipo', 'isControlled' => 'Controlado', 'isCritical' => 'Critico', 'isForSale' => 'Venta', 'isActive' => 'Estado']
- getFormType(): ArticleFormType::class
- createNewEntity(): new Article()
- getTemplatePath(): 'maintainers/logistics/article/index.html.twig'
- getPageTitle(): 'create'=>'Crear Articulo', 'edit'=>'Editar Articulo', default=>'Articulos'
- Export columns: ['code', 'name', 'shortName', 'genericName', 'articleType.name', 'subCompany.name', 'isConsignment', 'isControlled', 'hasExpirationDate', 'isCritical', 'isGeneric', 'isResterilizable', 'isForSale', 'isBillable', 'isFirstAidDeduction', 'minStock', 'criticalStock', 'optimalStock', 'maxStock', 'cenabastCode', 'isActive']
- Export headers: ['Codigo', 'Nombre', 'Nombre Abreviado', 'Nombre Generico', 'Tipo', 'Sub Empresa', 'Consignacion', 'Controlado', 'Fecha Venc.', 'Critico', 'Generico', 'Reesterilizable', 'Venta', 'Facturable', 'Rebaja Botiquin', 'Stock Min', 'Stock Crit', 'Stock Opt', 'Stock Max', 'Cod Cenabast', 'Activo']
- filename: 'articulos_'.date('Y-m-d').'.csv'

NOTA: Solo se muestran las columnas mas relevantes en la tabla del index.
La exportacion CSV incluye todos los campos.
```

#### Prompt Copilot - Template Article

```
Crea templates/maintainers/logistics/article/index.html.twig.

- page_title: 'Articulos'
- icon: 'bx-box'
- breadcrumb_section: 'Logistica'
- description: 'Gestiona el catalogo de articulos del inventario'
- Rutas: app_maintainers_logistics_article_{create,edit,delete,export}
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
1. Fase 1: ProductConditionType, ArticleOutflowType, InventoryAdjustmentReason
2. Fase 2: DispatchType, ArticleType, SignatureFooter
3. Fase 4: Article (crear ANTES de Fase 3 relaciones que dependen de Article)
4. Fase 3: WarehouseSpecialty, ArticleSupplier, ArticleWarehouse
5. Migracion BD
6. Registro en Menu (MenuItem)
7. Validacion Multi-Tenant
```

**NOTA IMPORTANTE:** Article (Fase 4) debe crearse ANTES que ArticleSupplier y ArticleWarehouse (Fase 3) porque estas dependen de Article como FK.

---

## Registro en Menu

Despues de crear todos los controllers, registrar en el sistema de menus.

**Tabla:** `menu_items`
**IMPORTANTE:** La columna `id` NO es auto-increment. Hay que asignarlo manualmente.

### Paso 1: Obtener ID maximo actual y del padre

```sql
SELECT MAX(id) FROM menu_items;
-- Usar el siguiente numero como base (ej: si MAX=71, empezar en 72)

SELECT id FROM menu_items WHERE name = 'mantenedores';
-- Anotar como {MANTENEDORES_ID} (actualmente = 4)
```

### Paso 2: Insertar categoria + 10 mantenedores

Reemplazar los IDs segun corresponda. En este ejemplo:
- `{MANTENEDORES_ID}` = 4 (padre Mantenedores)
- ID base = 72 (siguiente disponible despues de Tesoreria)

```sql
-- Categoria Logistica (hijo de Mantenedores)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES (72, 'maintenance_logistics', 'Logistica', NULL, 'bx bx-package', NULL, 4, 6, true, true, true, '["ROLE_USER"]', NOW());

-- 10 mantenedores (hijos de Logistica, parent_id = 72)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES
-- Tipos y catalogos simples
(73, 'product_condition_type', 'Condiciones de Producto', 'app_maintainers_logistics_product_condition_type_index', 'bx bx-check-shield', 'maintenance_logistics', 72, 1, true, true, true, '["ROLE_USER"]', NOW()),
(74, 'article_outflow_type', 'Tipos de Egreso', 'app_maintainers_logistics_article_outflow_type_index', 'bx bx-export', 'maintenance_logistics', 72, 2, true, true, true, '["ROLE_USER"]', NOW()),
(75, 'inventory_adjustment_reason', 'Motivos Ajuste Inventario', 'app_maintainers_logistics_inventory_adjustment_reason_index', 'bx bx-revision', 'maintenance_logistics', 72, 3, true, true, true, '["ROLE_USER"]', NOW()),
(76, 'dispatch_type', 'Tipos de Despacho', 'app_maintainers_logistics_dispatch_type_index', 'bx bx-package', 'maintenance_logistics', 72, 4, true, true, true, '["ROLE_USER"]', NOW()),
(77, 'article_type', 'Tipos de Articulo', 'app_maintainers_logistics_article_type_index', 'bx bx-category-alt', 'maintenance_logistics', 72, 5, true, true, true, '["ROLE_USER"]', NOW()),
(78, 'signature_footer', 'Pies de Firma', 'app_maintainers_logistics_signature_footer_index', 'bx bx-pen', 'maintenance_logistics', 72, 6, true, true, true, '["ROLE_USER"]', NOW()),
-- Entidades principales
(79, 'article', 'Articulos', 'app_maintainers_logistics_article_index', 'bx bx-box', 'maintenance_logistics', 72, 7, true, true, true, '["ROLE_USER"]', NOW()),
-- Relaciones
(80, 'warehouse_specialty', 'Especialidades por Bodega', 'app_maintainers_logistics_warehouse_specialty_index', 'bx bx-sitemap', 'maintenance_logistics', 72, 8, true, true, true, '["ROLE_USER"]', NOW()),
(81, 'article_supplier', 'Proveedores por Articulo', 'app_maintainers_logistics_article_supplier_index', 'bx bx-store', 'maintenance_logistics', 72, 9, true, true, true, '["ROLE_USER"]', NOW()),
(82, 'article_warehouse', 'Articulos por Bodega', 'app_maintainers_logistics_article_warehouse_index', 'bx bx-cabinet', 'maintenance_logistics', 72, 10, true, true, true, '["ROLE_USER"]', NOW());
```

### Paso 3: Limpiar cache de menu

```bash
php bin/console cache:clear
```

### Rollback (en caso de necesitar borrar)

```sql
DELETE FROM menu_items WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintenance_logistics');
DELETE FROM menu_items WHERE name = 'maintenance_logistics';
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
| Entities | 10 | src/Entity/Tenant/ |
| Repositories | 10 | src/Repository/Tenant/ |
| FormTypes | 10 | src/Form/Maintainers/Logistics/ |
| Controllers | 10 | src/Controller/Maintainers/Logistics/ |
| Templates | 10 | templates/maintainers/logistics/ |
| **TOTAL** | **50 archivos** | |

(10 mantenedores nuevos = 10 entidades x 5 archivos cada uno)
