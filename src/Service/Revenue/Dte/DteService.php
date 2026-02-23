<?php

namespace App\Service\Revenue\Dte;

use App\Entity\Tenant\DteDocument;
use App\Entity\Tenant\PaymentAccount;
use App\Entity\Tenant\VoucherEntry;
use App\Repository\Tenant\ClinicalActionPatientRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Psr\Log\LoggerInterface;

/**
 * DteService
 *
 * Emite boletas electrónicas (DTE) vía Aces para un PaymentAccount confirmado.
 *
 * Lógica de separación afecta/exenta:
 *   - BillingItem.taxAffectationType == null        → exenta (tipodte=41)
 *   - TaxAffectationType.name contiene "afect" (ci) → afecta  (tipodte=39)
 *   - cualquier otro valor                           → exenta (tipodte=41)
 *
 * Para tipodte=39 (Afecta):
 *   mnttotal = suma montoitem; mntneto = round(mnttotal / 1.19); iva = mnttotal - mntneto
 * Para tipodte=41 (Exenta):
 *   mnttotal = suma montoitem; mntneto = mnttotal; iva = 0
 *
 * Se llama DESPUÉS del flush() en PaymentConfirmController para que
 * VoucherEntry y ClinicalActionPatient ya estén commitados.
 * Un fallo en este servicio NO revierte el pago.
 */
class DteService
{
    public function __construct(
        private readonly AcesClient                       $acesClient,
        private readonly TenantEntityManager              $em,
        private readonly ClinicalActionPatientRepository  $clinicalActionRepository,
        private readonly LoggerInterface                  $logger,
    ) {}

    // ── API pública ───────────────────────────────────────────────────────────

    /**
     * Emite las boletas DTE correspondientes a un PaymentAccount.
     *
     * Puede generar hasta 2 DteDocuments (uno afecto + uno exento).
     * Cada DteDocument se persiste independientemente; un error en uno
     * no cancela el otro.
     *
     * @return DteDocument[]
     */
    public function emitirBoletas(PaymentAccount $paymentAccount): array
    {
        // ── 1. Obtener prestaciones activas ───────────────────────────────────
        $acciones = $this->clinicalActionRepository->findByPaymentAccount($paymentAccount);

        if (empty($acciones)) {
            $this->logger->info('DTE: PaymentAccount #{id} sin ClinicalActionPatient, no se emite.', [
                'id' => $paymentAccount->getId(),
            ]);
            return [];
        }

        // ── 2. Obtener folio ─────────────────────────────────────────────────
        $voucherEntry = $this->em->getRepository(VoucherEntry::class)
            ->findOneBy(['paymentAccount' => $paymentAccount]);

        $folioNumber = $voucherEntry?->getFolioNumber() ?? 0;

        // ── 3. Separar afectas / exentas ──────────────────────────────────────
        $afectas = [];
        $exentas = [];

        foreach ($acciones as $accion) {
            if ($accion->isCancelled()) {
                continue;
            }
            $taxType = $accion->getBillingItem()?->getTaxAffectationType();
            if ($taxType !== null && stripos((string) $taxType->getName(), 'afect') !== false) {
                $afectas[] = $accion;
            } else {
                $exentas[] = $accion;
            }
        }

        // ── 4. Datos comunes del PaymentAccount ───────────────────────────────
        $patient    = $paymentAccount->getPatient();
        $person     = $patient?->getPerson();
        $subCompany = $paymentAccount->getSubCompany();

        $commonData = [
            'nrocaja'    => $paymentAccount->getCashRegister()?->getId() ?? 0,
            'nrotienda'  => $subCompany?->getId() ?? 0,
            'rutemisor'  => $subCompany?->getTaxId() ?? '',
            'fchemis'    => (new \DateTimeImmutable())->format('Y-m-d'),
            'tasaiva'    => '19.00',
            'rutrecep'   => $person?->getIdentification() ?? '',
            'correorecep'=> $person?->getEmail() ?? '',
            'dirrecep'   => '',
            'cmnarecep'  => '',
            'rznsocrecep'=> trim(($person?->getName() ?? '') . ' ' . ($person?->getLastName() ?? '')),
        ];

        // ── 5. Emitir boletas ─────────────────────────────────────────────────
        $documents = [];

        if (!empty($afectas)) {
            $documents[] = $this->emitirGrupo('39', $afectas, $folioNumber, $commonData, $paymentAccount);
        }

        if (!empty($exentas)) {
            $documents[] = $this->emitirGrupo('41', $exentas, $folioNumber, $commonData, $paymentAccount);
        }

        return array_filter($documents);
    }

