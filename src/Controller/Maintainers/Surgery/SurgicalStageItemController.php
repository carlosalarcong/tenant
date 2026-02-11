<?php

namespace App\Controller\Maintainers\Surgery;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\SurgicalStageItem;
use App\Form\Maintainers\Surgery\SurgicalStageItemType;
use App\Repository\Tenant\SurgicalStageItemRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/surgery/surgical-stage-item')]
class SurgicalStageItemController extends AbstractMantenedorController
{
    public function __construct(
        TenantEntityManager $entityManager,
        private SurgicalStageItemRepository $repository,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($entityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_surgery_surgical_stage_item_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_surgery_surgical_stage_item_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_surgery_surgical_stage_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_surgery_surgical_stage_item_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }

    #[Route('/export', name: 'app_maintainers_surgery_surgical_stage_item_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_surgery_surgical_stage_item_index';
    }

    protected function getEntityClass(): string
    {
        return SurgicalStageItem::class;
    }

    protected function getFormType(): string
    {
        return SurgicalStageItemType::class;
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/surgery/surgical_stage_item/index.html.twig';
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->repository->createQueryBuilder('ssi')
            ->leftJoin('ssi.surgicalStage', 'ss')
            ->leftJoin('ssi.parent', 'p')
            ->addSelect('ss', 'p')
            ->orderBy('ssi.sortOrder', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
        'sortOrder' => 'Orden',
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'surgicalStage.name' => 'Etapa',
        'parent.name' => 'Padre',
        'isMandatory' => 'Obligatorio',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function createNewEntity(): object
    {
        return new SurgicalStageItem();
    }

    protected function getExportColumns(): array
    {
        return [
            'id' => 'ID',
            'sortOrder' => 'Orden',
            'name' => 'Nombre',
            'surgicalStage.name' => 'Etapa',
            'parent.name' => 'Padre',
            'isMandatory' => 'Obligatorio',
            'isActive' => 'Estado',
            'createdAt' => 'Fecha Creación'
        ];
    }

    protected function getExportFileName(): string
    {
        return 'items_etapas_quirurgicas_' . date('Y-m-d_His');
    }

    protected function getMenuIcon(): string
    {
        return 'bx-detail';
    }
}
