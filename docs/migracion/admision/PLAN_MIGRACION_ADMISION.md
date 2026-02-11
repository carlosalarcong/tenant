# Plan de Migracion - Categoria Admision

## Resumen Ejecutivo

**Categoria:** MantenedorAdmision
**Origen Legacy:** `melisa_prod/src/Rebsol/MantenedoresBundle/Controller/_Default/MantenedorMaestro/MantenedorEmpresa/MantenedorAdmision/`
**Destino Nuevo:** `melisa_tenant/src/Controller/Maintainers/Admission/`
**Total Entidades:** 3 mantenedores
**Complejidad Global:** Baja

---

## Inventario Completo

### Entidades a Migrar

| # | Legacy (ES) | Nuevo (EN) | Tabla Nueva | Complejidad | Fase |
|---|-------------|------------|-------------|-------------|------|
| 1 | ConvenioEmpresa | CompanyAgreement | company_agreement | Simple+ | 1 |
| 2 | MotivoAnulacion | CancellationReason | cancellation_reason | Simple | 1 |
| 3 | TipoConsultaUrgencia | EmergencyConsultationType | emergency_consultation_type | Simple | 1 |

### Dependencias Externas

| Entidad | Ubicacion | Estado |
|---------|-----------|--------|
| *(ninguna)* | - | - |

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
| FormType | `src/Form/Maintainers/Personal/GenderType.php` | `src/Form/Maintainers/Admission/` |
| Controller | `src/Controller/Maintainers/Basic/GenderController.php` | `src/Controller/Maintainers/Admission/` |
| Template | `templates/maintainers/basic/gender/index.html.twig` | `templates/maintainers/admission/` |

### Herencia de Controllers

```
AbstractController
  -> AbstractTenantAwareController
    -> AbstractMantenedorController (Template Method Pattern)
      -> [Tu nuevo controller]
```

### Convenciones de Nombres

- **Rutas:** `app_maintainers_admission_{entity_snake}_{action}`
- **Ejemplo:** `app_maintainers_admission_company_agreement_index`
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

---

## FASE 1: Entidades Simples

Todas las 3 entidades de Admision son simples, con CRUD basico sin relaciones complejas.

---

### 1.1 CompanyAgreement (ConvenioEmpresa)

**Legacy:** `ConvenioEmpresa.php` - Campos: id, nombre(100), descripcion(text), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea el archivo src/Entity/Tenant/CompanyAgreement.php siguiendo EXACTAMENTE el patron de src/Entity/Tenant/Gender.php.

Especificaciones:
- Tabla: company_agreement
- Campos:
  - id: integer, PK, auto-increment
  - name: string(100), NOT NULL, Assert\NotBlank, Assert\Length(max=100)
  - description: text, nullable
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable

- Incluir getters/setters para todos los campos
- Constructor inicializa createdAt = new \DateTime()
- Namespace: App\Entity\Tenant
- Repository: CompanyAgreementRepository

