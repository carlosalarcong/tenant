<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\SurgicalStageItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SurgicalStageItemRepository::class)]
#[ORM\Table(name: 'surgical_stage_item')]
#[ORM\HasLifecycleCallbacks]
class SurgicalStageItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER, name: 'sort_order')]
    private ?int $sortOrder = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, length: 2000, nullable: true)]
    private ?string $alternatives = null;

    #[ORM\Column(type: Types::BOOLEAN, name: 'has_response')]
    private ?bool $hasResponse = false;

    #[ORM\Column(type: Types::BOOLEAN, name: 'is_mandatory')]
    private ?bool $isMandatory = false;

    #[ORM\Column(type: Types::INTEGER, nullable: true, name: 'item_type_id')]
    private ?int $itemTypeId = null;

    #[ORM\ManyToOne(targetEntity: SurgicalStageItem::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', nullable: true)]
    private ?SurgicalStageItem $parent = null;

    #[ORM\OneToMany(targetEntity: SurgicalStageItem::class, mappedBy: 'parent')]
    private Collection $children;

    #[ORM\ManyToOne(targetEntity: SurgicalStage::class)]
    #[ORM\JoinColumn(name: 'surgical_stage_id', nullable: false)]
    private ?SurgicalStage $surgicalStage = null;

    #[ORM\Column(type: Types::BOOLEAN, name: 'is_active')]
    private ?bool $isActive = true;

    #[ORM\Column(name: 'id_estado')]
    private ?int $idEstado = 1;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: 'created_at')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, name: 'updated_at')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, name: 'created_by')]
    private ?int $createdBy = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
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

    public function getSortOrder(): ?int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): static
    {
        $this->sortOrder = $sortOrder;

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

    public function getAlternatives(): ?string
    {
        return $this->alternatives;
    }

    public function setAlternatives(?string $alternatives): static
    {
        $this->alternatives = $alternatives;

        return $this;
    }

    public function getHasResponse(): ?bool
    {
        return $this->hasResponse;
    }

    public function setHasResponse(bool $hasResponse): static
    {
        $this->hasResponse = $hasResponse;

        return $this;
    }

    public function getIsMandatory(): ?bool
    {
        return $this->isMandatory;
    }

    public function setIsMandatory(bool $isMandatory): static
    {
        $this->isMandatory = $isMandatory;

        return $this;
    }

    public function getItemTypeId(): ?int
    {
        return $this->itemTypeId;
    }

    public function setItemTypeId(?int $itemTypeId): static
    {
        $this->itemTypeId = $itemTypeId;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): static
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): static
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getSurgicalStage(): ?SurgicalStage
    {
        return $this->surgicalStage;
    }

    public function setSurgicalStage(?SurgicalStage $surgicalStage): static
    {
        $this->surgicalStage = $surgicalStage;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getIdEstado(): ?int
    {
        return $this->idEstado;
    }

    public function setIdEstado(int $idEstado): static
    {
        $this->idEstado = $idEstado;

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

    public function setUpdatedAt(\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?int $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
