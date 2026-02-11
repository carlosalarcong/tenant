<?php

namespace App\Controller\Maintainers\Surgery;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\SurgicalStage;
use App\Form\Maintainers\Surgery\SurgicalStageType;
use App\Repository\Tenant\SurgicalStageRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/surgery/surgical-stage')]
class SurgicalStageController extends AbstractMantenedorController
{
    public function __construct(
        TenantEntityManager $entityManager,
        private SurgicalStageRepository $repository,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($entityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_surgery_surgical_stage_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_surgery_surgical_stage_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_surgery_surgical_stage_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_surgery_surgical_stage_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }

    #[Route('/export', name: 'app_maintainers_surgery_surgical_stage_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_surgery_surgical_stage_index';
    }

    protected function getEntityClass(): string
    {
        return SurgicalStage::class;
    }

    protected function getFormType(): string
    {
        return SurgicalStageType::class;
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/surgery/surgical_stage/index.html.twig';
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->repository->createQueryBuilder('ss')
            ->leftJoin('ss.branch', 'b')
            ->addSelect('b')
            ->orderBy('ss.sortOrder', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
        'sortOrder' => 'Orden',
        'abbreviation' => 'Abreviación',
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'isMandatory' => 'Obligatorio',
        'requiresLogin' => 'Req. Login',
        'isSequential' => 'Secuencial',
        'branch.name' => 'Sucursal',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function createNewEntity(): object
    {
        return new SurgicalStage();
    }

    protected function getExportColumns(): array
    {
        return [
            'id' => 'ID',
            'sortOrder' => 'Orden',
            'abbreviation' => 'Abreviación',
            'name' => 'Nombre',
            'isMandatory' => 'Obligatorio',
            'requiresLogin' => 'Req. Login',
            'isSequential' => 'Secuencial',
            'branch.name' => 'Sucursal',
            'isActive' => 'Estado',
            'createdAt' => 'Fecha Creación'
        ];
    }

    protected function getExportFileName(): string
    {
        return 'etapas_quirurgicas_' . date('Y-m-d_His');
    }

    protected function getMenuIcon(): string
    {
        return 'bx-list-ol';
    }
}
