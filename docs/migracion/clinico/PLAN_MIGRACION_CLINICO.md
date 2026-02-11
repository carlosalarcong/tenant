# Plan de Migracion - Categoria Clinico

## Resumen Ejecutivo

**Categoria:** MantenedorClinico
**Origen Legacy:** `melisa_prod/src/Rebsol/MantenedoresBundle/Controller/_Default/MantenedorMaestro/MantenedorEmpresa/MantenedorClinico/`
**Destino Nuevo:** `melisa_tenant/src/Controller/Maintainers/Clinical/`
**Total Entidades:** 28 mantenedores (divididos en 5 fases tematicas)
**Complejidad Global:** Alta

---

## Inventario Completo

### Entidades a Migrar

| # | Legacy (ES) | Nuevo (EN) | Tabla Nueva | Complejidad | Fase |
|---|-------------|------------|-------------|-------------|------|
| **FASE 1: Diagnosticos (6 entidades)** |
| 1 | Antecedente | MedicalHistory | medical_history | Simple | 1 |
| 2 | Diagnostico | Diagnosis | diagnosis | Simple | 1 |
| 3 | DiagnosticoInmunoterapia | ImmunotherapyDiagnosis | immunotherapy_diagnosis | Simple | 1 |
| 4 | DiagnosticoPorPatologia | DiagnosisByPathology | diagnosis_by_pathology | Simple | 1 |
| 5 | EstadoDiagnostico | DiagnosisStatus | diagnosis_status | Simple | 1 |
| 6 | TipoAntecedente | MedicalHistoryType | medical_history_type | Simple | 1 |
| **FASE 2: Examenes (6 entidades)** |
| 7 | AgrupacionExamen | ExamGroup | exam_group | Simple | 2 |
| 8 | ExamenFisicoAgrupacion | PhysicalExamGroup | physical_exam_group | Simple | 2 |
| 9 | ExamenFisicoCampo | PhysicalExamField | physical_exam_field | Simple+ | 2 |
| 10 | ExamenPrestacion | ExamService | exam_service | Simple | 2 |
| 11 | TipoExamenFisico | PhysicalExamType | physical_exam_type | Simple | 2 |
| 12 | TipoPrestacionExamen | ExamServiceType | exam_service_type | Simple | 2 |
| **FASE 3: Medicamentos (7 entidades)** |
| 13 | Concentracion | Concentration | concentration | Simple | 3 |
| 14 | Dosis | Dose | dose | Simple | 3 |
| 15 | Indicacion | Indication | indication | Simple | 3 |
| 16 | MedicamentoBioequivalente | BioequivalentMedicine | bioequivalent_medicine | Simple+ | 3 |
| 17 | Periodicidad | Periodicity | periodicity | Simple | 3 |
| 18 | Reaccion | Reaction | reaction | Simple | 3 |
| 19 | TipoConcentracion | ConcentrationType | concentration_type | Simple | 3 |
| **FASE 4: Documentos (5 entidades)** |
| 20 | DocumentoEncabezadoPie | DocumentHeaderFooter | document_header_footer | Simple | 4 |
| 21 | DocumentoPlantillaEncabezado | DocumentTemplateHeader | document_template_header | Simple | 4 |
| 22 | DocumentoPlantillaFc | DocumentTemplateFC | document_template_fc | Simple | 4 |
| 23 | DocumentoPlantillaPiePagina | DocumentTemplateFooter | document_template_footer | Simple | 4 |
| 24 | TipoDocumentoFc | DocumentTypeFC | document_type_fc | Simple | 4 |
| **FASE 5: Oftalmologia/Otros (4 entidades)** |
| 25 | TipoLente | LensType | lens_type | Simple | 5 |
| 26 | TipoLenteDetalle | LensTypeDetail | lens_type_detail | Moderada | 5 |
| 27 | ItemAtencionPorEspecialidad | CareItemBySpecialty | care_item_by_specialty | Moderada | 5 |
| 28 | Sintoma | Symptom | symptom | Simple | 5 |
| 29 | UbicacionCuerpo | BodyLocation | body_location | Simple | 5 |

### Dependencias Externas

| Entidad | Ubicacion | Estado |
|---------|-----------|--------|
| Specialty | src/Entity/Tenant/Specialty.php | YA EXISTE |

### Dependencias Internas

| Entidad | Depende De | Fase |
|---------|------------|------|
| LensTypeDetail | LensType | 5 (crear LensType antes) |
| CareItemBySpecialty | Specialty | 5 (ya existe) |

### Entidades Descartadas

| Legacy | Razon |
|--------|-------|
| *(ninguna)* | Todas las 28 entidades del inventario son migrables (divididas en 5 fases) |

---

## Patron a Seguir (Referencia)

Todos los archivos nuevos deben seguir EXACTAMENTE el patron existente:

| Tipo | Ejemplo Referencia | Ubicacion |
|------|-------------------|-----------|
| Entity | `src/Entity/Tenant/Gender.php` | `src/Entity/Tenant/` |
| Repository | `src/Repository/Tenant/GenderRepository.php` | `src/Repository/Tenant/` |
| FormType | `src/Form/Maintainers/Personal/GenderType.php` | `src/Form/Maintainers/Clinical/` |
| Controller | `src/Controller/Maintainers/Basic/GenderController.php` | `src/Controller/Maintainers/Clinical/` |
| Template | `templates/maintainers/basic/gender/index.html.twig` | `templates/maintainers/clinical/` |

### Herencia de Controllers

```
AbstractController
  -> AbstractTenantAwareController
    -> AbstractMantenedorController (Template Method Pattern)
      -> [Tu nuevo controller]
```

### Convenciones de Nombres

- **Rutas:** `app_maintainers_clinical_{entity_snake}_{action}`
- **Ejemplo:** `app_maintainers_clinical_diagnosis_index`
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
'lensType.name' => 'Tipo Lente',           // Relacion ManyToOne
'specialty.name' => 'Especialidad',         // Relacion ManyToOne
```

---

## FASE 1: Diagnosticos (6 Entidades)

Entidades relacionadas con diagnosticos y antecedentes medicos.

---

### 1.1 MedicalHistory (Antecedente)

**Legacy:** `Antecedente.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea el archivo src/Entity/Tenant/MedicalHistory.php siguiendo EXACTAMENTE el patron de src/Entity/Tenant/Gender.php.

