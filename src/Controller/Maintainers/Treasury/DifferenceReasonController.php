<?php

namespace App\Controller\Maintainers\Treasury;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\DifferenceReason;
use App\Form\Maintainers\Treasury\DifferenceReasonType;
use App\Repository\Tenant\DifferenceReasonRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * DifferenceReason Controller
 * 
 * Gestiona el mantenedor de Motivos de Diferencia
 */
#[Route('/maintainers/treasury/difference-reason')]
class DifferenceReasonController extends AbstractMantenedorController
{
    public function __construct(
        private DifferenceReasonRepository $differenceReasonRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_treasury_difference_reason_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_treasury_difference_reason_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_treasury_difference_reason_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_treasury_difference_reason_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_treasury_difference_reason_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'differenceDirection.name', 'isActive'],
            headers: $this->translateColumns(['name', 'direction', 'is_active']),
            filename: 'motivos_diferencia_' . date('Y-m-d') . '.csv'
        );
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->differenceReasonRepository->createQueryBuilder('dr')
            ->leftJoin('dr.differenceDirection', 'dd')
            ->addSelect('dd')
            ->orderBy('dr.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'differenceDirection.name' => 'Sentido',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/treasury/difference_reason/index.html.twig';
    }

    protected function getFormType(): string
    {
        return DifferenceReasonType::class;
    }

    protected function createNewEntity(): object
    {
        return new DifferenceReason();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_treasury_difference_reason_index';
    }

    protected function beforeSave(object $entity, Request $request): void
    {
        $entity->setUpdatedAt(new \DateTime());
    }

    protected function canDelete(object $entity): bool
    {
        return true;
    }

}
