<?php

namespace App\Service\Budget;

use App\Entity\Tenant\Agreement;
use App\Entity\Tenant\Branch;
use App\Entity\Tenant\BranchPayer;
use App\Entity\Tenant\Budget;
use App\Entity\Tenant\BudgetFooter;
use App\Entity\Tenant\BudgetFooterByFunder;
use App\Entity\Tenant\InsurancePlan;
use App\Entity\Tenant\OpenPlanDistribution;
use App\Entity\Tenant\Payer;
use App\Entity\Tenant\SurgeryFeePrice;
use App\Entity\Tenant\SurgeryPackageItem;
use App\Entity\Tenant\SurgeryPackagePlan;
use App\Entity\Tenant\SurgeryPackagePrice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * BudgetPricingService
 *
 * Encapsula la resolución de precios y textos auxiliares del módulo Presupuesto:
 * - valorización abierta por prestación
 * - valorización paquetizada por ítem
 * - fallback de pie de página
 * - correlativo de presupuesto por sucursal
 */
class BudgetPricingService
{
    public function __construct(
        #[Autowire(service: 'doctrine.orm.tenant_entity_manager')]
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * @param int[] $medicalServiceIds
     * @return array<int, array{
     *   medicalServiceId: int,
     *   pabellonAmount: float,
     *   honorariumAmount: float,
     *   clinicalAmount: float,
     *   valuableAmount: float,
     *   totalAmount: float
     * }>
     */
    public function calcularPreciosAbierta(
        array $medicalServiceIds,
        InsurancePlan $plan,
        BranchPayer $branchPayer,
        bool $includesHonorariums = true
    ): array {
        $medicalServiceIds = $this->normalizeIds($medicalServiceIds);
        if ($medicalServiceIds === []) {
            return [];
        }

        $today = new \DateTimeImmutable('today');

        $pabellonPrices = $this->findCurrentSurgeryFeePrices($medicalServiceIds, $branchPayer, $today);
        $distributions = $this->findCurrentOpenPlanDistributions($medicalServiceIds, $plan, $branchPayer, $today);

        $rows = [];
        foreach ($medicalServiceIds as $medicalServiceId) {
            $pabellonAmount = isset($pabellonPrices[$medicalServiceId])
                ? $this->toFloat($pabellonPrices[$medicalServiceId]->getPrice())
                : 0.0;

            $distribution = $distributions[$medicalServiceId] ?? null;
            $honorariumAmount = $distribution ? $this->toFloat($distribution->getHonorariumAmount()) : 0.0;
            $clinicalAmount = $distribution ? $this->toFloat($distribution->getClinicalAmount()) : 0.0;
            $valuableAmount = $distribution ? $this->toFloat($distribution->getValuableAmount()) : 0.0;

            if (!$includesHonorariums) {
                $honorariumAmount = 0.0;
            }

            $rows[] = [
                'medicalServiceId' => $medicalServiceId,
                'basePabellonAmount' => $pabellonAmount,
                'honorariumAmount' => $honorariumAmount,
                'clinicalAmount' => $clinicalAmount,
                'valuableAmount' => $valuableAmount,
            ];
        }

        usort(
            $rows,
            static fn (array $a, array $b): int => $b['basePabellonAmount'] <=> $a['basePabellonAmount']
        );

        $result = [];
        foreach ($rows as $index => $row) {
            $factor = $index === 0 ? 1.0 : 0.5;
            $pabellonAmount = $row['basePabellonAmount'] * $factor;
            $totalAmount = $pabellonAmount
                + $row['honorariumAmount']
                + $row['clinicalAmount']
                + $row['valuableAmount'];

            $result[] = [
                'medicalServiceId' => $row['medicalServiceId'],
                'pabellonAmount' => $pabellonAmount,
                'honorariumAmount' => $row['honorariumAmount'],
                'clinicalAmount' => $row['clinicalAmount'],
                'valuableAmount' => $row['valuableAmount'],
                'totalAmount' => $totalAmount,
            ];
        }

        return $result;
    }

    /**
     * @param int[] $surgeryPackageItemIds
     * @return array<int, array{
     *   surgeryPackageItemId: int,
     *   itemType: string,
     *   priceIsapre: float,
     *   priceFonasa: float,
     *   included: bool
     * }>
     */
    public function calcularPreciosPaquetizada(
        array $surgeryPackageItemIds,
        SurgeryPackagePlan $plan,
        BranchPayer $branchPayer,
        bool $includesHonorariums = true
    ): array {
        $surgeryPackageItemIds = $this->normalizeIds($surgeryPackageItemIds);
        if ($surgeryPackageItemIds === []) {
            return [];
        }

        $today = new \DateTimeImmutable('today');

        $items = $this->findSurgeryPackageItems($surgeryPackageItemIds);
        $prices = $this->findCurrentSurgeryPackagePrices($surgeryPackageItemIds, $plan, $branchPayer, $today);

        $rows = [];
        foreach ($surgeryPackageItemIds as $itemId) {
            $item = $items[$itemId] ?? null;
            $price = $prices[$itemId] ?? null;

            $rows[] = [
                'surgeryPackageItemId' => $itemId,
                'itemType' => $item?->getItemType() ?? '',
                'basePriceIsapre' => $price ? $this->toFloat($price->getPriceIsapre()) : 0.0,
                'basePriceFonasa' => $price ? $this->toFloat($price->getPriceFonasa()) : 0.0,
            ];
        }

        usort(
            $rows,
            static fn (array $a, array $b): int => $b['basePriceIsapre'] <=> $a['basePriceIsapre']
        );

        $result = [];
        foreach ($rows as $index => $row) {
            $factor = $index === 0 ? 1.0 : 0.5;
            $included = $includesHonorariums || $row['itemType'] !== 'honorario';

            $result[] = [
                'surgeryPackageItemId' => $row['surgeryPackageItemId'],
                'itemType' => $row['itemType'],
                'priceIsapre' => $row['basePriceIsapre'] * $factor,
                'priceFonasa' => $row['basePriceFonasa'] * $factor,
                'included' => $included,
            ];
        }

        return $result;
    }

    public function calcularPiePagina(
        ?Payer $payer,
        ?Agreement $agreement,
        Branch $branch
    ): ?string {
        // Reservado para una futura capa de fallback por sucursal.
        unset($branch);

        $agreementPayer = $agreement?->getPayer();
        if ($agreementPayer !== null) {
            $detail = $this->findBudgetFooterByPayer($agreementPayer)?->getDetail();
            if ($detail !== null && $detail !== '') {
                return $detail;
            }
        }

        if ($payer !== null) {
            $detail = $this->findBudgetFooterByPayer($payer)?->getDetail();
            if ($detail !== null && $detail !== '') {
                return $detail;
            }
        }

        $footer = $this->em->getRepository(BudgetFooter::class)
            ->createQueryBuilder('bf')
            ->where('bf.isActive = true')
            ->orderBy('bf.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $footer?->getDetail();
    }

    public function calcularNumeroPresupuesto(Branch $branch): int
    {
        $nextNumber = $this->em->createQuery(
            'SELECT COALESCE(MAX(b.number), 0) + 1 FROM ' . Budget::class . ' b WHERE b.branch = :branch'
        )
            ->setParameter('branch', $branch)
            ->getSingleScalarResult();

        return (int) $nextNumber;
    }

    /**
     * @param int[] $medicalServiceIds
     * @return array<int, SurgeryFeePrice>
     */
    private function findCurrentSurgeryFeePrices(
        array $medicalServiceIds,
        BranchPayer $branchPayer,
        \DateTimeImmutable $today
    ): array {
        $rows = $this->em->getRepository(SurgeryFeePrice::class)
            ->createQueryBuilder('sfp')
            ->innerJoin('sfp.medicalService', 'ms')
            ->addSelect('ms')
            ->where('ms.id IN (:medicalServiceIds)')
            ->andWhere('sfp.branchPayer = :branchPayer')
            ->andWhere('sfp.feeType = :feeType')
            ->andWhere('sfp.isActive = true')
            ->andWhere('sfp.effectiveDate <= :today')
            ->andWhere('sfp.expirationDate IS NULL OR sfp.expirationDate >= :today')
            ->setParameter('medicalServiceIds', $medicalServiceIds)
            ->setParameter('branchPayer', $branchPayer)
            ->setParameter('feeType', 'pabellon')
            ->setParameter('today', $today->format('Y-m-d'))
            ->orderBy('ms.id', 'ASC')
            ->addOrderBy('sfp.effectiveDate', 'DESC')
            ->addOrderBy('sfp.id', 'DESC')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($rows as $row) {
            $medicalServiceId = $row->getMedicalService()?->getId();
            if ($medicalServiceId === null || isset($result[$medicalServiceId])) {
                continue;
            }

            $result[$medicalServiceId] = $row;
        }

        return $result;
    }

    /**
     * @param int[] $medicalServiceIds
     * @return array<int, OpenPlanDistribution>
     */
    private function findCurrentOpenPlanDistributions(
        array $medicalServiceIds,
        InsurancePlan $plan,
        BranchPayer $branchPayer,
        \DateTimeImmutable $today
    ): array {
        $rows = $this->em->getRepository(OpenPlanDistribution::class)
            ->createQueryBuilder('opd')
            ->innerJoin('opd.medicalService', 'ms')
            ->addSelect('ms')
            ->where('ms.id IN (:medicalServiceIds)')
            ->andWhere('opd.insurancePlan = :plan')
            ->andWhere('opd.branchPayer = :branchPayer')
            ->andWhere('opd.isActive = true')
            ->andWhere('opd.effectiveDate <= :today')
            ->andWhere('opd.expirationDate IS NULL OR opd.expirationDate >= :today')
            ->setParameter('medicalServiceIds', $medicalServiceIds)
            ->setParameter('plan', $plan)
            ->setParameter('branchPayer', $branchPayer)
            ->setParameter('today', $today->format('Y-m-d'))
            ->orderBy('ms.id', 'ASC')
            ->addOrderBy('opd.effectiveDate', 'DESC')
            ->addOrderBy('opd.id', 'DESC')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($rows as $row) {
            $medicalServiceId = $row->getMedicalService()?->getId();
            if ($medicalServiceId === null || isset($result[$medicalServiceId])) {
                continue;
            }

            $result[$medicalServiceId] = $row;
        }

        return $result;
    }

    /**
     * @param int[] $surgeryPackageItemIds
     * @return array<int, SurgeryPackageItem>
     */
    private function findSurgeryPackageItems(array $surgeryPackageItemIds): array
    {
        $rows = $this->em->getRepository(SurgeryPackageItem::class)
            ->createQueryBuilder('spi')
            ->where('spi.id IN (:ids)')
            ->setParameter('ids', $surgeryPackageItemIds)
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($rows as $row) {
            if ($row->getId() !== null) {
                $result[$row->getId()] = $row;
            }
        }

        return $result;
    }

    /**
     * @param int[] $surgeryPackageItemIds
     * @return array<int, SurgeryPackagePrice>
     */
    private function findCurrentSurgeryPackagePrices(
        array $surgeryPackageItemIds,
        SurgeryPackagePlan $plan,
        BranchPayer $branchPayer,
        \DateTimeImmutable $today
    ): array {
        $rows = $this->em->getRepository(SurgeryPackagePrice::class)
            ->createQueryBuilder('spp')
            ->innerJoin('spp.surgeryPackageItem', 'spi')
            ->addSelect('spi')
            ->where('spi.id IN (:itemIds)')
            ->andWhere('spp.surgeryPackagePlan = :plan')
            ->andWhere('spp.branchPayer = :branchPayer')
            ->andWhere('spp.isActive = true')
            ->andWhere('spp.effectiveDate <= :today')
            ->andWhere('spp.expirationDate IS NULL OR spp.expirationDate >= :today')
            ->setParameter('itemIds', $surgeryPackageItemIds)
            ->setParameter('plan', $plan)
            ->setParameter('branchPayer', $branchPayer)
            ->setParameter('today', $today->format('Y-m-d'))
            ->orderBy('spi.id', 'ASC')
            ->addOrderBy('spp.effectiveDate', 'DESC')
            ->addOrderBy('spp.id', 'DESC')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($rows as $row) {
            $itemId = $row->getSurgeryPackageItem()?->getId();
            if ($itemId === null || isset($result[$itemId])) {
                continue;
            }

            $result[$itemId] = $row;
        }

        return $result;
    }

    private function findBudgetFooterByPayer(Payer $payer): ?BudgetFooterByFunder
    {
        return $this->em->getRepository(BudgetFooterByFunder::class)
            ->createQueryBuilder('bff')
            ->where('bff.isActive = true')
            ->andWhere('bff.payer = :payer')
            ->setParameter('payer', $payer)
            ->orderBy('bff.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int[] $ids
     * @return int[]
     */
    private function normalizeIds(array $ids): array
    {
        $normalized = [];

        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $normalized[$id] = $id;
            }
        }

        return array_values($normalized);
    }

    private function toFloat(?string $value): float
    {
        return $value !== null ? (float) $value : 0.0;
    }
}