Especificaciones:
- Tabla: medical_history
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
- Repository: MedicalHistoryRepository

RESTRICCIONES:
- NO agregar campo idEmpresa (multi-tenancy por Hakam)
- NO agregar relaciones adicionales
- Usar PHP 8.2 attributes (#[ORM\...])
- Usar Assert constraints de Symfony
```

#### Prompt Copilot - Repository

```
Crea el archivo src/Repository/Tenant/MedicalHistoryRepository.php siguiendo EXACTAMENTE el patron de src/Repository/Tenant/GenderRepository.php.

- Extends ServiceEntityRepository
- Entity class: MedicalHistory
- Metodos:
  - findAllActive(): array - orderBy name ASC, where isActive=true
- Namespace: App\Repository\Tenant
```

#### Prompt Copilot - FormType

```
Crea el archivo src/Form/Maintainers/Clinical/MedicalHistoryType.php siguiendo EXACTAMENTE el patron de src/Form/Maintainers/Personal/GenderType.php.

Campos del formulario:
- name: TextType, label='Nombre', required, placeholder='Ingrese antecedente medico', class='form-control'
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: MedicalHistory
Namespace: App\Form\Maintainers\Clinical
```

#### Prompt Copilot - Controller

```
Crea el archivo src/Controller/Maintainers/Clinical/MedicalHistoryController.php siguiendo EXACTAMENTE el patron de src/Controller/Maintainers/Basic/GenderController.php.

Especificaciones:
- Route base: /maintainers/clinical/medical-history
- Rutas:
  - GET '' -> index (app_maintainers_clinical_medical_history_index)
  - GET/POST '/create' -> create (app_maintainers_clinical_medical_history_create)
  - GET/POST '/{id}/edit' -> edit (app_maintainers_clinical_medical_history_edit)
  - POST '/{id}/delete' -> delete (app_maintainers_clinical_medical_history_delete)
  - GET '/export' -> export (app_maintainers_clinical_medical_history_export)

- Inyectar: MedicalHistoryRepository, TenantEntityManager, ExportService
- getData(): createQueryBuilder('mh')->orderBy('mh.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getFormType(): MedicalHistoryType::class
- createNewEntity(): new MedicalHistory()
- getTemplatePath(): 'maintainers/clinical/medical_history/index.html.twig'
- getPageTitle(): 'create'=>'Crear Antecedente', 'edit'=>'Editar Antecedente', default=>'Antecedentes Medicos'

- Export columns: ['name', 'isActive']
- Export headers: ['Nombre', 'Activo']
- Export filename: 'antecedentes_medicos_'.date('Y-m-d').'.csv'

RESTRICCIONES:
- NO cambiar el patron de AbstractMantenedorController
- NO agregar logica de negocio extra
- Mantener multi-tenant (AbstractTenantAwareController lo maneja)
```

#### Prompt Copilot - Template

```
Crea el archivo templates/maintainers/clinical/medical_history/index.html.twig siguiendo EXACTAMENTE el patron de templates/maintainers/basic/gender/index.html.twig.

Variables a configurar:
- page_title: 'Antecedentes Medicos'
- icon: 'bx-history'
- breadcrumb_section: 'Clinico'
- description: 'Gestiona los antecedentes medicos del sistema'
- create_route: 'app_maintainers_clinical_medical_history_create'
- edit_route: 'app_maintainers_clinical_medical_history_edit'
- delete_route: 'app_maintainers_clinical_medical_history_delete'
- export_route: 'app_maintainers_clinical_medical_history_export'

Extends: maintainers/modern_index.html.twig
```

---

### 1.2 Diagnosis (Diagnostico)

**Legacy:** `Diagnostico.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/Diagnosis.php siguiendo el patron de Gender.php.

- Tabla: diagnosis
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: DiagnosisRepository
- Namespace: App\Entity\Tenant
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/DiagnosisRepository.php siguiendo el patron de GenderRepository.

- Entity: Diagnosis
- Metodo findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/DiagnosisType.php siguiendo el patron de GenderType.php.

Campos: name, isActive
data_class: Diagnosis
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/DiagnosisController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/diagnosis
- Prefijo rutas: app_maintainers_clinical_diagnosis_
- getColumns(): ['name' => 'Nombre', 'isActive' => 'Estado']
- getPageTitle(): default=>'Diagnosticos'
- filename: 'diagnosticos_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/diagnosis/index.html.twig.

- page_title: 'Diagnosticos'
- icon: 'bx-check-shield'
- breadcrumb_section: 'Clinico'
- description: 'Gestiona los diagnosticos del sistema'
```

---

### 1.3 ImmunotherapyDiagnosis (DiagnosticoInmunoterapia)

**Legacy:** `DiagnosticoInmunoterapia.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/ImmunotherapyDiagnosis.php siguiendo el patron de Gender.php.

- Tabla: immunotherapy_diagnosis
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: ImmunotherapyDiagnosisRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/ImmunotherapyDiagnosisRepository.php siguiendo el patron de GenderRepository.

- Entity: ImmunotherapyDiagnosis
- Metodo findAllActive(): orderBy name ASC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/ImmunotherapyDiagnosisType.php siguiendo el patron de GenderType.php.

Campos: name, isActive
data_class: ImmunotherapyDiagnosis
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/ImmunotherapyDiagnosisController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/immunotherapy-diagnosis
- Prefijo rutas: app_maintainers_clinical_immunotherapy_diagnosis_
- getPageTitle(): default=>'Diagnosticos de Inmunoterapia'
- filename: 'diagnosticos_inmunoterapia_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/immunotherapy_diagnosis/index.html.twig.

- page_title: 'Diagnosticos de Inmunoterapia'
- icon: 'bx-injection'
- breadcrumb_section: 'Clinico'
```

---

### 1.4 DiagnosisByPathology (DiagnosticoPorPatologia)

**Legacy:** `DiagnosticoPorPatologia.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/DiagnosisByPathology.php siguiendo el patron de Gender.php.

- Tabla: diagnosis_by_pathology
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: DiagnosisByPathologyRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/DiagnosisByPathologyRepository.php siguiendo el patron de GenderRepository.

- Entity: DiagnosisByPathology
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/DiagnosisByPathologyType.php siguiendo el patron de GenderType.php.

data_class: DiagnosisByPathology
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/DiagnosisByPathologyController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/diagnosis-by-pathology
- Prefijo rutas: app_maintainers_clinical_diagnosis_by_pathology_
- getPageTitle(): default=>'Diagnosticos por Patologia'
- filename: 'diagnosticos_patologia_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/diagnosis_by_pathology/index.html.twig.

- page_title: 'Diagnosticos por Patologia'
- icon: 'bx-test-tube'
- breadcrumb_section: 'Clinico'
```

---

### 1.5 DiagnosisStatus (EstadoDiagnostico)

**Legacy:** `EstadoDiagnostico.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/DiagnosisStatus.php siguiendo el patron de Gender.php.

- Tabla: diagnosis_status
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: DiagnosisStatusRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/DiagnosisStatusRepository.php siguiendo el patron de GenderRepository.

- Entity: DiagnosisStatus
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/DiagnosisStatusType.php siguiendo el patron de GenderType.php.

data_class: DiagnosisStatus
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/DiagnosisStatusController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/diagnosis-status
- Prefijo rutas: app_maintainers_clinical_diagnosis_status_
- getPageTitle(): default=>'Estados de Diagnostico'
- filename: 'estados_diagnostico_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/diagnosis_status/index.html.twig.

- page_title: 'Estados de Diagnostico'
- icon: 'bx-check-circle'
- breadcrumb_section: 'Clinico'
```

---

### 1.6 MedicalHistoryType (TipoAntecedente)

**Legacy:** `TipoAntecedente.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/MedicalHistoryType.php siguiendo el patron de Gender.php.

- Tabla: medical_history_type
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: MedicalHistoryTypeRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/MedicalHistoryTypeRepository.php siguiendo el patron de GenderRepository.

- Entity: MedicalHistoryType
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/MedicalHistoryTypeType.php siguiendo el patron de GenderType.php.

data_class: MedicalHistoryType
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/MedicalHistoryTypeController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/medical-history-type
- Prefijo rutas: app_maintainers_clinical_medical_history_type_
- getPageTitle(): default=>'Tipos de Antecedente'
- filename: 'tipos_antecedente_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/medical_history_type/index.html.twig.

- page_title: 'Tipos de Antecedente'
- icon: 'bx-category'
- breadcrumb_section: 'Clinico'
```

---

## FASE 2: Examenes (6 Entidades)

Entidades relacionadas con examenes medicos y fisicos.

---

### 2.1 ExamGroup (AgrupacionExamen)

**Legacy:** `AgrupacionExamen.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/ExamGroup.php siguiendo el patron de Gender.php.

- Tabla: exam_group
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: ExamGroupRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/ExamGroupRepository.php siguiendo el patron de GenderRepository.

- Entity: ExamGroup
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/ExamGroupType.php siguiendo el patron de GenderType.php.

data_class: ExamGroup
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/ExamGroupController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/exam-group
- Prefijo rutas: app_maintainers_clinical_exam_group_
- getPageTitle(): default=>'Agrupaciones de Examen'
- filename: 'agrupaciones_examen_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/exam_group/index.html.twig.

- page_title: 'Agrupaciones de Examen'
- icon: 'bx-layer'
- breadcrumb_section: 'Clinico'
```

---

### 2.2 PhysicalExamGroup (ExamenFisicoAgrupacion)

**Legacy:** `ExamenFisicoAgrupacion.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/PhysicalExamGroup.php siguiendo el patron de Gender.php.

- Tabla: physical_exam_group
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: PhysicalExamGroupRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/PhysicalExamGroupRepository.php siguiendo el patron de GenderRepository.

- Entity: PhysicalExamGroup
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/PhysicalExamGroupType.php siguiendo el patron de GenderType.php.

data_class: PhysicalExamGroup
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/PhysicalExamGroupController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/physical-exam-group
- Prefijo rutas: app_maintainers_clinical_physical_exam_group_
- getPageTitle(): default=>'Agrupaciones Examen Fisico'
- filename: 'agrupaciones_examen_fisico_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/physical_exam_group/index.html.twig.

- page_title: 'Agrupaciones Examen Fisico'
- icon: 'bx-user-circle'
- breadcrumb_section: 'Clinico'
```

---

### 2.3 PhysicalExamField (ExamenFisicoCampo)

**Legacy:** `ExamenFisicoCampo.php` - Campos: id, nombre(255), descripcion(text), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/PhysicalExamField.php siguiendo el patron de Gender.php.

- Tabla: physical_exam_field
- Campos: id, name(255), description(text,nullable), isActive, idEstado, createdAt, updatedAt
- Repository: PhysicalExamFieldRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/PhysicalExamFieldRepository.php siguiendo el patron de GenderRepository.

- Entity: PhysicalExamField
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/PhysicalExamFieldType.php siguiendo el patron de GenderType.php.

Campos: name, description (TextareaType), isActive
data_class: PhysicalExamField
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/PhysicalExamFieldController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/physical-exam-field
- Prefijo rutas: app_maintainers_clinical_physical_exam_field_
- getColumns(): ['name' => 'Nombre', 'description' => 'Descripcion', 'isActive' => 'Estado']
- getPageTitle(): default=>'Campos Examen Fisico'
- filename: 'campos_examen_fisico_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/physical_exam_field/index.html.twig.

- page_title: 'Campos Examen Fisico'
- icon: 'bx-text'
- breadcrumb_section: 'Clinico'
```

---

### 2.4 ExamService (ExamenPrestacion)

**Legacy:** `ExamenPrestacion.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/ExamService.php siguiendo el patron de Gender.php.

- Tabla: exam_service
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: ExamServiceRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/ExamServiceRepository.php siguiendo el patron de GenderRepository.

- Entity: ExamService
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/ExamServiceType.php siguiendo el patron de GenderType.php.

data_class: ExamService
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/ExamServiceController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/exam-service
- Prefijo rutas: app_maintainers_clinical_exam_service_
- getPageTitle(): default=>'Examenes Prestacion'
- filename: 'examenes_prestacion_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/exam_service/index.html.twig.

- page_title: 'Examenes Prestacion'
- icon: 'bx-file'
- breadcrumb_section: 'Clinico'
```

---

### 2.5 PhysicalExamType (TipoExamenFisico)

**Legacy:** `TipoExamenFisico.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/PhysicalExamType.php siguiendo el patron de Gender.php.

- Tabla: physical_exam_type
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: PhysicalExamTypeRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/PhysicalExamTypeRepository.php siguiendo el patron de GenderRepository.

- Entity: PhysicalExamType
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/PhysicalExamTypeType.php siguiendo el patron de GenderType.php.

data_class: PhysicalExamType
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/PhysicalExamTypeController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/physical-exam-type
- Prefijo rutas: app_maintainers_clinical_physical_exam_type_
- getPageTitle(): default=>'Tipos Examen Fisico'
- filename: 'tipos_examen_fisico_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/physical_exam_type/index.html.twig.

- page_title: 'Tipos Examen Fisico'
- icon: 'bx-category-alt'
- breadcrumb_section: 'Clinico'
```

---

### 2.6 ExamServiceType (TipoPrestacionExamen)

**Legacy:** `TipoPrestacionExamen.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/ExamServiceType.php siguiendo el patron de Gender.php.

- Tabla: exam_service_type
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: ExamServiceTypeRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/ExamServiceTypeRepository.php siguiendo el patron de GenderRepository.

- Entity: ExamServiceType
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/ExamServiceTypeType.php siguiendo el patron de GenderType.php.

data_class: ExamServiceType
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/ExamServiceTypeController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/exam-service-type
- Prefijo rutas: app_maintainers_clinical_exam_service_type_
- getPageTitle(): default=>'Tipos Prestacion Examen'
- filename: 'tipos_prestacion_examen_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/exam_service_type/index.html.twig.

- page_title: 'Tipos Prestacion Examen'
- icon: 'bx-collection'
- breadcrumb_section: 'Clinico'
```

---

## FASE 3: Medicamentos (7 Entidades)

Entidades relacionadas con medicamentos, dosis y concentraciones.

---

### 3.1 Concentration (Concentracion)

**Legacy:** `Concentracion.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/Concentration.php siguiendo el patron de Gender.php.

- Tabla: concentration
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: ConcentrationRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/ConcentrationRepository.php siguiendo el patron de GenderRepository.

- Entity: Concentration
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/ConcentrationType.php siguiendo el patron de GenderType.php.

data_class: Concentration
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/ConcentrationController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/concentration
- Prefijo rutas: app_maintainers_clinical_concentration_
- getPageTitle(): default=>'Concentraciones'
- filename: 'concentraciones_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/concentration/index.html.twig.

- page_title: 'Concentraciones'
- icon: 'bx-water'
- breadcrumb_section: 'Clinico'
```

---

### 3.2 Dose (Dosis)

**Legacy:** `Dosis.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/Dose.php siguiendo el patron de Gender.php.

- Tabla: dose
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: DoseRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/DoseRepository.php siguiendo el patron de GenderRepository.

- Entity: Dose
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/DoseType.php siguiendo el patron de GenderType.php.

data_class: Dose
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/DoseController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/dose
- Prefijo rutas: app_maintainers_clinical_dose_
- getPageTitle(): default=>'Dosis'
- filename: 'dosis_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/dose/index.html.twig.

- page_title: 'Dosis'
- icon: 'bx-capsule'
- breadcrumb_section: 'Clinico'
```

---

### 3.3 Indication (Indicacion)

**Legacy:** `Indicacion.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/Indication.php siguiendo el patron de Gender.php.

- Tabla: indication
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: IndicationRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/IndicationRepository.php siguiendo el patron de GenderRepository.

- Entity: Indication
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/IndicationType.php siguiendo el patron de GenderType.php.

data_class: Indication
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/IndicationController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/indication
- Prefijo rutas: app_maintainers_clinical_indication_
- getPageTitle(): default=>'Indicaciones'
- filename: 'indicaciones_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/indication/index.html.twig.

- page_title: 'Indicaciones'
- icon: 'bx-notepad'
- breadcrumb_section: 'Clinico'
```

---

### 3.4 BioequivalentMedicine (MedicamentoBioequivalente)

**Legacy:** `MedicamentoBioequivalente.php` - Campos: id, nombre(255), descripcion(text), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/BioequivalentMedicine.php siguiendo el patron de Gender.php.

- Tabla: bioequivalent_medicine
- Campos: id, name(255), description(text,nullable), isActive, idEstado, createdAt, updatedAt
- Repository: BioequivalentMedicineRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/BioequivalentMedicineRepository.php siguiendo el patron de GenderRepository.

- Entity: BioequivalentMedicine
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/BioequivalentMedicineType.php siguiendo el patron de GenderType.php.

Campos: name, description (TextareaType), isActive
data_class: BioequivalentMedicine
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/BioequivalentMedicineController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/bioequivalent-medicine
- Prefijo rutas: app_maintainers_clinical_bioequivalent_medicine_
- getColumns(): ['name' => 'Nombre', 'description' => 'Descripcion', 'isActive' => 'Estado']
- getPageTitle(): default=>'Medicamentos Bioequivalentes'
- filename: 'medicamentos_bioequivalentes_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/bioequivalent_medicine/index.html.twig.

- page_title: 'Medicamentos Bioequivalentes'
- icon: 'bx-book-content'
- breadcrumb_section: 'Clinico'
```

---

### 3.5 Periodicity (Periodicidad)

**Legacy:** `Periodicidad.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/Periodicity.php siguiendo el patron de Gender.php.

- Tabla: periodicity
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: PeriodicityRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/PeriodicityRepository.php siguiendo el patron de GenderRepository.

- Entity: Periodicity
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/PeriodicityType.php siguiendo el patron de GenderType.php.

data_class: Periodicity
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/PeriodicityController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/periodicity
- Prefijo rutas: app_maintainers_clinical_periodicity_
- getPageTitle(): default=>'Periodicidades'
- filename: 'periodicidades_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/periodicity/index.html.twig.

- page_title: 'Periodicidades'
- icon: 'bx-time-five'
- breadcrumb_section: 'Clinico'
```

---

### 3.6 Reaction (Reaccion)

**Legacy:** `Reaccion.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/Reaction.php siguiendo el patron de Gender.php.

- Tabla: reaction
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: ReactionRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/ReactionRepository.php siguiendo el patron de GenderRepository.

- Entity: Reaction
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/ReactionType.php siguiendo el patron de GenderType.php.

data_class: Reaction
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/ReactionController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/reaction
- Prefijo rutas: app_maintainers_clinical_reaction_
- getPageTitle(): default=>'Reacciones'
- filename: 'reacciones_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/reaction/index.html.twig.

- page_title: 'Reacciones'
- icon: 'bx-error-alt'
- breadcrumb_section: 'Clinico'
```

---

### 3.7 ConcentrationType (TipoConcentracion)

**Legacy:** `TipoConcentracion.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/ConcentrationType.php siguiendo el patron de Gender.php.

- Tabla: concentration_type
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: ConcentrationTypeRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/ConcentrationTypeRepository.php siguiendo el patron de GenderRepository.

- Entity: ConcentrationType
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/ConcentrationTypeType.php siguiendo el patron de GenderType.php.

data_class: ConcentrationType
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/ConcentrationTypeController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/concentration-type
- Prefijo rutas: app_maintainers_clinical_concentration_type_
- getPageTitle(): default=>'Tipos de Concentracion'
- filename: 'tipos_concentracion_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/concentration_type/index.html.twig.

- page_title: 'Tipos de Concentracion'
- icon: 'bx-category'
- breadcrumb_section: 'Clinico'
```

---

## FASE 4: Documentos (5 Entidades)

Entidades relacionadas con documentos y plantillas clinicas.

---

### 4.1 DocumentHeaderFooter (DocumentoEncabezadoPie)

**Legacy:** `DocumentoEncabezadoPie.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/DocumentHeaderFooter.php siguiendo el patron de Gender.php.

- Tabla: document_header_footer
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: DocumentHeaderFooterRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/DocumentHeaderFooterRepository.php siguiendo el patron de GenderRepository.

- Entity: DocumentHeaderFooter
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/DocumentHeaderFooterType.php siguiendo el patron de GenderType.php.

data_class: DocumentHeaderFooter
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/DocumentHeaderFooterController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/document-header-footer
- Prefijo rutas: app_maintainers_clinical_document_header_footer_
- getPageTitle(): default=>'Documentos Encabezado Pie'
- filename: 'documentos_encabezado_pie_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/document_header_footer/index.html.twig.

- page_title: 'Documentos Encabezado Pie'
- icon: 'bx-file-blank'
- breadcrumb_section: 'Clinico'
```

---

### 4.2 DocumentTemplateHeader (DocumentoPlantillaEncabezado)

**Legacy:** `DocumentoPlantillaEncabezado.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/DocumentTemplateHeader.php siguiendo el patron de Gender.php.

- Tabla: document_template_header
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: DocumentTemplateHeaderRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/DocumentTemplateHeaderRepository.php siguiendo el patron de GenderRepository.

- Entity: DocumentTemplateHeader
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/DocumentTemplateHeaderType.php siguiendo el patron de GenderType.php.

data_class: DocumentTemplateHeader
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/DocumentTemplateHeaderController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/document-template-header
- Prefijo rutas: app_maintainers_clinical_document_template_header_
- getPageTitle(): default=>'Plantillas Encabezado'
- filename: 'plantillas_encabezado_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/document_template_header/index.html.twig.

- page_title: 'Plantillas Encabezado'
- icon: 'bx-header'
- breadcrumb_section: 'Clinico'
```

---

### 4.3 DocumentTemplateFC (DocumentoPlantillaFc)

**Legacy:** `DocumentoPlantillaFc.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/DocumentTemplateFC.php siguiendo el patron de Gender.php.

- Tabla: document_template_fc
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: DocumentTemplateFCRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/DocumentTemplateFCRepository.php siguiendo el patron de GenderRepository.

- Entity: DocumentTemplateFC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/DocumentTemplateFCType.php siguiendo el patron de GenderType.php.

data_class: DocumentTemplateFC
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/DocumentTemplateFCController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/document-template-fc
- Prefijo rutas: app_maintainers_clinical_document_template_fc_
- getPageTitle(): default=>'Plantillas Ficha Clinica'
- filename: 'plantillas_fc_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/document_template_fc/index.html.twig.

- page_title: 'Plantillas Ficha Clinica'
- icon: 'bx-clipboard'
- breadcrumb_section: 'Clinico'
```

---

### 4.4 DocumentTemplateFooter (DocumentoPlantillaPiePagina)

**Legacy:** `DocumentoPlantillaPiePagina.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/DocumentTemplateFooter.php siguiendo el patron de Gender.php.

- Tabla: document_template_footer
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: DocumentTemplateFooterRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/DocumentTemplateFooterRepository.php siguiendo el patron de GenderRepository.

- Entity: DocumentTemplateFooter
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/DocumentTemplateFooterType.php siguiendo el patron de GenderType.php.

data_class: DocumentTemplateFooter
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/DocumentTemplateFooterController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/document-template-footer
- Prefijo rutas: app_maintainers_clinical_document_template_footer_
- getPageTitle(): default=>'Plantillas Pie de Pagina'
- filename: 'plantillas_pie_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/document_template_footer/index.html.twig.

- page_title: 'Plantillas Pie de Pagina'
- icon: 'bx-footer'
- breadcrumb_section: 'Clinico'
```

---

### 4.5 DocumentTypeFC (TipoDocumentoFc)

**Legacy:** `TipoDocumentoFc.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/DocumentTypeFC.php siguiendo el patron de Gender.php.

- Tabla: document_type_fc
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: DocumentTypeFCRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/DocumentTypeFCRepository.php siguiendo el patron de GenderRepository.

- Entity: DocumentTypeFC
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/DocumentTypeFCType.php siguiendo el patron de GenderType.php.

data_class: DocumentTypeFC
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/DocumentTypeFCController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/document-type-fc
- Prefijo rutas: app_maintainers_clinical_document_type_fc_
- getPageTitle(): default=>'Tipos Documento Ficha Clinica'
- filename: 'tipos_documento_fc_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/document_type_fc/index.html.twig.

- page_title: 'Tipos Documento Ficha Clinica'
- icon: 'bx-folder'
- breadcrumb_section: 'Clinico'
```

---

## FASE 5: Oftalmologia/Otros (5 Entidades)

Entidades relacionadas con oftalmologia, sintomas y ubicaciones corporales.

---

### 5.1 LensType (TipoLente)

**Legacy:** `TipoLente.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/LensType.php siguiendo el patron de Gender.php.

- Tabla: lens_type
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: LensTypeRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/LensTypeRepository.php siguiendo el patron de GenderRepository.

- Entity: LensType
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/LensTypeType.php siguiendo el patron de GenderType.php.

data_class: LensType
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/LensTypeController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/lens-type
- Prefijo rutas: app_maintainers_clinical_lens_type_
- getPageTitle(): default=>'Tipos de Lente'
- filename: 'tipos_lente_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/lens_type/index.html.twig.

- page_title: 'Tipos de Lente'
- icon: 'bx-low-vision'
- breadcrumb_section: 'Clinico'
```

---

### 5.2 LensTypeDetail (TipoLenteDetalle)

**Legacy:** `TipoLenteDetalle.php` - Campos: id, nombre(255), idTipoLente(FK->LensType), idEstado, idEmpresa

**DEPENDENCIA:** Crear LensType ANTES (5.1)

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/LensTypeDetail.php siguiendo el patron de Gender.php pero CON relacion ManyToOne.

- Tabla: lens_type_detail
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - lensType: ManyToOne -> LensType, nullable, JoinColumn(name='lens_type_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: LensTypeDetailRepository
- Namespace: App\Entity\Tenant

IMPORTANTE: La relacion ManyToOne a LensType debe usar:
#[ORM\ManyToOne(targetEntity: LensType::class)]
#[ORM\JoinColumn(name: 'lens_type_id', nullable: true)]
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/LensTypeDetailRepository.php siguiendo el patron de GenderRepository.

- Entity: LensTypeDetail
- Metodos:
  - findAllActive(): orderBy name ASC
  - findByLensType(int $lensTypeId): array
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/LensTypeDetailType.php.

Seguir el patron de MedicalServiceType.php para campos con EntityType (relaciones).

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese detalle', class='form-control'
- lensType: EntityType, class=LensType::class, label='Tipo de Lente',
  choice_label='name', placeholder='Seleccione tipo lente...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: LensTypeDetail
Namespace: App\Form\Maintainers\Clinical
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/LensTypeDetailController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/lens-type-detail
- Prefijo rutas: app_maintainers_clinical_lens_type_detail_
- getData(): createQueryBuilder('ltd')
    ->leftJoin('ltd.lensType', 'lt')
    ->addSelect('lt')
    ->orderBy('ltd.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'lensType.name' => 'Tipo Lente', 'isActive' => 'Estado']
- getFormType(): LensTypeDetailType::class
- createNewEntity(): new LensTypeDetail()
- getTemplatePath(): 'maintainers/clinical/lens_type_detail/index.html.twig'
- getPageTitle(): default=>'Detalles Tipo Lente'
- Export: ['name', 'lensType.name', 'isActive'], headers: ['Nombre', 'Tipo Lente', 'Activo']
- filename: 'detalles_tipo_lente_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/lens_type_detail/index.html.twig.

- page_title: 'Detalles Tipo Lente'
- icon: 'bx-detail'
- breadcrumb_section: 'Clinico'
```

---

### 5.3 CareItemBySpecialty (ItemAtencionPorEspecialidad)

**Legacy:** `ItemAtencionPorEspecialidad.php` - Campos: id, nombre(255), idEspecialidadMedica(FK->Specialty), idEstado, idEmpresa

**DEPENDENCIA:** Specialty ya existe en el proyecto

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/CareItemBySpecialty.php siguiendo el patron de Gender.php pero CON relacion ManyToOne.

- Tabla: care_item_by_specialty
- Campos:
  - id: integer, PK, auto-increment
  - name: string(255), NOT NULL, Assert\NotBlank, Assert\Length(max=255)
  - specialty: ManyToOne -> Specialty, nullable, JoinColumn(name='specialty_id')
  - isActive: boolean, default true
  - idEstado: integer, default 1
  - createdAt: datetime, auto-set en constructor
  - updatedAt: datetime, nullable
- Repository: CareItemBySpecialtyRepository
- Namespace: App\Entity\Tenant

IMPORTANTE: La relacion ManyToOne a Specialty debe usar:
#[ORM\ManyToOne(targetEntity: Specialty::class)]
#[ORM\JoinColumn(name: 'specialty_id', nullable: true)]
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/CareItemBySpecialtyRepository.php siguiendo el patron de GenderRepository.

- Entity: CareItemBySpecialty
- Metodos:
  - findAllActive(): orderBy name ASC
  - findBySpecialty(int $specialtyId): array
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/CareItemBySpecialtyType.php.

Campos:
- name: TextType, label='Nombre', required, placeholder='Ingrese item de atencion', class='form-control'
- specialty: EntityType, class=Specialty::class, label='Especialidad',
  choice_label='name', placeholder='Seleccione especialidad...', required=false, class='form-select',
  query_builder: filtrar por isActive=true, orderBy name ASC
- isActive: CheckboxType, label='Activo', required=false, class='form-check-input'

data_class: CareItemBySpecialty
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/CareItemBySpecialtyController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/care-item-by-specialty
- Prefijo rutas: app_maintainers_clinical_care_item_by_specialty_
- getData(): createQueryBuilder('cibs')
    ->leftJoin('cibs.specialty', 's')
    ->addSelect('s')
    ->orderBy('cibs.id', 'DESC')
- getColumns(): ['name' => 'Nombre', 'specialty.name' => 'Especialidad', 'isActive' => 'Estado']
- getPageTitle(): default=>'Items Atencion por Especialidad'
- filename: 'items_atencion_especialidad_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/care_item_by_specialty/index.html.twig.

- page_title: 'Items Atencion por Especialidad'
- icon: 'bx-list-ul'
- breadcrumb_section: 'Clinico'
```

---

### 5.4 Symptom (Sintoma)

**Legacy:** `Sintoma.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/Symptom.php siguiendo el patron de Gender.php.

- Tabla: symptom
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: SymptomRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/SymptomRepository.php siguiendo el patron de GenderRepository.

- Entity: Symptom
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/SymptomType.php siguiendo el patron de GenderType.php.

data_class: Symptom
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/SymptomController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/symptom
- Prefijo rutas: app_maintainers_clinical_symptom_
- getPageTitle(): default=>'Sintomas'
- filename: 'sintomas_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/symptom/index.html.twig.

- page_title: 'Sintomas'
- icon: 'bx-first-aid'
- breadcrumb_section: 'Clinico'
```

---

### 5.5 BodyLocation (UbicacionCuerpo)

**Legacy:** `UbicacionCuerpo.php` - Campos: id, nombre(255), idEstado, idEmpresa

#### Prompt Copilot - Entity

```
Crea src/Entity/Tenant/BodyLocation.php siguiendo el patron de Gender.php.

- Tabla: body_location
- Campos: id, name(255), isActive, idEstado, createdAt, updatedAt
- Repository: BodyLocationRepository
```

#### Prompt Copilot - Repository

```
Crea src/Repository/Tenant/BodyLocationRepository.php siguiendo el patron de GenderRepository.

- Entity: BodyLocation
```

#### Prompt Copilot - FormType

```
Crea src/Form/Maintainers/Clinical/BodyLocationType.php siguiendo el patron de GenderType.php.

data_class: BodyLocation
```

#### Prompt Copilot - Controller

```
Crea src/Controller/Maintainers/Clinical/BodyLocationController.php siguiendo el patron de GenderController.

- Route base: /maintainers/clinical/body-location
- Prefijo rutas: app_maintainers_clinical_body_location_
- getPageTitle(): default=>'Ubicaciones del Cuerpo'
- filename: 'ubicaciones_cuerpo_'.date('Y-m-d').'.csv'
```

#### Prompt Copilot - Template

```
Crea templates/maintainers/clinical/body_location/index.html.twig.

- page_title: 'Ubicaciones del Cuerpo'
- icon: 'bx-body'
- breadcrumb_section: 'Clinico'
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
1. Fase 1: Diagnosticos (6 entidades)
2. Fase 2: Examenes (6 entidades)
3. Fase 3: Medicamentos (7 entidades)
4. Fase 4: Documentos (5 entidades)
5. Fase 5: Oftalmologia/Otros (5 entidades - crear LensType antes de LensTypeDetail)
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
-- Usar el siguiente numero como base

SELECT id FROM menu_items WHERE name = 'mantenedores';
-- Anotar como {MANTENEDORES_ID} (actualmente = 4)
```

### Paso 2: Insertar categoria + 29 mantenedores (por fases)

Reemplazar los IDs segun corresponda. En este ejemplo:
- `{MANTENEDORES_ID}` = 4 (padre Mantenedores)
- ID base = siguiente disponible

```sql
-- Categoria Clinico (hijo de Mantenedores)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES ({BASE_ID}, 'maintenance_clinical', 'Clinico', NULL, 'bx bx-heart', NULL, 4, 16, true, true, true, '["ROLE_USER"]', NOW());

-- FASE 1: Diagnosticos (6 items)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES
({BASE_ID+1}, 'medical_history', 'Antecedentes Medicos', 'app_maintainers_clinical_medical_history_index', 'bx bx-history', 'maintenance_clinical', {BASE_ID}, 1, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+2}, 'diagnosis', 'Diagnosticos', 'app_maintainers_clinical_diagnosis_index', 'bx bx-check-shield', 'maintenance_clinical', {BASE_ID}, 2, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+3}, 'immunotherapy_diagnosis', 'Diagnosticos Inmunoterapia', 'app_maintainers_clinical_immunotherapy_diagnosis_index', 'bx bx-injection', 'maintenance_clinical', {BASE_ID}, 3, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+4}, 'diagnosis_by_pathology', 'Diagnosticos por Patologia', 'app_maintainers_clinical_diagnosis_by_pathology_index', 'bx bx-test-tube', 'maintenance_clinical', {BASE_ID}, 4, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+5}, 'diagnosis_status', 'Estados de Diagnostico', 'app_maintainers_clinical_diagnosis_status_index', 'bx bx-check-circle', 'maintenance_clinical', {BASE_ID}, 5, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+6}, 'medical_history_type', 'Tipos de Antecedente', 'app_maintainers_clinical_medical_history_type_index', 'bx bx-category', 'maintenance_clinical', {BASE_ID}, 6, true, true, true, '["ROLE_USER"]', NOW());

