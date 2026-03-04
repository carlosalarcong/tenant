<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\OpenPlanMassAdjustmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OpenPlanMassAdjustmentRepository::class)]
#[ORM\Table(name: 'open_plan_mass_adjustment')]
class OpenPlanMassAdjustment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'effective_date', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $effectiveDate = null;

    #[ORM\Column(name: 'adjustment_percentage', type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $adjustmentPercentage = null;

    #[ORM\Column(name: 'confirmed_count', type: 'integer', options: ['default' => 0])]
    private int $confirmedCount = 0;

    #[ORM\Column(name: 'plan_count', type: 'integer', options: ['default' => 0])]
    private int $planCount = 0;

    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'cancellation_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $cancellationDate = null;

    #[ORM\Column(name: 'closure_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $closureDate = null;

    #[ORM\ManyToOne(targetEntity: BranchPayer::class)]
    #[ORM\JoinColumn(name: 'branch_payer_id', referencedColumnName: 'id', nullable: false)]
    private ?BranchPayer $branchPayer = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', nullable: false)]
    private ?Member $createdBy = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'cancellation_user_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $cancellationUser = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'closure_user_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $closureUser = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAdjustmentPercentage(): ?string
    {
        return $this->adjustmentPercentage;
    }

    public function setAdjustmentPercentage(string $adjustmentPercentage): self
    {
        $this->adjustmentPercentage = $adjustmentPercentage;

        return $this;
    }

    public function getConfirmedCount(): int
    {
        return $this->confirmedCount;
    }

    public function setConfirmedCount(int $confirmedCount): self
    {
        $this->confirmedCount = $confirmedCount;

        return $this;
    }

    public function getPlanCount(): int
    {
        return $this->planCount;
    }

    public function setPlanCount(int $planCount): self
    {
        $this->planCount = $planCount;

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

    public function getCancellationDate(): ?\DateTimeInterface
    {
        return $this->cancellationDate;
    }

    public function setCancellationDate(?\DateTimeInterface $cancellationDate): self
    {
        $this->cancellationDate = $cancellationDate;

        return $this;
    }

    public function getClosureDate(): ?\DateTimeInterface
    {
        return $this->closureDate;
    }

    public function setClosureDate(?\DateTimeInterface $closureDate): self
    {
        $this->closureDate = $closureDate;

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

    public function getCreatedBy(): ?Member
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Member $createdBy): self
    {
        $this->createdBy = $createdBy;

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

    public function getClosureUser(): ?Member
    {
        return $this->closureUser;
    }

    public function setClosureUser(?Member $closureUser): self
    {
        $this->closureUser = $closureUser;

        return $this;
    }
}
