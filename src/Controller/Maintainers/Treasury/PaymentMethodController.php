<?php

namespace App\Controller\Maintainers\Treasury;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\PaymentMethod;
use App\Form\Maintainers\Treasury\PaymentMethodFormType;
use App\Repository\Tenant\PaymentMethodRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/treasury/payment-method')]
class PaymentMethodController extends AbstractMantenedorController
{
    public function __construct(
        private readonly PaymentMethodRepository $repository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('/', name: 'app_maintainers_treasury_payment_method_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_treasury_payment_method_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_treasury_payment_method_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_treasury_payment_method_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }

    #[Route('/export', name: 'app_maintainers_treasury_payment_method_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        $exportColumns = $this->getExportColumns();
        return $this->handleExport(
            request: $request,
            columns: array_keys($exportColumns),
            headers: array_values($exportColumns),
            filename: $this->getExportFileName()
        );
    }

    protected function getEntityClass(): string
    {
        return PaymentMethod::class;
    }

    protected function getFormType(): string
    {
        return PaymentMethodFormType::class;
    }

    protected function getRepository()
    {
        return $this->repository;
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/treasury/payment_method/index.html.twig';
    }

    protected function getRoutePrefix(): string
    {
        return 'app_maintainers_treasury_payment_method';
    }

    protected function getColumns(): array
    {
        return [
        'id' => $this->translator->trans('maintainers.columns.id', [], 'maintainers'),
        'code' => $this->translator->trans('maintainers.columns.code', [], 'maintainers'),
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'parent.name' => 'Padre',
        'paymentMethodType.name' => $this->translator->trans('maintainers.columns.type', [], 'maintainers'),
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function createNewEntity(): object
    {
        return new PaymentMethod();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_treasury_payment_method_index';
    }
    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->repository->createQueryBuilder('pm')
            ->leftJoin('pm.parent', 'parent')
            ->addSelect('parent')
            ->leftJoin('pm.paymentMethodType', 'pmt')
            ->addSelect('pmt')
            ->orderBy('pm.name', 'ASC');
    }

    protected function getExportColumns(): array
    {
        return [
            'id' => 'ID',
            'code' => 'Código',
            'name' => 'Nombre',
            'parent.name' => 'Método Padre',
            'paymentMethodType.name' => 'Tipo',
            'issuesReceipt' => 'Emite Recibo',
            'isGuarantee' => 'Es Garantía',
            'isProfessionalPayment' => 'Pago Profesional',
            'isWebPayment' => 'Pago Web',
            'documentTypeCode' => 'Código Doc.',
            'accountingCode' => 'Código Contable',
            'visibleInCashRegister' => 'Visible en Caja',
            'creditCardPayment' => 'Pago con Tarjeta',
            'isActive' => 'Activo'
        ];
    }

    protected function getExportFileName(): string
    {
        return 'metodos_pago_' . date('Y-m-d_His') . '.csv';
    }
}
