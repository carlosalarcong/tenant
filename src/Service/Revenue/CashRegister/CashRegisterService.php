<?php

namespace App\Service\Revenue\CashRegister;

use App\Entity\Tenant\CashRegister;
use App\Entity\Tenant\CashRegisterDetail;
use App\Entity\Tenant\CashRegisterLocation;
use App\Entity\Tenant\Member;
use App\Entity\Tenant\PaymentMethod;
use App\Entity\Tenant\Bank;
use App\Repository\Tenant\CashRegisterRepository;
use App\Repository\Tenant\VoucherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;

/**
 * CashRegisterService
 *
 * Encapsula las operaciones de ciclo de vida de una caja:
 * - Apertura de caja para un cajero
 * - Validación del estado operativo (abierta/cerrada/sin_cerrar/sin_talonario)
 * - Cierre de caja con registro de detalle por forma de pago
 *
 * Legacy: GestionCaja/GestionCajaController + ValidacionComplementariaCaja
 */
class CashRegisterService
{
    public function __construct(
        private readonly TenantEntityManager $em,
        private readonly CashRegisterRepository $cashRegisterRepository,
        private readonly VoucherRepository $voucherRepository,
    ) {}

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Abre una nueva caja para el cajero en la ubicación dada.
     *
     * Precondición: el llamador debe haber validado que no existe una caja
     * ya abierta hoy para este usuario (usar validateOperatingStatus primero).
     *
     * Legacy: gestionAbrirCajaAction
     */
    public function openForUser(Member $member, CashRegisterLocation $location): CashRegister
    {
        $cashRegister = new CashRegister();
        $cashRegister->setMember($member);
        $cashRegister->setCashRegisterLocation($location);
        $cashRegister->setBranch($location->getBranch());
        $cashRegister->setOpenedAt(new \DateTime());
        $cashRegister->setStatus('abierta');

        $this->em->persist($cashRegister);
        $this->em->flush();

        return $cashRegister;
    }

    /**
     * Evalúa el estado operativo de la caja para un cajero.
     *
     * Retorna uno de los siguientes códigos:
     *   'open'       — hay una caja abierta hoy y con talonario con folios disponibles
     *   'no_voucher' — hay una caja abierta hoy pero sin talonario activo con folios
     *   'unclosed'   — existe una caja de días anteriores aún sin cerrar (error operativo)
     *   'closed'     — no existe ninguna caja abierta para el cajero
     *
     * Legacy: ValidacionComplementariaCaja (~150 líneas)
     */
    public function validateOperatingStatus(Member $member): string
    {
        $openRegister = $this->findOpenRegisterForMember($member);

        if ($openRegister === null) {
            return 'closed';
        }

        // ¿La caja abierta corresponde a hoy?
        $openedDate = $openRegister->getOpenedAt()?->format('Y-m-d');
        $today      = (new \DateTime())->format('Y-m-d');

        if ($openedDate !== $today) {
            // Existe una caja abierta de un día anterior → error operativo
            return 'unclosed';
        }

        // Verificar que exista al menos un talonario con folios disponibles
        if (!$this->hasAvailableVoucher($openRegister->getCashRegisterLocation())) {
            return 'no_voucher';
        }

        return 'open';
    }

