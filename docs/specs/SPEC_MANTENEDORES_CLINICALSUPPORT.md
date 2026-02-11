# SPEC: Mantenedores Clinical Support

**Categoría**: Clinical Support  
**Total Mantenedores**: 1  
**Estado**: ✅ Implementado  
**Última actualización**: 2026-02-09

---

## 📐 Arquitectura

El mantenedor de apoyo clínico extiende `AbstractMantenedorController` que implementa:
- ✅ CRUD completo con Template Method Pattern
- ✅ Paginación automática vía QueryBuilder
- ✅ Turbo Frames para modales
- ✅ Multi-tenancy con TenantEntityManager
- ✅ Exportación CSV

**Ruta base**: `/maintainers/clinical-support/{mantenedor}`

---

## 🗂️ Mantenedor Implementado

### Exam Report (Informes de Examen)

**Controlador**: `App\Controller\Maintainers\ClinicalSupport\ExamReportController`  
**Entidad**: `App\Entity\Tenant\ExamReport`  
**Form**: `App\Form\Maintainers\ClinicalSupport\ExamReportType`  
**Template**: `templates/maintainers/clinical_support/exam_report/index.html.twig`

**Endpoints**:
- `GET /maintainers/clinical-support/exam-report` → `app_maintainers_clinical_support_exam_report_index`
- `GET /maintainers/clinical-support/exam-report/create` → `app_maintainers_clinical_support_exam_report_create`
- `GET /maintainers/clinical-support/exam-report/{id}/edit` → `app_maintainers_clinical_support_exam_report_edit`
- `POST /maintainers/clinical-support/exam-report/{id}/delete` → `app_maintainers_clinical_support_exam_report_delete`
- `GET /maintainers/clinical-support/exam-report/export` → `app_maintainers_clinical_support_exam_report_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV

**Export**:
- Columnas: `name`, `isActive`
- Headers: Traducciones de `name`, `is_active`
- Filename: `informes_examen_YYYY-MM-DD.csv`

---

## 🔄 Patrón de Implementación

```php
<?php

namespace App\Controller\Maintainers\ClinicalSupport;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\ExamReport;
use App\Form\Maintainers\ClinicalSupport\ExamReportType;
use App\Repository\Tenant\ExamReportRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/clinical-support/exam-report')]
class ExamReportController extends AbstractMantenedorController
{
    public function __construct(
        private ExamReportRepository $examReportRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->examReportRepository->createQueryBuilder('er')
            ->orderBy('er.id', 'DESC');
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
        return 'maintainers/clinical_support/exam_report/index.html.twig';
    }

    protected function getEntityClass(): string
    {
        return ExamReport::class;
    }

    protected function getFormType(): string
    {
        return ExamReportType::class;
    }
}
```

---

## 📊 Resumen

| Mantenedor | Entidad | Columnas | Relaciones |
|------------|---------|----------|------------|
| Exam Report | ExamReport | name, isActive | - |

**Características**:
- ✅ Paginación automática
- ✅ Exportación CSV
- ✅ Turbo Frames
- ✅ Multi-tenancy
- ✅ Traducciones i18n
- ✅ Validación de formularios
- ✅ Soft deletes (isActive)

**Uso**:
Los Informes de Examen se utilizan para definir plantillas o tipos de informes que se generan a partir de exámenes médicos realizados a los pacientes. Son parte del módulo de apoyo clínico y se integran con el sistema de laboratorio e imagenología.

**Relación con otros módulos**:
- Se utiliza en el módulo de Laboratorio
- Se integra con el módulo de Imagenología
- Referenciado desde el módulo de Atención Clínica
