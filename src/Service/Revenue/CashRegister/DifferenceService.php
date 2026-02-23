<?php

namespace App\Service\Revenue\CashRegister;

use App\Entity\Tenant\Difference;
use App\Entity\Tenant\Member;
use App\Entity\Tenant\PatientAccount;
use App\Repository\Tenant\DifferenceDirectionRepository;
use App\Repository\Tenant\DifferenceReasonRepository;
use App\Repository\Tenant\DifferenceTypeRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;

/**
 * DifferenceService
 *
 * Gestiona el ciclo de vida de las solicitudes de diferencia (descuentos con
 * o sin autorización de supervisor).
 *
 * Ciclo de vida:
 *   solicitada → autorizada | auto_aprobada | rechazada | anulada
 */
class DifferenceService
{
    public function __construct(
        private readonly TenantEntityManager          $em,
        private readonly DifferenceTypeRepository     $differenceTypeRepository,
        private readonly DifferenceReasonRepository   $differenceReasonRepository,
        private readonly DifferenceDirectionRepository $differenceDirectionRepository,
    ) {}

    // ── Solicitud ─────────────────────────────────────────────────────────────

    /**
     * Crea una nueva solicitud de diferencia con estado 'solicitada'.
     *
     * @param array{
     *   difference_type_id?: int|string,
     *   difference_reason_id?: int|string,
     *   difference_direction_id?: int|string,
     *   total_account: string,
     *   total_discount: string,
     *   total_after_discount: string,
     * } $data
     */
    public function requestDiscount(
        Member $requestedBy,
        array $data,
        ?PatientAccount $patientAccount = null,
    ): Difference {
        $difference = new Difference();
        $difference->setRequestedByMember($requestedBy);
        $difference->setRequestedAt(new \DateTime());
        $difference->setStatus('solicitada');
        $difference->setTotalAccount($this->toDecimal($data['total_account'] ?? '0'));
        $difference->setTotalDiscount($this->toDecimal($data['total_discount'] ?? '0'));
        $difference->setTotalAfterDiscount($this->toDecimal($data['total_after_discount'] ?? '0'));

        if (!empty($data['difference_type_id'])) {
            $type = $this->differenceTypeRepository->find((int) $data['difference_type_id']);
            if ($type !== null) {
                $difference->setDifferenceType($type);
            }
        }

        if (!empty($data['difference_reason_id'])) {
            $reason = $this->differenceReasonRepository->find((int) $data['difference_reason_id']);
            if ($reason !== null) {
                $difference->setDifferenceReason($reason);
            }
        }

        if (!empty($data['difference_direction_id'])) {
            $direction = $this->differenceDirectionRepository->find((int) $data['difference_direction_id']);
            if ($direction !== null) {
                $difference->setDifferenceDirection($direction);
            }
        }

        if ($patientAccount !== null) {
            $difference->setPatientAccount($patientAccount);
        }

        $this->em->persist($difference);
        $this->em->flush();

        return $difference;
    }

    // ── Autorización / Rechazo ────────────────────────────────────────────────

    /**
     * Autoriza la diferencia. Registra el supervisor y la fecha de autorización.
     */
    public function approve(Difference $difference, Member $authorizedBy): void
    {
        $difference->setStatus('autorizada');
        $difference->setAuthorizedByMember($authorizedBy);
        $difference->setAuthorizedAt(new \DateTime());
        $this->em->flush();
    }

    /**
     * Rechaza la diferencia. Registra el supervisor y la fecha de resolución.
     */
    public function reject(Difference $difference, Member $authorizedBy): void
    {
        $difference->setStatus('rechazada');
        $difference->setAuthorizedByMember($authorizedBy);
        $difference->setAuthorizedAt(new \DateTime());
        $this->em->flush();
    }

    // ── Anulación ─────────────────────────────────────────────────────────────

    /**
     * Anula la diferencia. Solo se permite si está pendiente.
     * El cancelledByMember queda como el mismo cajero que la solicitó.
     */
    public function cancel(Difference $difference): void
    {
        $difference->setStatus('anulada');
        $difference->setCancelledByMember($difference->getRequestedByMember());
        $difference->setCancelledAt(new \DateTime());
        $this->em->flush();
    }

    // ── Auto-aprobación ───────────────────────────────────────────────────────

    /**
     * Verifica si la diferencia puede aprobarse automáticamente sin intervención
     * de un supervisor. Si es elegible, persiste el estado 'auto_aprobada' y
     * retorna true.
     *
     * Convención provisional: DifferenceType.idEstado === 2 habilita la
     * auto-aprobación. Reemplazar por un campo dedicado cuando se agregue la
     * migración correspondiente (DifferenceType.allowsAutoApprove boolean).
     */
    public function autoApproveIfEligible(Difference $difference): bool
    {
        $type = $difference->getDifferenceType();

        if ($type === null || $type->getIdEstado() !== 2) {
            return false;
        }

        $difference->setStatus('auto_aprobada');
        $difference->setAuthorizedAt(new \DateTime());
        $this->em->flush();

        return true;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function toDecimal(mixed $value): string
    {
        $str = str_replace([',', ' '], ['.', ''], (string) $value);
        return number_format((float) $str, 2, '.', '');
    }
}
