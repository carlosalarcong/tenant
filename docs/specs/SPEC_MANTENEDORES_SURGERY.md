# SPEC: Mantenedores Quirúrgicos (Surgery)

**Categoría**: Surgery  
**Total Mantenedores**: 13  
**Estado**: ✅ Implementado  
**Última actualización**: 2026-02-09

---

## 📐 Arquitectura

Todos los mantenedores quirúrgicos extienden `AbstractMantenedorController` que implementa:
- ✅ CRUD completo con Template Method Pattern
- ✅ Paginación automática vía QueryBuilder
- ✅ Turbo Frames para modales
- ✅ Multi-tenancy con TenantEntityManager
- ✅ Exportación CSV

**Ruta base**: `/maintainers/surgery/{mantenedor}`

---

## 🗂️ Mantenedores Implementados

### 1. Anesthesia Type (Tipo de Anestesia)

**Controlador**: `App\Controller\Maintainers\Surgery\AnesthesiaTypeController`  
**Entidad**: `App\Entity\Tenant\AnesthesiaType`  
**Form**: `App\Form\Maintainers\Surgery\AnesthesiaTypeType`  
**Template**: `templates/maintainers/surgery/anesthesia_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/surgery/anesthesia-type` → `app_maintainers_surgery_anesthesia_type_index`
- `GET /maintainers/surgery/anesthesia-type/create` → `app_maintainers_surgery_anesthesia_type_create`
- `GET /maintainers/surgery/anesthesia-type/{id}/edit` → `app_maintainers_surgery_anesthesia_type_edit`
- `POST /maintainers/surgery/anesthesia-type/{id}/delete` → `app_maintainers_surgery_anesthesia_type_delete`
- `GET /maintainers/surgery/anesthesia-type/export` → `app_maintainers_surgery_anesthesia_type_export`

**Columnas**: name, description, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV

**Export Config**:
- Columnas: name, description, isActive
- Headers: Nombre, Descripción, Activo
- Filename: `tipos_anestesia_YYYY-MM-DD.csv`

---

### 2. Blood Type (Grupo Sanguíneo)

**Controlador**: `App\Controller\Maintainers\Surgery\BloodTypeController`  
**Entidad**: `App\Entity\Tenant\BloodType`  
**Form**: `App\Form\Maintainers\Surgery\BloodTypeType`  
**Template**: `templates/maintainers/surgery/blood_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/surgery/blood-type` → `app_maintainers_surgery_blood_type_index`
- `GET /maintainers/surgery/blood-type/create` → `app_maintainers_surgery_blood_type_create`
- `GET /maintainers/surgery/blood-type/{id}/edit` → `app_maintainers_surgery_blood_type_edit`
- `POST /maintainers/surgery/blood-type/{id}/delete` → `app_maintainers_surgery_blood_type_delete`
- `GET /maintainers/surgery/blood-type/export` → `app_maintainers_surgery_blood_type_export`

**Columnas**: name, description, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV

**Export Config**:
- Columnas: name, description, isActive
- Headers: Nombre, Descripción, Activo
- Filename: `grupos_sanguineos_YYYY-MM-DD.csv`

---

### 3. Surgery Block Reason (Motivo de Bloqueo de Cirugía)

**Controlador**: `App\Controller\Maintainers\Surgery\SurgeryBlockReasonController`  
**Entidad**: `App\Entity\Tenant\SurgeryBlockReason`  
**Form**: `App\Form\Maintainers\Surgery\SurgeryBlockReasonType`  
**Template**: `templates/maintainers/surgery/surgery_block_reason/index.html.twig`

**Endpoints**:
- `GET /maintainers/surgery/surgery-block-reason` → `app_maintainers_surgery_surgery_block_reason_index`
- `GET /maintainers/surgery/surgery-block-reason/create` → `app_maintainers_surgery_surgery_block_reason_create`
- `GET /maintainers/surgery/surgery-block-reason/{id}/edit` → `app_maintainers_surgery_surgery_block_reason_edit`
- `POST /maintainers/surgery/surgery-block-reason/{id}/delete` → `app_maintainers_surgery_surgery_block_reason_delete`
- `GET /maintainers/surgery/surgery-block-reason/export` → `app_maintainers_surgery_surgery_block_reason_export`

