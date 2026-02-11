<?php

namespace App\Controller\Maintainers\Treasury;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\BillingPaymentMethod;
use App\Form\Maintainers\Treasury\BillingPaymentMethodType;
use App\Repository\Tenant\BillingPaymentMethodRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * BillingPaymentMethod Controller
 * 
 * Gestiona el mantenedor de Formas de Pago Facturación
 */
#[Route('/maintainers/treasury/billing-payment-method')]
class BillingPaymentMethodController extends AbstractMantenedorController
{
    public function __construct(
        private BillingPaymentMethodRepository $billingPaymentMethodRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_treasury_billing_payment_method_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_treasury_billing_payment_method_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_treasury_billing_payment_method_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_treasury_billing_payment_method_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_treasury_billing_payment_method_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['code', 'name', 'isCash', 'isActive'],
            headers: $this->translateColumns(['code', 'name', 'is_cash', 'is_active']),
            filename: 'formas_pago_facturacion_' . date('Y-m-d') . '.csv'
        );
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->billingPaymentMethodRepository->createQueryBuilder('bpm')
            ->orderBy('bpm.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'code' => $this->translator->trans('maintainers.columns.code', [], 'maintainers'),
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'isCash' => 'Efectivo',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/treasury/billing_payment_method/index.html.twig';
    }

    protected function getFormType(): string
    {
        return BillingPaymentMethodType::class;
    }

    protected function createNewEntity(): object
    {
        return new BillingPaymentMethod();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_treasury_billing_payment_method_index';
    }

    protected function beforeSave(object $entity, Request $request): void
    {
        if ($entity instanceof BillingPaymentMethod) {
            $entity->setCode(strtoupper($entity->getCode()));
        }
        $entity->setUpdatedAt(new \DateTime());
    }

    protected function canDelete(object $entity): bool
    {
        return true;
    }

}
