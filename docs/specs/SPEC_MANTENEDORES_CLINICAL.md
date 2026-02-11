# SPEC: Mantenedores Clínicos

**Categoría**: Clinical  
**Total Mantenedores**: 12  
**Estado**: ✅ Implementado  
**Última actualización**: 2026-02-09

---

## 📐 Arquitectura

Todos los mantenedores clínicos extienden `AbstractMantenedorController` que implementa:
- ✅ CRUD completo con Template Method Pattern
- ✅ Paginación automática vía QueryBuilder
- ✅ Turbo Frames para modales
- ✅ Multi-tenancy con TenantEntityManager
- ✅ Exportación CSV

**Ruta base**: `/maintainers/clinical/{mantenedor}`

---

## 🗂️ Mantenedores Implementados

### 1. Diagnosis (Diagnóstico)

**Controlador**: `App\Controller\Maintainers\Clinical\DiagnosisController`  
**Entidad**: `App\Entity\Tenant\Diagnosis`  
**Form**: `App\Form\Maintainers\Clinical\DiagnosisType`  
**Template**: `templates/maintainers/clinical/diagnosis/index.html.twig`

**Endpoints**:
- `GET /maintainers/clinical/diagnosis` → `app_maintainers_clinical_diagnosis_index`
- `GET /maintainers/clinical/diagnosis/create` → `app_maintainers_clinical_diagnosis_create`
- `GET /maintainers/clinical/diagnosis/{id}/edit` → `app_maintainers_clinical_diagnosis_edit`
- `POST /maintainers/clinical/diagnosis/{id}/delete` → `app_maintainers_clinical_diagnosis_delete`
- `GET /maintainers/clinical/diagnosis/export` → `app_maintainers_clinical_diagnosis_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Export filename**: `diagnosticos_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 2. Diagnosis By Pathology (Diagnóstico por Patología)

**Controlador**: `App\Controller\Maintainers\Clinical\DiagnosisByPathologyController`  
**Entidad**: `App\Entity\Tenant\DiagnosisByPathology`  
**Form**: `App\Form\Maintainers\Clinical\DiagnosisByPathologyType`  
**Template**: `templates/maintainers/clinical/diagnosis_by_pathology/index.html.twig`

**Endpoints**:
- `GET /maintainers/clinical/diagnosis-by-pathology` → `app_maintainers_clinical_diagnosis_by_pathology_index`
- `GET /maintainers/clinical/diagnosis-by-pathology/create` → `app_maintainers_clinical_diagnosis_by_pathology_create`
- `GET /maintainers/clinical/diagnosis-by-pathology/{id}/edit` → `app_maintainers_clinical_diagnosis_by_pathology_edit`
- `POST /maintainers/clinical/diagnosis-by-pathology/{id}/delete` → `app_maintainers_clinical_diagnosis_by_pathology_delete`
- `GET /maintainers/clinical/diagnosis-by-pathology/export` → `app_maintainers_clinical_diagnosis_by_pathology_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `diagnosticos_patologia_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV  
**Hook**: `beforeSave()` actualiza `updatedAt`

---

### 3. Diagnosis Status (Estado de Diagnóstico)

**Controlador**: `App\Controller\Maintainers\Clinical\DiagnosisStatusController`  
**Entidad**: `App\Entity\Tenant\DiagnosisStatus`  
**Form**: `App\Form\Maintainers\Clinical\DiagnosisStatusType`  
**Template**: `templates/maintainers/clinical/diagnosis_status/index.html.twig`

**Endpoints**:
- `GET /maintainers/clinical/diagnosis-status` → `app_maintainers_clinical_diagnosis_status_index`
- `GET /maintainers/clinical/diagnosis-status/create` → `app_maintainers_clinical_diagnosis_status_create`
- `GET /maintainers/clinical/diagnosis-status/{id}/edit` → `app_maintainers_clinical_diagnosis_status_edit`
- `POST /maintainers/clinical/diagnosis-status/{id}/delete` → `app_maintainers_clinical_diagnosis_status_delete`
- `GET /maintainers/clinical/diagnosis-status/export` → `app_maintainers_clinical_diagnosis_status_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `estados_diagnostico_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV  
**Hook**: `beforeSave()` actualiza `updatedAt`

---

### 4. Exam Group (Agrupación de Examen)

**Controlador**: `App\Controller\Maintainers\Clinical\ExamGroupController`  
**Entidad**: `App\Entity\Tenant\ExamGroup`  
**Form**: `App\Form\Maintainers\Clinical\ExamGroupType`  
**Template**: `templates/maintainers/clinical/exam_group/index.html.twig`

