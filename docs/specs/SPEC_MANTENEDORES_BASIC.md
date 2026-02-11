# SPEC: Mantenedores Básicos

**Categoría**: Basic  
**Total Mantenedores**: 14  
**Estado**: ✅ Implementado  
**Última actualización**: 2026-02-09

---

## 📐 Arquitectura

Todos los mantenedores básicos extienden `AbstractMantenedorController` que implementa:
- ✅ CRUD completo con Template Method Pattern
- ✅ Paginación automática vía QueryBuilder
- ✅ Turbo Frames para modales
- ✅ Multi-tenancy con TenantEntityManager
- ✅ Exportación CSV

**Ruta base**: `/maintainers/basic/{mantenedor}`

---

## 🗂️ Mantenedores Implementados

### 1. Doctor Type (Tipos de Médico)

**Controlador**: `App\Controller\Maintainers\Basic\DoctorTypeController`  
**Entidad**: `App\Entity\Tenant\DoctorType`  
**Form**: `App\Form\Maintainers\Clinical\DoctorTypeType`  
**Template**: `templates/maintainers/basic/doctor_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/basic/doctor-type` → Listado con paginación
- `GET /maintainers/basic/doctor-type/create` → Crear (modal)
- `GET /maintainers/basic/doctor-type/{id}/edit` → Editar (modal)
- `POST /maintainers/basic/doctor-type/{id}/delete` → Eliminar
- `GET /maintainers/basic/doctor-type/export` → Exportar CSV

**Columnas**: name, code, isActive  
**Paginación**: ✅ QueryBuilder  
**Features**: CRUD + Export

---

### 2. Education Level (Nivel Educativo)

**Controlador**: `App\Controller\Maintainers\Basic\EducationLevelController`  
**Entidad**: `App\Entity\Tenant\EducationLevel`  
**Form**: TBD  
**Template**: `templates/maintainers/basic/education_level/index.html.twig`

**Endpoints**:
- `GET /maintainers/basic/education-level` → Listado
- `GET /maintainers/basic/education-level/create` → Crear
- `GET /maintainers/basic/education-level/{id}/edit` → Editar
- `POST /maintainers/basic/education-level/{id}/delete` → Eliminar
- `GET /maintainers/basic/education-level/export` → Exportar

**Features**: CRUD + Export

---

### 3. Education Level Detail (Detalle Nivel Educativo)

**Controlador**: `App\Controller\Maintainers\Basic\EducationLevelDetailController`  
**Entidad**: `App\Entity\Tenant\EducationLevelDetail`  
**Form**: TBD  
**Template**: `templates/maintainers/basic/education_level_detail/index.html.twig`

**Endpoints**:
- `GET /maintainers/basic/education-level-detail` → Listado
- `GET /maintainers/basic/education-level-detail/create` → Crear
- `GET /maintainers/basic/education-level-detail/{id}/edit` → Editar
- `POST /maintainers/basic/education-level-detail/{id}/delete` → Eliminar
- `GET /maintainers/basic/education-level-detail/export` → Exportar

**Features**: CRUD + Export  
**Relación**: Depende de EducationLevel

---

### 4. Ethnic Group (Etnia)

**Controlador**: `App\Controller\Maintainers\Basic\EthnicGroupController`  
**Entidad**: `App\Entity\Tenant\EthnicGroup`  
**Form**: TBD  
**Template**: `templates/maintainers/basic/ethnic_group/index.html.twig`

**Endpoints**:
- `GET /maintainers/basic/ethnic-group` → Listado
- `GET /maintainers/basic/ethnic-group/create` → Crear
- `GET /maintainers/basic/ethnic-group/{id}/edit` → Editar
- `POST /maintainers/basic/ethnic-group/{id}/delete` → Eliminar
- `GET /maintainers/basic/ethnic-group/export` → Exportar

**Columnas típicas**: name, code, isActive  
**Features**: CRUD + Export

---

### 5. Gender (Sexo/Género)

