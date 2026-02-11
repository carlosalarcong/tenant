<?php

namespace App\Controller\Maintainers\Logistics;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\ProductConditionType;
use App\Form\Maintainers\Logistics\ProductConditionTypeType;
use App\Repository\Tenant\ProductConditionTypeRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ProductConditionType Controller
 * 
 * Gestiona el mantenedor de Tipos de Condición de Productos
 */
#[Route('/maintainers/logistics/product-condition-type')]
class ProductConditionTypeController extends AbstractMantenedorController
{
    public function __construct(
        private ProductConditionTypeRepository $productConditionTypeRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_logistics_product_condition_type_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_logistics_product_condition_type_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_logistics_product_condition_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_logistics_product_condition_type_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_logistics_product_condition_type_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'isActive'],
            headers: $this->translateColumns(['name', 'is_active']),
            filename: 'tipos_condicion_producto_' . date('Y-m-d') . '.csv'
        );
    }

    // ========================================================================
    // Implementación de métodos abstractos
    // ========================================================================

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->productConditionTypeRepository->createQueryBuilder('pct')
            ->orderBy('pct.id', 'DESC');
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
        return 'maintainers/logistics/product_condition_type/index.html.twig';
    }

    protected function getFormType(): string
    {
        return ProductConditionTypeType::class;
    }

    protected function createNewEntity(): object
    {
        return new ProductConditionType();
    }

    protected function getEntityById(int $id): ?object
    {
        return $this->productConditionTypeRepository->find($id);
    }

    protected function getDeleteSuccessMessage(): string
    {
        return 'Tipo de Condición de Producto eliminado exitosamente';
    }

    protected function getDeleteErrorMessage(): string
    {
        return 'No se pudo eliminar el Tipo de Condición de Producto';
    }

    protected function getEntityNotFoundMessage(): string
    {
        return 'Tipo de Condición de Producto no encontrado';
    }

    protected function getCreateSuccessMessage(): string
    {
        return 'Tipo de Condición de Producto creado exitosamente';
    }

    protected function getUpdateSuccessMessage(): string
    {
        return 'Tipo de Condición de Producto actualizado exitosamente';
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_logistics_product_condition_type_index';
    }

    protected function getEditRoute(): string
    {
        return 'app_maintainers_logistics_product_condition_type_edit';
    }

    protected function getCreateRoute(): string
    {
        return 'app_maintainers_logistics_product_condition_type_create';
    }

}
