<?php

namespace App\Controller\Maintainers\Hospital;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\PhysicalExamField;
use App\Form\Maintainers\Hospital\PhysicalExamFieldType;
use App\Repository\Tenant\PhysicalExamFieldRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/hospital/physical-exam-field')]
class PhysicalExamFieldController extends AbstractMantenedorController
{
    public function __construct(
        private PhysicalExamFieldRepository $physicalExamFieldRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_hospital_physical_exam_field_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_hospital_physical_exam_field_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_hospital_physical_exam_field_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_hospital_physical_exam_field_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_hospital_physical_exam_field_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'sortOrder', 'unit', 'grouping1.name', 'isWeight', 'isTemperature', 'isActive'],
            headers: $this->translateColumns(['name', 'order', 'unit', 'grouping_1', 'weight', 'temperature', 'is_active']),
            filename: 'campos_examen_fisico_' . date('Y-m-d') . '.csv'
        );
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->physicalExamFieldRepository->createQueryBuilder('pef')
            ->leftJoin('pef.grouping1', 'g1')
            ->addSelect('g1')
            ->leftJoin('pef.grouping2', 'g2')
            ->addSelect('g2')
            ->orderBy('pef.sortOrder', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'sortOrder' => 'Orden',
        'unit' => 'Unidad',
        'grouping1.name' => 'Agrupacion 1',
        'isWeight' => 'Peso',
        'isTemperature' => 'Temp',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/hospital/physical_exam_field/index.html.twig';
    }

    protected function getFormType(): string
    {
        return PhysicalExamFieldType::class;
    }

    protected function createNewEntity(): object
    {
        return new PhysicalExamField();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_hospital_physical_exam_field_index';
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
