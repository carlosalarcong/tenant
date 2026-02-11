<?php

namespace App\Controller\Maintainers\Logistics;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\ArticleWarehouse;
use App\Form\Maintainers\Logistics\ArticleWarehouseType;
use App\Repository\Tenant\ArticleWarehouseRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ArticleWarehouse Controller
 * 
 * Gestiona el mantenedor de Artículos por Bodega
 */
#[Route('/maintainers/logistics/article-warehouse')]
class ArticleWarehouseController extends AbstractMantenedorController
{
    public function __construct(
        private ArticleWarehouseRepository $articleWarehouseRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_logistics_article_warehouse_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_logistics_article_warehouse_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_logistics_article_warehouse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_logistics_article_warehouse_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_logistics_article_warehouse_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['article.name', 'warehouse.name', 'minStock', 'criticalStock', 'optimalStock', 'isCritical', 'isActive'],
            headers: $this->translateColumns(['article', 'warehouse', 'min_stock', 'critical_stock', 'optimal_stock', 'is_critical', 'is_active']),
            filename: 'articulos_bodega_' . date('Y-m-d') . '.csv'
        );
    }

    // ========================================================================
    // Implementación de métodos abstractos
    // ========================================================================

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->articleWarehouseRepository->createQueryBuilder('aw')
            ->leftJoin('aw.article', 'a')
            ->leftJoin('aw.warehouse', 'w')
            ->addSelect('a', 'w')
            ->orderBy('aw.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'article.name' => 'Artículo',
        'warehouse.name' => 'Bodega',
        'minStock' => 'Stock Mín.',
        'criticalStock' => 'Stock Crít.',
        'optimalStock' => 'Stock Ópt.',
        'isCritical' => $this->translator->trans('maintainers.columns.critical', [], 'maintainers'),
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/logistics/article_warehouse/index.html.twig';
    }

    protected function getFormType(): string
    {
        return ArticleWarehouseType::class;
    }

    protected function createNewEntity(): object
    {
        return new ArticleWarehouse();
    }

    protected function getEntityById(int $id): ?object
    {
        return $this->articleWarehouseRepository->find($id);
    }

    protected function getDeleteSuccessMessage(): string
    {
        return 'Artículo por Bodega eliminado exitosamente';
    }

    protected function getDeleteErrorMessage(): string
    {
        return 'No se pudo eliminar el Artículo por Bodega';
    }

    protected function getEntityNotFoundMessage(): string
    {
        return 'Artículo por Bodega no encontrado';
    }

    protected function getCreateSuccessMessage(): string
    {
        return 'Artículo por Bodega creado exitosamente';
    }

    protected function getUpdateSuccessMessage(): string
    {
        return 'Artículo por Bodega actualizado exitosamente';
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_logistics_article_warehouse_index';
    }

    protected function getEditRoute(): string
    {
        return 'app_maintainers_logistics_article_warehouse_edit';
    }

    protected function getCreateRoute(): string
    {
        return 'app_maintainers_logistics_article_warehouse_create';
    }

}
