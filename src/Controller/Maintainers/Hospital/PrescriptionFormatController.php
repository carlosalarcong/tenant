<?php

namespace App\Controller\Maintainers\Hospital;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\PrescriptionFormat;
use App\Form\Maintainers\Hospital\PrescriptionFormatType;
use App\Repository\Tenant\PrescriptionFormatRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/hospital/prescription-format')]
class PrescriptionFormatController extends AbstractMantenedorController
{
    public function __construct(
        private PrescriptionFormatRepository $prescriptionFormatRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_hospital_prescription_format_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_hospital_prescription_format_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_hospital_prescription_format_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_hospital_prescription_format_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_hospital_prescription_format_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'sortOrder', 'isActive'],
            headers: $this->translateColumns(['name', 'order', 'is_active']),
            filename: 'formatos_receta_' . date('Y-m-d') . '.csv'
        );
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->prescriptionFormatRepository->createQueryBuilder('pf2')
            ->orderBy('pf2.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'sortOrder' => 'Orden',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/hospital/prescription_format/index.html.twig';
    }

    protected function getFormType(): string
    {
        return PrescriptionFormatType::class;
    }

    protected function createNewEntity(): object
    {
        return new PrescriptionFormat();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_hospital_prescription_format_index';
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