**Columnas**: name, description, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV

**Export Config**:
- Columnas: name, description, isActive
- Headers: Nombre, Descripción, Activo
- Filename: `motivos_bloqueo_cirugia_YYYY-MM-DD.csv`

---

### 4. Surgery Cancellation Reason (Motivo de Anulación de Cirugía)

**Controlador**: `App\Controller\Maintainers\Surgery\SurgeryCancellationReasonController`  
**Entidad**: `App\Entity\Tenant\SurgeryCancellationReason`  
**Form**: `App\Form\Maintainers\Surgery\SurgeryCancellationReasonType`  
**Template**: `templates/maintainers/surgery/surgery_cancellation_reason/index.html.twig`

**Endpoints**:
- `GET /maintainers/surgery/surgery-cancellation-reason` → `app_maintainers_surgery_surgery_cancellation_reason_index`
- `GET /maintainers/surgery/surgery-cancellation-reason/create` → `app_maintainers_surgery_surgery_cancellation_reason_create`
- `GET /maintainers/surgery/surgery-cancellation-reason/{id}/edit` → `app_maintainers_surgery_surgery_cancellation_reason_edit`
- `POST /maintainers/surgery/surgery-cancellation-reason/{id}/delete` → `app_maintainers_surgery_surgery_cancellation_reason_delete`
- `GET /maintainers/surgery/surgery-cancellation-reason/export` → `app_maintainers_surgery_surgery_cancellation_reason_export`

**Columnas**: name, description, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV

**Export Config**:
- Columnas: name, description, isActive
- Headers: Nombre, Descripción, Activo
- Filename: `motivos_anulacion_cirugia_YYYY-MM-DD.csv`

---

### 5. Surgery Patient Status Config (Configuración de Estado de Paciente)

**Controlador**: `App\Controller\Maintainers\Surgery\SurgeryPatientStatusConfigController`  
**Entidad**: `App\Entity\Tenant\SurgeryPatientStatusConfig`  
**Form**: `App\Form\Maintainers\Surgery\SurgeryPatientStatusConfigType`  
**Template**: `templates/maintainers/surgery/surgery_patient_status_config/index.html.twig`

**Endpoints**:
- `GET /maintainers/surgery/surgery-patient-status-config` → `app_maintainers_surgery_surgery_patient_status_config_index`
- `GET /maintainers/surgery/surgery-patient-status-config/create` → `app_maintainers_surgery_surgery_patient_status_config_create`
- `GET /maintainers/surgery/surgery-patient-status-config/{id}/edit` → `app_maintainers_surgery_surgery_patient_status_config_edit`
- `POST /maintainers/surgery/surgery-patient-status-config/{id}/delete` → `app_maintainers_surgery_surgery_patient_status_config_delete`
- `GET /maintainers/surgery/surgery-patient-status-config/export` → `app_maintainers_surgery_surgery_patient_status_config_export`

**Columnas**: surgeryPatientStatus.name, color, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV  
**Relación**: 
- LEFT JOIN con `surgeryPatientStatus` (eager loading)

**Export Config**:
- Columnas: surgeryPatientStatus.name, color, isActive
- Headers: Estado Paciente, Color, Activo
- Filename: `config_estados_paciente_YYYY-MM-DD.csv`

---

### 6. Surgery Patient Status (Estado de Paciente)

**Controlador**: `App\Controller\Maintainers\Surgery\SurgeryPatientStatusController`  
**Entidad**: `App\Entity\Tenant\SurgeryPatientStatus`  
**Form**: `App\Form\Maintainers\Surgery\SurgeryPatientStatusType`  
**Template**: `templates/maintainers/surgery/surgery_patient_status/index.html.twig`

