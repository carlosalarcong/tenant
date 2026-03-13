<?php

namespace App\Controller\Maintainers\Commercial;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\InsurancePlan;
use App\Form\Maintainers\Commercial\InsurancePlanType;
use App\Repository\Tenant\BranchPayerRepository;
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
        private BranchPayerRepository $branchPayerRepository,
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
            ->leftJoin('ip.branchPayer', 'bp')->addSelect('bp')
            ->leftJoin('bp.branch', 'b')->addSelect('b')
            ->leftJoin('bp.payer', 'p')->addSelect('p')
            ->orderBy('ip.name', 'ASC');
    }

    protected function getColumns(): array { return []; }
    protected function getTemplatePath(): string { return 'maintainers/commercial/insurance_plan/index.html.twig'; }
    protected function getFormType(): string { return InsurancePlanType::class; }
    protected function createNewEntity(): object { return new InsurancePlan(); }
    protected function getIndexRoute(): string { return 'app_maintainers_commercial_insurance_plan_index'; }

    #[Route('', name: 'app_maintainers_commercial_insurance_plan_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $branchPayerId = (int) $request->query->get('branchPayerId', 0);
        $branchPayer   = $branchPayerId ? $this->branchPayerRepository->find($branchPayerId) : null;

        $qb = $this->insurancePlanRepository->createQueryBuilder('ip')
            ->leftJoin('ip.branchPayer', 'bp')->addSelect('bp')
            ->leftJoin('bp.branch', 'b')->addSelect('b')
            ->leftJoin('bp.payer', 'p')->addSelect('p')
            ->orderBy('ip.name', 'ASC');

        if ($branchPayer) {
            $qb->where('ip.branchPayer = :bp')->setParameter('bp', $branchPayer);
        }

        $plans = $qb->getQuery()->getResult();

        return $this->render('maintainers/commercial/insurance_plan/index.html.twig', [
            'plans'          => $plans,
            'branch_payer'   => $branchPayer,
            'branch_payer_id'=> $branchPayerId,
        ]);
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

    #[Route('/{id}/link-package', name: 'app_maintainers_commercial_insurance_plan_link_package', methods: ['GET', 'POST'])]
    public function linkPackage(Request $request, InsurancePlan $plan): Response
    {
        $packagePlans = $this->insurancePlanRepository->createQueryBuilder('ip')
            ->where('ip.isPackage = true')
            ->orderBy('ip.name', 'ASC')
            ->getQuery()->getResult();

        if ($request->isMethod('POST')) {
            $parentPlanId = (int) $request->request->get('parent_plan_id');
            $parentPlan   = $parentPlanId ? $this->insurancePlanRepository->find($parentPlanId) : null;
            $plan->setParentPlan($parentPlan);
            $this->entityManager->persist($plan);
            $this->entityManager->flush();

            return $this->render('maintainers/commercial/insurance_plan/link_package_success.html.twig');
        }

        return $this->render('maintainers/commercial/insurance_plan/link_package.html.twig', [
            'plan'          => $plan,
            'package_plans' => $packagePlans,
        ]);
    }

    #[Route('/export', name: 'app_maintainers_commercial_insurance_plan_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
