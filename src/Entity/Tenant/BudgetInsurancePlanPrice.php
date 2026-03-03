<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\BudgetInsurancePlanPriceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * BudgetInsurancePlanPrice (PrPrecio)
 *
 * Tabla legacy: pr_precio
 *
 * Precio de honorario/copago para una MedicalService en un InsurancePlan dado.
 */
#[ORM\Entity(repositoryClass: BudgetInsurancePlanPriceRepository::class)]
#[ORM\Table(name: 'budget_insurance_plan_price')]
class BudgetInsurancePlanPrice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'unit_price', type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $unitPrice = null;

    #[ORM\Column(name: 'copay_amount', type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $copayAmount = null;

    #[ORM\Column(name: 'effective_date', type: 'datetime')]
    private ?\DateTimeInterface $effectiveDate = null;

    #[ORM\Column(name: 'expires_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $expiresAt = null;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: MedicalService::class)]
    #[ORM\JoinColumn(name: 'medical_service_id', nullable: false)]
    private ?MedicalService $medicalService = null;

    #[ORM\ManyToOne(targetEntity: InsurancePlan::class)]
    #[ORM\JoinColumn(name: 'insurance_plan_id', nullable: false)]
    private ?InsurancePlan $insurancePlan = null;

    #[ORM\ManyToOne(targetEntity: BranchPayer::class)]
    #[ORM\JoinColumn(name: 'branch_payer_id', nullable: true)]
    private ?BranchPayer $branchPayer = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUnitPrice(): ?string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): self
    {
        $this->unitPrice = $unitPrice;
        return $this;
    }

    public function getCopayAmount(): ?string
    {
        return $this->copayAmount;
    }

    public function setCopayAmount(?string $copayAmount): self
    {
        $this->copayAmount = $copayAmount;
        return $this;
    }

    public function getEffectiveDate(): ?\DateTimeInterface
    {
        return $this->effectiveDate;
    }

    public function setEffectiveDate(\DateTimeInterface $effectiveDate): self
    {
        $this->effectiveDate = $effectiveDate;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getMedicalService(): ?MedicalService
    {
        return $this->medicalService;
    }

    public function setMedicalService(?MedicalService $medicalService): self
    {
        $this->medicalService = $medicalService;
        return $this;
    }

    public function getInsurancePlan(): ?InsurancePlan
    {
        return $this->insurancePlan;
    }

    public function setInsurancePlan(?InsurancePlan $insurancePlan): self
    {
        $this->insurancePlan = $insurancePlan;
        return $this;
    }

    public function getBranchPayer(): ?BranchPayer
    {
        return $this->branchPayer;
    }

    public function setBranchPayer(?BranchPayer $branchPayer): self
    {
        $this->branchPayer = $branchPayer;
        return $this;
    }
}