-- FASE 2: Examenes (6 items)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES
({BASE_ID+7}, 'exam_group', 'Agrupaciones Examen', 'app_maintainers_clinical_exam_group_index', 'bx bx-layer', 'maintenance_clinical', {BASE_ID}, 7, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+8}, 'physical_exam_group', 'Agrupaciones Examen Fisico', 'app_maintainers_clinical_physical_exam_group_index', 'bx bx-user-circle', 'maintenance_clinical', {BASE_ID}, 8, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+9}, 'physical_exam_field', 'Campos Examen Fisico', 'app_maintainers_clinical_physical_exam_field_index', 'bx bx-text', 'maintenance_clinical', {BASE_ID}, 9, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+10}, 'exam_service', 'Examenes Prestacion', 'app_maintainers_clinical_exam_service_index', 'bx bx-file', 'maintenance_clinical', {BASE_ID}, 10, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+11}, 'physical_exam_type', 'Tipos Examen Fisico', 'app_maintainers_clinical_physical_exam_type_index', 'bx bx-category-alt', 'maintenance_clinical', {BASE_ID}, 11, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+12}, 'exam_service_type', 'Tipos Prestacion Examen', 'app_maintainers_clinical_exam_service_type_index', 'bx bx-collection', 'maintenance_clinical', {BASE_ID}, 12, true, true, true, '["ROLE_USER"]', NOW());

