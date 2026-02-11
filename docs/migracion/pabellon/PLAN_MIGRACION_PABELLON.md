# Plan de Migracion - Categoria Pabellon

## Resumen Ejecutivo

**Categoria:** MantenedorPabellon
**Origen Legacy:** `melisa_prod/src/Rebsol/MantenedoresBundle/Controller/_Default/MantenedorMaestro/MantenedorEmpresa/MantenedorPabellon/`
**Destino Nuevo:** `melisa_tenant/src/Controller/Maintainers/Surgery/`
**Total Entidades:** 13 mantenedores (0 dependencias externas nuevas)
**Complejidad Global:** Media-Alta

---

## Inventario Completo

### Entidades a Migrar

| # | Legacy (ES) | Nuevo (EN) | Tabla Nueva | Complejidad | Fase |
|---|-------------|------------|-------------|-------------|------|
| 1 | PabAnestesia | AnesthesiaType | anesthesia_type | Simple+ | 1 |
| 2 | PabGrupoSanguineo | BloodType | blood_type | Simple+ | 1 |
| 3 | PabCausaSuspension | SurgerySuspensionCause | surgery_suspension_cause | Simple | 1 |
| 4 | PabMotivoBloqueo | SurgeryBlockReason | surgery_block_reason | Simple | 1 |
| 5 | PabMotivoAnulacion | SurgeryCancellationReason | surgery_cancellation_reason | Simple+ | 1 |
| 6 | PabTipoHerida | WoundType | wound_type | Simple | 1 |
| 7 | PabEstadoPaciente | SurgeryPatientStatus | surgery_patient_status | Simple+ | 2 |
| 8 | RelEmpresaPabEstadoPaciente | SurgeryPatientStatusConfig | surgery_patient_status_config | Moderada | 2 |
| 9 | Regimen | TreatmentRegimen | treatment_regimen | Moderada | 3 |
| 10 | PabRolEquipoQuirurgico | SurgicalTeamRole | surgical_team_role | Moderada | 3 |
| 11 | Pabellon | SurgicalBlock | surgical_block | Moderada | 3 |
| 12 | PabEtapa | SurgicalStage | surgical_stage | Compleja | 4 |
| 13 | PabEtapaItem | SurgicalStageItem | surgical_stage_item | Compleja | 4 |

### Dependencias Externas (ya existentes en el proyecto)

| Entidad (EN) | Tabla | Requerido Por | Estado |
|--------------|-------|---------------|--------|
| Branch | branch | TreatmentRegimen, SurgicalStage | YA EXISTE |
| MedicalService | medical_service | SurgicalBlock | YA EXISTE |
| SurgeryItem | surgery_item | SurgicalTeamRole | YA EXISTE |

### Dependencias Internas (orden de creacion)

| Entidad | Depende De | Fase |
|---------|------------|------|
| SurgeryPatientStatusConfig | SurgeryPatientStatus | 2 (crear SurgeryPatientStatus antes) |
| SurgicalStageItem | SurgicalStage | 4 (crear SurgicalStage antes) |

### Entidades Descartadas

| Legacy | Razon |
|--------|-------|
| *(ninguna)* | Todas las 13 entidades del inventario son migrables |

---

## Patron a Seguir (Referencia)

Todos los archivos nuevos deben seguir EXACTAMENTE el patron existente:

| Tipo | Ejemplo Referencia | Ubicacion |
|------|-------------------|-----------|
| Entity | `src/Entity/Tenant/Gender.php` | `src/Entity/Tenant/` |
| Repository | `src/Repository/Tenant/GenderRepository.php` | `src/Repository/Tenant/` |
| FormType | `src/Form/Maintainers/Personal/GenderType.php` | `src/Form/Maintainers/Surgery/` |
| Controller | `src/Controller/Maintainers/Treasury/CreditCardController.php` | `src/Controller/Maintainers/Surgery/` |
| Template | `templates/maintainers/basic/gender/index.html.twig` | `templates/maintainers/surgery/` |

### Herencia de Controllers

```
AbstractController
  -> AbstractTenantAwareController
    -> AbstractMantenedorController (Template Method Pattern)
      -> [Tu nuevo controller]
```

### Convenciones de Nombres

- **Rutas:** `app_maintainers_surgery_{entity_snake}_{action}`
- **Ejemplo:** `app_maintainers_surgery_anesthesia_type_index`
- **Acciones:** `index`, `create`, `edit`, `delete`, `export`

### Nuevo Formato: getColumns() Asociativo

**IMPORTANTE:** A partir de febrero 2026, todos los controllers usan formato asociativo para columnas.
**Referencia principal:** `src/Controller/Maintainers/Treasury/CreditCardController.php`

```php
// Formato ANTIGUO (deprecated)
protected function getColumns(): array {
    return ['name', 'code', 'isActive'];
}

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

**Relaciones (con addSelect en getData):**
```php
'branch.name' => 'Sucursal',              // Relacion ManyToOne
'medicalService.name' => 'Servicio',       // Relacion ManyToOne
'surgeryPatientStatus.name' => 'Estado Paciente' // Relacion
```

**getData() con joins (patron CreditCardController):**
```php
protected function getData(Request $request): array|QueryBuilder
{
    return $this->repository->createQueryBuilder('e')
        ->leftJoin('e.branch', 'b')
        ->addSelect('b')
        ->orderBy('e.id', 'DESC');
}
```

---

## FASE 1: Entidades Simples

Entidades con campos basicos (`name`, `description`, `isActive`) sin relaciones FK. CRUD basico.

---

### 1.1 AnesthesiaType (PabAnestesia)

**Legacy:** `PabAnestesia.php` - Campos: id, nombre(100), descripcion(text,nullable), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea el archivo src/Entity/Tenant/AnesthesiaType.php siguiendo EXACTAMENTE el patron de src/Entity/Tenant/Gender.php.

Especificaciones:
- Tabla: anesthesia_type
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
- Repository: AnesthesiaTypeRepository

RESTRICCIONES:
- NO agregar campo idEmpresa (multi-tenancy por Hakam)
- NO agregar relaciones adicionales
- Usar PHP 8.2 attributes (#[ORM\...])
- Usar Assert constraints de Symfony
```

#### Prompt Copilot - Repository

```
Crea el archivo src/Repository/Tenant/AnesthesiaTypeRepository.php siguiendo EXACTAMENTE el patron de src/Repository/Tenant/GenderRepository.php.

- Extends ServiceEntityRepository
- Entity class: AnesthesiaType
- Metodos:
  - findAllActive(): array - orderBy name ASC, where isActive=true
- Namespace: App\Repository\Tenant
```

#### Prompt Copilot - FormType