**Endpoints**:
- `GET /maintainers/clinical/exam-group` → `app_maintainers_clinical_exam_group_index`
- `GET /maintainers/clinical/exam-group/create` → `app_maintainers_clinical_exam_group_create`
- `GET /maintainers/clinical/exam-group/{id}/edit` → `app_maintainers_clinical_exam_group_edit`
- `POST /maintainers/clinical/exam-group/{id}/delete` → `app_maintainers_clinical_exam_group_delete`
- `GET /maintainers/clinical/exam-group/export` → `app_maintainers_clinical_exam_group_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `agrupaciones_examen_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 5. Exam Service (Examen de Prestación)

**Controlador**: `App\Controller\Maintainers\Clinical\ExamServiceController`  
**Entidad**: `App\Entity\Tenant\ExamService`  
**Form**: `App\Form\Maintainers\Clinical\ExamServiceType`  
**Template**: `templates/maintainers/clinical/exam_service/index.html.twig`

**Endpoints**:
- `GET /maintainers/clinical/exam-service` → `app_maintainers_clinical_exam_service_index`
- `GET /maintainers/clinical/exam-service/create` → `app_maintainers_clinical_exam_service_create`
- `GET /maintainers/clinical/exam-service/{id}/edit` → `app_maintainers_clinical_exam_service_edit`
- `POST /maintainers/clinical/exam-service/{id}/delete` → `app_maintainers_clinical_exam_service_delete`
- `GET /maintainers/clinical/exam-service/export` → `app_maintainers_clinical_exam_service_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `examenes_prestacion_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 6. Exam Service Type (Tipo de Prestación de Examen)

**Controlador**: `App\Controller\Maintainers\Clinical\ExamServiceTypeController`  
**Entidad**: `App\Entity\Tenant\ExamServiceType`  
**Form**: `App\Form\Maintainers\Clinical\ExamServiceTypeForm`  
**Template**: `templates/maintainers/clinical/exam_service_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/clinical/exam-service-type` → `app_maintainers_clinical_exam_service_type_index`
- `GET /maintainers/clinical/exam-service-type/create` → `app_maintainers_clinical_exam_service_type_create`
- `GET /maintainers/clinical/exam-service-type/{id}/edit` → `app_maintainers_clinical_exam_service_type_edit`
- `POST /maintainers/clinical/exam-service-type/{id}/delete` → `app_maintainers_clinical_exam_service_type_delete`
- `GET /maintainers/clinical/exam-service-type/export` → `app_maintainers_clinical_exam_service_type_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `tipos_prestacion_examen_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV  
**Relación**: Asociado con ExamService

---

### 7. Immunotherapy Diagnosis (Diagnóstico de Inmunoterapia)

**Controlador**: `App\Controller\Maintainers\Clinical\ImmunotherapyDiagnosisController`  
**Entidad**: `App\Entity\Tenant\ImmunotherapyDiagnosis`  
**Form**: `App\Form\Maintainers\Clinical\ImmunotherapyDiagnosisType`  
**Template**: `templates/maintainers/clinical/immunotherapy_diagnosis/index.html.twig`

**Endpoints**:
- `GET /maintainers/clinical/immunotherapy-diagnosis` → `app_maintainers_clinical_immunotherapy_diagnosis_index`
- `GET /maintainers/clinical/immunotherapy-diagnosis/create` → `app_maintainers_clinical_immunotherapy_diagnosis_create`
- `GET /maintainers/clinical/immunotherapy-diagnosis/{id}/edit` → `app_maintainers_clinical_immunotherapy_diagnosis_edit`
- `POST /maintainers/clinical/immunotherapy-diagnosis/{id}/delete` → `app_maintainers_clinical_immunotherapy_diagnosis_delete`
- `GET /maintainers/clinical/immunotherapy-diagnosis/export` → `app_maintainers_clinical_immunotherapy_diagnosis_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `diagnosticos_inmunoterapia_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV  
**Hook**: `beforeSave()` actualiza `updatedAt`

---

### 8. Medical History (Antecedente Médico)

**Controlador**: `App\Controller\Maintainers\Clinical\MedicalHistoryController`  
**Entidad**: `App\Entity\Tenant\MedicalHistory`  
**Form**: `App\Form\Maintainers\Clinical\MedicalHistoryType`  
**Template**: `templates/maintainers/clinical/medical_history/index.html.twig`

