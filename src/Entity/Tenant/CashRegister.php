<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\CashRegisterRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * CashRegister (Caja)
 *
 * Tabla legacy: caja_registro (origen: Caja)
 *
 * Registro de apertura/cierre de caja por usuario y día.
 * Un cajero puede tener múltiples registros, uno por jornada.
 * La lógica de estado (abierta/cerrada/sin_cerrar) se evalúa
 * en CashOpeningService al buscar la caja activa del día.
 */
#[ORM\Entity(repositoryClass: CashRegisterRepository::class)]
#[ORM\Table(name: 'cash_register')]
class CashRegister
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Cajero que abrió la caja.
     * Legacy: ID_USUARIO → UsuariosRebsol
     */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id', nullable: false)]
    private ?Member $member = null;

    /**
     * Punto de cobro físico donde se abrió la caja.
     * Legacy: ID_UBICACION_CAJERO → UbicacionCaja
     */
    #[ORM\ManyToOne(targetEntity: CashRegisterLocation::class)]
    #[ORM\JoinColumn(name: 'cash_register_location_id', referencedColumnName: 'id', nullable: false)]
    private ?CashRegisterLocation $cashRegisterLocation = null;

    /**
     * Sucursal a la que pertenece esta caja.
     * Legacy: ID_SUCURSAL → Sucursal
     */
    #[ORM\ManyToOne(targetEntity: Branch::class)]
    #[ORM\JoinColumn(name: 'branch_id', referencedColumnName: 'id', nullable: true)]
    private ?Branch $branch = null;

    /** Legacy: FECHA_APERTURA */
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $openedAt = null;

    /** Legacy: FECHA_CIERRE — null mientras la caja esté abierta */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $closedAt = null;

    /** Monto inicial declarado al abrir la caja. Legacy: MONTO_INICIAL */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, options: ['default' => '0.00'])]
    private string $initialAmount = '0.00';

    /** Monto real contabilizado al cierre. Legacy: MONTO_REAL */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $realAmount = null;

    /** Diferencia positiva entre real y esperado. Legacy: SUPERAVIT */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $surplus = null;

    /** Diferencia negativa entre real y esperado. Legacy: DEFICIT */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $deficit = null;

    /**
     * Estado legible de la caja: abierta | cerrada | sin_cerrar.
     * Calculado y guardado en CashOpeningService.
     */
    #[ORM\Column(length: 20, options: ['default' => 'abierta'])]
    private string $status = 'abierta';

    /**
     * Miembro supervisor que reautorizó la reapertura de la caja (si aplica).
     * Legacy: ID_ESTADO_REAPERTURA + supervisorAction
     */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'reopened_by_member_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $reopenedByMember = null;

    /** Fecha de reapertura autorizada. Legacy: FECHA_REAPERTURA */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $reopenedAt = null;

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

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;
        return $this;
    }

    public function getCashRegisterLocation(): ?CashRegisterLocation
    {
        return $this->cashRegisterLocation;
    }

    public function setCashRegisterLocation(?CashRegisterLocation $cashRegisterLocation): self
    {
        $this->cashRegisterLocation = $cashRegisterLocation;
        return $this;
    }

    public function getBranch(): ?Branch
    {
        return $this->branch;
    }

    public function setBranch(?Branch $branch): self
    {
        $this->branch = $branch;
        return $this;
    }

    public function getOpenedAt(): ?\DateTimeInterface
    {
        return $this->openedAt;
    }

    public function setOpenedAt(\DateTimeInterface $openedAt): self
    {
        $this->openedAt = $openedAt;
        return $this;
    }

    public function getClosedAt(): ?\DateTimeInterface
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeInterface $closedAt): self
    {
        $this->closedAt = $closedAt;
        return $this;
    }

    public function getInitialAmount(): string
    {
        return $this->initialAmount;
    }

    public function setInitialAmount(string $initialAmount): self
    {
        $this->initialAmount = $initialAmount;
        return $this;
    }

    public function getRealAmount(): ?string
    {
        return $this->realAmount;
    }

    public function setRealAmount(?string $realAmount): self
    {
        $this->realAmount = $realAmount;
        return $this;
    }

    public function getSurplus(): ?string
    {
        return $this->surplus;
    }

    public function setSurplus(?string $surplus): self
    {
        $this->surplus = $surplus;
        return $this;
    }

    public function getDeficit(): ?string
    {
        return $this->deficit;
    }

    public function setDeficit(?string $deficit): self
    {
        $this->deficit = $deficit;
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

    public function getReopenedByMember(): ?Member
    {
        return $this->reopenedByMember;
    }

    public function setReopenedByMember(?Member $reopenedByMember): self
    {
        $this->reopenedByMember = $reopenedByMember;
        return $this;
    }

    public function getReopenedAt(): ?\DateTimeInterface
    {
        return $this->reopenedAt;
    }

    public function setReopenedAt(?\DateTimeInterface $reopenedAt): self
    {
        $this->reopenedAt = $reopenedAt;
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

    public function isOpen(): bool
    {
        return $this->closedAt === null;
    }
}
