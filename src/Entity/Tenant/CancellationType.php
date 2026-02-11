<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\CancellationTypeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * CancellationType
 * 
 * Legacy table: tipo_anulacion
 * Spanish name: Tipo Anulación
 * 
 * Represents the types of cancellation reasons:
 * - Patient request
 * - Medical reasons
 * - Administrative error
 * - No show
 * - Insurance issues
 * - Rescheduling
 * - etc.
 */
#[ORM\Entity(repositoryClass: CancellationTypeRepository::class)]
#[ORM\Table(name: 'cancellation_type')]
class CancellationType
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
    private bool $requiresJustification = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $allowsRefund = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $affectsStatistics = true;

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

    public function isRequiresJustification(): bool
    {
        return $this->requiresJustification;
    }

    public function setRequiresJustification(bool $requiresJustification): static
    {
        $this->requiresJustification = $requiresJustification;
        return $this;
    }

    public function isAllowsRefund(): bool
    {
        return $this->allowsRefund;
    }

    public function setAllowsRefund(bool $allowsRefund): static
    {
        $this->allowsRefund = $allowsRefund;
        return $this;
    }

    public function isAffectsStatistics(): bool
    {
        return $this->affectsStatistics;
    }

    public function setAffectsStatistics(bool $affectsStatistics): static
    {
        $this->affectsStatistics = $affectsStatistics;
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
