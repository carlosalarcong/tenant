<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\TreatmentTypeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * TreatmentType
 * 
 * Legacy table: tipo_tratamiento
 * Spanish name: Tipo Tratamiento
 * 
 * Represents the types of medical treatments:
 * - Medication
 * - Physical therapy
 * - Chemotherapy
 * - Radiotherapy
 * - Dialysis
 * - Oxygen therapy
 * - etc.
 */
#[ORM\Entity(repositoryClass: TreatmentTypeRepository::class)]
#[ORM\Table(name: 'treatment_type')]
class TreatmentType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $requiresSpecialist = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $requiresAuthorization = false;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $averageDurationDays = null;

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

    public function isRequiresSpecialist(): bool
    {
        return $this->requiresSpecialist;
    }

    public function setRequiresSpecialist(bool $requiresSpecialist): static
    {
        $this->requiresSpecialist = $requiresSpecialist;
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

    public function getAverageDurationDays(): ?int
    {
        return $this->averageDurationDays;
    }

    public function setAverageDurationDays(?int $averageDurationDays): static
    {
        $this->averageDurationDays = $averageDurationDays;
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
