# SPEC: Mantenedores Logística

**Categoría**: Logistics  
**Total Mantenedores**: 10  
**Estado**: ✅ Implementado  
**Última actualización**: 2026-02-09

---

## 📐 Arquitectura

Todos los mantenedores de logística extienden `AbstractMantenedorController` que implementa:
- ✅ CRUD completo con Template Method Pattern
- ✅ Paginación automática vía QueryBuilder
- ✅ Turbo Frames para modales
- ✅ Multi-tenancy con TenantEntityManager
- ✅ Exportación CSV

**Ruta base**: `/maintainers/logistics/{mantenedor}`

---

## 🗂️ Mantenedores Implementados

### 1. Article (Artículos)

**Controlador**: `App\Controller\Maintainers\Logistics\ArticleController`  
**Entidad**: `App\Entity\Tenant\Article`  
**Form**: `App\Form\Maintainers\Logistics\ArticleFormType`  
**Template**: `templates/maintainers/logistics/article/index.html.twig`

**Endpoints**:
- `GET /maintainers/logistics/article` → `app_maintainers_logistics_article_index`
- `GET /maintainers/logistics/article/create` → `app_maintainers_logistics_article_create`
- `GET /maintainers/logistics/article/{id}/edit` → `app_maintainers_logistics_article_edit`
- `POST /maintainers/logistics/article/{id}/delete` → `app_maintainers_logistics_article_delete`
- `GET /maintainers/logistics/article/export` → `app_maintainers_logistics_article_export`

**Columnas**:
- `code` - Código del artículo
- `name` - Nombre completo
- `articleType.name` - Tipo de artículo
- `isControlled` - Artículo controlado
- `isCritical` - Artículo crítico
- `isForSale` - Para venta
- `isActive` - Estado activo

**Export Columns**: code, name, shortName, genericName, articleType.name, subCompany.name, isConsignment, isControlled, hasExpirationDate, isCritical, isGeneric, isResterilizable, isForSale, isBillable, isFirstAidDeduction, minStock, criticalStock, optimalStock, maxStock, cenabastCode, isActive

**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Relaciones**: ArticleType, SubCompany  
**Features**: CRUD completo + Export CSV + Gestión de stocks + Flags múltiples

**Características especiales**:
- Gestión de stocks (mínimo, crítico, óptimo, máximo)
- Control de productos farmacéuticos
- Gestión de productos en consignación
- Control de vencimiento y esterilización
- Integración con código CENABAST

---

### 2. Article Outflow Type (Tipos de Egreso de Artículos)

