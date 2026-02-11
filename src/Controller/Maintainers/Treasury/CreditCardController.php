<?php

namespace App\Controller\Maintainers\Treasury;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\CreditCard;
use App\Form\Maintainers\Treasury\CreditCardFormType;
use App\Repository\Tenant\CreditCardRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/treasury/credit-card')]
class CreditCardController extends AbstractMantenedorController
{
    public function __construct(
        private CreditCardRepository $creditCardRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_treasury_credit_card_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_treasury_credit_card_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_treasury_credit_card_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_treasury_credit_card_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_treasury_credit_card_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'abbreviation', 'creditCardType.name', 'isActive'],
            headers: $this->translateColumns(['name', 'abbreviation', 'type', 'is_active']),
            filename: 'tarjetas_credito_' . date('Y-m-d') . '.csv'
        );
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->creditCardRepository->createQueryBuilder('cc')
            ->leftJoin('cc.creditCardType', 'cct')
            ->addSelect('cct')
            ->orderBy('cc.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'abbreviation' => 'Abreviatura',
        'creditCardType.name' => 'Tipo Tarjeta',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/treasury/credit_card/index.html.twig';
    }

    protected function getFormType(): string
    {
        return CreditCardFormType::class;
    }

    protected function createNewEntity(): object
    {
        return new CreditCard();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_treasury_credit_card_index';
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
