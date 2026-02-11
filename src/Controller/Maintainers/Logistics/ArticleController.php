<?php

namespace App\Controller\Maintainers\Logistics;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\Article;
use App\Form\Maintainers\Logistics\ArticleFormType;
use App\Repository\Tenant\ArticleRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Article Controller
 * 
 * Gestiona el mantenedor de Artículos
 */
#[Route('/maintainers/logistics/article')]
class ArticleController extends AbstractMantenedorController
{
    public function __construct(
        private ArticleRepository $articleRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_logistics_article_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_logistics_article_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_logistics_article_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_logistics_article_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_logistics_article_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['code', 'name', 'shortName', 'genericName', 'articleType.name', 'subCompany.name', 
                     'isConsignment', 'isControlled', 'hasExpirationDate', 'isCritical', 'isGeneric', 
                     'isResterilizable', 'isForSale', 'isBillable', 'isFirstAidDeduction', 
                     'minStock', 'criticalStock', 'optimalStock', 'maxStock', 'cenabastCode', 'isActive'],
            headers: $this->translateColumns(['code', 'name', 'short_name', 'generic_name', 'type', 'sub_company', 'consignment', 'controlled', 'expiration_date', 'critical', 'generic', 'resterilizable', 'sale', 'billable', 'first_aid_deduction', 'min_stock', 'critical_stock', 'optimal_stock', 'max_stock', 'cenabast_code', 'is_active']),
            filename: 'articulos_' . date('Y-m-d') . '.csv'
        );
    }

    // ========================================================================
    // Implementación de métodos abstractos
    // ========================================================================

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->articleRepository->createQueryBuilder('a')
            ->leftJoin('a.articleType', 'at')
            ->leftJoin('a.subCompany', 'sc')
            ->addSelect('at', 'sc')
            ->orderBy('a.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'code' => $this->translator->trans('maintainers.columns.code', [], 'maintainers'),
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'articleType.name' => $this->translator->trans('maintainers.columns.type', [], 'maintainers'),
        'isControlled' => $this->translator->trans('maintainers.columns.controlled', [], 'maintainers'),
        'isCritical' => $this->translator->trans('maintainers.columns.critical', [], 'maintainers'),
        'isForSale' => $this->translator->trans('maintainers.columns.sale', [], 'maintainers'),
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/logistics/article/index.html.twig';
    }

    protected function getFormType(): string
    {
        return ArticleFormType::class;
    }

    protected function createNewEntity(): object
    {
        return new Article();
    }

    protected function getEntityById(int $id): ?object
    {
        return $this->articleRepository->find($id);
    }

    protected function getDeleteSuccessMessage(): string
    {
        return 'Artículo eliminado exitosamente';
    }

    protected function getDeleteErrorMessage(): string
    {
        return 'No se pudo eliminar el Artículo';
    }

    protected function getEntityNotFoundMessage(): string
    {
        return 'Artículo no encontrado';
    }

    protected function getCreateSuccessMessage(): string
    {
        return 'Artículo creado exitosamente';
    }

    protected function getUpdateSuccessMessage(): string
    {
        return 'Artículo actualizado exitosamente';
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_logistics_article_index';
    }

    protected function getEditRoute(): string
    {
        return 'app_maintainers_logistics_article_edit';
    }

    protected function getCreateRoute(): string
    {
        return 'app_maintainers_logistics_article_create';
    }

}
