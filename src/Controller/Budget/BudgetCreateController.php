<?php

namespace App\Controller\Budget;

use App\Entity\Tenant\Budget;
use App\Entity\Tenant\BranchPayer;
use App\Entity\Tenant\BudgetDetail;
use App\Entity\Tenant\Member;
use App\Repository\Tenant\AgreementRepository;
use App\Repository\Tenant\BranchPayerRepository;
use App\Repository\Tenant\BudgetRepository;
use App\Repository\Tenant\CareTypeRepository;
use App\Repository\Tenant\InsurancePlanRepository;
use App\Repository\Tenant\MedicalServiceRepository;
use App\Repository\Tenant\OriginRepository;
use App\Repository\Tenant\PayerRepository;
use App\Repository\Tenant\PersonRepository;
use App\Repository\Tenant\ProfessionalRepository;
use App\Repository\Tenant\SurgeryPackageItemRepository;
use App\Repository\Tenant\SurgeryPackagePlanRepository;
use App\Service\Budget\BudgetPricingService;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/budget')]
class BudgetCreateController extends AbstractController
{
    public function __construct(
        private TenantEntityManager $tenantEntityManager,
        private PersonRepository $personRepository,
        private BranchPayerRepository $branchPayerRepository,
        private InsurancePlanRepository $insurancePlanRepository,
        private AgreementRepository $agreementRepository,
        private ProfessionalRepository $professionalRepository,
        private SurgeryPackagePlanRepository $surgeryPackagePlanRepository,
        private SurgeryPackageItemRepository $surgeryPackageItemRepository,
        private MedicalServiceRepository $medicalServiceRepository,
        private BudgetPricingService $budgetPricingService,
        private BudgetRepository $budgetRepository,
        private PayerRepository $payerRepository,
        private CareTypeRepository $careTypeRepository,
        private OriginRepository $originRepository,
    ) {}

    private function resolveBranchPayer(?int $payerId): ?BranchPayer
    {
        if (!$payerId) {
            return null;
        }
        // Fallback: cualquier BranchPayer con ese payer
        return $this->branchPayerRepository->findOneBy(['payer' => $payerId]);
    }

    #[Route('/new/{personId}', name: 'app_budget_new', methods: ['GET'], requirements: ['personId' => '\d+'])]
    public function new(int $personId): Response
    {
        $person = $this->personRepository->find($personId);
        if (!$person) {
            throw $this->createNotFoundException('Paciente no encontrado.');
        }

        return $this->render('budget/new.html.twig', [
            'person'              => $person,
            'surgeryPackagePlans' => $this->surgeryPackagePlanRepository->findBy(['isActive' => true], ['name' => 'ASC']),
            'insurancePlans'      => $this->insurancePlanRepository->findBy([], ['name' => 'ASC']),
            'payers'              => $this->payerRepository->findBy(['isActive' => true], ['name' => 'ASC']),
            'careTypes'           => $this->careTypeRepository->findBy([], ['name' => 'ASC']),
            'origins'             => $this->originRepository->findBy([], ['name' => 'ASC']),
        ]);
    }

    #[Route('/ajax/agreements', name: 'app_budget_ajax_agreements', methods: ['GET'])]
    public function getAgreements(Request $request): JsonResponse
    {
        $payerId = (int) $request->query->get('payerId', 0);
        if ($payerId === 0) {
            return new JsonResponse([]);
        }

        $agreements = $this->agreementRepository->findBy(['payer' => $payerId, 'isActive' => true], ['name' => 'ASC']);

        return new JsonResponse(array_map(
            fn($a) => ['id' => $a->getId(), 'name' => $a->getName()],
            $agreements
        ));
    }

    #[Route('/ajax/professionals', name: 'app_budget_ajax_professionals', methods: ['GET'])]
    public function getProfessionals(): JsonResponse
    {
        $professionals = $this->professionalRepository->findBy(['isActive' => true], ['lastName' => 'ASC']);

        return new JsonResponse(array_map(
            fn($p) => ['id' => $p->getId(), 'name' => $p->getLastName() . ', ' . $p->getFirstName()],
            $professionals
        ));
    }