**Controlador**: `App\Controller\Maintainers\Basic\GenderController`  
**Entidad**: `App\Entity\Tenant\Gender`  
**Form**: `App\Form\Maintainers\Personal\GenderType`  
**Template**: `templates/maintainers/basic/gender/index.html.twig`

**Endpoints**:
- `GET /maintainers/basic/gender` → `app_maintainers_gender_index`
- `GET /maintainers/basic/gender/create` → `app_maintainers_gender_create`
- `GET /maintainers/basic/gender/{id}/edit` → `app_maintainers_gender_edit`
- `POST /maintainers/basic/gender/{id}/delete` → `app_maintainers_gender_delete`
- `GET /maintainers/basic/gender/export` → `app_maintainers_gender_export`

**Columnas**: name, code, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV

**Form Fields**:
```php
- name: TextType (required)
- code: TextType (optional, max 10 chars)
- isActive: CheckboxType
```

---

### 6. Insurance Administrator (Administradora de Seguros)

**Controlador**: `App\Controller\Maintainers\Basic\InsuranceAdministratorController`  
**Entidad**: `App\Entity\Tenant\InsuranceAdministrator`  
**Form**: TBD  
**Template**: `templates/maintainers/basic/insurance_administrator/index.html.twig`

**Endpoints**:
- `GET /maintainers/basic/insurance-administrator` → Listado
- `GET /maintainers/basic/insurance-administrator/create` → Crear
- `GET /maintainers/basic/insurance-administrator/{id}/edit` → Editar
- `POST /maintainers/basic/insurance-administrator/{id}/delete` → Eliminar
- `GET /maintainers/basic/insurance-administrator/export` → Exportar

**Features**: CRUD + Export

---

### 7. Job Position (Cargo/Posición)

**Controlador**: `App\Controller\Maintainers\Basic\JobPositionController`  
**Entidad**: `App\Entity\Tenant\JobPosition`  
**Form**: TBD  
**Template**: `templates/maintainers/basic/job_position/index.html.twig`

**Endpoints**:
- `GET /maintainers/basic/job-position` → Listado
- `GET /maintainers/basic/job-position/create` → Crear
- `GET /maintainers/basic/job-position/{id}/edit` → Editar
- `POST /maintainers/basic/job-position/{id}/delete` → Eliminar
- `GET /maintainers/basic/job-position/export` → Exportar

**Features**: CRUD + Export

---

### 8. Location (Ubicación)

**Controlador**: `App\Controller\Maintainers\Basic\LocationController`  
**Entidad**: `App\Entity\Tenant\Location`  
**Form**: TBD  
**Template**: `templates/maintainers/basic/location/index.html.twig`

**Endpoints**:
- `GET /maintainers/basic/location` → Listado
- `GET /maintainers/basic/location/create` → Crear
- `GET /maintainers/basic/location/{id}/edit` → Editar
- `POST /maintainers/basic/location/{id}/delete` → Eliminar
- `GET /maintainers/basic/location/export` → Exportar

**Features**: CRUD + Export

---

### 9. Marital Status (Estado Civil)

**Controlador**: `App\Controller\Maintainers\Basic\MaritalStatusController`  
**Entidad**: `App\Entity\Tenant\MaritalStatus`  
**Form**: TBD  
**Template**: `templates/maintainers/basic/marital_status/index.html.twig`

**Endpoints**:
- `GET /maintainers/basic/marital-status` → Listado
- `GET /maintainers/basic/marital-status/create` → Crear
- `GET /maintainers/basic/marital-status/{id}/edit` → Editar
- `POST /maintainers/basic/marital-status/{id}/delete` → Eliminar
- `GET /maintainers/basic/marital-status/export` → Exportar

**Columnas típicas**: name, code, isActive  
**Features**: CRUD + Export

---

### 10. Medical Box (Caja Médica)

**Controlador**: `App\Controller\Maintainers\Basic\MedicalBoxController`  
**Entidad**: `App\Entity\Tenant\MedicalBox`  
**Form**: TBD  
**Template**: `templates/maintainers/basic/medical_box/index.html.twig`

