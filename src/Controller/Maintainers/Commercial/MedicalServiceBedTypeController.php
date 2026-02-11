<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\MedicalServiceBedType;
use App\Form\Maintainers\Commercial\MedicalServiceBedTypeType;
use App\Repository\Tenant\MedicalServiceBedTypeRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/medical-service-bed-type')]
class MedicalServiceBedTypeController extends AbstractMantenedorController
{
    public function __construct(
        private MedicalServiceBedTypeRepository $medicalServiceBedTypeRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->medicalServiceBedTypeRepository->createQueryBuilder('msbt')
            ->leftJoin('msbt.medicalService', 'ms')
            ->leftJoin('msbt.bedType', 'bt')
            ->addSelect('ms', 'bt')
            ->orderBy('msbt.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'id' => $this->translator->trans('maintainers.columns.id', [], 'maintainers'),
        'medicalService.name' => 'MedicalService.name',
        'bedType.name' => 'BedType.name',
        'quantity' => 'Quantity',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/medical_service_bed_type/index.html.twig';
    }

    protected function getFormType(): string
    {
        return MedicalServiceBedTypeType::class;
    }

    protected function createNewEntity(): object
    {
        return new MedicalServiceBedType();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_medical_service_bed_type_index';
    }

    #[Route('', name: 'app_maintainers_commercial_medical_service_bed_type_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_medical_service_bed_type_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_medical_service_bed_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MedicalServiceBedType $medicalServiceBedType): Response
    {
        return $this->handleEdit($request, $medicalServiceBedType->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_medical_service_bed_type_delete', methods: ['DELETE'])]
    public function delete(Request $request, MedicalServiceBedType $medicalServiceBedType): Response
    {
        return $this->handleDelete($request, $medicalServiceBedType->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_medical_service_bed_type_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
