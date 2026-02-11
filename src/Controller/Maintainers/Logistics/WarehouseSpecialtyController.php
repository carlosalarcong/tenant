<?php

namespace App\Controller\Maintainers\Logistics;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\WarehouseSpecialty;
use App\Form\Maintainers\Logistics\WarehouseSpecialtyType;
use App\Repository\Tenant\WarehouseSpecialtyRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * WarehouseSpecialty Controller
 * 
 * Gestiona el mantenedor de Especialidades por Bodega
 */
#[Route('/maintainers/logistics/warehouse-specialty')]
class WarehouseSpecialtyController extends AbstractMantenedorController
{
    public function __construct(
        private WarehouseSpecialtyRepository $warehouseSpecialtyRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_logistics_warehouse_specialty_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_logistics_warehouse_specialty_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_logistics_warehouse_specialty_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_logistics_warehouse_specialty_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_logistics_warehouse_specialty_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['warehouse.name', 'specialty.name', 'isActive'],
            headers: $this->translateColumns(['warehouse', 'specialty', 'is_active']),
            filename: 'especialidades_bodega_' . date('Y-m-d') . '.csv'
        );
    }

    // ========================================================================
    // Implementación de métodos abstractos
    // ========================================================================

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->warehouseSpecialtyRepository->createQueryBuilder('ws')
            ->leftJoin('ws.warehouse', 'w')
            ->leftJoin('ws.specialty', 's')
            ->addSelect('w', 's')
            ->orderBy('ws.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'warehouse.name' => 'Bodega',
        'specialty.name' => 'Especialidad',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/logistics/warehouse_specialty/index.html.twig';
    }

    protected function getFormType(): string
    {
        return WarehouseSpecialtyType::class;
    }

    protected function createNewEntity(): object
    {
        return new WarehouseSpecialty();
    }

    protected function getEntityById(int $id): ?object
    {
        return $this->warehouseSpecialtyRepository->find($id);
    }

    protected function getDeleteSuccessMessage(): string
    {
        return 'Especialidad por Bodega eliminada exitosamente';
    }

    protected function getDeleteErrorMessage(): string
    {
        return 'No se pudo eliminar la Especialidad por Bodega';
    }

    protected function getEntityNotFoundMessage(): string
    {
        return 'Especialidad por Bodega no encontrada';
    }

    protected function getCreateSuccessMessage(): string
    {
        return 'Especialidad por Bodega creada exitosamente';
    }

    protected function getUpdateSuccessMessage(): string
    {
        return 'Especialidad por Bodega actualizada exitosamente';
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_logistics_warehouse_specialty_index';
    }

    protected function getEditRoute(): string
    {
        return 'app_maintainers_logistics_warehouse_specialty_edit';
    }

    protected function getCreateRoute(): string
    {
        return 'app_maintainers_logistics_warehouse_specialty_create';
    }

}