**Endpoints**:
- `GET /maintainers/surgery/surgery-patient-status` → `app_maintainers_surgery_surgery_patient_status_index`
- `GET /maintainers/surgery/surgery-patient-status/create` → `app_maintainers_surgery_surgery_patient_status_create`
- `GET /maintainers/surgery/surgery-patient-status/{id}/edit` → `app_maintainers_surgery_surgery_patient_status_edit`
- `POST /maintainers/surgery/surgery-patient-status/{id}/delete` → `app_maintainers_surgery_surgery_patient_status_delete`
- `GET /maintainers/surgery/surgery-patient-status/export` → `app_maintainers_surgery_surgery_patient_status_export`

**Columnas**: name, color, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV

**Export Config**:
- Columnas: name, color, isActive
- Headers: Nombre, Color, Activo
- Filename: `estados_paciente_YYYY-MM-DD.csv`

---

### 7. Surgery Suspension Cause (Causa de Suspensión de Cirugía)

**Controlador**: `App\Controller\Maintainers\Surgery\SurgerySuspensionCauseController`  
**Entidad**: `App\Entity\Tenant\SurgerySuspensionCause`  
**Form**: `App\Form\Maintainers\Surgery\SurgerySuspensionCauseType`  
**Template**: `templates/maintainers/surgery/surgery_suspension_cause/index.html.twig`

**Endpoints**:
- `GET /maintainers/surgery/surgery-suspension-cause` → `app_maintainers_surgery_surgery_suspension_cause_index`
- `GET /maintainers/surgery/surgery-suspension-cause/create` → `app_maintainers_surgery_surgery_suspension_cause_create`
- `GET /maintainers/surgery/surgery-suspension-cause/{id}/edit` → `app_maintainers_surgery_surgery_suspension_cause_edit`
- `POST /maintainers/surgery/surgery-suspension-cause/{id}/delete` → `app_maintainers_surgery_surgery_suspension_cause_delete`
- `GET /maintainers/surgery/surgery-suspension-cause/export` → `app_maintainers_surgery_surgery_suspension_cause_export`

**Columnas**: name, description, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV

**Export Config**:
- Columnas: name, description, isActive
- Headers: Nombre, Descripción, Activo
- Filename: `causas_suspension_cirugia_YYYY-MM-DD.csv`

---

### 8. Surgical Block (Pabellón Quirúrgico)

**Controlador**: `App\Controller\Maintainers\Surgery\SurgicalBlockController`  
**Entidad**: `App\Entity\Tenant\SurgicalBlock`  
**Form**: `App\Form\Maintainers\Surgery\SurgicalBlockType`  
**Template**: `templates/maintainers/surgery/surgical_block/index.html.twig`

**Endpoints**:
- `GET /maintainers/surgery/surgical-block` → `app_maintainers_surgery_surgical_block_index`
- `GET /maintainers/surgery/surgical-block/create` → `app_maintainers_surgery_surgical_block_create`
- `GET /maintainers/surgery/surgical-block/{id}/edit` → `app_maintainers_surgery_surgical_block_edit`
- `POST /maintainers/surgery/surgical-block/{id}/delete` → `app_maintainers_surgery_surgical_block_delete`
- `GET /maintainers/surgery/surgical-block/export` → `app_maintainers_surgery_surgical_block_export`

**Columnas**: name, medicalService.name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV  
**Menu Icon**: `bx-door-open`  
**Relación**: 
- LEFT JOIN con `medicalService` (eager loading)

**Export Config**:
- Columnas: id, name, medicalService.name, isActive, createdAt
- Headers: ID, Nombre, Servicio Médico, Estado, Fecha Creación
- Filename: `pabellones_YYYY-MM-DD_HIS.csv`

---

### 9. Surgical Stage (Etapa Quirúrgica)

**Controlador**: `App\Controller\Maintainers\Surgery\SurgicalStageController`  
**Entidad**: `App\Entity\Tenant\SurgicalStage`  
**Form**: `App\Form\Maintainers\Surgery\SurgicalStageType`  
**Template**: `templates/maintainers/surgery/surgical_stage/index.html.twig`

