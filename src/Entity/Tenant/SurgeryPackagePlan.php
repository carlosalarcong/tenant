<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\SurgeryPackagePlanRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * SurgeryPackagePlan (PqPlan)
 *
 * Tabla legacy: pq_plan
 *
 * Plan paquetizado de cirugía asociado opcionalmente a una relación sucursal-financiador.
 */
#[ORM\Entity(repositoryClass: SurgeryPackagePlanRepository::class)]
#[ORM\Table(name: 'surgery_package_plan')]
#[ORM\HasLifecycleCallbacks]
class SurgeryPackagePlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isDisabled = false;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $isTelemedicine = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $cancelledAt = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'cancelled_by_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $cancelledBy = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: 'parent_plan_id', referencedColumnName: 'id', nullable: true)]
    private ?SurgeryPackagePlan $parentPlan = null;

    #[ORM\ManyToOne(targetEntity: BranchPayer::class)]
    #[ORM\JoinColumn(name: 'branch_payer_id', referencedColumnName: 'id', nullable: true)]
    private ?BranchPayer $branchPayer = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function isDisabled(): bool
    {
        return $this->isDisabled;
    }

    public function setIsDisabled(bool $isDisabled): self
    {
        $this->isDisabled = $isDisabled;
        return $this;
    }

    public function getIsTelemedicine(): ?bool
    {
        return $this->isTelemedicine;
    }

    public function setIsTelemedicine(?bool $isTelemedicine): self
    {
        $this->isTelemedicine = $isTelemedicine;
        return $this;
    }

    public function getCancelledAt(): ?\DateTimeInterface
    {
        return $this->cancelledAt;
    }

    public function setCancelledAt(?\DateTimeInterface $cancelledAt): self
    {
        $this->cancelledAt = $cancelledAt;
        return $this;
    }

    public function getCancelledBy(): ?Member
    {
        return $this->cancelledBy;
    }

    public function setCancelledBy(?Member $cancelledBy): self
    {
        $this->cancelledBy = $cancelledBy;
        return $this;
    }

    public function getParentPlan(): ?SurgeryPackagePlan
    {
        return $this->parentPlan;
    }

    public function setParentPlan(?SurgeryPackagePlan $parentPlan): self
    {
        $this->parentPlan = $parentPlan;
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

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
