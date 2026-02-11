<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\SpecialtyBranch;
use App\Form\Maintainers\Commercial\SpecialtyBranchType;
use App\Repository\Tenant\SpecialtyBranchRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/specialty-branch')]
class SpecialtyBranchController extends AbstractMantenedorController
{
    public function __construct(
        private SpecialtyBranchRepository $specialtyBranchRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->specialtyBranchRepository->createQueryBuilder('sb')
            ->leftJoin('sb.specialty', 's')
            ->leftJoin('sb.branch', 'b')
            ->addSelect('s', 'b')
            ->orderBy('sb.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'id' => $this->translator->trans('maintainers.columns.id', [], 'maintainers'),
        'specialty.name' => 'Specialty.name',
        'branch.name' => 'Sucursal',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/specialty_branch/index.html.twig';
    }

    protected function getFormType(): string
    {
        return SpecialtyBranchType::class;
    }

    protected function createNewEntity(): object
    {
        return new SpecialtyBranch();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_specialty_branch_index';
    }

    #[Route('', name: 'app_maintainers_commercial_specialty_branch_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_specialty_branch_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_specialty_branch_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SpecialtyBranch $specialtyBranch): Response
    {
        return $this->handleEdit($request, $specialtyBranch->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_specialty_branch_delete', methods: ['DELETE'])]
    public function delete(Request $request, SpecialtyBranch $specialtyBranch): Response
    {
        return $this->handleDelete($request, $specialtyBranch->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_specialty_branch_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
