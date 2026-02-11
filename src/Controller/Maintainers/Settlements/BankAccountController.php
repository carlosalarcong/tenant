<?php

namespace App\Controller\Maintainers\Settlements;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\BankAccount;
use App\Form\Maintainers\Settlements\BankAccountFormType;
use App\Repository\Tenant\BankAccountRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/settlements/bank-account')]
class BankAccountController extends AbstractMantenedorController
{
    public function __construct(
        private BankAccountRepository $bankAccountRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_settlements_bank_account_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_settlements_bank_account_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_settlements_bank_account_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_settlements_bank_account_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_settlements_bank_account_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['accountNumber', 'bank.name', 'bankAccountType.name', 'isActive'],
            headers: $this->translateColumns(['account_number', 'bank', 'bank_account_type', 'is_active']),
            filename: 'cuentas_bancarias_' . date('Y-m-d') . '.csv'
        );
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->bankAccountRepository->createQueryBuilder('ba')
            ->leftJoin('ba.bank', 'b')
            ->leftJoin('ba.bankAccountType', 'bat')
            ->addSelect('b', 'bat')
            ->orderBy('ba.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
            'accountNumber' => $this->translator->trans('maintainers.columns.account_number', [], 'maintainers'),
            'bank.name' => $this->translator->trans('maintainers.columns.bank', [], 'maintainers'),
            'bankAccountType.name' => $this->translator->trans('maintainers.columns.bank_account_type', [], 'maintainers'),
            'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
        ];
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/settlements/bank_account/index.html.twig';
    }

    protected function getFormType(): string
    {
        return BankAccountFormType::class;
    }

    protected function createNewEntity(): object
    {
        return new BankAccount();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_settlements_bank_account_index';
    }

    protected function getPageTitle(?string $action = null): string
    {
        return match($action) {
            'create' => 'Crear Cuenta Bancaria',
            'edit' => 'Editar Cuenta Bancaria',
            default => 'Cuentas Bancarias'
        };
    }

    protected function beforeSave(object $entity, Request $request): void
    {
        if ($entity instanceof BankAccount) {
            $entity->setUpdatedAt(new \DateTime());
        }
    }

    protected function canDelete(object $entity): bool
    {
        return true;
    }
}
