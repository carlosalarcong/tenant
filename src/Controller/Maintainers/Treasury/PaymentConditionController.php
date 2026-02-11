<?php

namespace App\Controller\Maintainers\Treasury;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\PaymentCondition;
use App\Form\Maintainers\Treasury\PaymentConditionType;
use App\Repository\Tenant\PaymentConditionRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * PaymentCondition Controller
 * 
 * Gestiona el mantenedor de Condiciones de Pago
 */
#[Route('/maintainers/treasury/payment-condition')]
class PaymentConditionController extends AbstractMantenedorController
{
    public function __construct(
        private PaymentConditionRepository $paymentConditionRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_treasury_payment_condition_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_treasury_payment_condition_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_treasury_payment_condition_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_treasury_payment_condition_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_treasury_payment_condition_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'interfaceCode', 'maxTerm', 'isUpToDate', 'isActive'],
            headers: $this->translateColumns(['name', 'interface_code', 'max_term', 'is_up_to_date', 'is_active']),
            filename: 'condiciones_pago_' . date('Y-m-d') . '.csv'
        );
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->paymentConditionRepository->createQueryBuilder('pc')
            ->orderBy('pc.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'interfaceCode' => 'Cód. Interfaz',
        'maxTerm' => 'Plazo Máx.',
        'isUpToDate' => 'Al Día',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/treasury/payment_condition/index.html.twig';
    }

    protected function getFormType(): string
    {
        return PaymentConditionType::class;
    }

    protected function createNewEntity(): object
    {
        return new PaymentCondition();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_treasury_payment_condition_index';
    }

    protected function beforeSave(object $entity, Request $request): void
    {
        $entity->setUpdatedAt(new \DateTime());
    }

    protected function canDelete(object $entity): bool
    {
        return true;
    }

}
