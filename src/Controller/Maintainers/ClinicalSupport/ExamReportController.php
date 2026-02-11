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

/**
 * ExamReport Controller
 * 
 * Gestiona el mantenedor de Informes de Examen
 */
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

    #[Route('', name: 'app_maintainers_clinical_support_exam_report_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_clinical_support_exam_report_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_clinical_support_exam_report_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_clinical_support_exam_report_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_clinical_support_exam_report_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'isActive'],
            headers: $this->translateColumns(['name', 'is_active']),
            filename: 'informes_examen_' . date('Y-m-d') . '.csv'
        );
    }

    // ========================================================================
    // Implementación de métodos abstractos
    // ========================================================================

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

    protected function getFormType(): string
    {
        return ExamReportType::class;
    }

    protected function createNewEntity(): object
    {
        return new ExamReport();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_clinical_support_exam_report_index';
    }

    protected function getPageTitle(?string $action = null): string
    {
        return match($action) {
            'create' => 'Crear Informe de Examen',
            'edit' => 'Editar Informe de Examen',
            default => 'Informes de Examen'
        };
    }

    // ========================================================================
    // Hooks personalizados (opcional)
    // ========================================================================

    protected function beforeSave(object $entity, Request $request): void
    {
        if ($entity instanceof ExamReport) {
            $entity->setUpdatedAt(new \DateTime());
        }
    }

    protected function canDelete(object $entity): bool
    {
        return true;
    }
}
