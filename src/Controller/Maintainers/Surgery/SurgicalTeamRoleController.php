<?php

namespace App\Controller\Maintainers\Surgery;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\SurgicalTeamRole;
use App\Form\Maintainers\Surgery\SurgicalTeamRoleType;
use App\Repository\Tenant\SurgicalTeamRoleRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/surgery/surgical-team-role')]
class SurgicalTeamRoleController extends AbstractMantenedorController
{
    public function __construct(
        private readonly SurgicalTeamRoleRepository $repository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_surgery_surgical_team_role_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_surgery_surgical_team_role_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_surgery_surgical_team_role_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_surgery_surgical_team_role_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }

    #[Route('/export', name: 'app_maintainers_surgery_surgical_team_role_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_surgery_surgical_team_role_index';
    }

    protected function getEntityClass(): string
    {
        return SurgicalTeamRole::class;
    }

    protected function getFormType(): string
    {
        return SurgicalTeamRoleType::class;
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/surgery/surgical_team_role/index.html.twig';
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->repository->createQueryBuilder('str')
            ->leftJoin('str.surgeryItem', 'si')
            ->addSelect('si')
            ->orderBy('str.sortOrder', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
        'sortOrder' => 'Orden',
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'surgeryItem.name' => 'Item Cirugía',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function createNewEntity(): object
    {
        return new SurgicalTeamRole();
    }

    protected function getExportColumns(): array
    {
        return [
            'id' => 'ID',
            'sortOrder' => 'Orden',
            'name' => 'Nombre',
            'surgeryItem.name' => 'Item Cirugía',
            'isActive' => 'Estado',
            'createdAt' => 'Fecha Creación'
        ];
    }

    protected function getExportFileName(): string
    {
        return 'roles_equipo_quirurgico_' . date('Y-m-d_His');
    }

    protected function getMenuIcon(): string
    {
        return 'bx-group';
    }
}
