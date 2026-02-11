<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\BranchPayer;
use App\Form\Maintainers\Commercial\BranchPayerType;
use App\Repository\Tenant\BranchPayerRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/branch-payer')]
class BranchPayerController extends AbstractMantenedorController
{
    public function __construct(
        private BranchPayerRepository $branchPayerRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->branchPayerRepository->createQueryBuilder('bp')
            ->leftJoin('bp.branch', 'b')
            ->leftJoin('bp.payer', 'p')
            ->addSelect('b', 'p')
            ->orderBy('bp.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'id' => $this->translator->trans('maintainers.columns.id', [], 'maintainers'),
        'branch.name' => 'Sucursal',
        'payer.name' => 'Payer.name',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/branch_payer/index.html.twig';
    }

    protected function getFormType(): string
    {
        return BranchPayerType::class;
    }

    protected function createNewEntity(): object
    {
        return new BranchPayer();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_branch_payer_index';
    }

    #[Route('', name: 'app_maintainers_commercial_branch_payer_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_branch_payer_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_branch_payer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BranchPayer $branchPayer): Response
    {
        return $this->handleEdit($request, $branchPayer->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_branch_payer_delete', methods: ['DELETE'])]
    public function delete(Request $request, BranchPayer $branchPayer): Response
    {
        return $this->handleDelete($request, $branchPayer->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_branch_payer_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
