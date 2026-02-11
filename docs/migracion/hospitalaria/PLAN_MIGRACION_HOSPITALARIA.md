# Plan de Migracion - Categoria Hospitalaria

## Resumen Ejecutivo

**Categoria:** MantenedorHospitalaria
**Origen Legacy:** `melisa_prod/src/Rebsol/MantenedoresBundle/Controller/_Default/MantenedorMaestro/MantenedorEmpresa/MantenedorHospitalaria/`
**Destino Nuevo:** `melisa_tenant/src/Controller/Maintainers/Hospital/`
**Total Entidades:** 24 mantenedores (0 dependencias externas nuevas)
**Complejidad Global:** Alta

---

## Inventario Completo

### Entidades a Migrar

| # | Legacy (ES) | Nuevo (EN) | Tabla Nueva | Complejidad | Fase |
|---|-------------|------------|-------------|-------------|------|
| 1 | RchDispositivo | MedicalDevice | medical_device | Simple | 1 |
| 2 | EstadoEbriedad | IntoxicationState | intoxication_state | Simple | 1 |
| 3 | CierreAtencionFcDestino | CareClosureDestination | care_closure_destination | Simple+ | 1 |
| 4 | TipoPosologia | DosageType | dosage_type | Simple | 1 |
| 5 | RchTipoRecetaFarmacos | PrescriptionType | prescription_type | Simple+ | 1 |
| 6 | RchCategoriaCuidados | CareCategory | care_category | Simple | 1 |
| 7 | RchIngresoNutricionistaIndiceImc | NutritionistBmiIndex | nutritionist_bmi_index | Simple | 1 |
| 8 | RchIngresoNutricionistaIndiceTe | NutritionistTeIndex | nutritionist_te_index | Simple | 1 |
| 9 | RchIngresoNutricionistaIndiceClasificacion | NutritionistIndexClassification | nutritionist_index_classification | Simple | 1 |
| 10 | RchIngresoNutricionistaDiagnosticoNutricional | NutritionalDiagnosis | nutritional_diagnosis | Simple | 1 |
| 11 | RchIngresoNutricionistaAntecedenteTca | EatingDisorderHistory | eating_disorder_history | Simple | 1 |
| 12 | CategoriaAc | ClinicalActionCategory | clinical_action_category | Simple | 1 |
| 13 | RchRecetaFrecuencia | PrescriptionFrequency | prescription_frequency | Simple+ | 2 |
| 14 | RchRecetaVia | PrescriptionRoute | prescription_route | Simple+ | 2 |
| 15 | RchRecetaDosis | PrescriptionDosage | prescription_dosage | Simple+ | 2 |
| 16 | RchRecetaFormato | PrescriptionFormat | prescription_format | Simple+ | 2 |
| 17 | RchRecetaDispensacion | PrescriptionDispensation | prescription_dispensation | Simple+ | 2 |
| 18 | ExamenFisicoAgrupacion | PhysicalExamGrouping | physical_exam_grouping | Simple+ | 2 |
| 19 | RchCuidados | CareIntervention | care_intervention | Moderada | 3 |
| 20 | PreguntaAc | ClinicalActionQuestion | clinical_action_question | Moderada+ | 3 |
| 21 | RespuestaAc | ClinicalActionAnswer | clinical_action_answer | Moderada | 3 |
| 22 | ExamenFisicoCampoBase | PhysicalExamBaseField | physical_exam_base_field | Compleja | 4 |
| 23 | ExamenFisicoCampo | PhysicalExamField | physical_exam_field | Compleja | 4 |
| 24 | RchRecetaDetallePrescripcionReglaDetalle | PrescriptionRuleDetail | prescription_rule_detail | Moderada | 4 |

### Dependencias Externas (ya existentes en el proyecto)

| Entidad (EN) | Tabla | Requerido Por | Estado |
|--------------|-------|---------------|--------|
| *(ninguna)* | - | - | - |

### Dependencias Internas (orden de creacion)

| Entidad | Depende De | Fase |
|---------|------------|------|
| CareIntervention | CareCategory | 3 (crear CareCategory en Fase 1 antes) |
| ClinicalActionQuestion | ClinicalActionCategory | 3 (crear ClinicalActionCategory en Fase 1 antes) |
| ClinicalActionAnswer | ClinicalActionQuestion | 3 (crear ClinicalActionQuestion antes) |
| PhysicalExamField | PhysicalExamGrouping | 4 (crear PhysicalExamGrouping en Fase 2 antes) |

### Entidades Descartadas

| Legacy | Razon |
|--------|-------|
| RelItemAtencionCategoriaAc | Entidad junction con dependencia a ItemAtencion (no migrado aun) |
| EmpresaFormularioItemAtencion | Entidad junction con dependencias no migradas |
| FormularioExamenFisico | Constructor de formularios complejo con dependencias no migradas |

---

## Patron a Seguir (Referencia)

Todos los archivos nuevos deben seguir EXACTAMENTE el patron existente:

| Tipo | Ejemplo Referencia | Ubicacion |
|------|-------------------|-----------|
| Entity | `src/Entity/Tenant/Gender.php` | `src/Entity/Tenant/` |
| Repository | `src/Repository/Tenant/GenderRepository.php` | `src/Repository/Tenant/` |
| FormType | `src/Form/Maintainers/Personal/GenderType.php` | `src/Form/Maintainers/Hospital/` |
| Controller | `src/Controller/Maintainers/Treasury/CreditCardController.php` | `src/Controller/Maintainers/Hospital/` |
| Template | `templates/maintainers/basic/gender/index.html.twig` | `templates/maintainers/hospital/` |

### Herencia de Controllers

```
AbstractController
  -> AbstractTenantAwareController
    -> AbstractMantenedorController (Template Method Pattern)
      -> [Tu nuevo controller]
```

### Convenciones de Nombres

- **Rutas:** `app_maintainers_hospital_{entity_snake}_{action}`
- **Ejemplo:** `app_maintainers_hospital_medical_device_index`
- **Acciones:** `index`, `create`, `edit`, `delete`, `export`

### Nuevo Formato: getColumns() Asociativo

**IMPORTANTE:** A partir de febrero 2026, todos los controllers usan formato asociativo para columnas.
**Referencia principal:** `src/Controller/Maintainers/Treasury/CreditCardController.php`

```php
// Formato NUEVO (usar siempre)
protected function getColumns(): array {
    return [
        'name' => 'Nombre',
        'description' => 'Descripcion',
        'isActive' => 'Estado'
    ];
}
```

**Relaciones (con addSelect en getData):**
```php
'careCategory.description' => 'Categoria',    // Relacion ManyToOne
'clinicalActionCategory.name' => 'Categoria',  // Relacion ManyToOne
```

**getData() con joins (patron CreditCardController):**
```php
protected function getData(Request $request): array|QueryBuilder
{
    return $this->repository->createQueryBuilder('e')
        ->leftJoin('e.careCategory', 'cc')
        ->addSelect('cc')
        ->orderBy('e.id', 'DESC');
}
```

---

## FASE 1: Entidades Simples

Entidades con campos basicos (`name`, `description`, `isActive`) sin relaciones FK. CRUD basico.

---

### 1.1 MedicalDevice (RchDispositivo)

**Legacy:** `RchDispositivo.php` - Campos: id, nombre(50), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea el archivo src/Entity/Tenant/MedicalDevice.php siguiendo EXACTAMENTE el patron de src/Entity/Tenant/Gender.php.

Especificaciones:
- Tabla: medical_device
- Campos:
  - id: integer, PK, auto-increment
  - name: string(50), NOT NULL, Assert\NotBlank, Assert\Length(max=50)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable

- Incluir getters/setters para todos los campos
- Constructor inicializa createdAt = new \DateTime()
- Namespace: App\Entity\Tenant
- Repository: MedicalDeviceRepository

