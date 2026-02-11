# SPEC: Mantenedores Admission

**Categoría**: Admission  
**Total Mantenedores**: 3  
**Estado**: ✅ Implementado  
**Última actualización**: 2026-02-09

---

## 📐 Arquitectura

Todos los mantenedores de admisión extienden `AbstractMantenedorController` que implementa:
- ✅ CRUD completo con Template Method Pattern
- ✅ Paginación automática vía QueryBuilder
- ✅ Turbo Frames para modales
- ✅ Multi-tenancy con TenantEntityManager
- ✅ Exportación CSV

**Ruta base**: `/maintainers/admission/{mantenedor}`

---

## 🗂️ Mantenedores Implementados

### 1. Cancellation Reason (Motivos de Anulación)

**Controlador**: `App\Controller\Maintainers\Admission\CancellationReasonController`  
**Entidad**: `App\Entity\Tenant\CancellationReason`  
**Form**: `App\Form\Maintainers\Admission\CancellationReasonType`  
**Template**: `templates/maintainers/admission/cancellation_reason/index.html.twig`

**Endpoints**:
- `GET /maintainers/admission/cancellation-reason` → `app_maintainers_admission_cancellation_reason_index`
- `GET /maintainers/admission/cancellation-reason/create` → `app_maintainers_admission_cancellation_reason_create`
- `GET /maintainers/admission/cancellation-reason/{id}/edit` → `app_maintainers_admission_cancellation_reason_edit`
- `POST /maintainers/admission/cancellation-reason/{id}/delete` → `app_maintainers_admission_cancellation_reason_delete`
- `GET /maintainers/admission/cancellation-reason/export` → `app_maintainers_admission_cancellation_reason_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV

**Export**:
- Columnas: `name`, `isActive`
- Headers: Traducciones de `name`, `is_active`
- Filename: `motivos_anulacion_YYYY-MM-DD.csv`

---

### 2. Company Agreement (Convenios de Empresa)

**Controlador**: `App\Controller\Maintainers\Admission\CompanyAgreementController`  
**Entidad**: `App\Entity\Tenant\CompanyAgreement`  
**Form**: `App\Form\Maintainers\Admission\CompanyAgreementType`  
**Template**: `templates/maintainers/admission/company_agreement/index.html.twig`

**Endpoints**:
- `GET /maintainers/admission/company-agreement` → `app_maintainers_admission_company_agreement_index`
- `GET /maintainers/admission/company-agreement/create` → `app_maintainers_admission_company_agreement_create`
- `GET /maintainers/admission/company-agreement/{id}/edit` → `app_maintainers_admission_company_agreement_edit`
- `POST /maintainers/admission/company-agreement/{id}/delete` → `app_maintainers_admission_company_agreement_delete`
- `GET /maintainers/admission/company-agreement/export` → `app_maintainers_admission_company_agreement_export`

**Columnas**: name, description, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV

**Export**:
- Columnas: `name`, `description`, `isActive`
- Headers: Traducciones de `name`, `description`, `is_active`
- Filename: `convenios_empresa_YYYY-MM-DD.csv`

---

### 3. Emergency Consultation Type (Tipos de Consulta de Urgencia)

**Controlador**: `App\Controller\Maintainers\Admission\EmergencyConsultationTypeController`  
**Entidad**: `App\Entity\Tenant\EmergencyConsultationType`  
**Form**: `App\Form\Maintainers\Admission\EmergencyConsultationTypeType`  
**Template**: `templates/maintainers/admission/emergency_consultation_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/admission/emergency-consultation-type` → `app_maintainers_admission_emergency_consultation_type_index`
- `GET /maintainers/admission/emergency-consultation-type/create` → `app_maintainers_admission_emergency_consultation_type_create`
- `GET /maintainers/admission/emergency-consultation-type/{id}/edit` → `app_maintainers_admission_emergency_consultation_type_edit`
- `POST /maintainers/admission/emergency-consultation-type/{id}/delete` → `app_maintainers_admission_emergency_consultation_type_delete`
- `GET /maintainers/admission/emergency-consultation-type/export` → `app_maintainers_admission_emergency_consultation_type_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV

**Export**:
- Columnas: `name`, `isActive`
- Headers: Traducciones de `name`, `is_active`
- Filename: `tipos_consulta_urgencia_YYYY-MM-DD.csv`

---

## 🔄 Patrón de Implementación

Todos los mantenedores de esta categoría siguen el mismo patrón:

```php
class {Mantenedor}Controller extends AbstractMantenedorController
{
    public function __construct(
        private {Mantenedor}Repository $repository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->repository->createQueryBuilder('alias')
            ->orderBy('alias.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
            'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
            'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
        ];
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/admission/{mantenedor}/index.html.twig';
    }

    protected function getEntityClass(): string
    {
        return {Entity}::class;
    }

    protected function getFormType(): string
    {
        return {Form}Type::class;
    }
}
```

---

## 📊 Resumen

| Mantenedor | Entidad | Columnas | Relaciones |
|------------|---------|----------|------------|
| Cancellation Reason | CancellationReason | name, isActive | - |
| Company Agreement | CompanyAgreement | name, description, isActive | - |
| Emergency Consultation Type | EmergencyConsultationType | name, isActive | - |

**Características comunes**:
- ✅ Paginación automática
- ✅ Exportación CSV
- ✅ Turbo Frames
- ✅ Multi-tenancy
- ✅ Traducciones i18n
- ✅ Validación de formularios
- ✅ Soft deletes (isActive)
