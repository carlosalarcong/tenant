<?php

namespace App\Controller\Maintainers\Budget;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\BudgetFooterByFunder;
use App\Form\Maintainers\Budget\BudgetFooterByFunderType;
use App\Repository\Tenant\BudgetFooterByFunderRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/budget/budget-footer-by-funder')]
class BudgetFooterByFunderController extends AbstractMantenedorController
{
    public function __construct(
        private BudgetFooterByFunderRepository $budgetFooterByFunderRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_budget_budget_footer_by_funder_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_budget_budget_footer_by_funder_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_budget_budget_footer_by_funder_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_budget_budget_footer_by_funder_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_budget_budget_footer_by_funder_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'budgetFooter.name', 'isActive'],
            headers: $this->translateColumns(['name', 'budget_footer', 'is_active']),
            filename: 'pies_presupuesto_financiador_' . date('Y-m-d') . '.csv'
        );
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->budgetFooterByFunderRepository->createQueryBuilder('bfbf')
            ->leftJoin('bfbf.budgetFooter', 'bf')
            ->addSelect('bf')
            ->orderBy('bfbf.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
            'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
            'budgetFooter.name' => $this->translator->trans('maintainers.columns.budget_footer', [], 'maintainers'),
            'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
        ];
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/budget/budget_footer_by_funder/index.html.twig';
    }

    protected function getFormType(): string
    {
        return BudgetFooterByFunderType::class;
    }

    protected function createNewEntity(): object
    {
        return new BudgetFooterByFunder();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_budget_budget_footer_by_funder_index';
    }

    protected function getPageTitle(?string $action = null): string
    {
        return match($action) {
            'create' => 'Crear Pie Presupuesto por Financiador',
            'edit' => 'Editar Pie Presupuesto por Financiador',
            default => 'Pies Presupuesto por Financiador'
        };
    }

    protected function beforeSave(object $entity, Request $request): void
    {
        if ($entity instanceof BudgetFooterByFunder) {
            $entity->setUpdatedAt(new \DateTime());
        }
    }

    protected function canDelete(object $entity): bool
    {
        return true;
    }
}
