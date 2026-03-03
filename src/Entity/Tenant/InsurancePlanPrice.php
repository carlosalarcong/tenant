<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\InsurancePlanPriceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * InsurancePlanPrice
 *
 * Precio de un plan por item de facturación y contexto comercial.
 */
#[ORM\Entity(repositoryClass: InsurancePlanPriceRepository::class)]
#[ORM\Table(name: 'insurance_plan_price')]
class InsurancePlanPrice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'unit_price', type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $unitPrice = null;

    #[ORM\Column(name: 'copay_amount', type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $copayAmount = null;

    #[ORM\Column(name: 'effective_date', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $effectiveDate = null;

    #[ORM\Column(name: 'cancellation_date', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $cancellationDate = null;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\ManyToOne(targetEntity: InsurancePlan::class)]
    #[ORM\JoinColumn(name: 'insurance_plan_id', referencedColumnName: 'id', nullable: false)]
    private ?InsurancePlan $insurancePlan = null;

    #[ORM\ManyToOne(targetEntity: BillingItem::class)]
    #[ORM\JoinColumn(name: 'billing_item_id', referencedColumnName: 'id', nullable: false)]
    private ?BillingItem $billingItem = null;

    #[ORM\ManyToOne(targetEntity: BranchPayer::class)]
    #[ORM\JoinColumn(name: 'branch_payer_id', referencedColumnName: 'id', nullable: true)]
    private ?BranchPayer $branchPayer = null;

    #[ORM\ManyToOne(targetEntity: BranchCareType::class)]
    #[ORM\JoinColumn(name: 'branch_care_type_id', referencedColumnName: 'id', nullable: true)]
    private ?BranchCareType $branchCareType = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'cancellation_user_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $cancellationUser = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $createdBy = null;

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

    public function setCopayAmount(string $copayAmount): self
    {
        $this->copayAmount = $copayAmount;
        return $this;
    }

    public function getEffectiveDate(): ?\DateTimeInterface
    {
        return $this->effectiveDate;
    }

    public function setEffectiveDate(?\DateTimeInterface $effectiveDate): self
    {
        $this->effectiveDate = $effectiveDate;
        return $this;
    }

    public function getCancellationDate(): ?\DateTimeInterface
    {
        return $this->cancellationDate;
    }

    public function setCancellationDate(?\DateTimeInterface $cancellationDate): self
    {
        $this->cancellationDate = $cancellationDate;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
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

    public function getInsurancePlan(): ?InsurancePlan
    {
        return $this->insurancePlan;
    }

    public function setInsurancePlan(?InsurancePlan $insurancePlan): self
    {
        $this->insurancePlan = $insurancePlan;
        return $this;
    }

    public function getBillingItem(): ?BillingItem
    {
        return $this->billingItem;
    }

    public function setBillingItem(?BillingItem $billingItem): self
    {
        $this->billingItem = $billingItem;
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

    public function getBranchCareType(): ?BranchCareType
    {
        return $this->branchCareType;
    }

    public function setBranchCareType(?BranchCareType $branchCareType): self
    {
        $this->branchCareType = $branchCareType;
        return $this;
    }

    public function getCancellationUser(): ?Member
    {
        return $this->cancellationUser;
    }

    public function setCancellationUser(?Member $cancellationUser): self
    {
        $this->cancellationUser = $cancellationUser;
        return $this;
    }

    public function getCreatedBy(): ?Member
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Member $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }
}
