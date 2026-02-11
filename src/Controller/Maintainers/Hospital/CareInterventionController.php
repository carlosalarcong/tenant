<?php

namespace App\Controller\Maintainers\Hospital;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\CareIntervention;
use App\Form\Maintainers\Hospital\CareInterventionType;
use App\Repository\Tenant\CareInterventionRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/hospital/care-intervention')]
class CareInterventionController extends AbstractMantenedorController
{
    public function __construct(
        private readonly CareInterventionRepository $repository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_hospital_care_intervention_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_hospital_care_intervention_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_hospital_care_intervention_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_hospital_care_intervention_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }

    #[Route('/export', name: 'app_maintainers_hospital_care_intervention_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_hospital_care_intervention_index';
    }

    protected function getEntityClass(): string
    {
        return CareIntervention::class;
    }

    protected function getFormType(): string
    {
        return CareInterventionType::class;
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/hospital/care_intervention/index.html.twig';
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->repository->createQueryBuilder('ci')
            ->leftJoin('ci.careCategory', 'cc')
            ->addSelect('cc')
            ->orderBy('ci.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'description' => $this->translator->trans('maintainers.columns.description', [], 'maintainers'),
        'careCategory.name' => 'Categoria',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function createNewEntity(): object
    {
        return new CareIntervention();
    }

    protected function getExportColumns(): array
    {
        return ['description', 'careCategory.name', 'isActive'];
    }

    protected function getExportHeaders(): array
    {
        return ['Descripcion', 'Categoria', 'Activo'];
    }

    protected function getExportFileName(): string
    {
        return 'cuidados_clinicos_' . date('Y-m-d') . '.csv';
    }

    protected function beforeSave(object $entity, Request $request): void
    {
        $entity->setUpdatedAt(new \DateTime());
    }

    protected function canDelete(object $entity): bool
    {
        // Add custom validation logic if needed
        return true;
    }

}
