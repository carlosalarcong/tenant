<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\GESPathologyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * GESPathology
 * 
 * Legacy table: patologia
 * Spanish name: Patología GES
 * 
 * Represents GES (Garantías Explícitas en Salud) pathologies:
 * - Government-guaranteed health conditions
 * - 85 priority health problems in Chile
 * - Specific age ranges and gender requirements
 * - Guaranteed coverage and treatment timelines
 */
#[ORM\Entity(repositoryClass: GESPathologyRepository::class)]
#[ORM\Table(name: 'ges_pathology')]
class GESPathology
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $pathologyNumber = null;

    #[ORM\Column(length: 200)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $minAge = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $maxAge = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $minAgeMonths = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $maxAgeMonths = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $genderRestriction = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $guaranteedDays = null;

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

    public function getPathologyNumber(): ?string
    {
        return $this->pathologyNumber;
    }

    public function setPathologyNumber(?string $pathologyNumber): static
    {
        $this->pathologyNumber = $pathologyNumber;
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

    public function getMinAge(): ?int
    {
        return $this->minAge;
    }

    public function setMinAge(?int $minAge): static
    {
        $this->minAge = $minAge;
        return $this;
    }

    public function getMaxAge(): ?int
    {
        return $this->maxAge;
    }

    public function setMaxAge(?int $maxAge): static
    {
        $this->maxAge = $maxAge;
        return $this;
    }

    public function getMinAgeMonths(): ?int
    {
        return $this->minAgeMonths;
    }

    public function setMinAgeMonths(?int $minAgeMonths): static
    {
        $this->minAgeMonths = $minAgeMonths;
        return $this;
    }

    public function getMaxAgeMonths(): ?int
    {
        return $this->maxAgeMonths;
    }

    public function setMaxAgeMonths(?int $maxAgeMonths): static
    {
        $this->maxAgeMonths = $maxAgeMonths;
        return $this;
    }

    public function getGenderRestriction(): ?string
    {
        return $this->genderRestriction;
    }

    public function setGenderRestriction(?string $genderRestriction): static
    {
        $this->genderRestriction = $genderRestriction;
        return $this;
    }

    public function getGuaranteedDays(): ?int
    {
        return $this->guaranteedDays;
    }

    public function setGuaranteedDays(?int $guaranteedDays): static
    {
        $this->guaranteedDays = $guaranteedDays;
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
