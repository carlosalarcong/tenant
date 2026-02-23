<?php

namespace App\Service\Revenue\CashRegister;

use App\Entity\Tenant\CashRegisterLocation;
use App\Entity\Tenant\Member;
use App\Entity\Tenant\PaymentAccount;
use App\Entity\Tenant\VoucherEntry;
use App\Repository\Tenant\VoucherRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;

/**
 * VoucherService
 *
 * Gestiona el ciclo de vida de talonarios (folios/boletas) en el flujo de caja.
 *
 * - hasAvailableVoucher: consulta rápida (sin lock) para la validación previa.
 * - consumeNextFolio: asigna el siguiente folio con lock pesimista para garantizar
 *   unicidad incluso bajo carga concurrente. DEBE llamarse dentro de una transacción.
 *
 * Legacy: TalonarioService / lógica dispersa en GestionCajaController
 */
class VoucherService
{
    public function __construct(
        private readonly TenantEntityManager  $em,
        private readonly VoucherRepository    $voucherRepository,
    ) {}

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Indica si existe un talonario activo con folios disponibles para la caja.
     * Consulta sin lock — usar solo para validaciones previas, no dentro del
     * flujo transaccional de consumo.
     */
    public function hasAvailableVoucher(CashRegisterLocation $location): bool
    {
        return $this->voucherRepository->findActiveByLocation($location) !== null;
    }

    /**
     * Consume el siguiente folio del talonario activo para la caja dada y
     * registra el uso en un VoucherEntry vinculado al pago.
     *
     * Operaciones:
     *   1. Obtiene el Voucher activo con PESSIMISTIC_WRITE (requiere transacción).
     *   2. Lee el folio actual y lo asigna al VoucherEntry.
     *   3. Incrementa currentFolio en el Voucher.
     *   4. Si se agotaron los folios, desactiva el talonario.
     *   5. Persiste el VoucherEntry (el Voucher ya está managed; sus cambios se
     *      flush junto al resto de la transacción).
     *
     * @throws \RuntimeException si no hay talonario disponible.
     */
    public function consumeNextFolio(
        CashRegisterLocation $location,
        PaymentAccount        $paymentAccount,
        Member                $member,
    ): VoucherEntry {
        $voucher = $this->voucherRepository->findActiveByLocation($location);

        if ($voucher === null || !$voucher->hasAvailableFolios()) {
            throw new \RuntimeException(sprintf(
                'No hay talonario activo con folios disponibles para la caja "%s".',
                $location->getName()
            ));
        }

        $folioNumber = $voucher->getCurrentFolio();

        // Avanzar el puntero; si se agota el rango, desactivar el talonario
        $voucher->setCurrentFolio($folioNumber + 1);
        if ($voucher->getCurrentFolio() > $voucher->getFolioTo()) {
            $voucher->setIsActive(false);
        }

        $entry = new VoucherEntry();
        $entry->setVoucher($voucher)
              ->setPaymentAccount($paymentAccount)
              ->setMember($member)
              ->setFolioNumber($folioNumber)
              ->setIssuedAt(new \DateTime());

        $this->em->persist($entry);

        return $entry;
    }
}