**Endpoints**:
- `GET /maintainers/basic/medical-box` → Listado
- `GET /maintainers/basic/medical-box/create` → Crear
- `GET /maintainers/basic/medical-box/{id}/edit` → Editar
- `POST /maintainers/basic/medical-box/{id}/delete` → Eliminar
- `GET /maintainers/basic/medical-box/export` → Exportar

**Features**: CRUD + Export

---

### 11. Occupation (Ocupación)

**Controlador**: `App\Controller\Maintainers\Basic\OccupationController`  
**Entidad**: `App\Entity\Tenant\Occupation`  
**Form**: TBD  
**Template**: `templates/maintainers/basic/occupation/index.html.twig`

**Endpoints**:
- `GET /maintainers/basic/occupation` → Listado
- `GET /maintainers/basic/occupation/create` → Crear
- `GET /maintainers/basic/occupation/{id}/edit` → Editar
- `POST /maintainers/basic/occupation/{id}/delete` → Eliminar
- `GET /maintainers/basic/occupation/export` → Exportar

**Features**: CRUD + Export

---

### 12. Origin (Procedencia)

**Controlador**: `App\Controller\Maintainers\Basic\OriginController`  
**Entidad**: `App\Entity\Tenant\Origin`  
**Form**: TBD  
**Template**: `templates/maintainers/basic/origin/index.html.twig`

**Endpoints**:
- `GET /maintainers/basic/origin` → Listado
- `GET /maintainers/basic/origin/create` → Crear
- `GET /maintainers/basic/origin/{id}/edit` → Editar
- `POST /maintainers/basic/origin/{id}/delete` → Eliminar
- `GET /maintainers/basic/origin/export` → Exportar

**Features**: CRUD + Export

---

### 13. Origin Type (Tipo de Procedencia)

**Controlador**: `App\Controller\Maintainers\Basic\OriginTypeController`  
**Entidad**: `App\Entity\Tenant\OriginType`  
**Form**: TBD  
**Template**: `templates/maintainers/basic/origin_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/basic/origin-type` → Listado
- `GET /maintainers/basic/origin-type/create` → Crear
- `GET /maintainers/basic/origin-type/{id}/edit` → Editar
- `POST /maintainers/basic/origin-type/{id}/delete` → Eliminar
- `GET /maintainers/basic/origin-type/export` → Exportar

**Features**: CRUD + Export  
**Relación**: Asociado con Origin

---

### 14. Religion (Religión)

**Controlador**: `App\Controller\Maintainers\Basic\ReligionController`  
**Entidad**: `App\Entity\Tenant\Religion`  
**Form**: TBD  
**Template**: `templates/maintainers/basic/religion/index.html.twig`

**Endpoints**:
- `GET /maintainers/basic/religion` → Listado
- `GET /maintainers/basic/religion/create` → Crear
- `GET /maintainers/basic/religion/{id}/edit` → Editar
- `POST /maintainers/basic/religion/{id}/delete` → Eliminar
- `GET /maintainers/basic/religion/export` → Exportar

**Columnas típicas**: name, code, isActive  
**Features**: CRUD + Export

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
Dashboard > Mantenedores > Básico > {Mantenedor}
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
| CRUD Completo | ✅ 14/14 |
| Paginación | ✅ 14/14 |
| Exportación | ✅ 14/14 |
| Turbo Frames | ✅ 14/14 |
| Forms validados | ⚠️ 2/14 documentados |
| Tests unitarios | ❌ No implementado |
| Permisos/Roles | ❌ No implementado |

---

## 📝 Notas Técnicas

1. **Multi-tenancy**: Todos los mantenedores usan `TenantEntityManager` para aislar datos por tenant
2. **Soft Delete**: No implementado - Delete es físico
3. **Audit Trail**: No implementado
4. **Ordenamiento**: Por defecto `ORDER BY id DESC`
5. **Búsqueda/Filtros**: No implementado en base
6. **Import masivo**: No implementado

---

## 🚀 Próximos Pasos

1. Documentar Forms faltantes (12 pendientes)
2. Implementar tests unitarios
3. Agregar sistema de permisos por rol
4. Implementar búsqueda/filtros
5. Agregar soft delete
6. Implementar import CSV masivo
