<?php

namespace App\Controller\Maintainers\Admission;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\CancellationReason;
use App\Form\Maintainers\Admission\CancellationReasonType;
use App\Repository\Tenant\CancellationReasonRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CancellationReason Controller
 * 
 * Gestiona el mantenedor de Motivos de Anulación
 */
#[Route('/maintainers/admission/cancellation-reason')]
class CancellationReasonController extends AbstractMantenedorController
{
    public function __construct(
        private CancellationReasonRepository $cancellationReasonRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_admission_cancellation_reason_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_admission_cancellation_reason_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_admission_cancellation_reason_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_admission_cancellation_reason_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_admission_cancellation_reason_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'isActive'],
            headers: $this->translateColumns(['name', 'is_active']),
            filename: 'motivos_anulacion_' . date('Y-m-d') . '.csv'
        );
    }

    // ========================================================================
    // Implementación de métodos abstractos
    // ========================================================================

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->cancellationReasonRepository->createQueryBuilder('cr')
            ->orderBy('cr.id', 'DESC');
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
        return 'maintainers/admission/cancellation_reason/index.html.twig';
    }

    protected function getFormType(): string
    {
        return CancellationReasonType::class;
    }

    protected function createNewEntity(): object
    {
        return new CancellationReason();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_admission_cancellation_reason_index';
    }

    protected function getPageTitle(?string $action = null): string
    {
        return match($action) {
            'create' => 'Crear Motivo Anulacion',
            'edit' => 'Editar Motivo Anulacion',
            default => 'Motivos de Anulacion'
        };
    }

    // ========================================================================
    // Hooks personalizados (opcional)
    // ========================================================================

    protected function beforeSave(object $entity, Request $request): void
    {
        if ($entity instanceof CancellationReason) {
            $entity->setUpdatedAt(new \DateTime());
        }
    }

    protected function canDelete(object $entity): bool
    {
        return true;
    }
}
