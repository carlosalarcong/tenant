<?php

namespace App\Controller\Maintainers\Logistics;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\ArticleSupplier;
use App\Form\Maintainers\Logistics\ArticleSupplierType;
use App\Repository\Tenant\ArticleSupplierRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * ArticleSupplier Controller
 * 
 * Gestiona el mantenedor de Proveedores por Artículo
 */
#[Route('/maintainers/logistics/article-supplier')]
class ArticleSupplierController extends AbstractMantenedorController
{
    public function __construct(
        private ArticleSupplierRepository $articleSupplierRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_logistics_article_supplier_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_logistics_article_supplier_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_logistics_article_supplier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_logistics_article_supplier_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_logistics_article_supplier_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['article.name', 'supplierName', 'price', 'isActive'],
            headers: $this->translateColumns(['article', 'supplier', 'price', 'is_active']),
            filename: 'proveedores_articulo_' . date('Y-m-d') . '.csv'
        );
    }

    // ========================================================================
    // Implementación de métodos abstractos
    // ========================================================================

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->articleSupplierRepository->createQueryBuilder('as2')
            ->leftJoin('as2.article', 'a')
            ->addSelect('a')
            ->orderBy('as2.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'article.name' => 'Artículo',
        'supplierName' => 'Proveedor',
        'price' => $this->translator->trans('maintainers.columns.price', [], 'maintainers'),
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/logistics/article_supplier/index.html.twig';
    }

    protected function getFormType(): string
    {
        return ArticleSupplierType::class;
    }

    protected function createNewEntity(): object
    {
        return new ArticleSupplier();
    }

    protected function getEntityById(int $id): ?object
    {
        return $this->articleSupplierRepository->find($id);
    }

    protected function getDeleteSuccessMessage(): string
    {
        return 'Proveedor de Artículo eliminado exitosamente';
    }

    protected function getDeleteErrorMessage(): string
    {
        return 'No se pudo eliminar el Proveedor de Artículo';
    }

    protected function getEntityNotFoundMessage(): string
    {
        return 'Proveedor de Artículo no encontrado';
    }

    protected function getCreateSuccessMessage(): string
    {
        return 'Proveedor de Artículo creado exitosamente';
    }

    protected function getUpdateSuccessMessage(): string
    {
        return 'Proveedor de Artículo actualizado exitosamente';
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_logistics_article_supplier_index';
    }

    protected function getEditRoute(): string
    {
        return 'app_maintainers_logistics_article_supplier_edit';
    }

    protected function getCreateRoute(): string
    {
        return 'app_maintainers_logistics_article_supplier_create';
    }

}
