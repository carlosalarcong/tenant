<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\MedicalService;
use App\Form\Maintainers\Commercial\MedicalServiceType;
use App\Repository\Tenant\MedicalServiceRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/medical-service')]
class MedicalServiceController extends AbstractMantenedorController
{
    public function __construct(
        private MedicalServiceRepository $medicalServiceRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->medicalServiceRepository->createQueryBuilder('ms')
            ->leftJoin('ms.figure', 'f')
            ->leftJoin('ms.serviceType', 'st')
            ->addSelect('f', 'st')
            ->orderBy('ms.code', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
        'id' => $this->translator->trans('maintainers.columns.id', [], 'maintainers'),
        'code' => $this->translator->trans('maintainers.columns.code', [], 'maintainers'),
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'fonasaCode' => 'FonasaCode',
        'imedCode' => 'ImedCode',
        'serviceType.name' => 'ServiceType.name',
        'isProcedure' => 'IsProcedure',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/medical_service/index.html.twig';
    }

    protected function getFormType(): string
    {
        return MedicalServiceType::class;
    }

    protected function createNewEntity(): object
    {
        return new MedicalService();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_medical_service_index';
    }

    #[Route('', name: 'app_maintainers_commercial_medical_service_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_medical_service_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_medical_service_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MedicalService $medicalService): Response
    {
        return $this->handleEdit($request, $medicalService->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_medical_service_delete', methods: ['DELETE'])]
    public function delete(Request $request, MedicalService $medicalService): Response
    {
        return $this->handleDelete($request, $medicalService->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_medical_service_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
