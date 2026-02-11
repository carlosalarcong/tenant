<?php

namespace App\Controller\Maintainers\Logistics;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\InventoryAdjustmentReason;
use App\Form\Maintainers\Logistics\InventoryAdjustmentReasonType;
use App\Repository\Tenant\InventoryAdjustmentReasonRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * InventoryAdjustmentReason Controller
 * 
 * Gestiona el mantenedor de Motivos de Ajuste de Inventario
 */
#[Route('/maintainers/logistics/inventory-adjustment-reason')]
class InventoryAdjustmentReasonController extends AbstractMantenedorController
{
    public function __construct(
        private InventoryAdjustmentReasonRepository $inventoryAdjustmentReasonRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_logistics_inventory_adjustment_reason_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_logistics_inventory_adjustment_reason_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_logistics_inventory_adjustment_reason_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_logistics_inventory_adjustment_reason_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_logistics_inventory_adjustment_reason_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'isActive'],
            headers: $this->translateColumns(['name', 'is_active']),
            filename: 'motivos_ajuste_inventario_' . date('Y-m-d') . '.csv'
        );
    }

    // ========================================================================
    // Implementación de métodos abstractos
    // ========================================================================

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->inventoryAdjustmentReasonRepository->createQueryBuilder('iar')
            ->orderBy('iar.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/logistics/inventory_adjustment_reason/index.html.twig';
    }

    protected function getFormType(): string
    {
        return InventoryAdjustmentReasonType::class;
    }

    protected function createNewEntity(): object
    {
        return new InventoryAdjustmentReason();
    }

    protected function getEntityById(int $id): ?object
    {
        return $this->inventoryAdjustmentReasonRepository->find($id);
    }

    protected function getDeleteSuccessMessage(): string
    {
        return 'Motivo de Ajuste de Inventario eliminado exitosamente';
    }

    protected function getDeleteErrorMessage(): string
    {
        return 'No se pudo eliminar el Motivo de Ajuste de Inventario';
    }

    protected function getEntityNotFoundMessage(): string
    {
        return 'Motivo de Ajuste de Inventario no encontrado';
    }

    protected function getCreateSuccessMessage(): string
    {
        return 'Motivo de Ajuste de Inventario creado exitosamente';
    }

    protected function getUpdateSuccessMessage(): string
    {
        return 'Motivo de Ajuste de Inventario actualizado exitosamente';
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_logistics_inventory_adjustment_reason_index';
    }

    protected function getEditRoute(): string
    {
        return 'app_maintainers_logistics_inventory_adjustment_reason_edit';
    }

    protected function getCreateRoute(): string
    {
        return 'app_maintainers_logistics_inventory_adjustment_reason_create';
    }

}
