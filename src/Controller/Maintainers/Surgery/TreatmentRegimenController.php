<?php

namespace App\Controller\Maintainers\Surgery;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\TreatmentRegimen;
use App\Form\Maintainers\Surgery\TreatmentRegimenType;
use App\Repository\Tenant\TreatmentRegimenRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/surgery/treatment-regimen')]
class TreatmentRegimenController extends AbstractMantenedorController
{
    public function __construct(
        private readonly TreatmentRegimenRepository $repository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_surgery_treatment_regimen_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_surgery_treatment_regimen_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_surgery_treatment_regimen_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_surgery_treatment_regimen_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }

    #[Route('/export', name: 'app_maintainers_surgery_treatment_regimen_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_surgery_treatment_regimen_index';
    }

    protected function getEntityClass(): string
    {
        return TreatmentRegimen::class;
    }

    protected function getFormType(): string
    {
        return TreatmentRegimenType::class;
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/surgery/treatment_regimen/index.html.twig';
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->repository->createQueryBuilder('tr')
            ->leftJoin('tr.branch', 'b')
            ->addSelect('b')
            ->orderBy('tr.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'branch.name' => 'Sucursal',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function createNewEntity(): object
    {
        return new TreatmentRegimen();
    }

    protected function getExportColumns(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Nombre',
            'branch.name' => 'Sucursal',
            'isActive' => 'Estado',
            'createdAt' => 'Fecha Creación'
        ];
    }

    protected function getExportFileName(): string
    {
        return 'regimenes_tratamiento_' . date('Y-m-d_His');
    }

    protected function getMenuIcon(): string
    {
        return 'bx-list-check';
    }
}