**Endpoints**:
- `GET /maintainers/clinical/medical-history` → `app_maintainers_clinical_medical_history_index`
- `GET /maintainers/clinical/medical-history/create` → `app_maintainers_clinical_medical_history_create`
- `GET /maintainers/clinical/medical-history/{id}/edit` → `app_maintainers_clinical_medical_history_edit`
- `POST /maintainers/clinical/medical-history/{id}/delete` → `app_maintainers_clinical_medical_history_delete`
- `GET /maintainers/clinical/medical-history/export` → `app_maintainers_clinical_medical_history_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Export filename**: `antecedentes_medicos_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 9. Medical History Type (Tipo de Antecedente Médico)

**Controlador**: `App\Controller\Maintainers\Clinical\MedicalHistoryTypeController`  
**Entidad**: `App\Entity\Tenant\MedicalHistoryType`  
**Form**: `App\Form\Maintainers\Clinical\MedicalHistoryTypeForm`  
**Template**: `templates/maintainers/clinical/medical_history_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/clinical/medical-history-type` → `app_maintainers_clinical_medical_history_type_index`
- `GET /maintainers/clinical/medical-history-type/create` → `app_maintainers_clinical_medical_history_type_create`
- `GET /maintainers/clinical/medical-history-type/{id}/edit` → `app_maintainers_clinical_medical_history_type_edit`
- `POST /maintainers/clinical/medical-history-type/{id}/delete` → `app_maintainers_clinical_medical_history_type_delete`
- `GET /maintainers/clinical/medical-history-type/export` → `app_maintainers_clinical_medical_history_type_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `tipos_antecedentes_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV  
**Hook**: `beforeSave()` actualiza `updatedAt`  
**Relación**: Asociado con MedicalHistory

---

### 10. Physical Exam Field (Campo de Examen Físico)

**Controlador**: `App\Controller\Maintainers\Clinical\PhysicalExamFieldController`  
**Entidad**: `App\Entity\Tenant\PhysicalExamField`  
**Form**: `App\Form\Maintainers\Clinical\PhysicalExamFieldType`  
**Template**: `templates/maintainers/clinical/physical_exam_field/index.html.twig`

**Endpoints**:
- `GET /maintainers/clinical/physical-exam-field` → `app_maintainers_clinical_physical_exam_field_index`
- `GET /maintainers/clinical/physical-exam-field/create` → `app_maintainers_clinical_physical_exam_field_create`
- `GET /maintainers/clinical/physical-exam-field/{id}/edit` → `app_maintainers_clinical_physical_exam_field_edit`
- `POST /maintainers/clinical/physical-exam-field/{id}/delete` → `app_maintainers_clinical_physical_exam_field_delete`
- `GET /maintainers/clinical/physical-exam-field/export` → `app_maintainers_clinical_physical_exam_field_export`

**Columnas**: name, description, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `campos_examen_fisico_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV  
**Nota**: Única entidad con campo `description`

---

### 11. Physical Exam Group (Agrupación de Examen Físico)

**Controlador**: `App\Controller\Maintainers\Clinical\PhysicalExamGroupController`  
**Entidad**: `App\Entity\Tenant\PhysicalExamGroup`  
**Form**: `App\Form\Maintainers\Clinical\PhysicalExamGroupType`  
**Template**: `templates/maintainers/clinical/physical_exam_group/index.html.twig`

**Endpoints**:
- `GET /maintainers/clinical/physical-exam-group` → `app_maintainers_clinical_physical_exam_group_index`
- `GET /maintainers/clinical/physical-exam-group/create` → `app_maintainers_clinical_physical_exam_group_create`
- `GET /maintainers/clinical/physical-exam-group/{id}/edit` → `app_maintainers_clinical_physical_exam_group_edit`
- `POST /maintainers/clinical/physical-exam-group/{id}/delete` → `app_maintainers_clinical_physical_exam_group_delete`
- `GET /maintainers/clinical/physical-exam-group/export` → `app_maintainers_clinical_physical_exam_group_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `agrupaciones_examen_fisico_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 12. Physical Exam Type (Tipo de Examen Físico)

**Controlador**: `App\Controller\Maintainers\Clinical\PhysicalExamTypeController`  
**Entidad**: `App\Entity\Tenant\PhysicalExamType`  
**Form**: `App\Form\Maintainers\Clinical\PhysicalExamTypeForm`  
**Template**: `templates/maintainers/clinical/physical_exam_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/clinical/physical-exam-type` → `app_maintainers_clinical_physical_exam_type_index`
- `GET /maintainers/clinical/physical-exam-type/create` → `app_maintainers_clinical_physical_exam_type_create`
- `GET /maintainers/clinical/physical-exam-type/{id}/edit` → `app_maintainers_clinical_physical_exam_type_edit`
- `POST /maintainers/clinical/physical-exam-type/{id}/delete` → `app_maintainers_clinical_physical_exam_type_delete`
- `GET /maintainers/clinical/physical-exam-type/export` → `app_maintainers_clinical_physical_exam_type_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `tipos_examen_fisico_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

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
protected function getIndexRoute(): string;
protected function findEntity(int $id): ?object;
```