RESTRICCIONES:
- NO agregar campo idEmpresa (multi-tenancy por Hakam)
- NO agregar relaciones adicionales
- Usar PHP 8.2 attributes (#[ORM\...])
- Usar Assert constraints de Symfony
```

#### Prompt Copilot - Repository

```
Crea el archivo src/Repository/Tenant/MedicalDeviceRepository.php siguiendo EXACTAMENTE el patron de src/Repository/Tenant/GenderRepository.php.

- Extends ServiceEntityRepository
- Entity class: MedicalDevice
- Metodos:
  - findAllActive(): array - orderBy name ASC, where isActive=true
- Namespace: App\Repository\Tenant
```

#### Prompt Copilot - FormType

```
Crea el archivo src/Form/Maintainers/Hospital/MedicalDeviceType.php siguiendo EXACTAMENTE el patron de src/Form/Maintainers/Personal/GenderType.php.

Campos del formulario:
- name: TextType, label='Nombre', required, placeholder='Ingrese nombre del dispositivo', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: MedicalDevice
Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea el archivo src/Controller/Maintainers/Hospital/MedicalDeviceController.php siguiendo EXACTAMENTE el patron de src/Controller/Maintainers/Treasury/CreditCardController.php.

Especificaciones:
- Route base: /maintainers/hospital/medical-device
- Rutas:
  - GET '' -> index (app_maintainers_hospital_medical_device_index)
  - GET/POST '/create' -> create (app_maintainers_hospital_medical_device_create)
  - GET/POST '/{id}/edit' -> edit (app_maintainers_hospital_medical_device_edit)
  - POST '/{id}/delete' -> delete (app_maintainers_hospital_medical_device_delete)
  - GET '/export' -> export (app_maintainers_hospital_medical_device_export)

- Inyectar: MedicalDeviceRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('md')->orderBy('md.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): MedicalDeviceType::class
- createNewEntity(): new MedicalDevice()
- getTemplatePath(): 'maintainers/hospital/medical_device/index.html.twig'
- getPageTitle(): 'create'=>'Crear Dispositivo Medico', 'edit'=>'Editar Dispositivo Medico', default=>'Dispositivos Medicos'

- Export columns: ['name', 'isActive']
- Export headers: ['Nombre', 'Activo']
- Export filename: 'dispositivos_medicos_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea el archivo templates/maintainers/hospital/medical_device/index.html.twig siguiendo EXACTAMENTE el patron de templates/maintainers/basic/gender/index.html.twig.

Variables a configurar:
- page_title: 'Dispositivos Medicos'
- icon: 'bx-devices'
- breadcrumb_section: 'Hospitalaria'
- description: 'Gestiona los dispositivos medicos del sistema'
- create_route: 'app_maintainers_hospital_medical_device_create'
- export_route: 'app_maintainers_hospital_medical_device_export'

Extends: maintainers/modern_index.html.twig
```

---

### 1.2 IntoxicationState (EstadoEbriedad)

**Legacy:** `EstadoEbriedad.php` - Campos: id, nombre(45), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/IntoxicationState.php siguiendo el patron de Gender.php.

- Tabla: intoxication_state
- Campos:
  - id: integer, PK, auto-increment
  - name: string(45), NOT NULL, Assert\NotBlank, Assert\Length(max=45)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: IntoxicationStateRepository
- NO agregar campo idEmpresa
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/IntoxicationStateRepository.php siguiendo el patron de GenderRepository.
- Entity: IntoxicationState
- Metodo findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/IntoxicationStateType.php siguiendo el patron de GenderType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ej: Sobrio, Leve, Moderado', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: IntoxicationState
Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/IntoxicationStateController.php siguiendo el patron de CreditCardController.php.

- Route base: /maintainers/hospital/intoxication-state
- Prefijo rutas: app_maintainers_hospital_intoxication_state_
- Inyectar: IntoxicationStateRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('is2')->orderBy('is2.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/intoxication_state/index.html.twig'
- getPageTitle(): 'create'=>'Crear Estado de Ebriedad', 'edit'=>'Editar Estado de Ebriedad', default=>'Estados de Ebriedad'
- Export columns: ['name', 'isActive'], headers: ['Nombre', 'Activo']
- filename: 'estados_ebriedad_'.date('Y-m-d').'.csv'

NOTA: Usar alias 'is2' en QueryBuilder porque 'is' es palabra reservada de PHP.
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/intoxication_state/index.html.twig.
- page_title: 'Estados de Ebriedad'
- icon: 'bx-wine'
- breadcrumb_section: 'Hospitalaria'
- description: 'Gestiona los estados de ebriedad del sistema'
- Rutas: app_maintainers_hospital_intoxication_state_{create,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 1.3 CareClosureDestination (CierreAtencionFcDestino)

**Legacy:** `CierreAtencionFcDestino.php` - Campos: id, nombre(45), tieneDetalle(bool)

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/CareClosureDestination.php siguiendo el patron de Gender.php.

- Tabla: care_closure_destination
- Campos:
  - id: integer, PK, auto-increment
  - name: string(45), NOT NULL, Assert\NotBlank, Assert\Length(max=45)
  - hasDetail: boolean, default false (legacy: tieneDetalle)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: CareClosureDestinationRepository
- NO agregar campo idEmpresa
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/CareClosureDestinationRepository.php siguiendo el patron de GenderRepository.
- Entity: CareClosureDestination
- Metodo findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/CareClosureDestinationType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ej: Alta medica, Traslado', class='form-control'
- hasDetail: CheckboxType, label='Tiene Detalle', required=false, class='form-check-input'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: CareClosureDestination
Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/CareClosureDestinationController.php siguiendo el patron de CreditCardController.

- Route base: /maintainers/hospital/care-closure-destination
- Prefijo rutas: app_maintainers_hospital_care_closure_destination_
- getData(): createQueryBuilder('ccd')->orderBy('ccd.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'hasDetail' => 'Tiene Detalle', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/care_closure_destination/index.html.twig'
- getPageTitle(): default=>'Destinos de Cierre de Atencion'
- Export columns: ['name', 'hasDetail', 'isActive'], headers: ['Nombre', 'Tiene Detalle', 'Activo']
- filename: 'destinos_cierre_atencion_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/care_closure_destination/index.html.twig.
- page_title: 'Destinos de Cierre de Atencion'
- icon: 'bx-log-out'
- breadcrumb_section: 'Hospitalaria'
- Rutas: app_maintainers_hospital_care_closure_destination_{create,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 1.4 DosageType (TipoPosologia)

**Legacy:** `TipoPosologia.php` - Campos: id, nombre(255)

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/DosageType.php siguiendo el patron de Gender.php.

- Tabla: dosage_type
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: DosageTypeRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/DosageTypeRepository.php siguiendo el patron de GenderRepository.
- Entity: DosageType
- Metodo findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/DosageTypeFormType.php.

NOTA: Nombrar DosageTypeFormType para evitar conflicto con la entidad DosageType.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ej: Oral, Intravenosa', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: DosageType
Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/DosageTypeController.php siguiendo el patron de CreditCardController.

- Route base: /maintainers/hospital/dosage-type
- Prefijo rutas: app_maintainers_hospital_dosage_type_
- getData(): createQueryBuilder('dt')->orderBy('dt.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): DosageTypeFormType::class
- getTemplatePath(): 'maintainers/hospital/dosage_type/index.html.twig'
- getPageTitle(): default=>'Tipos de Posologia'
- Export columns: ['name', 'isActive'], headers: ['Nombre', 'Activo']
- filename: 'tipos_posologia_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/dosage_type/index.html.twig.
- page_title: 'Tipos de Posologia'
- icon: 'bx-capsule'
- breadcrumb_section: 'Hospitalaria'
- Rutas: app_maintainers_hospital_dosage_type_{create,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 1.5 PrescriptionType (RchTipoRecetaFarmacos)

**Legacy:** `RchTipoRecetaFarmacos.php` - Campos: id, nombre(255), solicitaFolio(bool), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/PrescriptionType.php siguiendo el patron de Gender.php.

- Tabla: prescription_type
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - requestsFolio: boolean, default false (legacy: solicitaFolio)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: PrescriptionTypeRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/PrescriptionTypeRepository.php siguiendo el patron de GenderRepository.
- Entity: PrescriptionType
- Metodo findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/PrescriptionTypeFormType.php.

NOTA: Nombrar PrescriptionTypeFormType para evitar conflicto con la entidad PrescriptionType.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ej: Receta normal, Receta retenida', class='form-control'
- requestsFolio: CheckboxType, label='Solicita Folio', required=false, class='form-check-input'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: PrescriptionType
Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/PrescriptionTypeController.php siguiendo el patron de CreditCardController.

- Route base: /maintainers/hospital/prescription-type
- Prefijo rutas: app_maintainers_hospital_prescription_type_
- getData(): createQueryBuilder('pt')->orderBy('pt.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'requestsFolio' => 'Solicita Folio', 'isActive' => 'Estado']
- getFormType(): PrescriptionTypeFormType::class
- getTemplatePath(): 'maintainers/hospital/prescription_type/index.html.twig'
- getPageTitle(): default=>'Tipos de Receta'
- Export columns: ['name', 'requestsFolio', 'isActive'], headers: ['Nombre', 'Solicita Folio', 'Activo']
- filename: 'tipos_receta_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/prescription_type/index.html.twig.
- page_title: 'Tipos de Receta'
- icon: 'bx-file'
- breadcrumb_section: 'Hospitalaria'
- Rutas: app_maintainers_hospital_prescription_type_{create,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 1.6 CareCategory (RchCategoriaCuidados)

**Legacy:** `RchCategoriaCuidados.php` - Campos: id, descripcion(string), estado(bool), fechaCreacion, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/CareCategory.php siguiendo el patron de Gender.php.

- Tabla: care_category
- Campos:
  - id: integer, PK, auto-increment
  - description: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: CareCategoryRepository

NOTA: Esta entidad usa 'description' en lugar de 'name' como campo principal (legacy: descripcion).
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/CareCategoryRepository.php siguiendo el patron de GenderRepository.
- Entity: CareCategory
- Metodo findAllActive(): orderBy description ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/CareCategoryType.php.

Campos:
- description: TextType, label='Descripcion', required, placeholder='Ingrese categoria de cuidados', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: CareCategory
Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/CareCategoryController.php siguiendo el patron de CreditCardController.

- Route base: /maintainers/hospital/care-category
- Prefijo rutas: app_maintainers_hospital_care_category_
- getData(): createQueryBuilder('cc2')->orderBy('cc2.id', 'DESC')
- getColumns(): ['description' => 'Descripcion', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/care_category/index.html.twig'
- getPageTitle(): default=>'Categorias de Cuidados'
- Export columns: ['description', 'isActive'], headers: ['Descripcion', 'Activo']
- filename: 'categorias_cuidados_'.date('Y-m-d').'.csv'

NOTA: Usar alias 'cc2' para evitar conflicto con 'cc' de CreditCard en el sistema.
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/care_category/index.html.twig.
- page_title: 'Categorias de Cuidados'
- icon: 'bx-heart'
- breadcrumb_section: 'Hospitalaria'
- Rutas: app_maintainers_hospital_care_category_{create,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 1.7 NutritionistBmiIndex (RchIngresoNutricionistaIndiceImc)

**Legacy:** `RchIngresoNutricionistaIndiceImc.php` - Campos: id, nombre(100), estado(bool)

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/NutritionistBmiIndex.php siguiendo el patron de Gender.php.

- Tabla: nutritionist_bmi_index
- Campos:
  - id: integer, PK, auto-increment
  - name: string(100), NOT NULL, Assert\NotBlank, Assert\Length(max=100)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: NutritionistBmiIndexRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/NutritionistBmiIndexRepository.php siguiendo el patron de GenderRepository.
- Entity: NutritionistBmiIndex
- Metodo findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/NutritionistBmiIndexType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ej: Normal, Sobrepeso, Obesidad', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: NutritionistBmiIndex
Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/NutritionistBmiIndexController.php siguiendo el patron de CreditCardController.

- Route base: /maintainers/hospital/nutritionist-bmi-index
- Prefijo rutas: app_maintainers_hospital_nutritionist_bmi_index_
- getData(): createQueryBuilder('nbi')->orderBy('nbi.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/nutritionist_bmi_index/index.html.twig'
- getPageTitle(): default=>'Indices IMC Nutricionista'
- Export columns: ['name', 'isActive'], headers: ['Nombre', 'Activo']
- filename: 'indices_imc_nutricionista_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/nutritionist_bmi_index/index.html.twig.
- page_title: 'Indices IMC Nutricionista'
- icon: 'bx-body'
- breadcrumb_section: 'Hospitalaria'
- Rutas: app_maintainers_hospital_nutritionist_bmi_index_{create,export}
- Extends: maintainers/modern_index.html.twig
```

---

### 1.8 NutritionistTeIndex (RchIngresoNutricionistaIndiceTe)

**Legacy:** `RchIngresoNutricionistaIndiceTe.php` - Campos: id, nombre(100), estado(bool)

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/NutritionistTeIndex.php siguiendo el patron de Gender.php.
- Tabla: nutritionist_te_index
- Campos: id, name(100), isActive, idEstado, createdAt, updatedAt
- Repository: NutritionistTeIndexRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/NutritionistTeIndexRepository.php. Entity: NutritionistTeIndex. Metodo findAllActive().
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/NutritionistTeIndexType.php.
- name: TextType, label='Nombre', required
- isActive: CheckboxType
data_class: NutritionistTeIndex. Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/NutritionistTeIndexController.php.
- Route base: /maintainers/hospital/nutritionist-te-index
- Prefijo rutas: app_maintainers_hospital_nutritionist_te_index_
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/nutritionist_te_index/index.html.twig'
- getPageTitle(): default=>'Indices TE Nutricionista'
- filename: 'indices_te_nutricionista_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/nutritionist_te_index/index.html.twig.
- page_title: 'Indices TE Nutricionista'
- icon: 'bx-line-chart'
- breadcrumb_section: 'Hospitalaria'
- Extends: maintainers/modern_index.html.twig
```

---

### 1.9 NutritionistIndexClassification (RchIngresoNutricionistaIndiceClasificacion)

**Legacy:** `RchIngresoNutricionistaIndiceClasificacion.php` - Campos: id, nombre(100), estado(bool)

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/NutritionistIndexClassification.php siguiendo el patron de Gender.php.
- Tabla: nutritionist_index_classification
- Campos: id, name(100), isActive, idEstado, createdAt, updatedAt
- Repository: NutritionistIndexClassificationRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/NutritionistIndexClassificationRepository.php. Entity: NutritionistIndexClassification. Metodo findAllActive().
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/NutritionistIndexClassificationType.php.
- name: TextType, label='Nombre', required, placeholder='Ej: Bajo peso, Normal, Sobrepeso'
- isActive: CheckboxType
data_class: NutritionistIndexClassification. Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/NutritionistIndexClassificationController.php.
- Route base: /maintainers/hospital/nutritionist-index-classification
- Prefijo rutas: app_maintainers_hospital_nutritionist_index_classification_
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/nutritionist_index_classification/index.html.twig'
- getPageTitle(): default=>'Clasificaciones de Indices Nutricionista'
- filename: 'clasificaciones_indices_nutricionista_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/nutritionist_index_classification/index.html.twig.
- page_title: 'Clasificaciones de Indices Nutricionista'
- icon: 'bx-category'
- breadcrumb_section: 'Hospitalaria'
- Extends: maintainers/modern_index.html.twig
```

---

### 1.10 NutritionalDiagnosis (RchIngresoNutricionistaDiagnosticoNutricional)

**Legacy:** `RchIngresoNutricionistaDiagnosticoNutricional.php` - Campos: id, nombre(100), estado(bool)

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/NutritionalDiagnosis.php siguiendo el patron de Gender.php.
- Tabla: nutritional_diagnosis
- Campos: id, name(100), isActive, idEstado, createdAt, updatedAt
- Repository: NutritionalDiagnosisRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/NutritionalDiagnosisRepository.php. Entity: NutritionalDiagnosis. Metodo findAllActive().
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/NutritionalDiagnosisType.php.
- name: TextType, label='Nombre', required, placeholder='Ej: Desnutricion, Normal, Obesidad'
- isActive: CheckboxType
data_class: NutritionalDiagnosis. Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/NutritionalDiagnosisController.php.
- Route base: /maintainers/hospital/nutritional-diagnosis
- Prefijo rutas: app_maintainers_hospital_nutritional_diagnosis_
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/nutritional_diagnosis/index.html.twig'
- getPageTitle(): default=>'Diagnosticos Nutricionales'
- filename: 'diagnosticos_nutricionales_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/nutritional_diagnosis/index.html.twig.
- page_title: 'Diagnosticos Nutricionales'
- icon: 'bx-bowl-rice'
- breadcrumb_section: 'Hospitalaria'
- Extends: maintainers/modern_index.html.twig
```

---

### 1.11 EatingDisorderHistory (RchIngresoNutricionistaAntecedenteTca)

**Legacy:** `RchIngresoNutricionistaAntecedenteTca.php` - Campos: id, nombre(100), estado(bool)

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/EatingDisorderHistory.php siguiendo el patron de Gender.php.
- Tabla: eating_disorder_history
- Campos: id, name(100), isActive, idEstado, createdAt, updatedAt
- Repository: EatingDisorderHistoryRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/EatingDisorderHistoryRepository.php. Entity: EatingDisorderHistory. Metodo findAllActive().
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/EatingDisorderHistoryType.php.
- name: TextType, label='Nombre', required, placeholder='Ej: Anorexia, Bulimia, Sin antecedentes'
- isActive: CheckboxType
data_class: EatingDisorderHistory. Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/EatingDisorderHistoryController.php.
- Route base: /maintainers/hospital/eating-disorder-history
- Prefijo rutas: app_maintainers_hospital_eating_disorder_history_
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/eating_disorder_history/index.html.twig'
- getPageTitle(): default=>'Antecedentes TCA'
- filename: 'antecedentes_tca_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/eating_disorder_history/index.html.twig.
- page_title: 'Antecedentes TCA'
- icon: 'bx-food-menu'
- breadcrumb_section: 'Hospitalaria'
- Extends: maintainers/modern_index.html.twig
```

---

### 1.12 ClinicalActionCategory (CategoriaAc)

**Legacy:** `CategoriaAc.php` - Campos: id, nombre(100), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/ClinicalActionCategory.php siguiendo el patron de Gender.php.
- Tabla: clinical_action_category
- Campos: id, name(100), isActive, idEstado, createdAt, updatedAt
- Repository: ClinicalActionCategoryRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/ClinicalActionCategoryRepository.php. Entity: ClinicalActionCategory. Metodo findAllActive().
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/ClinicalActionCategoryType.php.
- name: TextType, label='Nombre', required, placeholder='Ingrese categoria de accion clinica'
- isActive: CheckboxType
data_class: ClinicalActionCategory. Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/ClinicalActionCategoryController.php.
- Route base: /maintainers/hospital/clinical-action-category
- Prefijo rutas: app_maintainers_hospital_clinical_action_category_
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/clinical_action_category/index.html.twig'
- getPageTitle(): default=>'Categorias de Accion Clinica'
- filename: 'categorias_accion_clinica_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/clinical_action_category/index.html.twig.
- page_title: 'Categorias de Accion Clinica'
- icon: 'bx-category-alt'
- breadcrumb_section: 'Hospitalaria'
- Extends: maintainers/modern_index.html.twig
```

---

## FASE 2: Entidades con Campos Extra

Entidades con 2-3 campos adicionales ademas de name + isActive. Sin FK complejas.

---

### 2.1 PrescriptionFrequency (RchRecetaFrecuencia)

**Legacy:** `RchRecetaFrecuencia.php` - Campos: id, nombre(100), cantidad(int), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/PrescriptionFrequency.php siguiendo el patron de Gender.php.

- Tabla: prescription_frequency
- Campos:
  - id: integer, PK, auto-increment
  - name: string(100), NOT NULL, Assert\NotBlank, Assert\Length(max=100)
  - quantity: integer, NOT NULL, default 1 (legacy: cantidad - veces por dia)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: PrescriptionFrequencyRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/PrescriptionFrequencyRepository.php. Entity: PrescriptionFrequency. Metodo findAllActive(): orderBy name ASC.
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/PrescriptionFrequencyType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ej: Cada 8 horas, Cada 12 horas'
- quantity: IntegerType, label='Cantidad', required, attr=['min'=>1], class='form-control'
- isActive: CheckboxType

data_class: PrescriptionFrequency. Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/PrescriptionFrequencyController.php.
- Route base: /maintainers/hospital/prescription-frequency
- Prefijo rutas: app_maintainers_hospital_prescription_frequency_
- getColumns(): ['name' => 'Nombre', 'quantity' => 'Cantidad', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/prescription_frequency/index.html.twig'
- getPageTitle(): default=>'Frecuencias de Receta'
- Export columns: ['name', 'quantity', 'isActive'], headers: ['Nombre', 'Cantidad', 'Activo']
- filename: 'frecuencias_receta_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/prescription_frequency/index.html.twig.
- page_title: 'Frecuencias de Receta'
- icon: 'bx-time-five'
- breadcrumb_section: 'Hospitalaria'
- Extends: maintainers/modern_index.html.twig
```

---

### 2.2 PrescriptionRoute (RchRecetaVia)

**Legacy:** `RchRecetaVia.php` - Campos: id, nombre(100), orden(int), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/PrescriptionRoute.php siguiendo el patron de Gender.php.
- Tabla: prescription_route
- Campos: id, name(100), sortOrder(int NOT NULL default 0), isActive, idEstado, createdAt, updatedAt
- Repository: PrescriptionRouteRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/PrescriptionRouteRepository.php. Entity: PrescriptionRoute. Metodo findAllActive(): orderBy sortOrder ASC.
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/PrescriptionRouteType.php.
- name: TextType, label='Nombre', required, placeholder='Ej: Oral, Intravenosa, Intramuscular'
- sortOrder: IntegerType, label='Orden', required, attr=['min'=>0]
- isActive: CheckboxType
data_class: PrescriptionRoute. Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/PrescriptionRouteController.php.
- Route base: /maintainers/hospital/prescription-route
- Prefijo rutas: app_maintainers_hospital_prescription_route_
- getColumns(): ['name' => 'Nombre', 'sortOrder' => 'Orden', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/prescription_route/index.html.twig'
- getPageTitle(): default=>'Vias de Administracion'
- filename: 'vias_administracion_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/prescription_route/index.html.twig.
- page_title: 'Vias de Administracion'
- icon: 'bx-injection'
- breadcrumb_section: 'Hospitalaria'
- Extends: maintainers/modern_index.html.twig
```

---

### 2.3 PrescriptionDosage (RchRecetaDosis)

**Legacy:** `RchRecetaDosis.php` - Campos: id, nombre(50), cantidad(decimal 10,2), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/PrescriptionDosage.php siguiendo el patron de Gender.php.
- Tabla: prescription_dosage
- Campos: id, name(50), quantity: decimal(10,2) NOT NULL, isActive, idEstado, createdAt, updatedAt
- NOTA: quantity es decimal (precision=10, scale=2) para dosis como 0.5, 1.5, etc.
- Repository: PrescriptionDosageRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/PrescriptionDosageRepository.php. Entity: PrescriptionDosage. Metodo findAllActive().
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/PrescriptionDosageType.php.
- name: TextType, label='Nombre', required, placeholder='Ej: 500mg, 1g, 5ml'
- quantity: NumberType, label='Cantidad', required, scale=2, attr=['step'=>'0.01']
- isActive: CheckboxType
data_class: PrescriptionDosage. Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/PrescriptionDosageController.php.
- Route base: /maintainers/hospital/prescription-dosage
- Prefijo rutas: app_maintainers_hospital_prescription_dosage_
- getColumns(): ['name' => 'Nombre', 'quantity' => 'Cantidad', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/prescription_dosage/index.html.twig'
- getPageTitle(): default=>'Dosis de Receta'
- filename: 'dosis_receta_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/prescription_dosage/index.html.twig.
- page_title: 'Dosis de Receta'
- icon: 'bx-calculator'
- breadcrumb_section: 'Hospitalaria'
- Extends: maintainers/modern_index.html.twig
```

---

### 2.4 PrescriptionFormat (RchRecetaFormato)

**Legacy:** `RchRecetaFormato.php` - Campos: id, nombre(100), orden(int), idEmpresa, idEstado

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/PrescriptionFormat.php siguiendo el patron de Gender.php.
- Tabla: prescription_format
- Campos: id, name(100), sortOrder(int NOT NULL default 0), isActive, idEstado, createdAt, updatedAt
- Repository: PrescriptionFormatRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/PrescriptionFormatRepository.php. Entity: PrescriptionFormat. Metodo findAllActive(): orderBy sortOrder ASC.
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/PrescriptionFormatType.php.
- name: TextType, label='Nombre', required, placeholder='Ej: Comprimido, Capsula, Jarabe'
- sortOrder: IntegerType, label='Orden', required, attr=['min'=>0]
- isActive: CheckboxType
data_class: PrescriptionFormat. Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/PrescriptionFormatController.php.
- Route base: /maintainers/hospital/prescription-format
- Prefijo rutas: app_maintainers_hospital_prescription_format_
- getColumns(): ['name' => 'Nombre', 'sortOrder' => 'Orden', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/prescription_format/index.html.twig'
- getPageTitle(): default=>'Formatos de Receta'
- filename: 'formatos_receta_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/prescription_format/index.html.twig.
- page_title: 'Formatos de Receta'
- icon: 'bx-capsule'
- breadcrumb_section: 'Hospitalaria'
- Extends: maintainers/modern_index.html.twig
```

---

### 2.5 PrescriptionDispensation (RchRecetaDispensacion)

**Legacy:** `RchRecetaDispensacion.php` - Campos: id, nombre(100), orden(int), cantidad(int), idEstado, idEmpresa, idUnidadMedidaTiempo

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/PrescriptionDispensation.php siguiendo el patron de Gender.php.
- Tabla: prescription_dispensation
- Campos:
  - id: integer, PK, auto-increment
  - name: string(100), NOT NULL
  - sortOrder: integer, NOT NULL, default 0
  - quantity: integer, NOT NULL, default 1 (legacy: cantidad)
  - timeUnit: string(50), nullable (legacy: idUnidadMedidaTiempo - migrado como string simple)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt, updatedAt
- Repository: PrescriptionDispensationRepository

NOTA: El campo idUnidadMedidaTiempo se migra como string 'timeUnit' por ahora (ej: 'horas', 'dias').
Cuando se migre la entidad TimeUnit, se puede convertir a FK.
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/PrescriptionDispensationRepository.php. Entity: PrescriptionDispensation. Metodo findAllActive(): orderBy sortOrder ASC.
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/PrescriptionDispensationType.php.
- name: TextType, label='Nombre', required
- sortOrder: IntegerType, label='Orden', required, attr=['min'=>0]
- quantity: IntegerType, label='Cantidad', required, attr=['min'=>1]
- timeUnit: TextType, label='Unidad de Tiempo', required=false, placeholder='Ej: horas, dias'
- isActive: CheckboxType
data_class: PrescriptionDispensation. Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/PrescriptionDispensationController.php.
- Route base: /maintainers/hospital/prescription-dispensation
- Prefijo rutas: app_maintainers_hospital_prescription_dispensation_
- getColumns(): ['name' => 'Nombre', 'sortOrder' => 'Orden', 'quantity' => 'Cantidad', 'timeUnit' => 'Unidad Tiempo', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/prescription_dispensation/index.html.twig'
- getPageTitle(): default=>'Dispensaciones de Receta'
- filename: 'dispensaciones_receta_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/prescription_dispensation/index.html.twig.
- page_title: 'Dispensaciones de Receta'
- icon: 'bx-package'
- breadcrumb_section: 'Hospitalaria'
- Extends: maintainers/modern_index.html.twig
```

---

### 2.6 PhysicalExamGrouping (ExamenFisicoAgrupacion)

**Legacy:** `ExamenFisicoAgrupacion.php` - Campos: id, nombreAgrupacion(100), usadoEnCurvas(bool), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/PhysicalExamGrouping.php siguiendo el patron de Gender.php.
- Tabla: physical_exam_grouping
- Campos:
  - id: integer, PK, auto-increment
  - groupingName: string(100), nullable (legacy: nombreAgrupacion)
  - usedInCurves: boolean, default false (legacy: usadoEnCurvas)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt, updatedAt
- Repository: PhysicalExamGroupingRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/PhysicalExamGroupingRepository.php. Entity: PhysicalExamGrouping. Metodo findAllActive(): orderBy groupingName ASC.
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/PhysicalExamGroupingType.php.
- groupingName: TextType, label='Nombre Agrupacion', required=false, placeholder='Ej: Signos Vitales, Antropometria'
- usedInCurves: CheckboxType, label='Usado en Curvas', required=false
- isActive: CheckboxType
data_class: PhysicalExamGrouping. Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/PhysicalExamGroupingController.php.
- Route base: /maintainers/hospital/physical-exam-grouping
- Prefijo rutas: app_maintainers_hospital_physical_exam_grouping_
- getColumns(): ['groupingName' => 'Nombre Agrupacion', 'usedInCurves' => 'Usado en Curvas', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/physical_exam_grouping/index.html.twig'
- getPageTitle(): default=>'Agrupaciones de Examen Fisico'
- filename: 'agrupaciones_examen_fisico_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/physical_exam_grouping/index.html.twig.
- page_title: 'Agrupaciones de Examen Fisico'
- icon: 'bx-collection'
- breadcrumb_section: 'Hospitalaria'
- Extends: maintainers/modern_index.html.twig
```

---

## FASE 3: Entidades Moderadas

Entidades con relaciones FK a entidades creadas en fases anteriores.

---

### 3.1 CareIntervention (RchCuidados)

**Legacy:** `RchCuidados.php` - Campos: id, descripcion(string), estado(bool), fechaCreacion, idCategoriaCuidados(FK), idEmpresa

**DEPENDENCIA:** Crear CareCategory (Fase 1.6) ANTES

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/CareIntervention.php siguiendo el patron de Gender.php.

- Tabla: care_intervention
- Campos:
  - id: integer, PK, auto-increment
  - description: string(255), NOT NULL, Assert\NotBlank
  - careCategory: ManyToOne -> CareCategory, nullable, JoinColumn(name='care_category_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: CareInterventionRepository
- NO agregar campo idEmpresa

IMPORTANTE: La relacion ManyToOne a CareCategory debe usar:
#[ORM\ManyToOne(targetEntity: CareCategory::class)]
#[ORM\JoinColumn(name: 'care_category_id', nullable: true)]
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/CareInterventionRepository.php.
- Entity: CareIntervention
- Metodos:
  - findAllActive(): orderBy description ASC
  - findByCategory(int $categoryId): array
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/CareInterventionType.php.

Campos:
- description: TextType, label='Descripcion', required, placeholder='Descripcion del cuidado'
- careCategory: EntityType, class=CareCategory::class, label='Categoria',
  choice_label='description', placeholder='Seleccione categoria...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy description ASC
- isActive: CheckboxType

data_class: CareIntervention. Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/CareInterventionController.php siguiendo el patron de CreditCardController.

- Route base: /maintainers/hospital/care-intervention
- Prefijo rutas: app_maintainers_hospital_care_intervention_
- Inyectar: CareInterventionRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('ci')
    ->leftJoin('ci.careCategory', 'cc')
    ->addSelect('cc')
    ->orderBy('ci.id', 'DESC')
- getColumns(): ['description' => 'Descripcion', 'careCategory.description' => 'Categoria', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/care_intervention/index.html.twig'
- getPageTitle(): default=>'Cuidados Clinicos'
- Export columns: ['description', 'careCategory.description', 'isActive']
- Export headers: ['Descripcion', 'Categoria', 'Activo']
- filename: 'cuidados_clinicos_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/care_intervention/index.html.twig.
- page_title: 'Cuidados Clinicos'
- icon: 'bx-heart'
- breadcrumb_section: 'Hospitalaria'
- Extends: maintainers/modern_index.html.twig
```

---

### 3.2 ClinicalActionQuestion (PreguntaAc)

**Legacy:** `PreguntaAc.php` - Campos: id, nombre(100), orden(int), rangoMin(int), rangoMax(int nullable), edadMin(int nullable), edadMax(int nullable), ayuda(100 nullable), esMultiple(bool), esExtendido(bool), esObligatorio(bool), idTipoCampo(FK), idCategoriaAc(FK), idRespuestaGatilladora(FK)

**DEPENDENCIA:** Crear ClinicalActionCategory (Fase 1.12) ANTES

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/ClinicalActionQuestion.php. Entidad MODERADA+.

- Tabla: clinical_action_question
- Campos:
  - id: integer, PK, auto-increment
  - name: string(100), NOT NULL, Assert\NotBlank
  - sortOrder: integer, NOT NULL, default 0 (legacy: orden)
  - rangeMin: integer, NOT NULL, default 0 (legacy: rangoMin)
  - rangeMax: integer, nullable (legacy: rangoMax)
  - ageMin: integer, nullable (legacy: edadMin)
  - ageMax: integer, nullable (legacy: edadMax)
  - helpText: string(100), nullable (legacy: ayuda)
  - isMultiple: boolean, default false (legacy: esMultiple)
  - isExtended: boolean, default false (legacy: esExtendido)
  - isRequired: boolean, default false (legacy: esObligatorio)
  - fieldType: string(50), nullable (legacy: idTipoCampo - migrado como string)
  - clinicalActionCategory: ManyToOne -> ClinicalActionCategory, nullable
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt, updatedAt
- Repository: ClinicalActionQuestionRepository

NOTA: idTipoCampo se migra como string 'fieldType' (ej: 'text', 'select', 'number').
idRespuestaGatilladora se omite por ahora (referencia circular con ClinicalActionAnswer).
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/ClinicalActionQuestionRepository.php.
- Entity: ClinicalActionQuestion
- Metodos:
  - findAllActive(): orderBy sortOrder ASC
  - findByCategory(int $categoryId): array - orderBy sortOrder ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/ClinicalActionQuestionType.php.

Campos:
- name: TextType, label='Nombre', required
- sortOrder: IntegerType, label='Orden', required, attr=['min'=>0]
- rangeMin: IntegerType, label='Rango Minimo', required, attr=['min'=>0]
- rangeMax: IntegerType, label='Rango Maximo', required=false
- ageMin: IntegerType, label='Edad Minima', required=false, attr=['min'=>0]
- ageMax: IntegerType, label='Edad Maxima', required=false
- helpText: TextType, label='Texto Ayuda', required=false
- fieldType: ChoiceType, label='Tipo Campo', required=false, choices=['Texto'=>'text','Numero'=>'number','Seleccion'=>'select','Checkbox'=>'checkbox']
- isMultiple: CheckboxType, label='Multiple'
- isExtended: CheckboxType, label='Extendido'
- isRequired: CheckboxType, label='Obligatorio'
- clinicalActionCategory: EntityType, class=ClinicalActionCategory::class, label='Categoria',
  choice_label='name', placeholder='Seleccione...', required=false
- isActive: CheckboxType

data_class: ClinicalActionQuestion. Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/ClinicalActionQuestionController.php.

- Route base: /maintainers/hospital/clinical-action-question
- Prefijo rutas: app_maintainers_hospital_clinical_action_question_
- getData(): createQueryBuilder('caq')
    ->leftJoin('caq.clinicalActionCategory', 'cac')
    ->addSelect('cac')
    ->orderBy('caq.sortOrder', 'ASC')
- getColumns(): ['name' => 'Nombre', 'sortOrder' => 'Orden', 'clinicalActionCategory.name' => 'Categoria', 'fieldType' => 'Tipo Campo', 'isRequired' => 'Obligatorio', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/clinical_action_question/index.html.twig'
- getPageTitle(): default=>'Preguntas de Accion Clinica'
- filename: 'preguntas_accion_clinica_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/clinical_action_question/index.html.twig.
- page_title: 'Preguntas de Accion Clinica'
- icon: 'bx-question-mark'
- breadcrumb_section: 'Hospitalaria'
- Extends: maintainers/modern_index.html.twig
```

---

### 3.3 ClinicalActionAnswer (RespuestaAc)

**Legacy:** `RespuestaAc.php` - Campos: id, orden(int), textoPrevio(255 nullable), textoPosterior(255 nullable), placeholder(100 nullable), valorDefecto(100 nullable), entidadRespuesta(100 nullable), esCheckeado(bool), idEstado, idPreguntaAc(FK)

**DEPENDENCIA:** Crear ClinicalActionQuestion (Fase 3.2) ANTES

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/ClinicalActionAnswer.php.

- Tabla: clinical_action_answer
- Campos:
  - id: integer, PK, auto-increment
  - sortOrder: integer, NOT NULL, default 0 (legacy: orden)
  - preText: string(255), nullable (legacy: textoPrevio)
  - postText: string(255), nullable (legacy: textoPosterior)
  - placeholder: string(100), nullable
  - defaultValue: string(100), nullable (legacy: valorDefecto)
  - entityResponse: string(100), nullable (legacy: entidadRespuesta)
  - isChecked: boolean, default false (legacy: esCheckeado)
  - clinicalActionQuestion: ManyToOne -> ClinicalActionQuestion, NOT NULL
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt, updatedAt
- Repository: ClinicalActionAnswerRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/ClinicalActionAnswerRepository.php.
- Entity: ClinicalActionAnswer
- Metodos:
  - findAllActive(): orderBy sortOrder ASC
  - findByQuestion(int $questionId): array - orderBy sortOrder ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/ClinicalActionAnswerType.php.

Campos:
- sortOrder: IntegerType, label='Orden', required, attr=['min'=>0]
- preText: TextType, label='Texto Previo', required=false
- postText: TextType, label='Texto Posterior', required=false
- placeholder: TextType, label='Placeholder', required=false
- defaultValue: TextType, label='Valor por Defecto', required=false
- entityResponse: TextType, label='Entidad Respuesta', required=false
- isChecked: CheckboxType, label='Seleccionado por Defecto'
- clinicalActionQuestion: EntityType, class=ClinicalActionQuestion::class, label='Pregunta',
  choice_label='name', placeholder='Seleccione pregunta...', required=true
- isActive: CheckboxType

data_class: ClinicalActionAnswer. Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/ClinicalActionAnswerController.php.

- Route base: /maintainers/hospital/clinical-action-answer
- Prefijo rutas: app_maintainers_hospital_clinical_action_answer_
- getData(): createQueryBuilder('caa')
    ->leftJoin('caa.clinicalActionQuestion', 'caq')
    ->addSelect('caq')
    ->orderBy('caq.sortOrder', 'ASC')
    ->addOrderBy('caa.sortOrder', 'ASC')
- getColumns(): ['sortOrder' => 'Orden', 'preText' => 'Texto Previo', 'clinicalActionQuestion.name' => 'Pregunta', 'isChecked' => 'Seleccionado', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/clinical_action_answer/index.html.twig'
- getPageTitle(): default=>'Respuestas de Accion Clinica'
- filename: 'respuestas_accion_clinica_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/clinical_action_answer/index.html.twig.
- page_title: 'Respuestas de Accion Clinica'
- icon: 'bx-check-circle'
- breadcrumb_section: 'Hospitalaria'
- Extends: maintainers/modern_index.html.twig
```

---

## FASE 4: Entidades Complejas

Entidades con muchos campos (10+), multiples relaciones FK, o estructura compleja.

---

### 4.1 PhysicalExamBaseField (ExamenFisicoCampoBase)

**Legacy:** `ExamenFisicoCampoBase.php` - 14 campos incluyendo rangos, alertas, tipos especiales

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/PhysicalExamBaseField.php. Entidad COMPLEJA.

- Tabla: physical_exam_base_field
- Campos:
  - id: integer, PK, auto-increment
  - name: string(45), nullable
  - description: string(255), nullable
  - sortOrder: integer, NOT NULL, default 0 (legacy: orden)
  - rangeMin: integer, nullable (legacy: rangoMin)
  - rangeMax: integer, nullable (legacy: rangoMax)
  - alertRangeMin: integer, nullable (legacy: rangoAlertaMin)
  - alertRangeMax: integer, nullable (legacy: rangoAlertaMax)
  - ageMin: integer, nullable (legacy: edadMin)
  - ageMax: integer, nullable (legacy: edadMax)
  - unit: string(45), nullable (legacy: unidad)
  - isWeight: boolean, default false (legacy: esPeso)
  - isHeight: boolean, default false (legacy: esTalla)
  - isBmi: boolean, default false (legacy: esImc)
  - options: string(45), nullable (legacy: opciones)
  - fieldType: string(50), nullable (legacy: idTipoCampo - migrado como string)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt, updatedAt
- Repository: PhysicalExamBaseFieldRepository

NOTA: idTipoCampo se migra como string 'fieldType' por ahora.
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/PhysicalExamBaseFieldRepository.php.
- Entity: PhysicalExamBaseField
- Metodo findAllActive(): orderBy sortOrder ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/PhysicalExamBaseFieldType.php.

Campos:
- name: TextType, label='Nombre', required=false
- description: TextType, label='Descripcion', required=false
- sortOrder: IntegerType, label='Orden', required, attr=['min'=>0]
- rangeMin: IntegerType, label='Rango Min', required=false
- rangeMax: IntegerType, label='Rango Max', required=false
- alertRangeMin: IntegerType, label='Alerta Min', required=false
- alertRangeMax: IntegerType, label='Alerta Max', required=false
- ageMin: IntegerType, label='Edad Min', required=false
- ageMax: IntegerType, label='Edad Max', required=false
- unit: TextType, label='Unidad', required=false, placeholder='Ej: kg, cm, °C'
- isWeight: CheckboxType, label='Es Peso'
- isHeight: CheckboxType, label='Es Talla'
- isBmi: CheckboxType, label='Es IMC'
- options: TextType, label='Opciones', required=false
- isActive: CheckboxType

data_class: PhysicalExamBaseField. Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/PhysicalExamBaseFieldController.php.

- Route base: /maintainers/hospital/physical-exam-base-field
- Prefijo rutas: app_maintainers_hospital_physical_exam_base_field_
- getData(): createQueryBuilder('pebf')->orderBy('pebf.sortOrder', 'ASC')
- getColumns(): ['name' => 'Nombre', 'sortOrder' => 'Orden', 'unit' => 'Unidad', 'rangeMin' => 'Min', 'rangeMax' => 'Max', 'isWeight' => 'Peso', 'isHeight' => 'Talla', 'isBmi' => 'IMC', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/physical_exam_base_field/index.html.twig'
- getPageTitle(): default=>'Campos Base Examen Fisico'
- filename: 'campos_base_examen_fisico_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/physical_exam_base_field/index.html.twig.
- page_title: 'Campos Base Examen Fisico'
- icon: 'bx-body'
- breadcrumb_section: 'Hospitalaria'
- Extends: maintainers/modern_index.html.twig
```

---

### 4.2 PhysicalExamField (ExamenFisicoCampo)

**Legacy:** `ExamenFisicoCampo.php` - 18+ campos, FK a TipoExamenFisico, ExamenFisicoAgrupacion(x2)

**DEPENDENCIA:** Crear PhysicalExamGrouping (Fase 2.6) ANTES

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/PhysicalExamField.php. Entidad MAS COMPLEJA de Hospitalaria.

- Tabla: physical_exam_field
- Campos:
  - id: integer, PK, auto-increment
  - name: string(45), nullable
  - description: string(255), nullable
  - sortOrder: integer, NOT NULL, default 0
  - rangeMin: integer, nullable
  - rangeMax: integer, nullable
  - ageMin: integer, nullable
  - ageMax: integer, nullable
  - unit: string(45), nullable
  - isWeight: boolean, default false (legacy: esPeso)
  - isHeight: boolean, default false (legacy: esTalla)
  - isBmi: boolean, default false (legacy: esImc)
  - isTemperature: boolean, default false (legacy: esTemperatura)
  - isSystolic: boolean, default false (legacy: esTas - tension arterial sistolica)
  - isDiastolic: boolean, default false (legacy: esTad - tension arterial diastolica)
  - isSaturation: boolean, default false (legacy: esSat)
  - isRespiratoryRate: boolean, default false (legacy: esFrecuenciaRespiratoria)
  - isPce: boolean, default false (legacy: esPce)
  - fieldType: string(50), nullable (legacy: idTipoCampo)
  - examType: string(50), nullable (legacy: idTipoExamenFisico)
  - grouping1: ManyToOne -> PhysicalExamGrouping, nullable, JoinColumn(name='grouping1_id') (legacy: idAgrupacion1)
  - grouping2: ManyToOne -> PhysicalExamGrouping, nullable, JoinColumn(name='grouping2_id') (legacy: idAgrupacion2)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt, updatedAt
- Repository: PhysicalExamFieldRepository

IMPORTANTE:
- Tiene DOS relaciones ManyToOne a PhysicalExamGrouping (grouping1 y grouping2)
- idTipoCampo y idTipoExamenFisico se migran como strings por ahora
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/PhysicalExamFieldRepository.php.
- Entity: PhysicalExamField
- Metodos:
  - findAllActive(): orderBy sortOrder ASC
  - findByGrouping(int $groupingId): array
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/PhysicalExamFieldType.php.

Campos:
- name: TextType, label='Nombre', required=false
- description: TextType, label='Descripcion', required=false
- sortOrder: IntegerType, label='Orden', required
- rangeMin: IntegerType, label='Rango Min', required=false
- rangeMax: IntegerType, label='Rango Max', required=false
- ageMin: IntegerType, label='Edad Min', required=false
- ageMax: IntegerType, label='Edad Max', required=false
- unit: TextType, label='Unidad', required=false
- isWeight, isHeight, isBmi, isTemperature, isSystolic, isDiastolic, isSaturation, isRespiratoryRate, isPce: CheckboxType
- grouping1: EntityType, class=PhysicalExamGrouping::class, label='Agrupacion 1',
  choice_label='groupingName', placeholder='Sin agrupacion', required=false
- grouping2: EntityType, class=PhysicalExamGrouping::class, label='Agrupacion 2',
  choice_label='groupingName', placeholder='Sin agrupacion', required=false
- isActive: CheckboxType

data_class: PhysicalExamField. Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/PhysicalExamFieldController.php.

- Route base: /maintainers/hospital/physical-exam-field
- Prefijo rutas: app_maintainers_hospital_physical_exam_field_
- getData(): createQueryBuilder('pef')
    ->leftJoin('pef.grouping1', 'g1')
    ->addSelect('g1')
    ->leftJoin('pef.grouping2', 'g2')
    ->addSelect('g2')
    ->orderBy('pef.sortOrder', 'ASC')
- getColumns(): ['name' => 'Nombre', 'sortOrder' => 'Orden', 'unit' => 'Unidad', 'grouping1.groupingName' => 'Agrupacion 1', 'isWeight' => 'Peso', 'isTemperature' => 'Temp', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/physical_exam_field/index.html.twig'
- getPageTitle(): default=>'Campos de Examen Fisico'
- filename: 'campos_examen_fisico_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/physical_exam_field/index.html.twig.
- page_title: 'Campos de Examen Fisico'
- icon: 'bx-pulse'
- breadcrumb_section: 'Hospitalaria'
- Extends: maintainers/modern_index.html.twig
```

---

### 4.3 PrescriptionRuleDetail (RchRecetaDetallePrescripcionReglaDetalle)

**Legacy:** `RchRecetaDetallePrescripcionReglaDetalle.php` - Campos: id, intervalos(string), cantidadPorDia(int), estado(bool), fechaCreacion

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/PrescriptionRuleDetail.php.

- Tabla: prescription_rule_detail
- Campos:
  - id: integer, PK, auto-increment
  - intervals: string(255), NOT NULL (legacy: intervalos - ej: "08:00,14:00,20:00")
  - dailyQuantity: integer, NOT NULL (legacy: cantidadPorDia)
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: PrescriptionRuleDetailRepository

NOTA: El FK idRchRecetaDetallePrescripcionRegla se omite por ahora (entidad padre no migrada).
Se puede agregar cuando se migre el modulo completo de prescripciones.
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/PrescriptionRuleDetailRepository.php.
- Entity: PrescriptionRuleDetail
- Metodo findAllActive(): orderBy dailyQuantity ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Hospital/PrescriptionRuleDetailType.php.

Campos:
- intervals: TextType, label='Intervalos', required, placeholder='Ej: 08:00,14:00,20:00'
- dailyQuantity: IntegerType, label='Cantidad por Dia', required, attr=['min'=>1]
- isActive: CheckboxType

data_class: PrescriptionRuleDetail. Namespace: App\Form\Maintainers\Hospital
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Hospital/PrescriptionRuleDetailController.php.

- Route base: /maintainers/hospital/prescription-rule-detail
- Prefijo rutas: app_maintainers_hospital_prescription_rule_detail_
- getData(): createQueryBuilder('prd')->orderBy('prd.dailyQuantity', 'ASC')
- getColumns(): ['intervals' => 'Intervalos', 'dailyQuantity' => 'Cant/Dia', 'isActive' => 'Estado']
- getTemplatePath(): 'maintainers/hospital/prescription_rule_detail/index.html.twig'
- getPageTitle(): default=>'Reglas de Prescripcion'
- filename: 'reglas_prescripcion_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/hospital/prescription_rule_detail/index.html.twig.
- page_title: 'Reglas de Prescripcion'
- icon: 'bx-list-ul'
- breadcrumb_section: 'Hospitalaria'
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
1. Fase 1: MedicalDevice, IntoxicationState, CareClosureDestination, DosageType, PrescriptionType, CareCategory, NutritionistBmiIndex, NutritionistTeIndex, NutritionistIndexClassification, NutritionalDiagnosis, EatingDisorderHistory, ClinicalActionCategory
2. Fase 2: PrescriptionFrequency, PrescriptionRoute, PrescriptionDosage, PrescriptionFormat, PrescriptionDispensation, PhysicalExamGrouping
3. Fase 3: CareIntervention, ClinicalActionQuestion, ClinicalActionAnswer
4. Fase 4: PhysicalExamBaseField, PhysicalExamField, PrescriptionRuleDetail
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
-- Usar el siguiente numero como base

SELECT id FROM menu_items WHERE name = 'mantenedores';
-- Anotar como {MANTENEDORES_ID} (actualmente = 4)
```

### Paso 2: Insertar categoria + 24 mantenedores

Reemplazar los IDs segun corresponda. IDs de ejemplo (ajustar segun MAX actual):

```sql
-- Categoria Hospitalaria (hijo de Mantenedores)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES (100, 'maintenance_hospital', 'Hospitalaria', NULL, 'bx bx-plus-medical', NULL, 4, 8, true, true, true, '["ROLE_USER"]', NOW());

-- 24 mantenedores (hijos de Hospitalaria, parent_id = 100)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES
-- Fase 1: Simples
(101, 'medical_device', 'Dispositivos Medicos', 'app_maintainers_hospital_medical_device_index', 'bx bx-devices', 'maintenance_hospital', 100, 1, true, true, true, '["ROLE_USER"]', NOW()),
(102, 'intoxication_state', 'Estados de Ebriedad', 'app_maintainers_hospital_intoxication_state_index', 'bx bx-wine', 'maintenance_hospital', 100, 2, true, true, true, '["ROLE_USER"]', NOW()),
(103, 'care_closure_destination', 'Destinos Cierre Atencion', 'app_maintainers_hospital_care_closure_destination_index', 'bx bx-log-out', 'maintenance_hospital', 100, 3, true, true, true, '["ROLE_USER"]', NOW()),
(104, 'dosage_type', 'Tipos de Posologia', 'app_maintainers_hospital_dosage_type_index', 'bx bx-capsule', 'maintenance_hospital', 100, 4, true, true, true, '["ROLE_USER"]', NOW()),
(105, 'prescription_type', 'Tipos de Receta', 'app_maintainers_hospital_prescription_type_index', 'bx bx-file', 'maintenance_hospital', 100, 5, true, true, true, '["ROLE_USER"]', NOW()),
(106, 'care_category', 'Categorias de Cuidados', 'app_maintainers_hospital_care_category_index', 'bx bx-heart', 'maintenance_hospital', 100, 6, true, true, true, '["ROLE_USER"]', NOW()),
(107, 'nutritionist_bmi_index', 'Indices IMC', 'app_maintainers_hospital_nutritionist_bmi_index_index', 'bx bx-body', 'maintenance_hospital', 100, 7, true, true, true, '["ROLE_USER"]', NOW()),
(108, 'nutritionist_te_index', 'Indices TE', 'app_maintainers_hospital_nutritionist_te_index_index', 'bx bx-line-chart', 'maintenance_hospital', 100, 8, true, true, true, '["ROLE_USER"]', NOW()),
(109, 'nutritionist_index_classification', 'Clasificaciones Indices', 'app_maintainers_hospital_nutritionist_index_classification_index', 'bx bx-category', 'maintenance_hospital', 100, 9, true, true, true, '["ROLE_USER"]', NOW()),
(110, 'nutritional_diagnosis', 'Diagnosticos Nutricionales', 'app_maintainers_hospital_nutritional_diagnosis_index', 'bx bx-bowl-rice', 'maintenance_hospital', 100, 10, true, true, true, '["ROLE_USER"]', NOW()),
(111, 'eating_disorder_history', 'Antecedentes TCA', 'app_maintainers_hospital_eating_disorder_history_index', 'bx bx-food-menu', 'maintenance_hospital', 100, 11, true, true, true, '["ROLE_USER"]', NOW()),
(112, 'clinical_action_category', 'Categorias Accion Clinica', 'app_maintainers_hospital_clinical_action_category_index', 'bx bx-category-alt', 'maintenance_hospital', 100, 12, true, true, true, '["ROLE_USER"]', NOW()),
-- Fase 2: Campos Extra
(113, 'prescription_frequency', 'Frecuencias de Receta', 'app_maintainers_hospital_prescription_frequency_index', 'bx bx-time-five', 'maintenance_hospital', 100, 13, true, true, true, '["ROLE_USER"]', NOW()),
(114, 'prescription_route', 'Vias de Administracion', 'app_maintainers_hospital_prescription_route_index', 'bx bx-injection', 'maintenance_hospital', 100, 14, true, true, true, '["ROLE_USER"]', NOW()),
(115, 'prescription_dosage', 'Dosis de Receta', 'app_maintainers_hospital_prescription_dosage_index', 'bx bx-calculator', 'maintenance_hospital', 100, 15, true, true, true, '["ROLE_USER"]', NOW()),
(116, 'prescription_format', 'Formatos de Receta', 'app_maintainers_hospital_prescription_format_index', 'bx bx-capsule', 'maintenance_hospital', 100, 16, true, true, true, '["ROLE_USER"]', NOW()),
(117, 'prescription_dispensation', 'Dispensaciones', 'app_maintainers_hospital_prescription_dispensation_index', 'bx bx-package', 'maintenance_hospital', 100, 17, true, true, true, '["ROLE_USER"]', NOW()),
(118, 'physical_exam_grouping', 'Agrupaciones Examen Fisico', 'app_maintainers_hospital_physical_exam_grouping_index', 'bx bx-collection', 'maintenance_hospital', 100, 18, true, true, true, '["ROLE_USER"]', NOW()),
-- Fase 3: Moderadas
(119, 'care_intervention', 'Cuidados Clinicos', 'app_maintainers_hospital_care_intervention_index', 'bx bx-heart', 'maintenance_hospital', 100, 19, true, true, true, '["ROLE_USER"]', NOW()),
(120, 'clinical_action_question', 'Preguntas Accion Clinica', 'app_maintainers_hospital_clinical_action_question_index', 'bx bx-question-mark', 'maintenance_hospital', 100, 20, true, true, true, '["ROLE_USER"]', NOW()),
(121, 'clinical_action_answer', 'Respuestas Accion Clinica', 'app_maintainers_hospital_clinical_action_answer_index', 'bx bx-check-circle', 'maintenance_hospital', 100, 21, true, true, true, '["ROLE_USER"]', NOW()),
-- Fase 4: Complejas
(122, 'physical_exam_base_field', 'Campos Base Examen Fisico', 'app_maintainers_hospital_physical_exam_base_field_index', 'bx bx-body', 'maintenance_hospital', 100, 22, true, true, true, '["ROLE_USER"]', NOW()),
(123, 'physical_exam_field', 'Campos Examen Fisico', 'app_maintainers_hospital_physical_exam_field_index', 'bx bx-pulse', 'maintenance_hospital', 100, 23, true, true, true, '["ROLE_USER"]', NOW()),
(124, 'prescription_rule_detail', 'Reglas de Prescripcion', 'app_maintainers_hospital_prescription_rule_detail_index', 'bx bx-list-ul', 'maintenance_hospital', 100, 24, true, true, true, '["ROLE_USER"]', NOW());
```

### Paso 3: Limpiar cache de menu

```bash
php bin/console cache:clear
```

### Rollback (en caso de necesitar borrar)

```sql
DELETE FROM menu_items WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintenance_hospital');
DELETE FROM menu_items WHERE name = 'maintenance_hospital';
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
| Entities | 24 | src/Entity/Tenant/ |
| Repositories | 24 | src/Repository/Tenant/ |
| FormTypes | 24 | src/Form/Maintainers/Hospital/ |
| Controllers | 24 | src/Controller/Maintainers/Hospital/ |
| Templates | 24 | templates/maintainers/hospital/ |
| **TOTAL** | **120 archivos** | |

(24 mantenedores = 24 entidades, sin dependencias externas nuevas)

---

**Fecha de creacion:** 2026-02-03
**Ultima actualizacion:** 2026-02-03
**Estado:** Pendiente de implementacion
