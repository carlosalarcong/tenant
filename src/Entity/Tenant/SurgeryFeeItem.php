<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\SurgeryFeeItemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * SurgeryFeeItem
 *
 * Ítem mantenedor para composición de guarismos quirúrgicos.
 */
#[ORM\Entity(repositoryClass: SurgeryFeeItemRepository::class)]
#[ORM\Table(name: 'surgery_fee_item')]
#[ORM\HasLifecycleCallbacks]
class SurgeryFeeItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $name = null;

    #[ORM\Column(name: 'budget_name', type: 'string', length: 30)]
    private ?string $budgetName = null;

    #[ORM\Column(name: 'display_order', type: 'integer', options: ['default' => 0])]
    private int $displayOrder = 0;

    #[ORM\Column(name: 'tax_rate', type: 'integer', nullable: true)]
    private ?int $taxRate = null;

    #[ORM\Column(name: 'is_medical_team', type: 'boolean', options: ['default' => false])]
    private bool $isMedicalTeam = false;

    #[ORM\Column(name: 'is_editable', type: 'boolean', options: ['default' => true])]
    private bool $isEditable = true;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: SurgeryFeeItemType::class)]
    #[ORM\JoinColumn(name: 'item_type_id', referencedColumnName: 'id', nullable: false)]
    private ?SurgeryFeeItemType $itemType = null;

    #[ORM\ManyToOne(targetEntity: Branch::class)]
    #[ORM\JoinColumn(name: 'branch_id', referencedColumnName: 'id', nullable: false)]
    private ?Branch $branch = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
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

    public function getBudgetName(): ?string
    {
        return $this->budgetName;
    }

    public function setBudgetName(string $budgetName): self
    {
        $this->budgetName = $budgetName;
        return $this;
    }

    public function getDisplayOrder(): int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): self
    {
        $this->displayOrder = $displayOrder;
        return $this;
    }

    public function getTaxRate(): ?int
    {
        return $this->taxRate;
    }

    public function setTaxRate(?int $taxRate): self
    {
        $this->taxRate = $taxRate;
        return $this;
    }

    public function isMedicalTeam(): bool
    {
        return $this->isMedicalTeam;
    }

    public function setIsMedicalTeam(bool $isMedicalTeam): self
    {
        $this->isMedicalTeam = $isMedicalTeam;
        return $this;
    }

    public function isEditable(): bool
    {
        return $this->isEditable;
    }

    public function setIsEditable(bool $isEditable): self
    {
        $this->isEditable = $isEditable;
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getItemType(): ?SurgeryFeeItemType
    {
        return $this->itemType;
    }

    public function setItemType(?SurgeryFeeItemType $itemType): self
    {
        $this->itemType = $itemType;
        return $this;
    }

    public function getBranch(): ?Branch
    {
        return $this->branch;
    }

    public function setBranch(?Branch $branch): self
    {
        $this->branch = $branch;
        return $this;
    }
}