RESTRICCIONES:
- NO agregar campo idEmpresa (multi-tenancy por Hakam)
- NO agregar relaciones adicionales
- Usar PHP 8.2 attributes (#[ORM\...])
- Usar Assert constraints de Symfony
```

#### Prompt Copilot - Repository

```
Crea el archivo src/Repository/Tenant/CompanyAgreementRepository.php siguiendo EXACTAMENTE el patron de src/Repository/Tenant/GenderRepository.php.

- Extends ServiceEntityRepository
- Entity class: CompanyAgreement
- Metodos:
  - findAllActive(): array - orderBy name ASC, where isActive=true
- Namespace: App\Repository\Tenant
```

#### Prompt Copilot - FormType

```
Crea el archivo src/Form/Maintainers/Admission/CompanyAgreementType.php siguiendo EXACTAMENTE el patron de src/Form/Maintainers/Personal/GenderType.php.

Campos del formulario:
- name: TextType, label='Nombre', required, placeholder='Ingrese nombre del convenio', class='form-control'
- description: TextareaType, label='Descripcion', required=false, placeholder='Descripcion del convenio', class='form-control', attr=['rows'=>3]
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: CompanyAgreement
Namespace: App\Form\Maintainers\Admission
```

#### Prompt Copilot - Controller

```
Crea el archivo src/Controller/Maintainers/Admission/CompanyAgreementController.php siguiendo EXACTAMENTE el patron de src/Controller/Maintainers/Basic/GenderController.php.

Especificaciones:
- Route base: /maintainers/admission/company-agreement
- Rutas:
  - GET '' -> index (app_maintainers_admission_company_agreement_index)
  - GET/POST '/create' -> create (app_maintainers_admission_company_agreement_create)
  - GET/POST '/{id}/edit' -> edit (app_maintainers_admission_company_agreement_edit)
  - POST '/{id}/delete' -> delete (app_maintainers_admission_company_agreement_delete)
  - GET '/export' -> export (app_maintainers_admission_company_agreement_export)

- Inyectar: CompanyAgreementRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('ca')->orderBy('ca.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'description' => 'Descripcion', 'isActive' => 'Estado']
- getFormType(): CompanyAgreementType::class
- createNewEntity(): new CompanyAgreement()
- getTemplatePath(): 'maintainers/admission/company_agreement/index.html.twig'
- getPageTitle(): 'create'=>'Crear Convenio Empresa', 'edit'=>'Editar Convenio Empresa', default=>'Convenios de Empresa'

- Export columns: ['name', 'description', 'isActive']
- Export headers: ['Nombre', 'Descripcion', 'Activo']
- Export filename: 'convenios_empresa_'.date('Y-m-d').'.csv'

RESTRICCIONES:
- NO cambiar el patron de AbstractMantenedorController
- NO agregar logica de negocio extra
- Mantener multi-tenant (AbstractTenantAwareController lo maneja)
```

#### Prompt Copilot - Template

```
Crea el archivo templates/maintainers/admission/company_agreement/index.html.twig siguiendo EXACTAMENTE el patron de templates/maintainers/basic/gender/index.html.twig.

Variables a configurar:
- page_title: 'Convenios de Empresa'
- icon: 'bx-file-blank'
- breadcrumb_section: 'Admision'
- description: 'Gestiona los convenios de empresa del sistema'
- create_route: 'app_maintainers_admission_company_agreement_create'
- edit_route: 'app_maintainers_admission_company_agreement_edit'
- delete_route: 'app_maintainers_admission_company_agreement_delete'
- export_route: 'app_maintainers_admission_company_agreement_export'

Extends: maintainers/modern_index.html.twig
```

---

### 1.2 CancellationReason (MotivoAnulacion)

**Legacy:** `MotivoAnulacion.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea el archivo src/Entity/Tenant/CancellationReason.php siguiendo EXACTAMENTE el patron de src/Entity/Tenant/Gender.php.

Especificaciones:
- Tabla: cancellation_reason
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable

- Namespace: App\Entity\Tenant
- Repository: CancellationReasonRepository

RESTRICCIONES:
- NO agregar campo idEmpresa
- Usar PHP 8.2 attributes
```

#### Prompt Copilot - Repository

```
Crea el archivo src/Repository/Tenant/CancellationReasonRepository.php siguiendo el patron de GenderRepository.php.

- Entity: CancellationReason
- Metodo findAllActive(): orderBy name ASC
- Namespace: App\Repository\Tenant
```

#### Prompt Copilot - FormType

```
Crea el archivo src/Form/Maintainers/Admission/CancellationReasonType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese motivo de anulacion', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: CancellationReason
Namespace: App\Form\Maintainers\Admission
```

#### Prompt Copilot - Controller

```
Crea el archivo src/Controller/Maintainers/Admission/CancellationReasonController.php siguiendo EXACTAMENTE el patron de GenderController.php.

- Route base: /maintainers/admission/cancellation-reason
- Prefijo rutas: app_maintainers_admission_cancellation_reason_
- Inyectar: CancellationReasonRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('cr')->orderBy('cr.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): CancellationReasonType::class
- createNewEntity(): new CancellationReason()
- getTemplatePath(): 'maintainers/admission/cancellation_reason/index.html.twig'
- getPageTitle(): 'create'=>'Crear Motivo Anulacion', 'edit'=>'Editar Motivo Anulacion', default=>'Motivos de Anulacion'
- Export columns: ['name', 'isActive'], headers: ['Nombre', 'Activo'], filename: 'motivos_anulacion_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/admission/cancellation_reason/index.html.twig siguiendo el patron de gender/index.html.twig.

- page_title: 'Motivos de Anulacion'
- icon: 'bx-x-circle'
- breadcrumb_section: 'Admision'
- description: 'Gestiona los motivos de anulacion de admision'
- Rutas: app_maintainers_admission_cancellation_reason_{create,edit,delete,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 1.3 EmergencyConsultationType (TipoConsultaUrgencia)

**Legacy:** `TipoConsultaUrgencia.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/EmergencyConsultationType.php siguiendo el patron de Gender.php.

- Tabla: emergency_consultation_type
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: EmergencyConsultationTypeRepository
- Namespace: App\Entity\Tenant
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/EmergencyConsultationTypeRepository.php siguiendo el patron de GenderRepository.

- Entity: EmergencyConsultationType
- Metodo findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Admission/EmergencyConsultationTypeType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese tipo de consulta', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: EmergencyConsultationType
Namespace: App\Form\Maintainers\Admission
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Admission/EmergencyConsultationTypeController.php siguiendo el patron de GenderController.

- Route base: /maintainers/admission/emergency-consultation-type
- Prefijo rutas: app_maintainers_admission_emergency_consultation_type_
- Inyectar: EmergencyConsultationTypeRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('ect')->orderBy('ect.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): EmergencyConsultationTypeType::class
- createNewEntity(): new EmergencyConsultationType()
- getTemplatePath(): 'maintainers/admission/emergency_consultation_type/index.html.twig'
- getPageTitle(): default=>'Tipos de Consulta Urgencia'
- Export columns: ['name', 'isActive'], headers: ['Nombre', 'Activo'], filename: 'tipos_consulta_urgencia_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/admission/emergency_consultation_type/index.html.twig.

- page_title: 'Tipos de Consulta Urgencia'
- icon: 'bx-plus-medical'
- breadcrumb_section: 'Admision'
- description: 'Gestiona los tipos de consulta de urgencia'
- Rutas: app_maintainers_admission_emergency_consultation_type_{create,edit,delete,export}
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
1. Fase 1: CompanyAgreement, CancellationReason, EmergencyConsultationType
2. Migracion BD
3. Registro en Menu (MenuItem)
4. Validacion Multi-Tenant
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
-- Categoria Admision (hijo de Mantenedores)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES ({BASE_ID}, 'maintenance_admission', 'Admision', NULL, 'bx bx-user-plus', NULL, 4, 10, true, true, true, '["ROLE_USER"]', NOW());

-- 3 mantenedores (hijos de Admision, parent_id = {BASE_ID})
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES
({BASE_ID+1}, 'company_agreement', 'Convenios de Empresa', 'app_maintainers_admission_company_agreement_index', 'bx bx-file-blank', 'maintenance_admission', {BASE_ID}, 1, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+2}, 'cancellation_reason', 'Motivos de Anulacion', 'app_maintainers_admission_cancellation_reason_index', 'bx bx-x-circle', 'maintenance_admission', {BASE_ID}, 2, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+3}, 'emergency_consultation_type', 'Tipos Consulta Urgencia', 'app_maintainers_admission_emergency_consultation_type_index', 'bx bx-plus-medical', 'maintenance_admission', {BASE_ID}, 3, true, true, true, '["ROLE_USER"]', NOW());
```

### Paso 3: Limpiar cache de menu

```bash
php bin/console cache:clear
```

### Rollback (en caso de necesitar borrar)

```sql
DELETE FROM menu_items WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintenance_admission');
DELETE FROM menu_items WHERE name = 'maintenance_admission';
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
| FormTypes | 3 | src/Form/Maintainers/Admission/ |
| Controllers | 3 | src/Controller/Maintainers/Admission/ |
| Templates | 3 | templates/maintainers/admission/ |
| **TOTAL** | **15 archivos** | |

(3 mantenedores = 3 entidades x 5 archivos cada uno)
