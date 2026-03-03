<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\SurgeryFeeItem;
use App\Form\Maintainers\Commercial\SurgeryFeeItemType;
use App\Repository\Tenant\SurgeryFeeItemRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/surgery-fee-item')]
class SurgeryFeeItemController extends AbstractMantenedorController
{
    public function __construct(
        private SurgeryFeeItemRepository $surgeryFeeItemRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->surgeryFeeItemRepository->createQueryBuilder('sfi')
            ->leftJoin('sfi.itemType', 'it')
            ->addSelect('it')
            ->leftJoin('sfi.branch', 'b')
            ->addSelect('b')
            ->orderBy('sfi.branch', 'ASC')
            ->addOrderBy('sfi.displayOrder', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
            'id' => 'ID',
            'name' => 'Nombre',
            'budgetName' => 'Nombre Presupuesto',
            'itemType.name' => 'Tipo',
            'branch.name' => 'Sucursal',
            'displayOrder' => 'Orden',
            'isActive' => 'Activo',
        ];
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/surgery_fee_item/index.html.twig';
    }

    protected function getFormType(): string
    {
        return SurgeryFeeItemType::class;
    }

    protected function createNewEntity(): object
    {
        return new SurgeryFeeItem();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_surgery_fee_item_index';
    }

    #[Route('', name: 'app_maintainers_commercial_surgery_fee_item_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_surgery_fee_item_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_surgery_fee_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SurgeryFeeItem $surgeryFeeItem): Response
    {
        return $this->handleEdit($request, $surgeryFeeItem->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_surgery_fee_item_delete', methods: ['DELETE'])]
    public function delete(Request $request, SurgeryFeeItem $surgeryFeeItem): Response
    {
        return $this->handleDelete($request, $surgeryFeeItem->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_surgery_fee_item_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
