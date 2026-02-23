<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\DifferenceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Difference (Diferencia)
 *
 * Tabla legacy: diferencia
 *
 * Solicitud de descuento o ajuste de monto iniciada por un cajero.
 * Si el monto supera el límite configurado (MONTO_MAXIMO_DIFERENCIA),
 * requiere autorización de un supervisor antes de ser aplicada.
 *
 * Ciclo de vida:
 *   solicitada → (autorizada | rechazada | anulada)
 */
#[ORM\Entity(repositoryClass: DifferenceRepository::class)]
#[ORM\Table(name: 'difference')]
class Difference
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Cajero que solicitó la diferencia.
     * Legacy: ID_USUARIO_SOLICITUD → UsuariosRebsol
     */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'requested_by_member_id', referencedColumnName: 'id', nullable: false)]
    private ?Member $requestedByMember = null;

    /**
     * Motivo de la diferencia (catálogo mantenedor).
     * Legacy: ID_MOTIVO_DIFERENCIA → MotivoDiferencia
     */
    #[ORM\ManyToOne(targetEntity: DifferenceReason::class)]
    #[ORM\JoinColumn(name: 'difference_reason_id', referencedColumnName: 'id', nullable: true)]
    private ?DifferenceReason $differenceReason = null;

    /**
     * Tipo de diferencia (catálogo mantenedor).
     * Legacy: ID_TIPO_DIFERENCIA → TipoDiferencia
     */
    #[ORM\ManyToOne(targetEntity: DifferenceType::class)]
    #[ORM\JoinColumn(name: 'difference_type_id', referencedColumnName: 'id', nullable: true)]
    private ?DifferenceType $differenceType = null;

    /**
     * Dirección/sentido de la diferencia (cargo o abono).
     * Legacy: ID_DIRECCION_DIFERENCIA → DireccionDiferencia
     */
    #[ORM\ManyToOne(targetEntity: DifferenceDirection::class)]
    #[ORM\JoinColumn(name: 'difference_direction_id', referencedColumnName: 'id', nullable: true)]
    private ?DifferenceDirection $differenceDirection = null;

    /**
     * Cuenta paciente sobre la que se aplica la diferencia.
     * Legacy: ID_CUENTA_PACIENTE → CuentaPaciente
     */
    #[ORM\ManyToOne(targetEntity: PatientAccount::class)]
    #[ORM\JoinColumn(name: 'patient_account_id', referencedColumnName: 'id', nullable: true)]
    private ?PatientAccount $patientAccount = null;

    /** Fecha en que se solicitó la diferencia. Legacy: FECHA_SOLICITUD */
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $requestedAt = null;

    /** Monto total de la cuenta antes del ajuste. Legacy: TOTAL_CUENTA */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, options: ['default' => '0.00'])]
    private string $totalAccount = '0.00';

    /** Monto del descuento/diferencia solicitado. Legacy: TOTAL_DESCUENTO */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, options: ['default' => '0.00'])]
    private string $totalDiscount = '0.00';

    /** Monto resultante después de aplicar la diferencia. Legacy: TOTAL_CUENTA_CON_DESCUENTO */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, options: ['default' => '0.00'])]
    private string $totalAfterDiscount = '0.00';

    /**
     * Estado de la diferencia.
     * Valores: solicitada | autorizada | rechazada | anulada | auto_aprobada
     * Legacy: ID_ESTADO_DIFERENCIA → EstadoDiferencia
     */
    #[ORM\Column(length: 20, options: ['default' => 'solicitada'])]
    private string $status = 'solicitada';

    /**
     * Supervisor que autorizó o rechazó la diferencia.
     * Legacy: ID_USUARIO_AUTORIZACION → UsuariosRebsol
     */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'authorized_by_member_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $authorizedByMember = null;

    /** Fecha de autorización/rechazo. Legacy: FECHA_AUTORIZACION */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $authorizedAt = null;

    /**
     * Cajero que anuló la diferencia (puede ser el mismo que la solicitó).
     * Legacy: ID_USUARIO_ANULACION → UsuariosRebsol
     */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'cancelled_by_member_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $cancelledByMember = null;

    /** Fecha de anulación. Legacy: FECHA_ANULACION */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $cancelledAt = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequestedByMember(): ?Member
    {
        return $this->requestedByMember;
    }

    public function setRequestedByMember(?Member $requestedByMember): self
    {
        $this->requestedByMember = $requestedByMember;
        return $this;
    }

    public function getDifferenceReason(): ?DifferenceReason
    {
        return $this->differenceReason;
    }

    public function setDifferenceReason(?DifferenceReason $differenceReason): self
    {
        $this->differenceReason = $differenceReason;
        return $this;
    }

    public function getDifferenceType(): ?DifferenceType
    {
        return $this->differenceType;
    }

    public function setDifferenceType(?DifferenceType $differenceType): self
    {
        $this->differenceType = $differenceType;
        return $this;
    }

    public function getDifferenceDirection(): ?DifferenceDirection
    {
        return $this->differenceDirection;
    }

    public function setDifferenceDirection(?DifferenceDirection $differenceDirection): self
    {
        $this->differenceDirection = $differenceDirection;
        return $this;
    }

    public function getPatientAccount(): ?PatientAccount
    {
        return $this->patientAccount;
    }

    public function setPatientAccount(?PatientAccount $patientAccount): self
    {
        $this->patientAccount = $patientAccount;
        return $this;
    }

    public function getRequestedAt(): ?\DateTimeInterface
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(\DateTimeInterface $requestedAt): self
    {
        $this->requestedAt = $requestedAt;
        return $this;
    }

    public function getTotalAccount(): string
    {
        return $this->totalAccount;
    }

    public function setTotalAccount(string $totalAccount): self
    {
        $this->totalAccount = $totalAccount;
        return $this;
    }

    public function getTotalDiscount(): string
    {
        return $this->totalDiscount;
    }

    public function setTotalDiscount(string $totalDiscount): self
    {
        $this->totalDiscount = $totalDiscount;
        return $this;
    }

    public function getTotalAfterDiscount(): string
    {
        return $this->totalAfterDiscount;
    }

    public function setTotalAfterDiscount(string $totalAfterDiscount): self
    {
        $this->totalAfterDiscount = $totalAfterDiscount;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getAuthorizedByMember(): ?Member
    {
        return $this->authorizedByMember;
    }

    public function setAuthorizedByMember(?Member $authorizedByMember): self
    {
        $this->authorizedByMember = $authorizedByMember;
        return $this;
    }

    public function getAuthorizedAt(): ?\DateTimeInterface
    {
        return $this->authorizedAt;
    }

    public function setAuthorizedAt(?\DateTimeInterface $authorizedAt): self
    {
        $this->authorizedAt = $authorizedAt;
        return $this;
    }

    public function getCancelledByMember(): ?Member
    {
        return $this->cancelledByMember;
    }

    public function setCancelledByMember(?Member $cancelledByMember): self
    {
        $this->cancelledByMember = $cancelledByMember;
        return $this;
    }

    public function getCancelledAt(): ?\DateTimeInterface
    {
        return $this->cancelledAt;
    }

    public function setCancelledAt(?\DateTimeInterface $cancelledAt): self
    {
        $this->cancelledAt = $cancelledAt;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === 'solicitada';
    }

    public function isApproved(): bool
    {
        return in_array($this->status, ['autorizada', 'auto_aprobada'], true);
    }
}