    #[Route('/ajax/services/search', name: 'app_budget_ajax_services_search', methods: ['GET'])]
    public function searchServices(Request $request): JsonResponse
    {
        $q = trim($request->query->get('q', ''));
        if (strlen($q) < 2) {
            return new JsonResponse([]);
        }

        $results = $this->medicalServiceRepository->createQueryBuilder('s')
            ->where('s.name LIKE :q OR s.code LIKE :q OR s.shortName LIKE :q')
            ->andWhere('s.isActive = true')
            ->setParameter('q', '%' . $q . '%')
            ->orderBy('s.name', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        return new JsonResponse(array_map(
            fn($s) => ['id' => $s->getId(), 'code' => $s->getCode(), 'name' => $s->getName()],
            $results
        ));
    }

    #[Route('/ajax/package-items', name: 'app_budget_ajax_package_items', methods: ['GET'])]
    public function getPackageItems(Request $request): JsonResponse
    {
        $planId = (int) $request->query->get('planId', 0);
        if ($planId === 0) {
            return new JsonResponse([]);
        }

        $plan = $this->surgeryPackagePlanRepository->find($planId);
        if (!$plan) {
            return new JsonResponse([]);
        }

        $items = $this->surgeryPackageItemRepository->findBy(
            ['surgeryPackagePlan' => $plan, 'isActive' => true],
            ['name' => 'ASC']
        );

        return new JsonResponse(array_map(
            fn($i) => [
                'id'       => $i->getId(),
                'name'     => $i->getName(),
                'itemType' => $i->getItemType(),
                'code'     => $i->getCode(),
            ],
            $items
        ));
    }

    #[Route('/ajax/preview', name: 'app_budget_ajax_preview', methods: ['POST'])]
    public function preview(Request $request): JsonResponse
    {
        $data     = $request->toArray();
        $planType = $data['planType'] ?? 'open';
        $planId   = (int) ($data['planId'] ?? 0);
        $payerId  = isset($data['payerId']) ? (int) $data['payerId'] : null;
        $ids      = $data['ids'] ?? [];

        $branchPayer = $this->resolveBranchPayer($payerId);

        if ($planType === 'open') {
            $plan = $this->insurancePlanRepository->find($planId);
            if (!$plan) {
                return new JsonResponse(['error' => 'Plan no encontrado'], 404);
            }

            $services = array_values(array_filter(array_map(
                fn($id) => $this->medicalServiceRepository->find($id),
                $ids
            )));

            $rows = $this->budgetPricingService->calcularPreciosAbierta($services, $plan, $branchPayer);

            $mappedRows = array_map(fn($row) => [
                'serviceId'     => $row['medicalService']->getId(),
                'name'          => $row['medicalService']->getName(),
                'unitPrice'     => $row['unitPrice'],
                'copayAmount'   => $row['copayAmount'],
                'theatreAmount' => $row['theatreAmount'],
                'theatreRate'   => $row['theatreRate'],
                'total'         => $row['unitPrice'] + $row['theatreAmount'],
            ], $rows);

        } else {
            $plan = $this->surgeryPackagePlanRepository->find($planId);
            if (!$plan) {
                return new JsonResponse(['error' => 'Plan no encontrado'], 404);
            }

            $items = array_values(array_filter(array_map(
                fn($id) => $this->surgeryPackageItemRepository->find($id),
                $ids
            )));

            $rows = $this->budgetPricingService->calcularPreciosPaquetizada(
                $items,
                $plan,
                $branchPayer,
                (bool) ($data['includesHonorariums'] ?? true)
            );

            $mappedRows = array_map(fn($row) => [
                'itemId'      => $row['surgeryPackageItem']->getId(),
                'name'        => $row['surgeryPackageItem']->getName(),
                'itemType'    => $row['itemType'],
                'priceIsapre' => $row['priceIsapre'],
                'priceRate'   => $row['priceRate'],
            ], $rows);
        }

        return new JsonResponse(['rows' => $mappedRows]);
    }

    #[Route('/new/{personId}/save', name: 'app_budget_save', methods: ['POST'], requirements: ['personId' => '\d+'])]
    public function save(int $personId, Request $request): JsonResponse
    {
        $person = $this->personRepository->find($personId);
        if (!$person) {
            return new JsonResponse(['error' => 'Paciente no encontrado'], 404);
        }

        $data       = $request->toArray();
        $planType   = $data['planType'] ?? 'open';
        $planId     = (int) ($data['planId'] ?? 0);
        $payerId    = isset($data['payerId']) ? (int) $data['payerId'] : null;
        $ids        = $data['ids'] ?? [];
        $branchPayer = $this->resolveBranchPayer($payerId);

        $budget = new Budget();
        $budget->setPerson($person);
        $budget->setNumber($this->budgetPricingService->calcularNumeroPresupuesto());
        $budget->setIsAmbulatory((bool) ($data['isAmbulatory'] ?? false));
        $budget->setIncludesHonorariums((bool) ($data['includesHonorariums'] ?? true));
        $budget->setObservation($data['observation'] ?? null);

        $user = $this->getUser();
        if ($user instanceof Member) {
            $budget->setMember($user);
        }

        if ($branchPayer) {
            $budget->setBranch($branchPayer->getBranch());
        }

        if ($payerId) {
            $budget->setPayer($this->payerRepository->find($payerId));
        }

        if ($data['agreementId'] ?? null) {
            $budget->setAgreement($this->agreementRepository->find($data['agreementId']));
        }

        if ($data['professionalId'] ?? null) {
            $budget->setProfessional($this->professionalRepository->find($data['professionalId']));
        }

        if ($planType === 'open') {
            $plan = $this->insurancePlanRepository->find($planId);
            $budget->setInsurancePlan($plan);

            $services = array_values(array_filter(array_map(
                fn($id) => $this->medicalServiceRepository->find($id),
                $ids
            )));

            $rows = $this->budgetPricingService->calcularPreciosAbierta($services, $plan, $branchPayer);

            foreach ($rows as $row) {
                if ($row['unitPrice'] > 0) {
                    $d = new BudgetDetail();
                    $d->setItemType('honorario');
                    $d->setMedicalService($row['medicalService']);
                    $d->setAmount((string) $row['unitPrice']);
                    $d->setQuantity(1);
                    $budget->addDetail($d);
                }
                if ($row['theatreAmount'] > 0) {
                    $d = new BudgetDetail();
                    $d->setItemType('pabellon');
                    $d->setMedicalService($row['medicalService']);
                    $d->setAmount((string) $row['theatreAmount']);
                    $d->setQuantity(1);
                    $budget->addDetail($d);
                }
            }
        } else {
            $plan = $this->surgeryPackagePlanRepository->find($planId);
            $budget->setSurgeryPackagePlan($plan);

            $items = array_values(array_filter(array_map(
                fn($id) => $this->surgeryPackageItemRepository->find($id),
                $ids
            )));

            $rows = $this->budgetPricingService->calcularPreciosPaquetizada(
                $items,
                $plan,
                $branchPayer,
                (bool) ($data['includesHonorariums'] ?? true)
            );

            foreach ($rows as $row) {
                if ($row['priceRate'] > 0) {
                    $d = new BudgetDetail();
                    $d->setItemType($row['itemType']);
                    $d->setSurgeryPackageItem($row['surgeryPackageItem']);
                    $d->setAmount((string) $row['priceIsapre']);
                    $d->setQuantity(1);
                    $budget->addDetail($d);
                }
            }
        }

        $this->tenantEntityManager->persist($budget);
        $this->tenantEntityManager->flush();

        return new JsonResponse([
            'success'     => true,
            'budgetId'    => $budget->getId(),
            'redirectUrl' => $this->generateUrl('app_budget_show', ['id' => $budget->getId()]),
        ]);
    }
}