-- FASE 3: Medicamentos (7 items)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES
({BASE_ID+13}, 'concentration', 'Concentraciones', 'app_maintainers_clinical_concentration_index', 'bx bx-water', 'maintenance_clinical', {BASE_ID}, 13, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+14}, 'dose', 'Dosis', 'app_maintainers_clinical_dose_index', 'bx bx-capsule', 'maintenance_clinical', {BASE_ID}, 14, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+15}, 'indication', 'Indicaciones', 'app_maintainers_clinical_indication_index', 'bx bx-notepad', 'maintenance_clinical', {BASE_ID}, 15, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+16}, 'bioequivalent_medicine', 'Medicamentos Bioequivalentes', 'app_maintainers_clinical_bioequivalent_medicine_index', 'bx bx-book-content', 'maintenance_clinical', {BASE_ID}, 16, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+17}, 'periodicity', 'Periodicidades', 'app_maintainers_clinical_periodicity_index', 'bx bx-time-five', 'maintenance_clinical', {BASE_ID}, 17, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+18}, 'reaction', 'Reacciones', 'app_maintainers_clinical_reaction_index', 'bx bx-error-alt', 'maintenance_clinical', {BASE_ID}, 18, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+19}, 'concentration_type', 'Tipos Concentracion', 'app_maintainers_clinical_concentration_type_index', 'bx bx-category', 'maintenance_clinical', {BASE_ID}, 19, true, true, true, '["ROLE_USER"]', NOW());

