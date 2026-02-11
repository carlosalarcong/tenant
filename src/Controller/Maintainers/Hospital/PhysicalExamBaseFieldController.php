<?php

namespace App\Controller\Maintainers\Hospital;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\PhysicalExamBaseField;
use App\Form\Maintainers\Hospital\PhysicalExamBaseFieldType;
use App\Repository\Tenant\PhysicalExamBaseFieldRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/hospital/physical-exam-base-field')]
class PhysicalExamBaseFieldController extends AbstractMantenedorController
{
    public function __construct(
        private PhysicalExamBaseFieldRepository $physicalExamBaseFieldRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_hospital_physical_exam_base_field_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_hospital_physical_exam_base_field_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_hospital_physical_exam_base_field_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_hospital_physical_exam_base_field_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_hospital_physical_exam_base_field_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'sortOrder', 'fieldType', 'isRequired', 'isActive'],
            headers: $this->translateColumns(['name', 'order', 'field_type', 'required', 'is_active']),
            filename: 'campos_base_examen_fisico_' . date('Y-m-d') . '.csv'
        );
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->physicalExamBaseFieldRepository->createQueryBuilder('pebf')
            ->orderBy('pebf.sortOrder', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'sortOrder' => 'Orden',
        'fieldType' => 'Tipo Campo',
        'isRequired' => 'Obligatorio',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/hospital/physical_exam_base_field/index.html.twig';
    }

    protected function getFormType(): string
    {
        return PhysicalExamBaseFieldType::class;
    }

    protected function createNewEntity(): object
    {
        return new PhysicalExamBaseField();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_hospital_physical_exam_base_field_index';
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