**Controlador**: `App\Controller\Maintainers\Logistics\ArticleOutflowTypeController`  
**Entidad**: `App\Entity\Tenant\ArticleOutflowType`  
**Form**: `App\Form\Maintainers\Logistics\ArticleOutflowTypeType`  
**Template**: `templates/maintainers/logistics/article_outflow_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/logistics/article-outflow-type` → `app_maintainers_logistics_article_outflow_type_index`
- `GET /maintainers/logistics/article-outflow-type/create` → `app_maintainers_logistics_article_outflow_type_create`
- `GET /maintainers/logistics/article-outflow-type/{id}/edit` → `app_maintainers_logistics_article_outflow_type_edit`
- `POST /maintainers/logistics/article-outflow-type/{id}/delete` → `app_maintainers_logistics_article_outflow_type_delete`
- `GET /maintainers/logistics/article-outflow-type/export` → `app_maintainers_logistics_article_outflow_type_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder  
**Features**: CRUD + Export

**Descripción**: Define los tipos de salida de artículos del inventario (venta, consumo interno, devolución, etc.)

---

### 3. Article Supplier (Proveedores por Artículo)

**Controlador**: `App\Controller\Maintainers\Logistics\ArticleSupplierController`  
**Entidad**: `App\Entity\Tenant\ArticleSupplier`  
**Form**: `App\Form\Maintainers\Logistics\ArticleSupplierType`  
**Template**: `templates/maintainers/logistics/article_supplier/index.html.twig`

**Endpoints**:
- `GET /maintainers/logistics/article-supplier` → `app_maintainers_logistics_article_supplier_index`
- `GET /maintainers/logistics/article-supplier/create` → `app_maintainers_logistics_article_supplier_create`
- `GET /maintainers/logistics/article-supplier/{id}/edit` → `app_maintainers_logistics_article_supplier_edit`
- `POST /maintainers/logistics/article-supplier/{id}/delete` → `app_maintainers_logistics_article_supplier_delete`
- `GET /maintainers/logistics/article-supplier/export` → `app_maintainers_logistics_article_supplier_export`

**Columnas**:
- `article.name` - Artículo
- `supplierName` - Proveedor
- `price` - Precio
- `isActive` - Estado

**Paginación**: ✅ QueryBuilder  
**Relaciones**: Article  
**Features**: CRUD + Export + Gestión de precios

**Descripción**: Relaciona artículos con sus proveedores y gestiona los precios de compra.

---

### 4. Article Type (Tipos de Artículo)

**Controlador**: `App\Controller\Maintainers\Logistics\ArticleTypeController`  
**Entidad**: `App\Entity\Tenant\ArticleType`  
**Form**: `App\Form\Maintainers\Logistics\ArticleTypeType`  
**Template**: `templates/maintainers/logistics/article_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/logistics/article-type` → `app_maintainers_logistics_article_type_index`
- `GET /maintainers/logistics/article-type/create` → `app_maintainers_logistics_article_type_create`
- `GET /maintainers/logistics/article-type/{id}/edit` → `app_maintainers_logistics_article_type_edit`
- `POST /maintainers/logistics/article-type/{id}/delete` → `app_maintainers_logistics_article_type_delete`
- `GET /maintainers/logistics/article-type/export` → `app_maintainers_logistics_article_type_export`

**Columnas**:
- `code` - Código
- `name` - Nombre
- `isPharmaceutical` - Fármaco
- `warehouse.name` - Bodega
- `isActive` - Estado

**Paginación**: ✅ QueryBuilder  
**Relaciones**: Warehouse  
**Features**: CRUD + Export + Flag farmacéutico

**Descripción**: Categoriza los artículos y define su bodega por defecto.

---

### 5. Article Warehouse (Artículos por Bodega)

**Controlador**: `App\Controller\Maintainers\Logistics\ArticleWarehouseController`  
**Entidad**: `App\Entity\Tenant\ArticleWarehouse`  
**Form**: `App\Form\Maintainers\Logistics\ArticleWarehouseType`  
**Template**: `templates/maintainers/logistics/article_warehouse/index.html.twig`

**Endpoints**:
- `GET /maintainers/logistics/article-warehouse` → `app_maintainers_logistics_article_warehouse_index`
- `GET /maintainers/logistics/article-warehouse/create` → `app_maintainers_logistics_article_warehouse_create`
- `GET /maintainers/logistics/article-warehouse/{id}/edit` → `app_maintainers_logistics_article_warehouse_edit`
- `POST /maintainers/logistics/article-warehouse/{id}/delete` → `app_maintainers_logistics_article_warehouse_delete`
- `GET /maintainers/logistics/article-warehouse/export` → `app_maintainers_logistics_article_warehouse_export`

**Columnas**:
- `article.name` - Artículo
- `warehouse.name` - Bodega
- `minStock` - Stock Mínimo
- `criticalStock` - Stock Crítico
- `optimalStock` - Stock Óptimo
- `isCritical` - Crítico
- `isActive` - Estado

**Paginación**: ✅ QueryBuilder  
**Relaciones**: Article, Warehouse  
**Features**: CRUD + Export + Gestión de stocks por bodega

**Descripción**: Define los niveles de stock de cada artículo en cada bodega.

---

### 6. Dispatch Type (Tipos de Despacho)

**Controlador**: `App\Controller\Maintainers\Logistics\DispatchTypeController`  
**Entidad**: `App\Entity\Tenant\DispatchType`  
**Form**: `App\Form\Maintainers\Logistics\DispatchTypeType`  
**Template**: `templates/maintainers/logistics/dispatch_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/logistics/dispatch-type` → `app_maintainers_logistics_dispatch_type_index`
- `GET /maintainers/logistics/dispatch-type/create` → `app_maintainers_logistics_dispatch_type_create`
- `GET /maintainers/logistics/dispatch-type/{id}/edit` → `app_maintainers_logistics_dispatch_type_edit`
- `POST /maintainers/logistics/dispatch-type/{id}/delete` → `app_maintainers_logistics_dispatch_type_delete`
- `GET /maintainers/logistics/dispatch-type/export` → `app_maintainers_logistics_dispatch_type_export`

**Columnas**: code, name, isActive  
**Paginación**: ✅ QueryBuilder  
**Features**: CRUD + Export

**Descripción**: Define los tipos de despacho para gestión de entregas (normal, urgente, programado, etc.)

---

### 7. Inventory Adjustment Reason (Motivos de Ajuste de Inventario)

**Controlador**: `App\Controller\Maintainers\Logistics\InventoryAdjustmentReasonController`  
**Entidad**: `App\Entity\Tenant\InventoryAdjustmentReason`  
**Form**: `App\Form\Maintainers\Logistics\InventoryAdjustmentReasonType`  
**Template**: `templates/maintainers/logistics/inventory_adjustment_reason/index.html.twig`

**Endpoints**:
- `GET /maintainers/logistics/inventory-adjustment-reason` → `app_maintainers_logistics_inventory_adjustment_reason_index`
- `GET /maintainers/logistics/inventory-adjustment-reason/create` → `app_maintainers_logistics_inventory_adjustment_reason_create`
- `GET /maintainers/logistics/inventory-adjustment-reason/{id}/edit` → `app_maintainers_logistics_inventory_adjustment_reason_edit`
- `POST /maintainers/logistics/inventory-adjustment-reason/{id}/delete` → `app_maintainers_logistics_inventory_adjustment_reason_delete`
- `GET /maintainers/logistics/inventory-adjustment-reason/export` → `app_maintainers_logistics_inventory_adjustment_reason_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder  
**Features**: CRUD + Export

