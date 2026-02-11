<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\ServiceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Service
 * 
 * Legacy table: servicio
 * Spanish name: Servicio
 * 
 * Represents medical services offered:
 * - Specific procedures
 * - Treatments
 * - Tests and examinations
 * - With pricing and codes
 */
#[ORM\Entity(repositoryClass: ServiceRepository::class)]
#[ORM\Table(name: 'service')]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(length: 200)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: ServiceType::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?ServiceType $serviceType = null;

    #[ORM\ManyToOne(targetEntity: Specialty::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Specialty $specialty = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $basePrice = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $estimatedDurationMinutes = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $requiresAuthorization = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $requiresSpecialist = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isAmbulatory = true;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $fonasaCode = null;

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getServiceType(): ?ServiceType
    {
        return $this->serviceType;
    }

    public function setServiceType(?ServiceType $serviceType): static
    {
        $this->serviceType = $serviceType;
        return $this;
    }

    public function getSpecialty(): ?Specialty
    {
        return $this->specialty;
    }

    public function setSpecialty(?Specialty $specialty): static
    {
        $this->specialty = $specialty;
        return $this;
    }

    public function getBasePrice(): ?string
    {
        return $this->basePrice;
    }

    public function setBasePrice(?string $basePrice): static
    {
        $this->basePrice = $basePrice;
        return $this;
    }

    public function getEstimatedDurationMinutes(): ?int
    {
        return $this->estimatedDurationMinutes;
    }

    public function setEstimatedDurationMinutes(?int $estimatedDurationMinutes): static
    {
        $this->estimatedDurationMinutes = $estimatedDurationMinutes;
        return $this;
    }

    public function isRequiresAuthorization(): bool
    {
        return $this->requiresAuthorization;
    }

    public function setRequiresAuthorization(bool $requiresAuthorization): static
    {
        $this->requiresAuthorization = $requiresAuthorization;
        return $this;
    }

    public function isRequiresSpecialist(): bool
    {
        return $this->requiresSpecialist;
    }

    public function setRequiresSpecialist(bool $requiresSpecialist): static
    {
        $this->requiresSpecialist = $requiresSpecialist;
        return $this;
    }

    public function isAmbulatory(): bool
    {
        return $this->isAmbulatory;
    }

    public function setIsAmbulatory(bool $isAmbulatory): static
    {
        $this->isAmbulatory = $isAmbulatory;
        return $this;
    }

    public function getFonasaCode(): ?string
    {
        return $this->fonasaCode;
    }

    public function setFonasaCode(?string $fonasaCode): static
    {
        $this->fonasaCode = $fonasaCode;
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

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