```
Crea el archivo src/Form/Maintainers/Surgery/AnesthesiaTypeType.php siguiendo EXACTAMENTE el patron de src/Form/Maintainers/Personal/GenderType.php.

Campos del formulario:
- name: TextType, label='Nombre', required, placeholder='Ingrese tipo de anestesia', class='form-control'
- description: TextareaType, label='Descripcion', required=false, placeholder='Descripcion opcional', class='form-control', attr=['rows'=>3]
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: AnesthesiaType
Namespace: App\Form\Maintainers\Surgery
```

#### Prompt Copilot - Controller

```
Crea el archivo src/Controller/Maintainers/Surgery/AnesthesiaTypeController.php siguiendo EXACTAMENTE el patron de src/Controller/Maintainers/Treasury/CreditCardController.php.

Especificaciones:
- Route base: /maintainers/surgery/anesthesia-type
- Rutas:
  - GET '' -> index (app_maintainers_surgery_anesthesia_type_index)
  - GET/POST '/create' -> create (app_maintainers_surgery_anesthesia_type_create)
  - GET/POST '/{id}/edit' -> edit (app_maintainers_surgery_anesthesia_type_edit)
  - POST '/{id}/delete' -> delete (app_maintainers_surgery_anesthesia_type_delete)
  - GET '/export' -> export (app_maintainers_surgery_anesthesia_type_export)

- Inyectar: AnesthesiaTypeRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('at')->orderBy('at.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'description' => 'Descripcion', 'isActive' => 'Estado']
- getFormType(): AnesthesiaTypeType::class
- createNewEntity(): new AnesthesiaType()
- getTemplatePath(): 'maintainers/surgery/anesthesia_type/index.html.twig'
- getPageTitle(): 'create'=>'Crear Tipo de Anestesia', 'edit'=>'Editar Tipo de Anestesia', default=>'Tipos de Anestesia'

- Export columns: ['name', 'description', 'isActive']
- Export headers: ['Nombre', 'Descripcion', 'Activo']
- Export filename: 'tipos_anestesia_'.date('Y-m-d').'.csv'

RESTRICCIONES:
- NO cambiar el patron de AbstractMantenedorController
- NO agregar logica de negocio extra
- Mantener multi-tenant (AbstractTenantAwareController lo maneja)
```

#### Prompt Copilot - Template

```
Crea el archivo templates/maintainers/surgery/anesthesia_type/index.html.twig siguiendo EXACTAMENTE el patron de templates/maintainers/basic/gender/index.html.twig.

Variables a configurar:
- page_title: 'Tipos de Anestesia'
- icon: 'bx-injection'
- breadcrumb_section: 'Pabellon'
- description: 'Gestiona los tipos de anestesia del sistema'
- create_route: 'app_maintainers_surgery_anesthesia_type_create'
- edit_route: 'app_maintainers_surgery_anesthesia_type_edit'
- delete_route: 'app_maintainers_surgery_anesthesia_type_delete'
- export_route: 'app_maintainers_surgery_anesthesia_type_export'

Extends: maintainers/modern_index.html.twig
```

---

### 1.2 BloodType (PabGrupoSanguineo)

**Legacy:** `PabGrupoSanguineo.php` - Campos: id, nombre(100), descripcion(text,nullable), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea el archivo src/Entity/Tenant/BloodType.php siguiendo EXACTAMENTE el patron de src/Entity/Tenant/Gender.php.

Especificaciones:
- Tabla: blood_type
- Campos:
  - id: integer, PK, auto-increment
  - name: string(100), NOT NULL, Assert\NotBlank, Assert\Length(max=100)
  - description: text, nullable
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable

- Namespace: App\Entity\Tenant
- Repository: BloodTypeRepository

RESTRICCIONES:
- NO agregar campo idEmpresa
- Usar PHP 8.2 attributes
```

#### Prompt Copilot - Repository

```
Crea el archivo src/Repository/Tenant/BloodTypeRepository.php siguiendo el patron de GenderRepository.php.

- Entity: BloodType
- Metodo findAllActive(): orderBy name ASC
- Namespace: App\Repository\Tenant
```

#### Prompt Copilot - FormType

```
Crea el archivo src/Form/Maintainers/Surgery/BloodTypeType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ej: O+, A-, AB+', class='form-control'
- description: TextareaType, label='Descripcion', required=false, class='form-control', attr=['rows'=>3]
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: BloodType
Namespace: App\Form\Maintainers\Surgery
```

#### Prompt Copilot - Controller

```
Crea el archivo src/Controller/Maintainers/Surgery/BloodTypeController.php siguiendo EXACTAMENTE el patron de CreditCardController.php.

