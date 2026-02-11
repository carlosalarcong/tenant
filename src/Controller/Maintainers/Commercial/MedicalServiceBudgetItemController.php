<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\MedicalServiceBudgetItem;
use App\Form\Maintainers\Commercial\MedicalServiceBudgetItemType;
use App\Repository\Tenant\MedicalServiceBudgetItemRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/medical-service-budget-item')]
class MedicalServiceBudgetItemController extends AbstractMantenedorController
{
    public function __construct(
        private MedicalServiceBudgetItemRepository $medicalServiceBudgetItemRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->medicalServiceBudgetItemRepository->createQueryBuilder('msbi')
            ->leftJoin('msbi.medicalService', 'ms')
            ->leftJoin('msbi.surgeryItem', 'si')
            ->addSelect('ms', 'si')
            ->orderBy('msbi.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'id' => $this->translator->trans('maintainers.columns.id', [], 'maintainers'),
        'medicalService.name' => 'MedicalService.name',
        'surgeryItem.name' => 'SurgeryItem.name',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/medical_service_budget_item/index.html.twig';
    }

    protected function getFormType(): string
    {
        return MedicalServiceBudgetItemType::class;
    }

    protected function createNewEntity(): object
    {
        return new MedicalServiceBudgetItem();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_medical_service_budget_item_index';
    }

    #[Route('', name: 'app_maintainers_commercial_medical_service_budget_item_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_medical_service_budget_item_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_medical_service_budget_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MedicalServiceBudgetItem $medicalServiceBudgetItem): Response
    {
        return $this->handleEdit($request, $medicalServiceBudgetItem->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_medical_service_budget_item_delete', methods: ['DELETE'])]
    public function delete(Request $request, MedicalServiceBudgetItem $medicalServiceBudgetItem): Response
    {
        return $this->handleDelete($request, $medicalServiceBudgetItem->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_medical_service_budget_item_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
