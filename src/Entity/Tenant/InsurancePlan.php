<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\InsurancePlanRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * InsurancePlan (PrPlan)
 *
 * Legacy table: pr_plan
 * Spanish name: Plan de Previsión / Plan de Salud
 *
 * Represents a specific health insurance plan or package associated
 * with a payer (ISAPRE, FONASA, etc.).
 */
#[ORM\Entity(repositoryClass: InsurancePlanRepository::class)]
#[ORM\Table(name: 'insurance_plan')]
#[ORM\HasLifecycleCallbacks]
class InsurancePlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isPackage = false;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function isPackage(): bool
    {
        return $this->isPackage;
    }

    public function setIsPackage(bool $isPackage): self
    {
        $this->isPackage = $isPackage;
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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