- Route base: /maintainers/surgery/blood-type
- Prefijo rutas: app_maintainers_surgery_blood_type_
- Inyectar: BloodTypeRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('bt')->orderBy('bt.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'description' => 'Descripcion', 'isActive' => 'Estado']
- getFormType(): BloodTypeType::class
- createNewEntity(): new BloodType()
- getTemplatePath(): 'maintainers/surgery/blood_type/index.html.twig'
- getPageTitle(): 'create'=>'Crear Grupo Sanguineo', 'edit'=>'Editar Grupo Sanguineo', default=>'Grupos Sanguineos'
- Export columns: ['name', 'description', 'isActive'], headers: ['Nombre', 'Descripcion', 'Activo'], filename: 'grupos_sanguineos_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/surgery/blood_type/index.html.twig siguiendo el patron de gender/index.html.twig.

- page_title: 'Grupos Sanguineos'
- icon: 'bx-droplet'
- breadcrumb_section: 'Pabellon'
- description: 'Gestiona los grupos sanguineos del sistema'
- Rutas: app_maintainers_surgery_blood_type_{create,edit,delete,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 1.3 SurgerySuspensionCause (PabCausaSuspension)

**Legacy:** `PabCausaSuspension.php` - Campos: id, nombre(150), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/SurgerySuspensionCause.php siguiendo el patron de Gender.php.

- Tabla: surgery_suspension_cause
- Campos:
  - id: integer, PK, auto-increment
  - name: string(150), NOT NULL, Assert\NotBlank, Assert\Length(max=150)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: SurgerySuspensionCauseRepository
- Namespace: App\Entity\Tenant
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/SurgerySuspensionCauseRepository.php siguiendo el patron de GenderRepository.

- Entity: SurgerySuspensionCause
- Metodo findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Surgery/SurgerySuspensionCauseType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese causa de suspension', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: SurgerySuspensionCause
Namespace: App\Form\Maintainers\Surgery
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Surgery/SurgerySuspensionCauseController.php siguiendo el patron de CreditCardController.php.

- Route base: /maintainers/surgery/surgery-suspension-cause
- Prefijo rutas: app_maintainers_surgery_surgery_suspension_cause_
- Inyectar: SurgerySuspensionCauseRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('ssc')->orderBy('ssc.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): SurgerySuspensionCauseType::class
- createNewEntity(): new SurgerySuspensionCause()
- getTemplatePath(): 'maintainers/surgery/surgery_suspension_cause/index.html.twig'
- getPageTitle(): 'create'=>'Crear Causa de Suspension', 'edit'=>'Editar Causa de Suspension', default=>'Causas de Suspension'
- Export columns: ['name', 'isActive'], headers: ['Nombre', 'Activo'], filename: 'causas_suspension_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/surgery/surgery_suspension_cause/index.html.twig.

- page_title: 'Causas de Suspension'
- icon: 'bx-pause-circle'
- breadcrumb_section: 'Pabellon'
- description: 'Gestiona las causas de suspension de cirugias'
- Rutas: app_maintainers_surgery_surgery_suspension_cause_{create,edit,delete,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 1.4 SurgeryBlockReason (PabMotivoBloqueo)

**Legacy:** `PabMotivoBloqueo.php` - Campos: id, nombre(150), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/SurgeryBlockReason.php siguiendo el patron de Gender.php.

- Tabla: surgery_block_reason
- Campos:
  - id: integer, PK, auto-increment
  - name: string(150), NOT NULL, Assert\NotBlank, Assert\Length(max=150)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: SurgeryBlockReasonRepository
- Namespace: App\Entity\Tenant
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/SurgeryBlockReasonRepository.php siguiendo el patron de GenderRepository.

- Entity: SurgeryBlockReason
- Metodo findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Surgery/SurgeryBlockReasonType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese motivo de bloqueo', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: SurgeryBlockReason
Namespace: App\Form\Maintainers\Surgery
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Surgery/SurgeryBlockReasonController.php siguiendo el patron de CreditCardController.php.

- Route base: /maintainers/surgery/surgery-block-reason
- Prefijo rutas: app_maintainers_surgery_surgery_block_reason_
- Inyectar: SurgeryBlockReasonRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('sbr')->orderBy('sbr.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): SurgeryBlockReasonType::class
- createNewEntity(): new SurgeryBlockReason()
- getTemplatePath(): 'maintainers/surgery/surgery_block_reason/index.html.twig'
- getPageTitle(): 'create'=>'Crear Motivo de Bloqueo', 'edit'=>'Editar Motivo de Bloqueo', default=>'Motivos de Bloqueo'
- Export columns: ['name', 'isActive'], headers: ['Nombre', 'Activo'], filename: 'motivos_bloqueo_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/surgery/surgery_block_reason/index.html.twig.

- page_title: 'Motivos de Bloqueo'
- icon: 'bx-block'
- breadcrumb_section: 'Pabellon'
- description: 'Gestiona los motivos de bloqueo de pabellones'
- Rutas: app_maintainers_surgery_surgery_block_reason_{create,edit,delete,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 1.5 SurgeryCancellationReason (PabMotivoAnulacion)

**Legacy:** `PabMotivoAnulacion.php` - Campos: id, nombre(100), descripcion(text,nullable), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/SurgeryCancellationReason.php siguiendo el patron de Gender.php.

- Tabla: surgery_cancellation_reason
- Campos:
  - id: integer, PK, auto-increment
  - name: string(100), NOT NULL, Assert\NotBlank, Assert\Length(max=100)
  - description: text, nullable
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: SurgeryCancellationReasonRepository
- Namespace: App\Entity\Tenant
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/SurgeryCancellationReasonRepository.php siguiendo el patron de GenderRepository.

- Entity: SurgeryCancellationReason
- Metodo findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Surgery/SurgeryCancellationReasonType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese motivo de anulacion', class='form-control'
- description: TextareaType, label='Descripcion', required=false, class='form-control', attr=['rows'=>3]
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: SurgeryCancellationReason
Namespace: App\Form\Maintainers\Surgery
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Surgery/SurgeryCancellationReasonController.php siguiendo el patron de CreditCardController.php.

- Route base: /maintainers/surgery/surgery-cancellation-reason
- Prefijo rutas: app_maintainers_surgery_surgery_cancellation_reason_
- Inyectar: SurgeryCancellationReasonRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('scr')->orderBy('scr.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'description' => 'Descripcion', 'isActive' => 'Estado']
- getFormType(): SurgeryCancellationReasonType::class
- createNewEntity(): new SurgeryCancellationReason()
- getTemplatePath(): 'maintainers/surgery/surgery_cancellation_reason/index.html.twig'
- getPageTitle(): 'create'=>'Crear Motivo de Anulacion', 'edit'=>'Editar Motivo de Anulacion', default=>'Motivos de Anulacion'
- Export columns: ['name', 'description', 'isActive'], headers: ['Nombre', 'Descripcion', 'Activo'], filename: 'motivos_anulacion_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/surgery/surgery_cancellation_reason/index.html.twig.

- page_title: 'Motivos de Anulacion'
- icon: 'bx-x-circle'
- breadcrumb_section: 'Pabellon'
- description: 'Gestiona los motivos de anulacion de cirugias'
- Rutas: app_maintainers_surgery_surgery_cancellation_reason_{create,edit,delete,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 1.6 WoundType (PabTipoHerida)

**Legacy:** `PabTipoHerida.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/WoundType.php siguiendo el patron de Gender.php.

- Tabla: wound_type
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: WoundTypeRepository
- Namespace: App\Entity\Tenant
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/WoundTypeRepository.php siguiendo el patron de GenderRepository.

- Entity: WoundType
- Metodo findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Surgery/WoundTypeType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese tipo de herida', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: WoundType
Namespace: App\Form\Maintainers\Surgery
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Surgery/WoundTypeController.php siguiendo el patron de CreditCardController.php.

- Route base: /maintainers/surgery/wound-type
- Prefijo rutas: app_maintainers_surgery_wound_type_
- Inyectar: WoundTypeRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('wt')->orderBy('wt.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): WoundTypeType::class
- createNewEntity(): new WoundType()
- getTemplatePath(): 'maintainers/surgery/wound_type/index.html.twig'
- getPageTitle(): 'create'=>'Crear Tipo de Herida', 'edit'=>'Editar Tipo de Herida', default=>'Tipos de Herida'
- Export columns: ['name', 'isActive'], headers: ['Nombre', 'Activo'], filename: 'tipos_herida_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/surgery/wound_type/index.html.twig.

- page_title: 'Tipos de Herida'
- icon: 'bx-band-aid'
- breadcrumb_section: 'Pabellon'
- description: 'Gestiona los tipos de herida quirurgica'
- Rutas: app_maintainers_surgery_wound_type_{create,edit,delete,export}
- Extends: maintainers/modern_index.html.twig
```

---

## FASE 2: Entidades con Campos Extra

Entidades con campos adicionales (color, checkboxes) y relaciones simples entre si.

---

### 2.1 SurgeryPatientStatus (PabEstadoPaciente)

**Legacy:** `PabEstadoPaciente.php` - Campos: id, nombre(150), color(20)

**NOTA:** Esta entidad NO tiene idEstado ni idEmpresa en el legacy. Es una tabla de catalogo global. En el nuevo sistema agregaremos isActive + idEstado por consistencia.

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/SurgeryPatientStatus.php siguiendo el patron de Gender.php.

- Tabla: surgery_patient_status
- Campos:
  - id: integer, PK, auto-increment
  - name: string(150), NOT NULL, Assert\NotBlank, Assert\Length(max=150)
  - color: string(20), nullable (codigo de color, ej: '#FF0000')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: SurgeryPatientStatusRepository
- Namespace: App\Entity\Tenant
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/SurgeryPatientStatusRepository.php siguiendo el patron de GenderRepository.

- Entity: SurgeryPatientStatus
- Metodos:
  - findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Surgery/SurgeryPatientStatusType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ej: En Espera, En Cirugia', class='form-control'
- color: TextType, label='Color', required=false, placeholder='Ej: #FF0000', class='form-control', attr=['maxlength'=>20]
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: SurgeryPatientStatus
Namespace: App\Form\Maintainers\Surgery
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Surgery/SurgeryPatientStatusController.php siguiendo el patron de CreditCardController.php.

- Route base: /maintainers/surgery/surgery-patient-status
- Prefijo rutas: app_maintainers_surgery_surgery_patient_status_
- Inyectar: SurgeryPatientStatusRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('sps')->orderBy('sps.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'color' => 'Color', 'isActive' => 'Estado']
- getFormType(): SurgeryPatientStatusType::class
- createNewEntity(): new SurgeryPatientStatus()
- getTemplatePath(): 'maintainers/surgery/surgery_patient_status/index.html.twig'
- getPageTitle(): 'create'=>'Crear Estado de Paciente', 'edit'=>'Editar Estado de Paciente', default=>'Estados de Paciente'
- Export columns: ['name', 'color', 'isActive'], headers: ['Nombre', 'Color', 'Activo'], filename: 'estados_paciente_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/surgery/surgery_patient_status/index.html.twig.

- page_title: 'Estados de Paciente'
- icon: 'bx-user-check'
- breadcrumb_section: 'Pabellon'
- description: 'Gestiona los estados de paciente en pabellon'
- Rutas: app_maintainers_surgery_surgery_patient_status_{create,edit,delete,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 2.2 SurgeryPatientStatusConfig (RelEmpresaPabEstadoPaciente)

**Legacy:** `RelEmpresaPabEstadoPaciente.php` - Campos: id, color(20), idPabEstadoPaciente(FK), idEmpresa

**DEPENDENCIA:** Crear SurgeryPatientStatus ANTES (2.1)

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/SurgeryPatientStatusConfig.php siguiendo el patron de Gender.php pero CON relacion ManyToOne.

- Tabla: surgery_patient_status_config
- Campos:
  - id: integer, PK, auto-increment
  - color: string(20), nullable (codigo de color personalizado por empresa)
  - surgeryPatientStatus: ManyToOne -> SurgeryPatientStatus, nullable, JoinColumn(name='surgery_patient_status_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: SurgeryPatientStatusConfigRepository
- Namespace: App\Entity\Tenant

IMPORTANTE: La relacion ManyToOne a SurgeryPatientStatus debe usar:
#[ORM\ManyToOne(targetEntity: SurgeryPatientStatus::class)]
#[ORM\JoinColumn(name: 'surgery_patient_status_id', nullable: true)]
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/SurgeryPatientStatusConfigRepository.php siguiendo el patron de GenderRepository.

- Entity: SurgeryPatientStatusConfig
- Metodos:
  - findAllActive(): orderBy id ASC
  - findByStatus(int $statusId): array
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Surgery/SurgeryPatientStatusConfigType.php.

Seguir el patron para campos con EntityType (relaciones).

Campos:
- surgeryPatientStatus: EntityType, class=SurgeryPatientStatus::class, label='Estado Paciente',
  choice_label='name', placeholder='Seleccione...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- color: TextType, label='Color', required=false, placeholder='Ej: #00FF00', class='form-control', attr=['maxlength'=>20]
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: SurgeryPatientStatusConfig
Namespace: App\Form\Maintainers\Surgery
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Surgery/SurgeryPatientStatusConfigController.php siguiendo el patron de CreditCardController.php.

- Route base: /maintainers/surgery/surgery-patient-status-config
- Prefijo rutas: app_maintainers_surgery_surgery_patient_status_config_
- Inyectar: SurgeryPatientStatusConfigRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('spsc')
    ->leftJoin('spsc.surgeryPatientStatus', 'sps')
    ->addSelect('sps')
    ->orderBy('spsc.id', 'DESC')
- getColumns(): ['surgeryPatientStatus.name' => 'Estado Paciente', 'color' => 'Color', 'isActive' => 'Estado']
- getFormType(): SurgeryPatientStatusConfigType::class
- createNewEntity(): new SurgeryPatientStatusConfig()
- getTemplatePath(): 'maintainers/surgery/surgery_patient_status_config/index.html.twig'
- getPageTitle(): 'create'=>'Crear Config. Estado Paciente', 'edit'=>'Editar Config. Estado Paciente', default=>'Config. Estados de Paciente'
- Export columns: ['surgeryPatientStatus.name', 'color', 'isActive']
- Export headers: ['Estado Paciente', 'Color', 'Activo']
- filename: 'config_estados_paciente_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/surgery/surgery_patient_status_config/index.html.twig.

- page_title: 'Config. Estados de Paciente'
- icon: 'bx-palette'
- breadcrumb_section: 'Pabellon'
- description: 'Gestiona la configuracion de estados de paciente por empresa'
- Rutas: app_maintainers_surgery_surgery_patient_status_config_{create,edit,delete,export}
- Extends: maintainers/modern_index.html.twig
```

---

## FASE 3: Entidades con Relaciones

Entidades que tienen FK a otras entidades existentes (Branch, MedicalService, SurgeryItem).

### DEPENDENCIAS PREVIAS

Antes de esta fase, asegurar que existan:
- **Branch** (Sucursal) -> YA EXISTE en el proyecto
- **MedicalService** (ServicioMedico) -> YA EXISTE en el proyecto
- **SurgeryItem** (ItemCirugia) -> YA EXISTE en el proyecto
- **SurgeryPatientStatus** -> Creado en Fase 2

---

### 3.1 TreatmentRegimen (Regimen)

**Legacy:** `Regimen.php` - Campos: id, nombre(100), idEstado, idSucursal(FK->Branch)

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/TreatmentRegimen.php siguiendo el patron de Gender.php pero CON relacion ManyToOne.

- Tabla: treatment_regimen
- Campos:
  - id: integer, PK, auto-increment
  - name: string(100), NOT NULL, Assert\NotBlank, Assert\Length(max=100)
  - branch: ManyToOne -> Branch, nullable, JoinColumn(name='branch_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: TreatmentRegimenRepository
- Namespace: App\Entity\Tenant

IMPORTANTE: La relacion ManyToOne a Branch debe usar:
#[ORM\ManyToOne(targetEntity: Branch::class)]
#[ORM\JoinColumn(name: 'branch_id', nullable: true)]
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/TreatmentRegimenRepository.php siguiendo el patron de GenderRepository.

- Entity: TreatmentRegimen
- Metodos:
  - findAllActive(): orderBy name ASC
  - findByBranch(int $branchId): array
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Surgery/TreatmentRegimenType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese nombre del regimen', class='form-control'
- branch: EntityType, class=Branch::class, label='Sucursal',
  choice_label='name', placeholder='Seleccione sucursal...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: TreatmentRegimen
Namespace: App\Form\Maintainers\Surgery
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Surgery/TreatmentRegimenController.php siguiendo el patron de CreditCardController.php.

- Route base: /maintainers/surgery/treatment-regimen
- Prefijo rutas: app_maintainers_surgery_treatment_regimen_
- Inyectar: TreatmentRegimenRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('tr')
    ->leftJoin('tr.branch', 'b')
    ->addSelect('b')
    ->orderBy('tr.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'branch.name' => 'Sucursal', 'isActive' => 'Estado']
- getFormType(): TreatmentRegimenType::class
- createNewEntity(): new TreatmentRegimen()
- getTemplatePath(): 'maintainers/surgery/treatment_regimen/index.html.twig'
- getPageTitle(): 'create'=>'Crear Regimen', 'edit'=>'Editar Regimen', default=>'Regimenes de Tratamiento'
- Export columns: ['name', 'branch.name', 'isActive']
- Export headers: ['Nombre', 'Sucursal', 'Activo']
- filename: 'regimenes_tratamiento_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/surgery/treatment_regimen/index.html.twig.

- page_title: 'Regimenes de Tratamiento'
- icon: 'bx-list-check'
- breadcrumb_section: 'Pabellon'
- description: 'Gestiona los regimenes de tratamiento por sucursal'
- Rutas: app_maintainers_surgery_treatment_regimen_{create,edit,delete,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 3.2 SurgicalTeamRole (PabRolEquipoQuirurgico)

**Legacy:** `PabRolEquipoQuirurgico.php` - Campos: id, orden(int), nombre(100), idItemPaqueteCirugia(FK->SurgeryItem), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/SurgicalTeamRole.php siguiendo el patron de Gender.php pero CON relacion ManyToOne y campo extra.

- Tabla: surgical_team_role
- Campos:
  - id: integer, PK, auto-increment
  - sortOrder: integer, NOT NULL, default 0, column: sort_order (legacy: orden)
  - name: string(100), NOT NULL, Assert\NotBlank, Assert\Length(max=100)
  - surgeryItem: ManyToOne -> SurgeryItem, nullable, JoinColumn(name='surgery_item_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: SurgicalTeamRoleRepository
- Namespace: App\Entity\Tenant

IMPORTANTE: La relacion ManyToOne a SurgeryItem debe usar:
#[ORM\ManyToOne(targetEntity: SurgeryItem::class)]
#[ORM\JoinColumn(name: 'surgery_item_id', nullable: true)]

Mapeo columnas:
- sortOrder -> column: sort_order
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/SurgicalTeamRoleRepository.php siguiendo el patron de GenderRepository.

- Entity: SurgicalTeamRole
- Metodos:
  - findAllActive(): orderBy sortOrder ASC
  - findAllOrdered(): orderBy sortOrder ASC (incluye inactivos)
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Surgery/SurgicalTeamRoleType.php.

Campos:
- sortOrder: IntegerType, label='Orden', required, class='form-control', attr=['min'=>0]
- name: TextType, label='Nombre', required, placeholder='Ej: Cirujano, Anestesiologo', class='form-control'
- surgeryItem: EntityType, class=SurgeryItem::class, label='Item Cirugia',
  choice_label='name', placeholder='Seleccione...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: SurgicalTeamRole
Namespace: App\Form\Maintainers\Surgery
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Surgery/SurgicalTeamRoleController.php siguiendo el patron de CreditCardController.php.

- Route base: /maintainers/surgery/surgical-team-role
- Prefijo rutas: app_maintainers_surgery_surgical_team_role_
- Inyectar: SurgicalTeamRoleRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('str')
    ->leftJoin('str.surgeryItem', 'si')
    ->addSelect('si')
    ->orderBy('str.sortOrder', 'ASC')
- getColumns(): ['sortOrder' => 'Orden', 'name' => 'Nombre', 'surgeryItem.name' => 'Item Cirugia', 'isActive' => 'Estado']
- getFormType(): SurgicalTeamRoleType::class
- createNewEntity(): new SurgicalTeamRole()
- getTemplatePath(): 'maintainers/surgery/surgical_team_role/index.html.twig'
- getPageTitle(): 'create'=>'Crear Rol Equipo Quirurgico', 'edit'=>'Editar Rol Equipo Quirurgico', default=>'Roles Equipo Quirurgico'
- Export columns: ['sortOrder', 'name', 'surgeryItem.name', 'isActive']
- Export headers: ['Orden', 'Nombre', 'Item Cirugia', 'Activo']
- filename: 'roles_equipo_quirurgico_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/surgery/surgical_team_role/index.html.twig.

- page_title: 'Roles Equipo Quirurgico'
- icon: 'bx-group'
- breadcrumb_section: 'Pabellon'
- description: 'Gestiona los roles del equipo quirurgico'
- Rutas: app_maintainers_surgery_surgical_team_role_{create,edit,delete,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 3.3 SurgicalBlock (Pabellon)

**Legacy:** `Pabellon.php` - Campos: id, nombre(150), idEstado, idServicio(FK->MedicalService)

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/SurgicalBlock.php siguiendo el patron de Gender.php pero CON relacion ManyToOne.

- Tabla: surgical_block
- Campos:
  - id: integer, PK, auto-increment
  - name: string(150), NOT NULL, Assert\NotBlank, Assert\Length(max=150)
  - medicalService: ManyToOne -> MedicalService, nullable, JoinColumn(name='medical_service_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: SurgicalBlockRepository
- Namespace: App\Entity\Tenant

IMPORTANTE: La relacion ManyToOne a MedicalService debe usar:
#[ORM\ManyToOne(targetEntity: MedicalService::class)]
#[ORM\JoinColumn(name: 'medical_service_id', nullable: true)]
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/SurgicalBlockRepository.php siguiendo el patron de GenderRepository.

- Entity: SurgicalBlock
- Metodos:
  - findAllActive(): orderBy name ASC
  - findByService(int $serviceId): array
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Surgery/SurgicalBlockType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ej: Pabellon 1, Quirofano A', class='form-control'
- medicalService: EntityType, class=MedicalService::class, label='Servicio Medico',
  choice_label='name', placeholder='Seleccione servicio...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: SurgicalBlock
Namespace: App\Form\Maintainers\Surgery
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Surgery/SurgicalBlockController.php siguiendo el patron de CreditCardController.php.

- Route base: /maintainers/surgery/surgical-block
- Prefijo rutas: app_maintainers_surgery_surgical_block_
- Inyectar: SurgicalBlockRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('sb')
    ->leftJoin('sb.medicalService', 'ms')
    ->addSelect('ms')
    ->orderBy('sb.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'medicalService.name' => 'Servicio Medico', 'isActive' => 'Estado']
- getFormType(): SurgicalBlockType::class
- createNewEntity(): new SurgicalBlock()
- getTemplatePath(): 'maintainers/surgery/surgical_block/index.html.twig'
- getPageTitle(): 'create'=>'Crear Pabellon', 'edit'=>'Editar Pabellon', default=>'Pabellones'
- Export columns: ['name', 'medicalService.name', 'isActive']
- Export headers: ['Nombre', 'Servicio Medico', 'Activo']
- filename: 'pabellones_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/surgery/surgical_block/index.html.twig.

- page_title: 'Pabellones'
- icon: 'bx-door-open'
- breadcrumb_section: 'Pabellon'
- description: 'Gestiona los pabellones quirurgicos del sistema'
- Rutas: app_maintainers_surgery_surgical_block_{create,edit,delete,export}
- Extends: maintainers/modern_index.html.twig
```

---

## FASE 4: Entidades Complejas

### 4.1 SurgicalStage (PabEtapa)

**Legacy:** `PabEtapa.php` - 12 campos incluyendo FK a Branch, booleans multiples, timestamps de auditoria

#### Prompt Copilot - Entity SurgicalStage

```
Crea src/Entity/Tenant/SurgicalStage.php. Esta es una entidad COMPLEJA de Pabellon.

- Tabla: surgical_stage
- Campos:
  - id: integer, PK, auto-increment
  - sortOrder: integer, NOT NULL, default 0, column: sort_order (legacy: orden)
  - abbreviation: string(255), nullable, column: abbreviation (legacy: nombreAbreviado)
  - name: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - description: string(2000), nullable, Assert\Length(max=2000)
  - isMandatory: boolean, default false, column: is_mandatory (legacy: esObligatorio)
  - requiresLogin: boolean, default false, column: requires_login (legacy: requiereLogin)
  - isSequential: boolean, default false, column: is_sequential (legacy: esSecuencia)
  - templateStageId: integer, nullable, column: template_stage_id (legacy: idPabEtapaPlantilla - referencia a plantilla)
  - branch: ManyToOne -> Branch, nullable, JoinColumn(name='branch_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor (legacy: fechaCreacion)
  - updatedAt: datetime, nullable
  - createdBy: integer, nullable, column: created_by (legacy: idUsuarioCreacion)
- Repository: SurgicalStageRepository
- Namespace: App\Entity\Tenant

IMPORTANTE:
- NO agregar campo idEmpresa (multi-tenancy por Hakam)
- El campo templateStageId es un integer simple (no FK por ahora, puede apuntar a plantillas futuras)
- El campo createdBy es un integer simple (referencia a usuario, no FK por seguridad cross-schema)

Mapeo columnas:
- sortOrder -> column: sort_order
- abbreviation -> column: abbreviation
- isMandatory -> column: is_mandatory
- requiresLogin -> column: requires_login
- isSequential -> column: is_sequential
- templateStageId -> column: template_stage_id
- createdBy -> column: created_by
```

#### Prompt Copilot - Repository SurgicalStage

```
Crea src/Repository/Tenant/SurgicalStageRepository.php siguiendo el patron de GenderRepository.

- Entity: SurgicalStage
- Metodos:
  - findAllActive(): orderBy sortOrder ASC
  - findByBranch(int $branchId): array - orderBy sortOrder ASC
  - findAllOrdered(): orderBy sortOrder ASC (incluye inactivos)
```

#### Prompt Copilot - FormType SurgicalStage

```
Crea src/Form/Maintainers/Surgery/SurgicalStageType.php.

Campos:
- sortOrder: IntegerType, label='Orden', required, class='form-control', attr=['min'=>0]
- abbreviation: TextType, label='Abreviacion', required=false, placeholder='Nombre abreviado', class='form-control'
- name: TextType, label='Nombre', required, placeholder='Nombre de la etapa', class='form-control'
- description: TextareaType, label='Descripcion', required=false, class='form-control', attr=['rows'=>3, 'maxlength'=>2000]
- isMandatory: CheckboxType, label='Obligatorio', required=false, class='form-check-input'
- requiresLogin: CheckboxType, label='Requiere Login', required=false, class='form-check-input'
- isSequential: CheckboxType, label='Es Secuencial', required=false, class='form-check-input'
- branch: EntityType, class=Branch::class, label='Sucursal',
  choice_label='name', placeholder='Seleccione sucursal...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: SurgicalStage
Namespace: App\Form\Maintainers\Surgery
```

#### Prompt Copilot - Controller SurgicalStage

```
Crea src/Controller/Maintainers/Surgery/SurgicalStageController.php siguiendo el patron de CreditCardController.php.

- Route base: /maintainers/surgery/surgical-stage
- Prefijo rutas: app_maintainers_surgery_surgical_stage_
- Inyectar: SurgicalStageRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('ss')
    ->leftJoin('ss.branch', 'b')
    ->addSelect('b')
    ->orderBy('ss.sortOrder', 'ASC')
- getColumns(): ['sortOrder' => 'Orden', 'abbreviation' => 'Abreviacion', 'name' => 'Nombre', 'isMandatory' => 'Obligatorio', 'requiresLogin' => 'Req. Login', 'isSequential' => 'Secuencial', 'branch.name' => 'Sucursal', 'isActive' => 'Estado']
- getFormType(): SurgicalStageType::class
- createNewEntity(): new SurgicalStage()
- getTemplatePath(): 'maintainers/surgery/surgical_stage/index.html.twig'
- getPageTitle(): 'create'=>'Crear Etapa Quirurgica', 'edit'=>'Editar Etapa Quirurgica', default=>'Etapas Quirurgicas'
- Export columns: ['sortOrder', 'abbreviation', 'name', 'description', 'isMandatory', 'requiresLogin', 'isSequential', 'branch.name', 'isActive']
- Export headers: ['Orden', 'Abreviacion', 'Nombre', 'Descripcion', 'Obligatorio', 'Req. Login', 'Secuencial', 'Sucursal', 'Activo']
- filename: 'etapas_quirurgicas_'.date('Y-m-d').'.csv'

NOTA: Las validaciones de negocio del legacy se implementaran como segundo paso si se requieren.
Por ahora solo el CRUD basico.
```

#### Prompt Copilot - Template SurgicalStage

```
Crea templates/maintainers/surgery/surgical_stage/index.html.twig.

- page_title: 'Etapas Quirurgicas'
- icon: 'bx-list-ol'
- breadcrumb_section: 'Pabellon'
- description: 'Gestiona las etapas del proceso quirurgico'
- Rutas: app_maintainers_surgery_surgical_stage_{create,edit,delete,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 4.2 SurgicalStageItem (PabEtapaItem)

**Legacy:** `PabEtapaItem.php` - 11 campos incluyendo auto-referencia, FK a SurgicalStage, timestamps de auditoria

**DEPENDENCIA:** Crear SurgicalStage ANTES (4.1)

#### Prompt Copilot - Entity SurgicalStageItem

```
Crea src/Entity/Tenant/SurgicalStageItem.php. Esta es la entidad MAS COMPLEJA de Pabellon.

- Tabla: surgical_stage_item
- Campos:
  - id: integer, PK, auto-increment
  - sortOrder: integer, NOT NULL, default 0, column: sort_order (legacy: orden)
  - name: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - alternatives: string(2000), nullable, Assert\Length(max=2000) (legacy: alternativas)
  - hasResponse: boolean, default false, column: has_response (legacy: tieneRespuesta)
  - isMandatory: boolean, default false, column: is_mandatory (legacy: esObligatorio)
  - itemTypeId: integer, nullable, column: item_type_id (legacy: idPabEtapaItemTipo - referencia a tipo)
  - parent: ManyToOne -> SurgicalStageItem (SELF), nullable, JoinColumn(name='parent_id')
  - surgicalStage: ManyToOne -> SurgicalStage, NOT NULL, JoinColumn(name='surgical_stage_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor (legacy: fechaCreacion)
  - updatedAt: datetime, nullable
  - createdBy: integer, nullable, column: created_by (legacy: idUsuarioCreacion)
  - children: OneToMany -> SurgicalStageItem, mappedBy='parent' (NO cascade, solo lectura)
- Repository: SurgicalStageItemRepository
- Namespace: App\Entity\Tenant

IMPORTANTE:
- La relacion parent es AUTO-REFERENCIA (ManyToOne a si mismo)
- children es la inversa de parent (OneToMany, mappedBy='parent')
- surgicalStage es NOT NULL (todo item pertenece a una etapa)
- El campo itemTypeId es un integer simple (no FK por ahora)
- El campo createdBy es un integer simple (referencia a usuario)
- NO agregar campo idEmpresa (multi-tenancy por Hakam)

Mapeo columnas:
- sortOrder -> column: sort_order
- hasResponse -> column: has_response
- isMandatory -> column: is_mandatory
- itemTypeId -> column: item_type_id
- createdBy -> column: created_by
```

#### Prompt Copilot - Repository SurgicalStageItem

```
Crea src/Repository/Tenant/SurgicalStageItemRepository.php siguiendo el patron de GenderRepository.

- Entity: SurgicalStageItem
- Metodos:
  - findAllActive(): orderBy sortOrder ASC
  - findByStage(int $stageId): array - orderBy sortOrder ASC
  - findRootItemsByStage(int $stageId): array - donde parent IS NULL, orderBy sortOrder ASC
```

#### Prompt Copilot - FormType SurgicalStageItem

```
Crea src/Form/Maintainers/Surgery/SurgicalStageItemType.php.

NOTA: Nombrar SurgicalStageItemType (no hay conflicto en este caso).

Campos:
- sortOrder: IntegerType, label='Orden', required, class='form-control', attr=['min'=>0]
- name: TextType, label='Nombre', required, placeholder='Nombre del item', class='form-control'
- alternatives: TextareaType, label='Alternativas', required=false, class='form-control', attr=['rows'=>3, 'maxlength'=>2000]
- hasResponse: CheckboxType, label='Tiene Respuesta', required=false, class='form-check-input'
- isMandatory: CheckboxType, label='Obligatorio', required=false, class='form-check-input'
- surgicalStage: EntityType, class=SurgicalStage::class, label='Etapa Quirurgica',
  choice_label='name', placeholder='Seleccione etapa...', required=true, class='form-select',
  query_builder: filtrar por isActive=true, orderBy sortOrder ASC
- parent: EntityType, class=SurgicalStageItem::class, label='Item Padre',
  choice_label='name', placeholder='Sin padre (raiz)', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy sortOrder ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: SurgicalStageItem
Namespace: App\Form\Maintainers\Surgery
```

#### Prompt Copilot - Controller SurgicalStageItem

```
Crea src/Controller/Maintainers/Surgery/SurgicalStageItemController.php siguiendo el patron de CreditCardController.php.

- Route base: /maintainers/surgery/surgical-stage-item
- Prefijo rutas: app_maintainers_surgery_surgical_stage_item_
- Inyectar: SurgicalStageItemRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('ssi')
    ->leftJoin('ssi.surgicalStage', 'ss')
    ->addSelect('ss')
    ->leftJoin('ssi.parent', 'p')
    ->addSelect('p')
    ->orderBy('ss.sortOrder', 'ASC')
    ->addOrderBy('ssi.sortOrder', 'ASC')
- getColumns(): ['sortOrder' => 'Orden', 'name' => 'Nombre', 'surgicalStage.name' => 'Etapa', 'parent.name' => 'Item Padre', 'hasResponse' => 'Tiene Respuesta', 'isMandatory' => 'Obligatorio', 'isActive' => 'Estado']
- getFormType(): SurgicalStageItemType::class
- createNewEntity(): new SurgicalStageItem()
- getTemplatePath(): 'maintainers/surgery/surgical_stage_item/index.html.twig'
- getPageTitle(): 'create'=>'Crear Item de Etapa', 'edit'=>'Editar Item de Etapa', default=>'Items de Etapas Quirurgicas'
- Export columns: ['sortOrder', 'name', 'alternatives', 'surgicalStage.name', 'parent.name', 'hasResponse', 'isMandatory', 'isActive']
- Export headers: ['Orden', 'Nombre', 'Alternativas', 'Etapa', 'Item Padre', 'Tiene Respuesta', 'Obligatorio', 'Activo']
- filename: 'items_etapas_quirurgicas_'.date('Y-m-d').'.csv'

NOTA: Las validaciones de negocio del legacy se implementaran como segundo paso si se requieren.
Por ahora solo el CRUD basico.
```

#### Prompt Copilot - Template SurgicalStageItem

```
Crea templates/maintainers/surgery/surgical_stage_item/index.html.twig.

- page_title: 'Items de Etapas Quirurgicas'
- icon: 'bx-check-square'
- breadcrumb_section: 'Pabellon'
- description: 'Gestiona los items de cada etapa del proceso quirurgico'
- Rutas: app_maintainers_surgery_surgical_stage_item_{create,edit,delete,export}
- Extends: maintainers/modern_index.html.twig
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
1. Fase 1: AnesthesiaType, BloodType, SurgerySuspensionCause, SurgeryBlockReason, SurgeryCancellationReason, WoundType
2. Fase 2: SurgeryPatientStatus, SurgeryPatientStatusConfig
3. Fase 3: TreatmentRegimen, SurgicalTeamRole, SurgicalBlock
4. Fase 4: SurgicalStage, SurgicalStageItem
5. Migracion BD
6. Registro en Menu (MenuItem)
7. Validacion Multi-Tenant
```

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

### Paso 2: Insertar categoria + 13 mantenedores

Reemplazar los IDs segun corresponda. En este ejemplo:
- `{MANTENEDORES_ID}` = 4 (padre Mantenedores)
- ID base = 72 (siguiente disponible, despues de Tesoreria)

```sql
-- Categoria Pabellon (hijo de Mantenedores)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES (72, 'maintenance_surgery', 'Pabellon', NULL, 'bx bx-plus-medical', NULL, 4, 6, true, true, true, '["ROLE_USER"]', NOW());

-- 13 mantenedores (hijos de Pabellon, parent_id = 72)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES
-- Tipos y catalogos simples (Fase 1)
(73, 'anesthesia_type', 'Tipos de Anestesia', 'app_maintainers_surgery_anesthesia_type_index', 'bx bx-injection', 'maintenance_surgery', 72, 1, true, true, true, '["ROLE_USER"]', NOW()),
(74, 'blood_type', 'Grupos Sanguineos', 'app_maintainers_surgery_blood_type_index', 'bx bx-droplet', 'maintenance_surgery', 72, 2, true, true, true, '["ROLE_USER"]', NOW()),
(75, 'surgery_suspension_cause', 'Causas de Suspension', 'app_maintainers_surgery_surgery_suspension_cause_index', 'bx bx-pause-circle', 'maintenance_surgery', 72, 3, true, true, true, '["ROLE_USER"]', NOW()),
(76, 'surgery_block_reason', 'Motivos de Bloqueo', 'app_maintainers_surgery_surgery_block_reason_index', 'bx bx-block', 'maintenance_surgery', 72, 4, true, true, true, '["ROLE_USER"]', NOW()),
(77, 'surgery_cancellation_reason', 'Motivos de Anulacion', 'app_maintainers_surgery_surgery_cancellation_reason_index', 'bx bx-x-circle', 'maintenance_surgery', 72, 5, true, true, true, '["ROLE_USER"]', NOW()),
(78, 'wound_type', 'Tipos de Herida', 'app_maintainers_surgery_wound_type_index', 'bx bx-band-aid', 'maintenance_surgery', 72, 6, true, true, true, '["ROLE_USER"]', NOW()),
-- Estados de paciente (Fase 2)
(79, 'surgery_patient_status', 'Estados de Paciente', 'app_maintainers_surgery_surgery_patient_status_index', 'bx bx-user-check', 'maintenance_surgery', 72, 7, true, true, true, '["ROLE_USER"]', NOW()),
(80, 'surgery_patient_status_config', 'Config. Estados Paciente', 'app_maintainers_surgery_surgery_patient_status_config_index', 'bx bx-palette', 'maintenance_surgery', 72, 8, true, true, true, '["ROLE_USER"]', NOW()),
-- Entidades con relaciones (Fase 3)
(81, 'treatment_regimen', 'Regimenes de Tratamiento', 'app_maintainers_surgery_treatment_regimen_index', 'bx bx-list-check', 'maintenance_surgery', 72, 9, true, true, true, '["ROLE_USER"]', NOW()),
(82, 'surgical_team_role', 'Roles Equipo Quirurgico', 'app_maintainers_surgery_surgical_team_role_index', 'bx bx-group', 'maintenance_surgery', 72, 10, true, true, true, '["ROLE_USER"]', NOW()),
(83, 'surgical_block', 'Pabellones', 'app_maintainers_surgery_surgical_block_index', 'bx bx-door-open', 'maintenance_surgery', 72, 11, true, true, true, '["ROLE_USER"]', NOW()),
-- Entidades complejas (Fase 4)
(84, 'surgical_stage', 'Etapas Quirurgicas', 'app_maintainers_surgery_surgical_stage_index', 'bx bx-list-ol', 'maintenance_surgery', 72, 12, true, true, true, '["ROLE_USER"]', NOW()),
(85, 'surgical_stage_item', 'Items Etapas Quirurgicas', 'app_maintainers_surgery_surgical_stage_item_index', 'bx bx-check-square', 'maintenance_surgery', 72, 13, true, true, true, '["ROLE_USER"]', NOW());
```

### Paso 3: Limpiar cache de menu

```bash
php bin/console cache:clear
```

### Rollback (en caso de necesitar borrar)

```sql
DELETE FROM menu_items WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintenance_surgery');
DELETE FROM menu_items WHERE name = 'maintenance_surgery';
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
| Entities | 13 | src/Entity/Tenant/ |
| Repositories | 13 | src/Repository/Tenant/ |
| FormTypes | 13 | src/Form/Maintainers/Surgery/ |
| Controllers | 13 | src/Controller/Maintainers/Surgery/ |
| Templates | 13 | templates/maintainers/surgery/ |
| **TOTAL** | **65 archivos** | |

(13 mantenedores = 13 entidades, sin dependencias externas nuevas)