    // ── Helpers privados ──────────────────────────────────────────────────────

    /**
     * Construye y envía la boleta para un grupo (afecta o exenta).
     * Persiste el DteDocument resultante.
     */
    private function emitirGrupo(
        string        $tipodte,
        array         $acciones,
        int           $folioNumber,
        array         $commonData,
        PaymentAccount $paymentAccount,
    ): DteDocument {
        $detalle   = $this->buildDetalle($acciones);
        $mnttotal  = $this->sumaMontoitem($detalle);
        [$mntneto, $iva] = $this->calcularIva($tipodte, $mnttotal);

        $businessData = array_merge($commonData, [
            'tipodte'  => $tipodte,
            'folio'    => $folioNumber,
            'mnttotal' => (string) $mnttotal,
            'mntneto'  => (string) $mntneto,
            'iva'      => (string) $iva,
            'Detalle'  => $detalle,
        ]);

        $dte = new DteDocument();
        $dte->setPaymentAccount($paymentAccount);
        $dte->setTipodte($tipodte);
        $dte->setFolioNumber($folioNumber);

        try {
            $response = $this->acesClient->send($businessData);

            $responseArray = json_decode(json_encode($response), true);
            $dte->setAcesResponse($responseArray);
            $dte->setSentAt(new \DateTimeImmutable());
            $dte->setStatus('sent');

        } catch (AcesException $e) {
            $this->logger->error('DTE error al emitir tipodte={tipo} folio={folio}: {msg}', [
                'tipo'  => $tipodte,
                'folio' => $folioNumber,
                'msg'   => $e->getMessage(),
            ]);
            $dte->setStatus('error');
            $dte->setRetryData($businessData);
        }

        $this->em->persist($dte);
        $this->em->flush();

        return $dte;
    }

    /**
     * Construye el array Detalle para el payload Aces.
     *
     * @param \App\Entity\Tenant\ClinicalActionPatient[] $acciones
     * @return array<int, array<string, mixed>>
     */
    private function buildDetalle(array $acciones): array
    {
        $detalle = [];

        foreach ($acciones as $accion) {
            $unitPrice      = (float) $accion->getUnitPrice();
            $quantity       = $accion->getQuantity();
            $totalAmount    = (float) $accion->getTotalAmount();
            $discountAmount = (float) $accion->getDiscountAmount();

            $brutoLinea   = $unitPrice * $quantity;
            $descuentoPct = ($brutoLinea > 0 && $discountAmount > 0)
                ? round($discountAmount / $brutoLinea * 100, 2)
                : 0;

            $detalle[] = [
                'nmbitem'       => $accion->getBillingItem()?->getName() ?? 'Prestación',
                'qtyitem'       => $quantity,
                'prcitem'       => (string) $unitPrice,
                'montoitem'     => (string) $totalAmount,
                'descuentomonto'=> (string) $discountAmount,
                'descuentopct'  => (string) $descuentoPct,
            ];
        }

        return $detalle;
    }

    /** Suma montoitem de todas las líneas del detalle. */
    private function sumaMontoitem(array $detalle): int
    {
        $total = 0.0;
        foreach ($detalle as $linea) {
            $total += (float) ($linea['montoitem'] ?? 0);
        }
        return (int) round($total);
    }

    /**
     * Calcula mntneto e iva según tipodte.
     *
     * @return array{0: int, 1: int} [mntneto, iva]
     */
    private function calcularIva(string $tipodte, int $mnttotal): array
    {
        if ($tipodte === '39') {
            $mntneto = (int) round($mnttotal / 1.19);
            $iva     = $mnttotal - $mntneto;
            return [$mntneto, $iva];
        }

        // Exenta (41): sin IVA
        return [$mnttotal, 0];
    }
}
