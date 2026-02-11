<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\ENOPathologyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * ENOPathology
 * 
 * Legacy table: patologia_eno
 * Spanish name: Patología ENO
 * 
 * Represents ENO (Enfermedad No Oncológica) pathologies:
 * - Non-oncological diseases
 * - Chronic conditions requiring specialized care
 * - Distinct from GES pathologies
 */
#[ORM\Entity(repositoryClass: ENOPathologyRepository::class)]
#[ORM\Table(name: 'eno_pathology')]
class ENOPathology
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

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $icd10Code = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $requiresSpecialist = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isChronic = false;

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

    public function getIcd10Code(): ?string
    {
        return $this->icd10Code;
    }

    public function setIcd10Code(?string $icd10Code): static
    {
        $this->icd10Code = $icd10Code;
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

    public function isChronic(): bool
    {
        return $this->isChronic;
    }

    public function setIsChronic(bool $isChronic): static
    {
        $this->isChronic = $isChronic;
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