**Métodos implementados**:
- `handleIndex()` - Listado con paginación automática
- `handleCreate()` - Crear con Turbo Frame
- `handleEdit()` - Editar con Turbo Frame
- `handleDelete()` - Eliminar con confirmación
- `handleExport()` - Exportar CSV
- `paginate()` - Paginación con Doctrine Paginator
- `isTurboFrameRequest()` - Detección Turbo Frame

**Hooks opcionales**:
- `beforeSave(object $entity, Request $request): void` - Pre-procesamiento antes de guardar
- `canDelete(object $entity): bool` - Validación antes de eliminar
- `getPageTitle(?string $action = null): string` - Títulos personalizados por acción

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
Dashboard > Mantenedores > Clínico > {Mantenedor}
```

**Columnas estándar**: name, isActive (11/12 mantenedores)  
**Columna especial**: PhysicalExamField incluye `description`

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
| CRUD Completo | ✅ 12/12 |
| Paginación | ✅ 12/12 |
| Exportación | ✅ 12/12 |
| Turbo Frames | ✅ 12/12 |
| Forms implementados | ✅ 12/12 |
| Hooks beforeSave | ✅ 5/12 (DiagnosisByPathology, DiagnosisStatus, ImmunotherapyDiagnosis, MedicalHistoryType, otros) |
| Tests unitarios | ❌ No implementado |
| Permisos/Roles | ❌ No implementado |

---

## 📝 Notas Técnicas

1. **Multi-tenancy**: Todos los mantenedores usan `TenantEntityManager` para aislar datos por tenant
2. **Soft Delete**: No implementado - Delete es físico
3. **Audit Trail**: Implementación parcial con `updatedAt` en algunos mantenedores
4. **Ordenamiento**: Por defecto `ORDER BY id DESC`
5. **Búsqueda/Filtros**: No implementado en base
6. **Import masivo**: No implementado
7. **Nomenclatura Forms**: Mezcla entre `*Type` y `*Form` (estandarizar a `*Type`)

---

## 🏥 Relaciones entre Mantenedores

```
MedicalHistory
    └── MedicalHistoryType (tipo de antecedente)

Diagnosis
    ├── DiagnosisStatus (estado del diagnóstico)
    ├── DiagnosisByPathology (diagnóstico por patología específica)
    └── ImmunotherapyDiagnosis (diagnóstico específico de inmunoterapia)

ExamService
    ├── ExamServiceType (tipo de prestación)
    └── ExamGroup (agrupación de exámenes)

PhysicalExam
    ├── PhysicalExamType (tipo de examen físico)
    ├── PhysicalExamGroup (agrupación de exámenes físicos)
    └── PhysicalExamField (campos específicos del examen)
```

---

## 🚀 Próximos Pasos

1. **Estandarización**: Renombrar Forms de `*Form` a `*Type` para consistencia
2. **Audit completo**: Implementar `createdAt` y `updatedAt` en todos los mantenedores
3. **Tests unitarios**: Cobertura mínima 80%
4. **Sistema de permisos**: Implementar roles por módulo clínico
5. **Búsqueda avanzada**: Filtros por nombre, estado, etc.
6. **Soft delete**: Implementar eliminación lógica
7. **Import CSV**: Carga masiva de datos clínicos
8. **Validaciones de negocio**: Reglas específicas por mantenedor
9. **Documentación médica**: Agregar descripciones clínicas a cada mantenedor
10. **Relaciones FK**: Validar integridad referencial entre mantenedores relacionados

---

## 📚 Referencias

- [AbstractMantenedorController](/var/www/html/melisa_tenant/src/Controller/AbstractMantenedorController.php)
- [Entidades Tenant](/var/www/html/melisa_tenant/src/Entity/Tenant/)
- [Forms Clinical](/var/www/html/melisa_tenant/src/Form/Maintainers/Clinical/)
- [Templates Clinical](/var/www/html/melisa_tenant/templates/maintainers/clinical/)