-- FASE 4: Documentos (5 items)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES
({BASE_ID+20}, 'document_header_footer', 'Documentos Encabezado Pie', 'app_maintainers_clinical_document_header_footer_index', 'bx bx-file-blank', 'maintenance_clinical', {BASE_ID}, 20, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+21}, 'document_template_header', 'Plantillas Encabezado', 'app_maintainers_clinical_document_template_header_index', 'bx bx-header', 'maintenance_clinical', {BASE_ID}, 21, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+22}, 'document_template_fc', 'Plantillas Ficha Clinica', 'app_maintainers_clinical_document_template_fc_index', 'bx bx-clipboard', 'maintenance_clinical', {BASE_ID}, 22, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+23}, 'document_template_footer', 'Plantillas Pie Pagina', 'app_maintainers_clinical_document_template_footer_index', 'bx bx-footer', 'maintenance_clinical', {BASE_ID}, 23, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+24}, 'document_type_fc', 'Tipos Documento FC', 'app_maintainers_clinical_document_type_fc_index', 'bx bx-folder', 'maintenance_clinical', {BASE_ID}, 24, true, true, true, '["ROLE_USER"]', NOW());

-- FASE 5: Oftalmologia/Otros (5 items)
INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at)
VALUES
({BASE_ID+25}, 'lens_type', 'Tipos de Lente', 'app_maintainers_clinical_lens_type_index', 'bx bx-low-vision', 'maintenance_clinical', {BASE_ID}, 25, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+26}, 'lens_type_detail', 'Detalles Tipo Lente', 'app_maintainers_clinical_lens_type_detail_index', 'bx bx-detail', 'maintenance_clinical', {BASE_ID}, 26, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+27}, 'care_item_by_specialty', 'Items Atencion por Especialidad', 'app_maintainers_clinical_care_item_by_specialty_index', 'bx bx-list-ul', 'maintenance_clinical', {BASE_ID}, 27, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+28}, 'symptom', 'Sintomas', 'app_maintainers_clinical_symptom_index', 'bx bx-first-aid', 'maintenance_clinical', {BASE_ID}, 28, true, true, true, '["ROLE_USER"]', NOW()),
({BASE_ID+29}, 'body_location', 'Ubicaciones del Cuerpo', 'app_maintainers_clinical_body_location_index', 'bx bx-body', 'maintenance_clinical', {BASE_ID}, 29, true, true, true, '["ROLE_USER"]', NOW());
```

### Paso 3: Limpiar cache de menu

```bash
php bin/console cache:clear
```

### Rollback (en caso de necesitar borrar)

```sql
DELETE FROM menu_items WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintenance_clinical');
DELETE FROM menu_items WHERE name = 'maintenance_clinical';
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
| Entities | 29 | src/Entity/Tenant/ |
| Repositories | 29 | src/Repository/Tenant/ |
| FormTypes | 29 | src/Form/Maintainers/Clinical/ |
| Controllers | 29 | src/Controller/Maintainers/Clinical/ |
| Templates | 29 | templates/maintainers/clinical/ |
| **TOTAL** | **145 archivos** | |

(29 mantenedores = 29 entidades x 5 archivos cada uno)

---

## Resumen por Fases

| Fase | Tematica | Entidades | Complejidad |
|------|----------|-----------|-------------|
| 1 | Diagnosticos | 6 | Baja |
| 2 | Examenes | 6 | Baja |
| 3 | Medicamentos | 7 | Baja |
| 4 | Documentos | 5 | Baja |
| 5 | Oftalmologia/Otros | 5 | Media (2 con FK) |
| **TOTAL** | **5 Fases** | **29** | **Media-Alta** |
