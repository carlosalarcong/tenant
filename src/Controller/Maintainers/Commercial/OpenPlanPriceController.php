<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\OpenPlanPrice;
use App\Form\Maintainers\Commercial\OpenPlanPriceType;
use App\Repository\Tenant\OpenPlanPriceRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/open-plan-price')]
class OpenPlanPriceController extends AbstractMantenedorController
{
    public function __construct(
        private OpenPlanPriceRepository $openPlanPriceRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->openPlanPriceRepository->createQueryBuilder('opp')
            ->leftJoin('opp.plan', 'ip')
            ->addSelect('ip')
            ->leftJoin('opp.billingItem', 'bi')
            ->addSelect('bi')
            ->leftJoin('opp.cancellationUser', 'cu')
            ->addSelect('cu')
            ->orderBy('ip.name', 'ASC')
            ->addOrderBy('bi.name', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
            'id' => 'ID',
            'plan.name' => 'Plan',
            'billingItem.name' => 'Prestación',
            'unitPrice' => 'Precio',
            'copayAmount' => 'Copago',
            'theatreAmount' => 'Pabellón',
            'effectiveDate' => 'F. Vigencia',
            'isActive' => 'Activo',
        ];
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/open_plan_price/index.html.twig';
    }

    protected function getFormType(): string
    {
        return OpenPlanPriceType::class;
    }

    protected function createNewEntity(): object
    {
        return new OpenPlanPrice();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_open_plan_price_index';
    }

    #[Route('', name: 'app_maintainers_commercial_open_plan_price_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_open_plan_price_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_open_plan_price_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, OpenPlanPrice $openPlanPrice): Response
    {
        return $this->handleEdit($request, $openPlanPrice->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_open_plan_price_delete', methods: ['DELETE'])]
    public function delete(Request $request, OpenPlanPrice $openPlanPrice): Response
    {
        return $this->handleDelete($request, $openPlanPrice->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_open_plan_price_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
