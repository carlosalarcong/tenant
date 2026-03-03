<?php

namespace App\Service\Budget;

use App\Entity\Tenant\BranchPayer;
use App\Entity\Tenant\InsurancePlan;
use App\Entity\Tenant\MedicalService;
use App\Entity\Tenant\SurgeryPackageItem;
use App\Entity\Tenant\SurgeryPackagePlan;
use App\Repository\Tenant\BudgetInsurancePlanPriceRepository;
use App\Repository\Tenant\BudgetRepository;
use App\Repository\Tenant\BudgetSurgeryFeePriceRepository;
use App\Repository\Tenant\SurgeryPackageItemPriceRepository;

class BudgetPricingService
{
    public function __construct(
        private BudgetInsurancePlanPriceRepository $insurancePlanPriceRepo,
        private BudgetSurgeryFeePriceRepository $surgeryFeePriceRepo,
        private SurgeryPackageItemPriceRepository $packageItemPriceRepo,
        private BudgetRepository $budgetRepository,
    ) {}

    /**
     * Calcula precios para modalidad abierta (prestaciones individuales).
     * Aplica regla 50/100%: el servicio con mayor valor pabellón paga 100%, el resto 50%.
     *
     * @param MedicalService[] $medicalServices
     */
    public function calcularPreciosAbierta(
        array $medicalServices,
        InsurancePlan $plan,
        ?BranchPayer $branchPayer
    ): array {
        // Paso 1: obtener theatreAmount para cada servicio
        $rows = [];
        foreach ($medicalServices as $service) {
            $feePrice = $this->surgeryFeePriceRepo->findActivePrice($service, $branchPayer);
            $rows[] = [
                'medicalService'   => $service,
                'rawTheatreAmount' => $feePrice ? (float) $feePrice->getTheatreAmount() : 0.0,
            ];
        }

        // Paso 2: ordenar de mayor a menor por rawTheatreAmount
        usort($rows, fn($a, $b) => $b['rawTheatreAmount'] <=> $a['rawTheatreAmount']);

        // Paso 3: determinar si todos tienen theatreAmount = 0
        $allZero = array_sum(array_column($rows, 'rawTheatreAmount')) === 0.0;

        // Paso 4: asignar rates y buscar precios de plan
        $result = [];
        foreach ($rows as $i => $row) {
            $service = $row['medicalService'];
            $raw     = $row['rawTheatreAmount'];

            $rate = ($allZero || $i === 0) ? 1.0 : 0.5;

            $planPrice = $this->insurancePlanPriceRepo->findActivePrice($service, $plan, $branchPayer);

            $result[] = [
                'medicalService'   => $service,
                'unitPrice'        => $planPrice ? (float) $planPrice->getUnitPrice() : 0.0,
                'copayAmount'      => $planPrice ? (float) $planPrice->getCopayAmount() : 0.0,
                'rawTheatreAmount' => $raw,
                'theatreRate'      => $rate,
                'theatreAmount'    => $raw * $rate,
            ];
        }

        return $result;
    }

    /**
     * Calcula precios para modalidad paquetizada.
     * Aplica regla 50/100%: el ítem con mayor priceIsapre paga 100%, el resto 50%.
     * Si !$includesHonorariums, los ítems de tipo 'honorario' se excluyen (rate = 0.0).
     *
     * @param SurgeryPackageItem[] $packageItems
     */
    public function calcularPreciosPaquetizada(
        array $packageItems,
        SurgeryPackagePlan $plan,
        ?BranchPayer $branchPayer,
        bool $includesHonorariums
    ): array {
        // Paso 1: obtener precios y clasificar participantes
        $rows = [];
        foreach ($packageItems as $item) {
            $price = $this->packageItemPriceRepo->findActivePrice($item, $plan, $branchPayer);
            $rows[] = [
                'surgeryPackageItem' => $item,
                'itemType'           => $item->getItemType(),
                'rawPriceIsapre'     => $price ? (float) $price->getPriceIsapre() : 0.0,
                'priceFonasa'        => $price ? ($price->getPriceFonasa() !== null ? (float) $price->getPriceFonasa() : null) : null,
                'participates'       => $includesHonorariums || $item->getItemType() !== 'honorario',
            ];
        }

        // Paso 2: separar participantes y excluidos, ordenar participantes de mayor a menor
        $participants = array_filter($rows, fn($r) => $r['participates']);
        usort($participants, fn($a, $b) => $b['rawPriceIsapre'] <=> $a['rawPriceIsapre']);

        $allZero = array_sum(array_column($participants, 'rawPriceIsapre')) === 0.0;

        // Asignar rate a participantes
        $rateMap = [];
        foreach (array_values($participants) as $i => $row) {
            $key           = spl_object_id($row['surgeryPackageItem']);
            $rateMap[$key] = ($allZero || $i === 0) ? 1.0 : 0.5;
        }

        // Paso 3: construir resultado en orden original
        $result = [];
        foreach ($rows as $row) {
            $key  = spl_object_id($row['surgeryPackageItem']);
            $rate = $row['participates'] ? ($rateMap[$key] ?? 1.0) : 0.0;
            $raw  = $row['rawPriceIsapre'];

            $result[] = [
                'surgeryPackageItem' => $row['surgeryPackageItem'],
                'itemType'           => $row['itemType'],
                'rawPriceIsapre'     => $raw,
                'priceRate'          => $rate,
                'priceIsapre'        => $raw * $rate,
                'priceFonasa'        => $row['priceFonasa'],
            ];
        }

        return $result;
    }

    /**
     * Retorna el próximo número de presupuesto disponible.
     */
    public function calcularNumeroPresupuesto(): int
    {
        return $this->budgetRepository->getNextNumber();
    }
}