    /**
     * Cierra la caja: persiste el detalle por forma de pago, calcula
     * surplus/deficit y marca la caja como cerrada.
     *
     * @param array<int, array{
     *     payment_method_id: int,
     *     amount: string,
     *     bank_id?: int|null,
     *     deposit_number?: string|null
     * }> $detailData  Datos del formulario de cierre, uno por forma de pago.
     *
     * Legacy: gestionCerrarCajaCerradoAction
     */
    public function closeRegister(CashRegister $cashRegister, array $detailData): void
    {
        $realTotal = '0.00';

        foreach ($detailData as $row) {
            $detail = $this->buildDetail($cashRegister, $row);
            $this->em->persist($detail);

            $realTotal = $this->bcAdd($realTotal, $detail->getAmount());
        }

        $expectedTotal = $this->calculateExpectedTotal($cashRegister);

        $cashRegister->setRealAmount($realTotal);
        $cashRegister->setSurplus($this->calculateSurplus($realTotal, $expectedTotal));
        $cashRegister->setDeficit($this->calculateDeficit($realTotal, $expectedTotal));
        $cashRegister->setClosedAt(new \DateTime());
        $cashRegister->setStatus('cerrada');

        $this->em->flush();
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Busca la caja abierta (status='abierta') del cajero, si existe.
     * No filtra por fecha — devuelve la primera abierta independientemente del día.
     */
    private function findOpenRegisterForMember(Member $member): ?CashRegister
    {
        return $this->cashRegisterRepository
            ->createQueryBuilder('cr')
            ->where('cr.member = :member')
            ->andWhere('cr.status = :status')
            ->setParameter('member', $member)
            ->setParameter('status', 'abierta')
            ->orderBy('cr.openedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Verifica que la ubicación tenga al menos un Voucher activo con folios disponibles.
     */
    private function hasAvailableVoucher(CashRegisterLocation $location): bool
    {
        $count = $this->voucherRepository
            ->createQueryBuilder('v')
            ->select('COUNT(v.id)')
            ->where('v.cashRegisterLocation = :location')
            ->andWhere('v.isActive = true')
            ->andWhere('v.currentFolio <= v.folioTo')
            ->setParameter('location', $location)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }

    /**
     * Construye una entidad CashRegisterDetail a partir de una fila del formulario.
     * Resuelve las referencias por ID dentro del tenant EM.
     *
     * @param array{
     *     payment_method_id: int,
     *     amount: string,
     *     bank_id?: int|null,
     *     deposit_number?: string|null
     * } $row
     */
    private function buildDetail(CashRegister $cashRegister, array $row): CashRegisterDetail
    {
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $this->em->getReference(PaymentMethod::class, $row['payment_method_id']);

        $bank = null;
        if (!empty($row['bank_id'])) {
            /** @var Bank $bank */
            $bank = $this->em->getReference(Bank::class, $row['bank_id']);
        }

        $detail = new CashRegisterDetail();
        $detail->setCashRegister($cashRegister);
        $detail->setPaymentMethod($paymentMethod);
        $detail->setBank($bank);
        $detail->setAmount($row['amount']);
        $detail->setDepositNumber($row['deposit_number'] ?? null);

        return $detail;
    }

    /**
     * Calcula el total esperado del sistema para la sesión de caja.
     *
     * TODO (Fase C): consultar payment_account_detail.amount sumado por los pagos
     * emitidos desde esta ubicación de caja entre openedAt y now.
     * Por ahora retorna '0.00' hasta que el flujo de pago esté implementado.
     */
    private function calculateExpectedTotal(CashRegister $cashRegister): string
    {
        return '0.00';
    }

    /**
     * Calcula el superávit: monto real mayor al esperado → diferencia positiva.
     * Retorna '0.00' si no hay superávit.
     */
    private function calculateSurplus(string $real, string $expected): string
    {
        $diff = $this->bcSub($real, $expected);
        return bccomp($diff, '0.00', 2) > 0 ? $diff : '0.00';
    }

    /**
     * Calcula el déficit: monto real menor al esperado → diferencia negativa expresada positiva.
     * Retorna '0.00' si no hay déficit.
     */
    private function calculateDeficit(string $real, string $expected): string
    {
        $diff = $this->bcSub($expected, $real);
        return bccomp($diff, '0.00', 2) > 0 ? $diff : '0.00';
    }

    private function bcAdd(string $a, string $b): string
    {
        return bcadd($a, $b, 2);
    }

    private function bcSub(string $a, string $b): string
    {
        return bcsub($a, $b, 2);
    }
}
