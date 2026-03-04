<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\InsurancePlan;
use App\Form\Maintainers\Commercial\InsurancePlanType;
use App\Repository\Tenant\InsurancePlanRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/commercial/insurance-plan')]
class InsurancePlanController extends AbstractMantenedorController
{
    public function __construct(
        private InsurancePlanRepository $insurancePlanRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->insurancePlanRepository->createQueryBuilder('ip')
            ->leftJoin('ip.branchPayer', 'bp')
            ->addSelect('bp')
            ->leftJoin('bp.branch', 'b')
            ->addSelect('b')
            ->leftJoin('bp.payer', 'p')
            ->addSelect('p')
            ->leftJoin('ip.parentPlan', 'pp')
            ->addSelect('pp')
            ->orderBy('b.name', 'ASC')
            ->addOrderBy('ip.name', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
            'branchPayer.branch.name' => 'Sucursal',
            'branchPayer.payer.name' => 'Financiador',
            'name' => 'Plan',
            'isPackage' => 'Paquete',
            'isTelemedicine' => 'Teleconsulta',
            'isActive' => 'Activo',
        ];
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/commercial/insurance_plan/index.html.twig';
    }

    protected function getFormType(): string
    {
        return InsurancePlanType::class;
    }

    protected function createNewEntity(): object
    {
        return new InsurancePlan();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_commercial_insurance_plan_index';
    }

    #[Route('', name: 'app_maintainers_commercial_insurance_plan_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_commercial_insurance_plan_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_commercial_insurance_plan_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, InsurancePlan $insurancePlan): Response
    {
        return $this->handleEdit($request, $insurancePlan->getId());
    }

    #[Route('/{id}', name: 'app_maintainers_commercial_insurance_plan_delete', methods: ['DELETE'])]
    public function delete(Request $request, InsurancePlan $insurancePlan): Response
    {
        return $this->handleDelete($request, $insurancePlan->getId());
    }

    #[Route('/export', name: 'app_maintainers_commercial_insurance_plan_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