**Descripción**: Define los motivos para ajustes de inventario (merma, vencimiento, robo, recuento, etc.)

---

### 8. Product Condition Type (Tipos de Condición de Productos)

**Controlador**: `App\Controller\Maintainers\Logistics\ProductConditionTypeController`  
**Entidad**: `App\Entity\Tenant\ProductConditionType`  
**Form**: `App\Form\Maintainers\Logistics\ProductConditionTypeType`  
**Template**: `templates/maintainers/logistics/product_condition_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/logistics/product-condition-type` → `app_maintainers_logistics_product_condition_type_index`
- `GET /maintainers/logistics/product-condition-type/create` → `app_maintainers_logistics_product_condition_type_create`
- `GET /maintainers/logistics/product-condition-type/{id}/edit` → `app_maintainers_logistics_product_condition_type_edit`
- `POST /maintainers/logistics/product-condition-type/{id}/delete` → `app_maintainers_logistics_product_condition_type_delete`
- `GET /maintainers/logistics/product-condition-type/export` → `app_maintainers_logistics_product_condition_type_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder  
**Features**: CRUD + Export

**Descripción**: Define las condiciones físicas de los productos (nuevo, usado, defectuoso, reacondicionado, etc.)

---

### 9. Signature Footer (Pies de Firma)

**Controlador**: `App\Controller\Maintainers\Logistics\SignatureFooterController`  
**Entidad**: `App\Entity\Tenant\SignatureFooter`  
**Form**: `App\Form\Maintainers\Logistics\SignatureFooterType`  
**Template**: `templates/maintainers/logistics/signature_footer/index.html.twig`

**Endpoints**:
- `GET /maintainers/logistics/signature-footer` → `app_maintainers_logistics_signature_footer_index`
- `GET /maintainers/logistics/signature-footer/create` → `app_maintainers_logistics_signature_footer_create`
- `GET /maintainers/logistics/signature-footer/{id}/edit` → `app_maintainers_logistics_signature_footer_edit`
- `POST /maintainers/logistics/signature-footer/{id}/delete` → `app_maintainers_logistics_signature_footer_delete`
- `GET /maintainers/logistics/signature-footer/export` → `app_maintainers_logistics_signature_footer_export`

**Columnas**:
- `code` - Código
- `name` - Nombre
- `position` - Cargo
- `branch.name` - Sucursal
- `isActive` - Estado

**Paginación**: ✅ QueryBuilder  
**Relaciones**: Branch  
**Features**: CRUD + Export

**Descripción**: Define los firmantes autorizados para documentos de logística (órdenes de compra, despachos, etc.)

---

### 10. Warehouse Specialty (Especialidades por Bodega)

**Controlador**: `App\Controller\Maintainers\Logistics\WarehouseSpecialtyController`  
**Entidad**: `App\Entity\Tenant\WarehouseSpecialty`  
**Form**: `App\Form\Maintainers\Logistics\WarehouseSpecialtyType`  
**Template**: `templates/maintainers/logistics/warehouse_specialty/index.html.twig`

**Endpoints**:
- `GET /maintainers/logistics/warehouse-specialty` → `app_maintainers_logistics_warehouse_specialty_index`
- `GET /maintainers/logistics/warehouse-specialty/create` → `app_maintainers_logistics_warehouse_specialty_create`
- `GET /maintainers/logistics/warehouse-specialty/{id}/edit` → `app_maintainers_logistics_warehouse_specialty_edit`
- `POST /maintainers/logistics/warehouse-specialty/{id}/delete` → `app_maintainers_logistics_warehouse_specialty_delete`
- `GET /maintainers/logistics/warehouse-specialty/export` → `app_maintainers_logistics_warehouse_specialty_export`

**Columnas**:
- `warehouse.name` - Bodega
- `specialty.name` - Especialidad
- `isActive` - Estado

**Paginación**: ✅ QueryBuilder  
**Relaciones**: Warehouse, Specialty  
**Features**: CRUD + Export

**Descripción**: Relaciona bodegas con especialidades médicas para control de stock especializado.

---

## 🔧 Componentes Compartidos

### Templates Base
- `templates/maintainers/modern_index.html.twig` - Template maestro
- `templates/maintainers/_base_index.html.twig` - Base alternativa
- `templates/maintainers/_modal_form.html.twig` - Modal para forms

### AbstractMantenedorController
**Ubicación**: `src/Controller/AbstractMantenedorController.php`

**Métodos requeridos** (Template Method):
```php
protected function getData(Request $request): array|QueryBuilder;
protected function getColumns(): array;
protected function getTemplatePath(): string;
protected function getFormType(): string;
protected function createNewEntity(): object;
protected function getEntityById(int $id): ?object;
protected function getIndexRoute(): string;
protected function getEditRoute(): string;
protected function getCreateRoute(): string;
protected function getDeleteSuccessMessage(): string;
protected function getDeleteErrorMessage(): string;
protected function getEntityNotFoundMessage(): string;
protected function getCreateSuccessMessage(): string;
protected function getUpdateSuccessMessage(): string;
```

**Métodos implementados**:
- `handleIndex()` - Listado con paginación automática
- `handleCreate()` - Crear con Turbo Frame
- `handleEdit()` - Editar con Turbo Frame
- `handleDelete()` - Eliminar con confirmación
- `handleExport()` - Exportar CSV
- `paginate()` - Paginación con Doctrine Paginator
- `isTurboFrameRequest()` - Detección Turbo Frame

### Paginación Automática
**Detección por tipo de retorno**:
- `QueryBuilder` → Paginación activada ✅
- `Array` → Sin paginación ❌

**Parámetros URL**: `?page=1&limit=10`  
**Default**: 10 items por página

---

## 🎨 UI/UX

**Framework**: Bootstrap 5.3.0  
**Iconos**: BoxIcons (`bx-*`)  
**Modales**: Turbo Frames  
**Confirmación delete**: SweetAlert2  
**Responsive**: ✅ Mobile-first

**Breadcrumb típico**:
```
Dashboard > Mantenedores > Logística > {Mantenedor}
```

**Iconos comunes**:
- Article: `bx-package`
- Warehouse: `bx-store`
- Dispatch: `bx-truck`
- Inventory: `bx-list-check`

---

## 🔐 Seguridad

- ✅ CSRF Protection en formularios
- ✅ Multi-tenancy aislamiento por TenantContext
- ✅ Validación Doctrine constraints
- ✅ Method-level security con `#[Route]` methods

