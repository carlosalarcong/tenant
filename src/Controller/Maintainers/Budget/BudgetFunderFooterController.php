<?php

namespace App\Controller\Maintainers\Budget;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\BudgetFunderFooter;
use App\Form\Maintainers\Budget\BudgetFunderFooterType;
use App\Repository\Tenant\BudgetFunderFooterRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/budget/budget-funder-footer')]
class BudgetFunderFooterController extends AbstractMantenedorController
{
    public function __construct(
        private BudgetFunderFooterRepository $budgetFunderFooterRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_budget_budget_funder_footer_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_budget_budget_funder_footer_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_budget_budget_funder_footer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_budget_budget_funder_footer_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_budget_budget_funder_footer_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'budgetFooter.name', 'isActive'],
            headers: $this->translateColumns(['name', 'budget_footer', 'is_active']),
            filename: 'presupuestos_pie_financiador_' . date('Y-m-d') . '.csv'
        );
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->budgetFunderFooterRepository->createQueryBuilder('bff')
            ->leftJoin('bff.budgetFooter', 'bf')
            ->addSelect('bf')
            ->orderBy('bff.id', 'DESC');
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
        return 'maintainers/budget/budget_funder_footer/index.html.twig';
    }

    protected function getFormType(): string
    {
        return BudgetFunderFooterType::class;
    }

    protected function createNewEntity(): object
    {
        return new BudgetFunderFooter();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_budget_budget_funder_footer_index';
    }

    protected function getPageTitle(?string $action = null): string
    {
        return match($action) {
            'create' => 'Crear Presupuesto Pie Financiador',
            'edit' => 'Editar Presupuesto Pie Financiador',
            default => 'Presupuestos Pie Financiador'
        };
    }

    protected function beforeSave(object $entity, Request $request): void
    {
        if ($entity instanceof BudgetFunderFooter) {
            $entity->setUpdatedAt(new \DateTime());
        }
    }

    protected function canDelete(object $entity): bool
    {
        return true;
    }
}
