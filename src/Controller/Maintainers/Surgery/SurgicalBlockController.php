<?php

namespace App\Controller\Maintainers\Surgery;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\SurgicalBlock;
use App\Form\Maintainers\Surgery\SurgicalBlockType;
use App\Repository\Tenant\SurgicalBlockRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/surgery/surgical-block')]
class SurgicalBlockController extends AbstractMantenedorController
{
    public function __construct(
        private readonly SurgicalBlockRepository $repository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_surgery_surgical_block_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_surgery_surgical_block_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_surgery_surgical_block_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_surgery_surgical_block_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }

    #[Route('/export', name: 'app_maintainers_surgery_surgical_block_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_surgery_surgical_block_index';
    }

    protected function getEntityClass(): string
    {
        return SurgicalBlock::class;
    }

    protected function getFormType(): string
    {
        return SurgicalBlockType::class;
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/surgery/surgical_block/index.html.twig';
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->repository->createQueryBuilder('sb')
            ->leftJoin('sb.medicalService', 'ms')
            ->addSelect('ms')
            ->orderBy('sb.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'medicalService.name' => 'Servicio Médico',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function createNewEntity(): object
    {
        return new SurgicalBlock();
    }

    protected function getExportColumns(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Nombre',
            'medicalService.name' => 'Servicio Médico',
            'isActive' => 'Estado',
            'createdAt' => 'Fecha Creación'
        ];
    }

    protected function getExportFileName(): string
    {
        return 'pabellones_' . date('Y-m-d_His');
    }

    protected function getMenuIcon(): string
    {
        return 'bx-door-open';
    }
}
