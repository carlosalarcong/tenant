<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\InsurancePlanPrice;
use App\Entity\Tenant\Member;
use App\Form\Maintainers\Commercial\InsurancePlanPriceType;
use App\Repository\Tenant\InsurancePlanPriceRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/insurance-plan-price')]
class InsurancePlanPriceController extends AbstractMantenedorController
{
    public function __construct(
        private InsurancePlanPriceRepository $insurancePlanPriceRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->insurancePlanPriceRepository->createQueryBuilder('ipp')
            ->leftJoin('ipp.insurancePlan', 'ip')
            ->addSelect('ip')
            ->leftJoin('ipp.billingItem', 'bi')
            ->addSelect('bi')
            ->leftJoin('ipp.branchPayer', 'bp')
            ->addSelect('bp')
            ->leftJoin('bp.branch', 'b')
            ->addSelect('b')
            ->leftJoin('bp.payer', 'p')
            ->addSelect('p')
            ->leftJoin('ipp.branchCareType', 'bct')
            ->addSelect('bct')
            ->leftJoin('bct.careType', 'ct')
            ->addSelect('ct')
            ->leftJoin('ipp.createdBy', 'cb')
            ->addSelect('cb')
            ->orderBy('ip.name', 'ASC')
            ->addOrderBy('bi.name', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
            'id' => 'ID',
            'insurancePlan.name' => 'Plan',
            'billingItem.name' => 'Prestación',
            'branchPayer.branch.name' => 'Sucursal',
            'unitPrice' => 'Precio',
            'copayAmount' => 'Copago',
            'effectiveDate' => 'F. Vigencia',
            'isActive' => 'Activo',
        ];
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/insurance_plan_price/index.html.twig';
    }

    protected function getFormType(): string
    {
        return InsurancePlanPriceType::class;
    }

    protected function createNewEntity(): object
    {
        return new InsurancePlanPrice();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_insurance_plan_price_index';
    }

    protected function beforeSave(object $entity, Request $request): void
    {
        if ($entity instanceof InsurancePlanPrice && $entity->getCreatedBy() === null) {
            $user = $this->getUser();
            if ($user instanceof Member) {
                $entity->setCreatedBy($user);
            }
        }
    }

    #[Route('', name: 'app_maintainers_commercial_insurance_plan_price_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_insurance_plan_price_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_insurance_plan_price_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, InsurancePlanPrice $insurancePlanPrice): Response
    {
        return $this->handleEdit($request, $insurancePlanPrice->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_insurance_plan_price_delete', methods: ['DELETE'])]
    public function delete(Request $request, InsurancePlanPrice $insurancePlanPrice): Response
    {
        return $this->handleDelete($request, $insurancePlanPrice->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_insurance_plan_price_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
