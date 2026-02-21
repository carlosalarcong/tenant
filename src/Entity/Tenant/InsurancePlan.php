<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\InsurancePlanRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * InsurancePlan (PrPlan)
 *
 * Tabla legacy: pr_plan
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

    /** Legacy: ES_INHABIL */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isDisabled = false;

    /** Legacy: ES_PLAN_TELECONSULTA */
    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $isTelemedicine = null;

    /** Legacy: FECHA_ANULACION */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $cancellationDate = null;

    /** Legacy: ID_USUARIO_ANULACION */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'cancellation_user_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $cancellationUser = null;

    /** Legacy: ID_PR_PLAN_PAQUETE_PRESTACION (auto-referencia) */
    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: 'parent_plan_id', referencedColumnName: 'id', nullable: true)]
    private ?InsurancePlan $parentPlan = null;

    /** Legacy: ID_REL_SUCURSAL_PREVISION */
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

    public function getCancellationDate(): ?\DateTimeInterface
    {
        return $this->cancellationDate;
    }

    public function setCancellationDate(?\DateTimeInterface $cancellationDate): self
    {
        $this->cancellationDate = $cancellationDate;
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

    public function getParentPlan(): ?InsurancePlan
    {
        return $this->parentPlan;
    }

    public function setParentPlan(?InsurancePlan $parentPlan): self
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

    public function __toString(): string
    {
        return $this->name;
    }
}
