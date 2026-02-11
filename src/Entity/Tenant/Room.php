<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\RoomRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Room
 * 
 * Legacy table: sala
 * Spanish name: Sala
 * 
 * Represents hospital rooms or wards:
 * - Patient rooms
 * - Operating rooms
 * - Emergency rooms
 * - ICU rooms
 * - Consultation rooms
 */
#[ORM\Entity(repositoryClass: RoomRepository::class)]
#[ORM\Table(name: 'room')]
class Room
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $roomNumber = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Clinic::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Clinic $clinic = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $roomType = null; // patient, operating, emergency, icu, consultation

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $floor = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $wing = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 1])]
    private int $capacity = 1;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $dailyRate = null;

    #[ORM\Column(length: 50)]
    private ?string $status = 'available'; // available, occupied, maintenance, reserved, cleaning

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $hasOxygen = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $hasBathroom = false;

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

    public function getRoomNumber(): ?string
    {
        return $this->roomNumber;
    }

    public function setRoomNumber(string $roomNumber): static
    {
        $this->roomNumber = $roomNumber;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getClinic(): ?Clinic
    {
        return $this->clinic;
    }

    public function setClinic(?Clinic $clinic): static
    {
        $this->clinic = $clinic;
        return $this;
    }

    public function getRoomType(): ?string
    {
        return $this->roomType;
    }

    public function setRoomType(?string $roomType): static
    {
        $this->roomType = $roomType;
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

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;
        return $this;
    }

    public function getDailyRate(): ?string
    {
        return $this->dailyRate;
    }

    public function setDailyRate(?string $dailyRate): static
    {
        $this->dailyRate = $dailyRate;
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

    public function isHasOxygen(): bool
    {
        return $this->hasOxygen;
    }

    public function setHasOxygen(bool $hasOxygen): static
    {
        $this->hasOxygen = $hasOxygen;
        return $this;
    }

    public function isHasBathroom(): bool
    {
        return $this->hasBathroom;
    }

    public function setHasBathroom(bool $hasBathroom): static
    {
        $this->hasBathroom = $hasBathroom;
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
            $this->clinic?->getName(),
            $this->wing,
            $this->floor ? 'Piso ' . $this->floor : null,
            'Sala ' . $this->roomNumber
        ]);
        return implode(' - ', $parts);
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', $this->roomNumber, $this->name);
    }
}
