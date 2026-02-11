<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\BedRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Bed
 * 
 * Legacy table: cama
 * Spanish name: Cama
 * 
 * Represents individual hospital beds:
 * - Physical bed units
 * - Room and floor location
 * - Bed type and availability
 * - Current patient assignment
 */
#[ORM\Entity(repositoryClass: BedRepository::class)]
#[ORM\Table(name: 'bed')]
class Bed
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $bedNumber = null;

    #[ORM\ManyToOne(targetEntity: BedType::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?BedType $bedType = null;

    #[ORM\ManyToOne(targetEntity: Room::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Room $room = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $floor = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $wing = null;

    #[ORM\Column(length: 50)]
    private ?string $status = 'available'; // available, occupied, maintenance, reserved, cleaning

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastMaintenanceDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observations = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBedNumber(): ?string
    {
        return $this->bedNumber;
    }

    public function setBedNumber(string $bedNumber): static
    {
        $this->bedNumber = $bedNumber;
        return $this;
    }

    public function getBedType(): ?BedType
    {
        return $this->bedType;
    }

    public function setBedType(?BedType $bedType): static
    {
        $this->bedType = $bedType;
        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): static
    {
        $this->room = $room;
        return $this;
    }

    public function getFloor(): ?string
    {
        return $this->floor;
    }

    public function setFloor(?string $floor): static
    {
        $this->floor = $floor;
        return $this;
    }

    public function getWing(): ?string
    {
        return $this->wing;
    }

    public function setWing(?string $wing): static
    {
        $this->wing = $wing;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getLastMaintenanceDate(): ?\DateTimeInterface
    {
        return $this->lastMaintenanceDate;
    }

    public function setLastMaintenanceDate(?\DateTimeInterface $lastMaintenanceDate): static
    {
        $this->lastMaintenanceDate = $lastMaintenanceDate;
        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): static
    {
        $this->observations = $observations;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getFullLocation(): string
    {
        $parts = array_filter([
            $this->room?->getFullLocation(),
            'Cama ' . $this->bedNumber
        ]);
        return implode(' - ', $parts);
    }

    public function __toString(): string
    {
        return $this->bedNumber ?? '';
    }
}
