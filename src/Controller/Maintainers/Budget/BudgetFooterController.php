<?php

namespace App\Controller\Maintainers\Budget;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\BudgetFooter;
use App\Form\Maintainers\Budget\BudgetFooterType;
use App\Repository\Tenant\BudgetFooterRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/budget/budget-footer')]
class BudgetFooterController extends AbstractMantenedorController
{
    public function __construct(
        private BudgetFooterRepository $budgetFooterRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_budget_budget_footer_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_budget_budget_footer_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_budget_budget_footer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_budget_budget_footer_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_budget_budget_footer_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'isActive'],
            headers: $this->translateColumns(['name', 'is_active']),
            filename: 'pies_presupuesto_' . date('Y-m-d') . '.csv'
        );
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->budgetFooterRepository->createQueryBuilder('bf')
            ->orderBy('bf.id', 'DESC');
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
        return 'maintainers/budget/budget_footer/index.html.twig';
    }

    protected function getFormType(): string
    {
        return BudgetFooterType::class;
    }

    protected function createNewEntity(): object
    {
        return new BudgetFooter();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_budget_budget_footer_index';
    }

    protected function getPageTitle(?string $action = null): string
    {
        return match($action) {
            'create' => 'Crear Pie Presupuesto',
            'edit' => 'Editar Pie Presupuesto',
            default => 'Pies de Presupuesto'
        };
    }

    protected function beforeSave(object $entity, Request $request): void
    {
        if ($entity instanceof BudgetFooter) {
            $entity->setUpdatedAt(new \DateTime());
        }
    }

    protected function canDelete(object $entity): bool
    {
        return true;
    }
}
