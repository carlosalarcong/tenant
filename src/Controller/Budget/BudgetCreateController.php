<?php

namespace App\Controller\Budget;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\Agreement;
use App\Entity\Tenant\Branch;
use App\Entity\Tenant\BranchPayer;
use App\Entity\Tenant\Budget;
use App\Entity\Tenant\BudgetDetail;
use App\Entity\Tenant\CareType;
use App\Entity\Tenant\CashierAssignment;
use App\Entity\Tenant\InsurancePlan;
use App\Entity\Tenant\MedicalService;
use App\Entity\Tenant\Member;
use App\Entity\Tenant\Origin;
use App\Entity\Tenant\Person;
use App\Entity\Tenant\Payer;
use App\Entity\Tenant\Professional;
use App\Entity\Tenant\SurgeryFeePrice;
use App\Entity\Tenant\SurgeryPackageItem;
use App\Entity\Tenant\SurgeryPackagePlan;
use App\Entity\Tenant\SurgeryPackagePrice;
use App\Repository\Tenant\AgreementRepository;
use App\Repository\Tenant\BranchPayerRepository;
use App\Repository\Tenant\CareTypeRepository;
use App\Repository\Tenant\OriginRepository;
use App\Repository\Tenant\PersonRepository;
use App\Repository\Tenant\PayerRepository;
use App\Repository\Tenant\ProfessionalRepository;
use App\Service\Budget\BudgetPricingService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/budget', name: 'budget_')]
class BudgetCreateController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly PersonRepository $personRepository,
        private readonly PayerRepository $payerRepository,
        private readonly CareTypeRepository $careTypeRepository,
        private readonly OriginRepository $originRepository,
        private readonly ProfessionalRepository $professionalRepository,
        private readonly AgreementRepository $agreementRepository,
        private readonly BranchPayerRepository $branchPayerRepository,
        private readonly BudgetPricingService $budgetPricingService,
        #[Autowire(service: 'doctrine.orm.tenant_entity_manager')]
        private readonly EntityManagerInterface $em,
    ) {}

    #[Route('/new/{personId}', name: 'new', methods: ['GET'], requirements: ['personId' => '\d+'])]
    public function new(int $personId): Response
    {
        $person = $this->personRepository->find($personId);
        if (!$person instanceof Person) {
            throw $this->createNotFoundException('Paciente no encontrado.');
        }

        $branchId = $this->resolveCurrentBranchId();

        $insurancePlans = $this->em->getRepository(InsurancePlan::class)
            ->createQueryBuilder('ip')
            ->where('ip.isActive = true')
            ->andWhere('ip.isDisabled = false')
            ->orderBy('ip.name', 'ASC')
            ->getQuery()
            ->getResult();

        $surgeryPackagePlans = $this->em->getRepository(SurgeryPackagePlan::class)
            ->createQueryBuilder('spp')
            ->where('spp.isActive = true')
            ->andWhere('spp.isDisabled = false')
            ->orderBy('spp.name', 'ASC')
            ->getQuery()
            ->getResult();

        $professionals = $branchId !== null
            ? $this->professionalRepository->findActiveChoicesByBranch($branchId)
            : array_map(
                static fn (Professional $professional): array => [
                    'id' => $professional->getId(),
                    'name' => $professional->getFullName(),
                ],
                $this->professionalRepository->findAllActive()
            );

        return $this->render('budget/create/new.html.twig', [
            'person' => $person,
            'payers' => $this->payerRepository->findActive(),
            'careTypes' => $this->careTypeRepository->findAllActive(),
            'origins' => $this->originRepository->findAllActive(),
            'professionals' => $professionals,
            'insurancePlans' => $insurancePlans,
            'surgeryPackagePlans' => $surgeryPackagePlans,
        ]);
    }

    #[Route('/agreements', name: 'get_agreements', methods: ['GET'])]
    public function getAgreements(Request $request): JsonResponse
    {
        $payerId = $request->query->getInt('payerId');
        if ($payerId <= 0) {
            return $this->json([]);
        }

        return $this->json($this->agreementRepository->findActiveChoicesByPayer($payerId));
    }

    #[Route('/professionals', name: 'get_professionals', methods: ['GET'])]
    public function getProfessionals(): JsonResponse
    {
        $branchId = $this->resolveCurrentBranchId();
        $professionals = $this->professionalRepository->findAllActive();

        $payload = array_map(static function (Professional $professional): array {
            return [
                'id' => $professional->getId(),
                'firstName' => $professional->getFirstName() ?? '',
                'lastName' => $professional->getLastName() ?? '',
                'specialty' => $professional->getSpecialty()?->getName() ?? '',
            ];
        }, $professionals);

        if ($branchId !== null) {
            // El modelo actual aún no relaciona Professional con Branch.
            return $this->json($payload);
        }

        return $this->json($payload);
    }

    #[Route('/services/validate', name: 'validate_services', methods: ['POST'])]
    public function validateServices(Request $request): JsonResponse
    {
        $payload = $this->decodeJsonPayload($request);

        $serviceId = (int) ($payload['medicalServiceId'] ?? $payload['surgeryPackageItemId'] ?? 0);
        $planId = (int) ($payload['planId'] ?? 0);
        $branchPayerId = (int) ($payload['branchPayerId'] ?? 0);
        $modalidad = (string) ($payload['modalidad'] ?? '');

        if ($serviceId <= 0 || $planId <= 0 || $modalidad === '') {
            return $this->json(['valid' => false, 'error' => 'Parámetros incompletos.'], Response::HTTP_BAD_REQUEST);
        }

        $branchPayer = $branchPayerId > 0 ? $this->branchPayerRepository->find($branchPayerId) : null;

        $today = new \DateTimeImmutable('today');

        if ($modalidad === 'abierta') {
            $medicalService = $this->em->getRepository(MedicalService::class)->find($serviceId);
            if (!$medicalService instanceof MedicalService) {
                return $this->json(['valid' => false, 'error' => 'Prestación no encontrada.'], Response::HTTP_NOT_FOUND);
            }

            if (!$branchPayer instanceof BranchPayer) {
                $plan = $this->em->getRepository(InsurancePlan::class)->find($planId);
                $branchPayer = $plan instanceof InsurancePlan ? $plan->getBranchPayer() : null;
            }

            if (!$branchPayer instanceof BranchPayer) {
                return $this->json(['valid' => false, 'error' => 'No fue posible resolver la sucursal-financiador.']);
            }

            $price = $this->em->getRepository(SurgeryFeePrice::class)
                ->createQueryBuilder('sfp')
                ->where('sfp.medicalService = :medicalService')
                ->andWhere('sfp.branchPayer = :branchPayer')
                ->andWhere('sfp.feeType = :feeType')
                ->andWhere('sfp.isActive = true')
                ->andWhere('sfp.effectiveDate <= :today')
                ->andWhere('sfp.expirationDate IS NULL OR sfp.expirationDate >= :today')
                ->setParameter('medicalService', $medicalService)
                ->setParameter('branchPayer', $branchPayer)
                ->setParameter('feeType', 'pabellon')
                ->setParameter('today', $today->format('Y-m-d'))
                ->orderBy('sfp.effectiveDate', 'DESC')
                ->addOrderBy('sfp.id', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if (!$price instanceof SurgeryFeePrice) {
                return $this->json(['valid' => false, 'error' => 'Sin precio de pabellón vigente']);
            }

            return $this->json([
                'valid' => true,
                'name' => $medicalService->getName(),
                'priceIsapre' => (float) $price->getPrice(),
                'itemType' => 'pabellon',
            ]);
        }

        if ($modalidad === 'paquetizada') {
            $item = $this->em->getRepository(SurgeryPackageItem::class)->find($serviceId);
            $plan = $this->em->getRepository(SurgeryPackagePlan::class)->find($planId);

            if (!$item instanceof SurgeryPackageItem || !$plan instanceof SurgeryPackagePlan) {
                return $this->json(['valid' => false, 'error' => 'Ítem paquetizado no encontrado.'], Response::HTTP_NOT_FOUND);
            }

            if (!$branchPayer instanceof BranchPayer) {
                $branchPayer = $plan->getBranchPayer();
            }

            if (!$branchPayer instanceof BranchPayer) {
                return $this->json(['valid' => false, 'error' => 'No fue posible resolver la sucursal-financiador.']);
            }

            $price = $this->em->getRepository(SurgeryPackagePrice::class)
                ->createQueryBuilder('spp')
                ->where('spp.surgeryPackageItem = :item')
                ->andWhere('spp.surgeryPackagePlan = :plan')
                ->andWhere('spp.branchPayer = :branchPayer')
                ->andWhere('spp.isActive = true')
                ->andWhere('spp.effectiveDate <= :today')
                ->andWhere('spp.expirationDate IS NULL OR spp.expirationDate >= :today')
                ->setParameter('item', $item)
                ->setParameter('plan', $plan)
                ->setParameter('branchPayer', $branchPayer)
                ->setParameter('today', $today->format('Y-m-d'))
                ->orderBy('spp.effectiveDate', 'DESC')
                ->addOrderBy('spp.id', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if (!$price instanceof SurgeryPackagePrice) {
                return $this->json(['valid' => false, 'error' => 'Sin precio paquetizado vigente']);
            }

            return $this->json([
                'valid' => true,
                'name' => $item->getName(),
                'priceIsapre' => (float) $price->getPriceIsapre(),
                'itemType' => $item->getItemType(),
            ]);
        }

        return $this->json(['valid' => false, 'error' => 'Modalidad inválida.'], Response::HTTP_BAD_REQUEST);
    }

    #[Route('/services/search', name: 'search_medical_services', methods: ['GET'])]
    public function searchMedicalServices(Request $request): JsonResponse
    {
        $q = trim((string) $request->query->get('q', ''));
        $modalidad = (string) $request->query->get('modalidad', '');

        if (mb_strlen($q) < 2) {
            return $this->json([]);
        }

        if ($modalidad === 'abierta') {
            $rows = $this->em->getRepository(MedicalService::class)
                ->createQueryBuilder('ms')
                ->select('ms.id AS id', 'ms.code AS code', 'ms.name AS name')
                ->where('ms.isActive = true')
                ->andWhere('(LOWER(ms.name) LIKE LOWER(:q) OR LOWER(ms.code) LIKE LOWER(:q))')
                ->setParameter('q', '%' . $q . '%')
                ->orderBy('ms.name', 'ASC')
                ->setMaxResults(15)
                ->getQuery()
                ->getArrayResult();

            return $this->json($rows);
        }

        if ($modalidad === 'paquetizada') {
            $rows = $this->em->getRepository(SurgeryPackageItem::class)
                ->createQueryBuilder('spi')
                ->select('spi.id AS id', 'spi.code AS code', 'spi.name AS name', 'spi.itemType AS itemType')
                ->where('spi.isActive = true')
                ->andWhere('(LOWER(spi.name) LIKE LOWER(:q) OR LOWER(spi.code) LIKE LOWER(:q))')
                ->setParameter('q', '%' . $q . '%')
                ->orderBy('spi.name', 'ASC')
                ->setMaxResults(15)
                ->getQuery()
                ->getArrayResult();

            return $this->json($rows);
        }

        return $this->json([]);
    }

    #[Route('/services/{id}/detail', name: 'service_detail', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getServiceDetail(int $id, Request $request): JsonResponse
    {
        $modalidad = (string) $request->query->get('modalidad', '');

        if ($modalidad === 'abierta') {
            $service = $this->em->getRepository(MedicalService::class)->find($id);
            if (!$service instanceof MedicalService) {
                throw $this->createNotFoundException('Prestación no encontrada.');
            }

            return $this->json([
                'id' => $service->getId(),
                'code' => $service->getCode(),
                'name' => $service->getName(),
            ]);
        }

        if ($modalidad === 'paquetizada') {
            $item = $this->em->getRepository(SurgeryPackageItem::class)->find($id);
            if (!$item instanceof SurgeryPackageItem) {
                throw $this->createNotFoundException('Ítem paquetizado no encontrado.');
            }

            return $this->json([
                'id' => $item->getId(),
                'code' => $item->getCode(),
                'name' => $item->getName(),
                'itemType' => $item->getItemType(),
            ]);
        }

        throw $this->createNotFoundException('Modalidad inválida.');
    }

    #[Route('/preview', name: 'preview', methods: ['POST'])]
    public function preview(Request $request): Response
    {
        try {
            $context = $this->resolveBudgetContext($this->decodeJsonPayload($request), false);
        } catch (\InvalidArgumentException $exception) {
            return $this->render('budget/create/_preview.html.twig', [
                'error' => $exception->getMessage(),
                'previewRows' => [],
                'subtotalByType' => [],
                'totalGeneral' => 0.0,
                'footerText' => null,
                'expiresAt' => null,
                'modalidad' => null,
            ]);
        }

        $pricingRows = $this->calculatePricing($context);
        $footerText = $this->budgetPricingService->calcularPiePagina(
            $context['payer'],
            $context['agreement'],
            $context['branch']
        );

        $preview = $this->buildPreviewData($context['modalidad'], $pricingRows);

        return $this->render('budget/create/_preview.html.twig', [
            'error' => null,
            'previewRows' => $preview['rows'],
            'subtotalByType' => $preview['subtotalByType'],
            'totalGeneral' => $preview['totalGeneral'],
            'footerText' => $footerText,
            'expiresAt' => $context['expiresAt'],
            'modalidad' => $context['modalidad'],
        ]);
    }

    #[Route('/save', name: 'save', methods: ['POST'])]
    public function save(Request $request): JsonResponse
    {
        try {
            $context = $this->resolveBudgetContext($this->decodeJsonPayload($request), true);
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['success' => false, 'error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $pricingRows = $this->calculatePricing($context);
        if ($pricingRows === []) {
            return $this->json(['success' => false, 'error' => 'No hay prestaciones para guardar.'], Response::HTTP_BAD_REQUEST);
        }

        $budget = (new Budget())
            ->setNumber($this->budgetPricingService->calcularNumeroPresupuesto($context['branch']))
            ->setPerson($context['person'])
            ->setMember($context['member'])
            ->setBranch($context['branch'])
            ->setProfessional($context['professional'])
            ->setPayer($context['payer'])
            ->setAgreement($context['agreement'])
            ->setInsurancePlan($context['insurancePlan'])
            ->setCareType($context['careType'])
            ->setOrigin($context['origin'])
            ->setSurgeryPackagePlan($context['surgeryPackagePlan'])
            ->setExpiresAt($context['expiresAt'])
            ->setObservation($context['observation'])
            ->setIncludesHonorariums($context['includesHonorariums'])
            ->setFooterText($this->budgetPricingService->calcularPiePagina(
                $context['payer'],
                $context['agreement'],
                $context['branch']
            ));

        foreach ($this->buildBudgetDetails($context['modalidad'], $pricingRows) as $detail) {
            $budget->addDetail($detail);
        }

        $this->em->persist($budget);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'budgetId' => $budget->getId(),
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{
     *   person: Person,
     *   payer: ?Payer,
     *   agreement: ?Agreement,
     *   insurancePlan: ?InsurancePlan,
     *   surgeryPackagePlan: ?SurgeryPackagePlan,
     *   professional: ?Professional,
     *   careType: ?CareType,
     *   origin: ?Origin,
     *   member: Member,
     *   branchPayer: BranchPayer,
     *   branch: Branch,
     *   modalidad: string,
     *   includesHonorariums: bool,
     *   expiresAt: ?\DateTimeInterface,
     *   observation: ?string,
     *   serviceIds: int[]
     * }
     */
    private function resolveBudgetContext(array $payload, bool $requireServices): array
    {
        $person = $this->personRepository->find((int) ($payload['personId'] ?? 0));
        if (!$person instanceof Person) {
            throw new \InvalidArgumentException('Paciente no encontrado.');
        }

        $payer = $this->resolveEntity(Payer::class, $payload['payerId'] ?? null);
        $agreement = $this->resolveEntity(Agreement::class, $payload['agreementId'] ?? null);
        $insurancePlan = $this->resolveEntity(InsurancePlan::class, $payload['insurancePlanId'] ?? null);
        $surgeryPackagePlan = $this->resolveEntity(SurgeryPackagePlan::class, $payload['surgeryPackagePlanId'] ?? null);
        $professional = $this->resolveEntity(Professional::class, $payload['professionalId'] ?? null);
        $careType = $this->resolveEntity(CareType::class, $payload['careTypeId'] ?? null);
        $origin = $this->resolveEntity(Origin::class, $payload['originId'] ?? null);
        $member = $this->resolveCurrentMember();

        $modalidad = (string) ($payload['modalidad'] ?? '');
        if (!in_array($modalidad, ['abierta', 'paquetizada'], true)) {
            throw new \InvalidArgumentException('Debe seleccionar una modalidad válida.');
        }

        if ($payer !== null && $agreement !== null && $agreement->getPayer()?->getId() !== $payer->getId()) {
            throw new \InvalidArgumentException('El convenio seleccionado no pertenece al financiador.');
        }

        if ($modalidad === 'abierta' && !$insurancePlan instanceof InsurancePlan) {
            throw new \InvalidArgumentException('Debe seleccionar un plan abierto.');
        }

        if ($modalidad === 'paquetizada' && !$surgeryPackagePlan instanceof SurgeryPackagePlan) {
            throw new \InvalidArgumentException('Debe seleccionar un plan paquetizado.');
        }

        $branchPayer = $this->resolveBranchPayer($payer, $insurancePlan, $surgeryPackagePlan);
        if (!$branchPayer instanceof BranchPayer) {
            throw new \InvalidArgumentException('No fue posible resolver la relación sucursal-financiador.');
        }

        $branch = $branchPayer->getBranch() ?? $origin?->getBranch();
        if (!$branch instanceof Branch) {
            throw new \InvalidArgumentException('No fue posible resolver la sucursal del presupuesto.');
        }

        $serviceIds = array_values(array_unique(array_map('intval', $payload['serviceIds'] ?? [])));
        $serviceIds = array_values(array_filter($serviceIds, static fn (int $id): bool => $id > 0));

        if ($requireServices && $serviceIds === []) {
            throw new \InvalidArgumentException('Debe agregar al menos una prestación.');
        }

        $expiresAt = null;
        $expiresAtRaw = $payload['expiresAt'] ?? null;
        if (is_string($expiresAtRaw) && trim($expiresAtRaw) !== '') {
            $expiresAt = \DateTimeImmutable::createFromFormat('Y-m-d', $expiresAtRaw) ?: null;
            if (!$expiresAt instanceof \DateTimeInterface) {
                throw new \InvalidArgumentException('La fecha probable tiene un formato inválido.');
            }
        }

        $observation = isset($payload['observation']) ? trim((string) $payload['observation']) : null;
        if ($observation === '') {
            $observation = null;
        }

        return [
            'person' => $person,
            'payer' => $payer,
            'agreement' => $agreement,
            'insurancePlan' => $insurancePlan,
            'surgeryPackagePlan' => $surgeryPackagePlan,
            'professional' => $professional,
            'careType' => $careType,
            'origin' => $origin,
            'member' => $member,
            'branchPayer' => $branchPayer,
            'branch' => $branch,
            'modalidad' => $modalidad,
            'includesHonorariums' => (bool) ($payload['includesHonorariums'] ?? true),
            'expiresAt' => $expiresAt,
            'observation' => $observation,
            'serviceIds' => $serviceIds,
        ];
    }

    /**
     * @param array{
     *   branchPayer: BranchPayer,
     *   modalidad: string,
     *   includesHonorariums: bool,
     *   insurancePlan: ?InsurancePlan,
     *   surgeryPackagePlan: ?SurgeryPackagePlan,
     *   serviceIds: int[]
     * } $context
     * @return array<int, array<string, mixed>>
     */
    private function calculatePricing(array $context): array
    {
        if ($context['serviceIds'] === []) {
            return [];
        }

        if ($context['modalidad'] === 'abierta' && $context['insurancePlan'] instanceof InsurancePlan) {
            return $this->budgetPricingService->calcularPreciosAbierta(
                $context['serviceIds'],
                $context['insurancePlan'],
                $context['branchPayer'],
                $context['includesHonorariums']
            );
        }

        if ($context['modalidad'] === 'paquetizada' && $context['surgeryPackagePlan'] instanceof SurgeryPackagePlan) {
            return $this->budgetPricingService->calcularPreciosPaquetizada(
                $context['serviceIds'],
                $context['surgeryPackagePlan'],
                $context['branchPayer'],
                $context['includesHonorariums']
            );
        }

        return [];
    }

    /**
     * @param array<int, array<string, mixed>> $pricingRows
     * @return array{rows: array<int, array<string, mixed>>, subtotalByType: array<string, float>, totalGeneral: float}
     */
    private function buildPreviewData(string $modalidad, array $pricingRows): array
    {
        if ($modalidad === 'abierta') {
            $serviceMap = $this->findMedicalServiceNames(array_column($pricingRows, 'medicalServiceId'));
            $subtotalByType = [
                'pabellon' => 0.0,
                'honorario' => 0.0,
                'clinico' => 0.0,
                'valorizable' => 0.0,
            ];

            $rows = array_map(function (array $row) use (&$subtotalByType, $serviceMap): array {
                $subtotalByType['pabellon'] += (float) $row['pabellonAmount'];
                $subtotalByType['honorario'] += (float) $row['honorariumAmount'];
                $subtotalByType['clinico'] += (float) $row['clinicalAmount'];
                $subtotalByType['valorizable'] += (float) $row['valuableAmount'];

                return [
                    'label' => $serviceMap[(int) $row['medicalServiceId']] ?? ('Prestación #' . $row['medicalServiceId']),
                    'itemType' => 'abierta',
                    'pabellonAmount' => (float) $row['pabellonAmount'],
                    'honorariumAmount' => (float) $row['honorariumAmount'],
                    'clinicalAmount' => (float) $row['clinicalAmount'],
                    'valuableAmount' => (float) $row['valuableAmount'],
                    'totalAmount' => (float) $row['totalAmount'],
                    'included' => true,
                ];
            }, $pricingRows);

            return [
                'rows' => $rows,
                'subtotalByType' => $subtotalByType,
                'totalGeneral' => array_sum(array_map(static fn (array $row): float => $row['totalAmount'], $rows)),
            ];
        }

        $itemMap = $this->findSurgeryPackageItems(array_column($pricingRows, 'surgeryPackageItemId'));
        $subtotalByType = [];
        $rows = [];

        foreach ($pricingRows as $row) {
            $itemId = (int) $row['surgeryPackageItemId'];
            $itemType = (string) $row['itemType'];
            $included = (bool) $row['included'];
            $amount = $included ? (float) $row['priceIsapre'] : 0.0;

            $subtotalByType[$itemType] = ($subtotalByType[$itemType] ?? 0.0) + $amount;

            $rows[] = [
                'label' => $itemMap[$itemId]['name'] ?? ('Ítem #' . $itemId),
                'itemType' => $itemType,
                'pabellonAmount' => null,
                'honorariumAmount' => null,
                'clinicalAmount' => null,
                'valuableAmount' => null,
                'totalAmount' => $amount,
                'included' => $included,
            ];
        }

        return [
            'rows' => $rows,
            'subtotalByType' => $subtotalByType,
            'totalGeneral' => array_sum(array_map(static fn (array $row): float => (float) $row['totalAmount'], $rows)),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $pricingRows
     * @return list<BudgetDetail>
     */
    private function buildBudgetDetails(string $modalidad, array $pricingRows): array
    {
        $details = [];

        if ($modalidad === 'abierta') {
            foreach ($pricingRows as $row) {
                $medicalService = $this->em->getRepository(MedicalService::class)->find((int) $row['medicalServiceId']);
                if (!$medicalService instanceof MedicalService) {
                    continue;
                }

                $components = [
                    'pabellon' => (float) $row['pabellonAmount'],
                    'honorario' => (float) $row['honorariumAmount'],
                    'clinico' => (float) $row['clinicalAmount'],
                    'valorizable' => (float) $row['valuableAmount'],
                ];

                foreach ($components as $itemType => $amount) {
                    if ($amount <= 0) {
                        continue;
                    }

                    $details[] = (new BudgetDetail())
                        ->setMedicalService($medicalService)
                        ->setItemType($itemType)
                        ->setQuantity(1)
                        ->setAmount(number_format($amount, 2, '.', ''));
                }
            }

            return $details;
        }

        foreach ($pricingRows as $row) {
            if (!(bool) $row['included']) {
                continue;
            }

            $item = $this->em->getRepository(SurgeryPackageItem::class)->find((int) $row['surgeryPackageItemId']);
            if (!$item instanceof SurgeryPackageItem) {
                continue;
            }

            $details[] = (new BudgetDetail())
                ->setSurgeryPackageItem($item)
                ->setItemType((string) $row['itemType'])
                ->setQuantity(1)
                ->setAmount(number_format((float) $row['priceIsapre'], 2, '.', ''));
        }

        return $details;
    }

    /**
     * @param array<int, int|string> $ids
     * @return array<int, string>
     */
    private function findMedicalServiceNames(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return [];
        }

        $rows = $this->em->getRepository(MedicalService::class)
            ->createQueryBuilder('ms')
            ->select('ms.id AS id', 'ms.name AS name')
            ->where('ms.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getArrayResult();

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row['id']] = (string) $row['name'];
        }

        return $result;
    }

    /**
     * @param array<int, int|string> $ids
     * @return array<int, array{name: string, itemType: string}>
     */
    private function findSurgeryPackageItems(array $ids): array
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if ($ids === []) {
            return [];
        }

        $rows = $this->em->getRepository(SurgeryPackageItem::class)
            ->createQueryBuilder('spi')
            ->select('spi.id AS id', 'spi.name AS name', 'spi.itemType AS itemType')
            ->where('spi.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getArrayResult();

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row['id']] = [
                'name' => (string) $row['name'],
                'itemType' => (string) $row['itemType'],
            ];
        }

        return $result;
    }

    private function resolveBranchPayer(
        ?Payer $payer,
        ?InsurancePlan $insurancePlan,
        ?SurgeryPackagePlan $surgeryPackagePlan
    ): ?BranchPayer {
        if ($insurancePlan?->getBranchPayer() instanceof BranchPayer && $insurancePlan->getBranchPayer()?->isActive()) {
            return $insurancePlan->getBranchPayer();
        }

        if ($surgeryPackagePlan?->getBranchPayer() instanceof BranchPayer && $surgeryPackagePlan->getBranchPayer()?->isActive()) {
            return $surgeryPackagePlan->getBranchPayer();
        }

        if (!$payer instanceof Payer) {
            return null;
        }

        return $this->branchPayerRepository->createQueryBuilder('bp')
            ->where('bp.payer = :payer')
            ->andWhere('bp.isActive = true')
            ->setParameter('payer', $payer)
            ->orderBy('bp.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    private function resolveCurrentMember(): Member
    {
        $user = $this->getUser();
        if (!$user instanceof Member) {
            throw $this->createAccessDeniedException('Usuario no autenticado.');
        }

        return $user;
    }

    private function resolveCurrentBranchId(): ?int
    {
        $member = $this->getUser();
        if (!$member instanceof Member) {
            return null;
        }

        $assignment = $this->em->getRepository(CashierAssignment::class)
            ->findOneBy(
                ['member' => $member, 'isActive' => true],
                ['id' => 'DESC']
            );

        return $assignment?->getCashRegisterLocation()?->getBranch()?->getId();
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJsonPayload(Request $request): array
    {
        $payload = json_decode($request->getContent(), true);
        return is_array($payload) ? $payload : [];
    }

    /**
     * @template T of object
     * @param class-string<T> $className
     * @return T|null
     */
    private function resolveEntity(string $className, mixed $id): ?object
    {
        $resolvedId = (int) $id;
        if ($resolvedId <= 0) {
            return null;
        }

        $entity = $this->em->getRepository($className)->find($resolvedId);
        return is_object($entity) ? $entity : null;
    }
}