---

## 📊 Estado de Implementación

| Característica | Estado |
|---------------|--------|
| CRUD Completo | ✅ 10/10 |
| Paginación | ✅ 10/10 |
| Exportación | ✅ 10/10 |
| Turbo Frames | ✅ 10/10 |
| Forms validados | ✅ 10/10 |
| Relaciones FK | ✅ 6/10 |
| Tests unitarios | ❌ No implementado |
| Permisos/Roles | ❌ No implementado |

---

## 📝 Notas Técnicas

### Mantenedores con Relaciones
1. **Article**: Relacionado con ArticleType y SubCompany
2. **ArticleSupplier**: Relacionado con Article
3. **ArticleType**: Relacionado con Warehouse
4. **ArticleWarehouse**: Relacionado con Article y Warehouse
5. **SignatureFooter**: Relacionado con Branch
6. **WarehouseSpecialty**: Relacionado con Warehouse y Specialty

### Características Especiales
- **Article**: Mantenedor más complejo con 20+ campos exportables
- **ArticleWarehouse**: Gestión de stocks multinivel (mínimo, crítico, óptimo)
- **ArticleSupplier**: Gestión de precios por proveedor
- **SignatureFooter**: Incluye cargo y sucursal para documentos oficiales

### Consideraciones
1. **Multi-tenancy**: Todos los mantenedores usan `TenantEntityManager`
2. **Soft Delete**: No implementado - Delete es físico
3. **Audit Trail**: No implementado
4. **Ordenamiento**: Por defecto `ORDER BY id DESC`
5. **Búsqueda/Filtros**: No implementado en base
6. **Import masivo**: No implementado
7. **Gestión de stock**: Integrado en Article y ArticleWarehouse

