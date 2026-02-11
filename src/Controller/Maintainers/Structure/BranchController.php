<?php

namespace App\Controller\Maintainers\Structure;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\Branch;
use App\Form\Maintainers\Organizational\BranchType;
use App\Repository\Tenant\BranchRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Branch Controller
 * 
 * Manages branches (sucursales) of the organization
 */
#[Route('/maintainers/structure/branch')]
class BranchController extends AbstractMantenedorController
{
    public function __construct(
        private BranchRepository $branchRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_branch_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_branch_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_branch_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_branch_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_branch_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'code', 'city', 'region', 'phone', 'email', 'isActive'],
            headers: $this->translateColumns(['name', 'code', 'city', 'region', 'phone', 'email', 'is_active']),
            filename: 'sucursales_' . date('Y-m-d') . '.csv'
        );
    }

    // ========================================================================
    // Implementation of abstract methods
    // ========================================================================

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->branchRepository->createQueryBuilder('b')
            ->orderBy('b.name', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'code' => $this->translator->trans('maintainers.columns.code', [], 'maintainers'),
        'city' => 'City',
        'region' => 'Region',
        'phone' => 'Phone',
        'email' => 'Email',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/structure/branch/index.html.twig';
    }

    protected function getFormType(): string
    {
        return BranchType::class;
    }

    protected function createNewEntity(): object
    {
        return new Branch();
    }

    protected function getEntityName(): string
    {
        return 'Sucursal';
    }

    protected function getItemsPerPage(): int
    {
        return 15;
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_branch_index';
    }

    // ========================================================================
    // Optional hooks
    // ========================================================================

    protected function beforeSave(object $entity, Request $request): void
    {
        // Set default active state for new entities
        if ($entity->getId() === null) {
            $entity->setActive(true);
        }
    }
}