**Endpoints**:
- `GET /maintainers/surgery/surgical-stage` → `app_maintainers_surgery_surgical_stage_index`
- `GET /maintainers/surgery/surgical-stage/create` → `app_maintainers_surgery_surgical_stage_create`
- `GET /maintainers/surgery/surgical-stage/{id}/edit` → `app_maintainers_surgery_surgical_stage_edit`
- `POST /maintainers/surgery/surgical-stage/{id}/delete` → `app_maintainers_surgery_surgical_stage_delete`
- `GET /maintainers/surgery/surgical-stage/export` → `app_maintainers_surgery_surgical_stage_export`

**Columnas**: sortOrder, abbreviation, name, isMandatory, requiresLogin, isSequential, branch.name, isActive  
**Paginación**: ✅ QueryBuilder (ASC por sortOrder)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV + Ordenamiento personalizado  
**Menu Icon**: `bx-list-ol`  
**Relación**: 
- LEFT JOIN con `branch` (eager loading)

**Export Config**:
- Columnas: id, sortOrder, abbreviation, name, isMandatory, requiresLogin, isSequential, branch.name, isActive, createdAt
- Headers: ID, Orden, Abreviación, Nombre, Obligatorio, Req. Login, Secuencial, Sucursal, Estado, Fecha Creación
- Filename: `etapas_quirurgicas_YYYY-MM-DD_HIS.csv`

---

### 10. Surgical Stage Item (Item de Etapa Quirúrgica)

**Controlador**: `App\Controller\Maintainers\Surgery\SurgicalStageItemController`  
**Entidad**: `App\Entity\Tenant\SurgicalStageItem`  
**Form**: `App\Form\Maintainers\Surgery\SurgicalStageItemType`  
**Template**: `templates/maintainers/surgery/surgical_stage_item/index.html.twig`

**Endpoints**:
- `GET /maintainers/surgery/surgical-stage-item` → `app_maintainers_surgery_surgical_stage_item_index`
- `GET /maintainers/surgery/surgical-stage-item/create` → `app_maintainers_surgery_surgical_stage_item_create`
- `GET /maintainers/surgery/surgical-stage-item/{id}/edit` → `app_maintainers_surgery_surgical_stage_item_edit`
- `POST /maintainers/surgery/surgical-stage-item/{id}/delete` → `app_maintainers_surgery_surgical_stage_item_delete`
- `GET /maintainers/surgery/surgical-stage-item/export` → `app_maintainers_surgery_surgical_stage_item_export`

**Columnas**: sortOrder, name, surgicalStage.name, parent.name, isMandatory, isActive  
**Paginación**: ✅ QueryBuilder (ASC por sortOrder)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV + Ordenamiento personalizado + Jerarquía (parent)  
**Menu Icon**: `bx-detail`  
**Relaciones**: 
- LEFT JOIN con `surgicalStage` (eager loading)
- LEFT JOIN con `parent` (self-referencing, eager loading)

**Export Config**:
- Columnas: id, sortOrder, name, surgicalStage.name, parent.name, isMandatory, isActive, createdAt
- Headers: ID, Orden, Nombre, Etapa, Padre, Obligatorio, Estado, Fecha Creación
- Filename: `items_etapas_quirurgicas_YYYY-MM-DD_HIS.csv`

---

### 11. Surgical Team Role (Rol de Equipo Quirúrgico)

**Controlador**: `App\Controller\Maintainers\Surgery\SurgicalTeamRoleController`  
**Entidad**: `App\Entity\Tenant\SurgicalTeamRole`  
**Form**: `App\Form\Maintainers\Surgery\SurgicalTeamRoleType`  
**Template**: `templates/maintainers/surgery/surgical_team_role/index.html.twig`

**Endpoints**:
- `GET /maintainers/surgery/surgical-team-role` → `app_maintainers_surgery_surgical_team_role_index`
- `GET /maintainers/surgery/surgical-team-role/create` → `app_maintainers_surgery_surgical_team_role_create`
- `GET /maintainers/surgery/surgical-team-role/{id}/edit` → `app_maintainers_surgery_surgical_team_role_edit`
- `POST /maintainers/surgery/surgical-team-role/{id}/delete` → `app_maintainers_surgery_surgical_team_role_delete`
- `GET /maintainers/surgery/surgical-team-role/export` → `app_maintainers_surgery_surgical_team_role_export`