---

## 🔄 Relaciones con Otros Módulos

### Dependencias
- **Basic Module**: Ubicaciones y configuraciones básicas
- **Clinical Module**: Especialidades médicas para WarehouseSpecialty
- **Commercial Module**: SubCompany para artículos
- **Company Module**: Branch para pies de firma

### Impacto
Los mantenedores de logística son fundamentales para:
- **Módulo de Compras**: Gestión de proveedores y precios
- **Módulo de Inventario**: Control de stock y movimientos
- **Módulo de Ventas**: Disponibilidad de artículos
- **Módulo de Farmacia**: Control de medicamentos
- **Módulo de Pabellón**: Material quirúrgico

---

## 🚀 Próximos Pasos

### Prioridad Alta
1. Implementar tests unitarios para lógica de stocks
2. Agregar sistema de permisos por rol
3. Implementar alertas de stock crítico
4. Agregar búsqueda/filtros en listados

### Prioridad Media
5. Implementar soft delete
6. Agregar audit trail para trazabilidad
7. Implementar import CSV masivo
8. Agregar validaciones de negocio avanzadas

### Prioridad Baja
9. Agregar dashboard de stocks
10. Implementar reportes de rotación de inventario
11. Agregar alertas de vencimiento de productos
12. Integración con sistema de compras

---

## 📚 Referencias

- [ARCHITECTURE.md](../../ARCHITECTURE.md) - Arquitectura general
- [AbstractMantenedorController](../../src/Controller/AbstractMantenedorController.php) - Controlador base
- [SPEC_MANTENEDORES_BASIC.md](./SPEC_MANTENEDORES_BASIC.md) - Referencia de formato
- [Symfony Routing](https://symfony.com/doc/current/routing.html) - Documentación de rutas
