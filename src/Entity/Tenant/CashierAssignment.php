<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\CashierAssignmentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * CashierAssignment (RelUbicacionCajero)
 *
 * Tabla legacy: rel_ubicacion_cajero
 *
 * Asignación de un cajero (Member) a una ubicación de caja (CashRegisterLocation).
 * Define qué usuarios pueden operar en cada caja.
 */
#[ORM\Entity(repositoryClass: CashierAssignmentRepository::class)]
#[ORM\Table(name: 'cashier_assignment')]
class CashierAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Cajero asignado a la ubicación.
     * Legacy: ID_USUARIO → UsuariosRebsol
     */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id', nullable: false)]
    private ?Member $member = null;

    /**
     * Ubicación de caja donde opera el cajero.
     * Legacy: ID_CAJA → UbicacionCaja
     */
    #[ORM\ManyToOne(targetEntity: CashRegisterLocation::class)]
    #[ORM\JoinColumn(name: 'cash_register_location_id', referencedColumnName: 'id', nullable: false)]
    private ?CashRegisterLocation $cashRegisterLocation = null;

    /** Legacy: ES_ACTIVO */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

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

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
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
}