**Columnas**: sortOrder, name, surgeryItem.name, isActive  
**Paginación**: ✅ QueryBuilder (ASC por sortOrder)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV + Ordenamiento personalizado  
**Menu Icon**: `bx-group`  
**Relación**: 
- LEFT JOIN con `surgeryItem` (eager loading)

**Export Config**:
- Columnas: id, sortOrder, name, surgeryItem.name, isActive, createdAt
- Headers: ID, Orden, Nombre, Item Cirugía, Estado, Fecha Creación
- Filename: `roles_equipo_quirurgico_YYYY-MM-DD_HIS.csv`

---

### 12. Treatment Regimen (Régimen de Tratamiento)

**Controlador**: `App\Controller\Maintainers\Surgery\TreatmentRegimenController`  
**Entidad**: `App\Entity\Tenant\TreatmentRegimen`  
**Form**: `App\Form\Maintainers\Surgery\TreatmentRegimenType`  
**Template**: `templates/maintainers/surgery/treatment_regimen/index.html.twig`

**Endpoints**:
- `GET /maintainers/surgery/treatment-regimen` → `app_maintainers_surgery_treatment_regimen_index`
- `GET /maintainers/surgery/treatment-regimen/create` → `app_maintainers_surgery_treatment_regimen_create`
- `GET /maintainers/surgery/treatment-regimen/{id}/edit` → `app_maintainers_surgery_treatment_regimen_edit`
- `POST /maintainers/surgery/treatment-regimen/{id}/delete` → `app_maintainers_surgery_treatment_regimen_delete`
- `GET /maintainers/surgery/treatment-regimen/export` → `app_maintainers_surgery_treatment_regimen_export`

**Columnas**: name, branch.name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV  
**Menu Icon**: `bx-list-check`  
**Relación**: 
- LEFT JOIN con `branch` (eager loading)

**Export Config**:
- Columnas: id, name, branch.name, isActive, createdAt
- Headers: ID, Nombre, Sucursal, Estado, Fecha Creación
- Filename: `regimenes_tratamiento_YYYY-MM-DD_HIS.csv`

---

### 13. Wound Type (Tipo de Herida)

**Controlador**: `App\Controller\Maintainers\Surgery\WoundTypeController`  
**Entidad**: `App\Entity\Tenant\WoundType`  
**Form**: `App\Form\Maintainers\Surgery\WoundTypeType`  
**Template**: `templates/maintainers/surgery/wound_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/surgery/wound-type` → `app_maintainers_surgery_wound_type_index`
- `GET /maintainers/surgery/wound-type/create` → `app_maintainers_surgery_wound_type_create`
- `GET /maintainers/surgery/wound-type/{id}/edit` → `app_maintainers_surgery_wound_type_edit`
- `POST /maintainers/surgery/wound-type/{id}/delete` → `app_maintainers_surgery_wound_type_delete`
- `GET /maintainers/surgery/wound-type/export` → `app_maintainers_surgery_wound_type_export`

**Columnas**: name, description, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV

**Export Config**:
- Columnas: name, description, isActive
- Headers: Nombre, Descripción, Activo
- Filename: `tipos_herida_YYYY-MM-DD.csv`

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
Dashboard > Mantenedores > Quirúrgicos > {Mantenedor}
```

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
| CRUD Completo | ✅ 13/13 |
| Paginación | ✅ 13/13 |
| Exportación | ✅ 13/13 |
| Turbo Frames | ✅ 13/13 |
| Forms validados | ✅ 13/13 |
| Relaciones eager loading | ✅ 6/13 |
| Ordenamiento personalizado | ✅ 3/13 (SurgicalStage, SurgicalStageItem, SurgicalTeamRole) |
| Menu Icons | ✅ 5/13 |
| Tests unitarios | ❌ No implementado |
| Permisos/Roles | ❌ No implementado |

---

## 🔗 Relaciones Entre Mantenedores

### Relaciones Jerárquicas
- **SurgicalStageItem** → **SurgicalStageItem** (parent, auto-referencia)
- **SurgicalStageItem** → **SurgicalStage** (etapa quirúrgica)
- **SurgeryPatientStatusConfig** → **SurgeryPatientStatus** (configuración de estado)

### Relaciones con Otros Módulos
- **SurgicalBlock** → **MedicalService** (servicio médico)
- **SurgicalStage** → **Branch** (sucursal)
- **SurgicalTeamRole** → **SurgeryItem** (item de cirugía)
- **TreatmentRegimen** → **Branch** (sucursal)

### Mantenedores Independientes
Los siguientes no tienen relaciones explícitas en sus controladores:
- AnesthesiaType
- BloodType
- SurgeryBlockReason
- SurgeryCancellationReason
- SurgerySuspensionCause
- WoundType

---

## 🎯 Características Destacadas

### Ordenamiento Personalizado
Tres mantenedores utilizan ordenamiento por `sortOrder` (ASC) en lugar del típico `id DESC`:
- **SurgicalStage**: Permite definir secuencia explícita de etapas
- **SurgicalStageItem**: Mantiene orden jerárquico de items
- **SurgicalTeamRole**: Define orden de roles en equipo quirúrgico

### Campos Especiales

#### Color
Dos mantenedores gestionan colores (probablemente para UI):
- **SurgeryPatientStatus**: color
- **SurgeryPatientStatusConfig**: color

#### Campos Booleanos Complejos
**SurgicalStage** tiene múltiples flags:
- `isMandatory`: Etapa obligatoria
- `requiresLogin`: Requiere autenticación
- `isSequential`: Debe seguir secuencia
- `abbreviation`: Código corto para etapa

**SurgicalStageItem**:
- `isMandatory`: Item obligatorio
- `parent`: Permite jerarquía multinivel

---

## 📝 Notas Técnicas

1. **Multi-tenancy**: Todos los mantenedores usan `TenantEntityManager` para aislar datos por tenant
2. **Soft Delete**: No implementado - Delete es físico
3. **Audit Trail**: No implementado
4. **Ordenamiento**: Mayoría usa `ORDER BY id DESC`, excepto 3 con `sortOrder ASC`
5. **Búsqueda/Filtros**: No implementado en base
6. **Import masivo**: No implementado
7. **Eager Loading**: 6/13 utilizan LEFT JOIN para optimizar queries con relaciones
8. **Export Headers**: Algunos usan traducciones, otros strings hard-coded en español

---

## 🚀 Próximos Pasos

1. ✅ Documentar Forms completos (13/13 implementados)
2. Implementar tests unitarios
3. Agregar sistema de permisos por rol
4. Implementar búsqueda/filtros
5. Agregar soft delete
6. Implementar import CSV masivo
7. Estandarizar export headers (usar traducciones consistentes)
8. Agregar iconos a los 8 mantenedores faltantes
9. Implementar audit trail (createdBy, updatedBy, deletedBy)
10. Optimizar queries en mantenedores sin relaciones (7/13)

---

## 🔍 Análisis de Patrones

### Patrones Comunes (10/13 mantenedores)
Estructura simple: `name`, `description`, `isActive`
- AnesthesiaType
- BloodType
- SurgeryBlockReason
- SurgeryCancellationReason
- SurgerySuspensionCause
- WoundType
- SurgeryPatientStatus (con `color` adicional)
- SurgicalBlock (con relación `medicalService`)
- SurgicalStage (estructura compleja)
- TreatmentRegimen (con relación `branch`)

### Patrones Avanzados (3/13)
- **SurgicalStage**: Configuración compleja con flags múltiples y ordenamiento
- **SurgicalStageItem**: Jerarquía auto-referencial + ordenamiento
- **SurgicalTeamRole**: Ordenamiento + relación externa

### Mantenedores de Configuración (1/13)
- **SurgeryPatientStatusConfig**: Extensión/configuración de otro mantenedor

---

**Generado**: 2026-02-09  
**Autor**: Sistema de Documentación Automática  
**Versión**: 1.0
