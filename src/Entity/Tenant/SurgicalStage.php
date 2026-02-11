<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\SurgicalStageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SurgicalStageRepository::class)]
#[ORM\Table(name: 'surgical_stage')]
#[ORM\HasLifecycleCallbacks]
class SurgicalStage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER, name: 'sort_order')]
    private ?int $sortOrder = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $abbreviation = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, length: 2000, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::BOOLEAN, name: 'is_mandatory')]
    private ?bool $isMandatory = false;

    #[ORM\Column(type: Types::BOOLEAN, name: 'requires_login')]
    private ?bool $requiresLogin = false;

    #[ORM\Column(type: Types::BOOLEAN, name: 'is_sequential')]
    private ?bool $isSequential = false;

    #[ORM\Column(type: Types::INTEGER, nullable: true, name: 'template_stage_id')]
    private ?int $templateStageId = null;

    #[ORM\ManyToOne(targetEntity: Branch::class)]
    #[ORM\JoinColumn(name: 'branch_id', nullable: true)]
    private ?Branch $branch = null;

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

    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(?string $abbreviation): static
    {
        $this->abbreviation = $abbreviation;

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

    public function getIsMandatory(): ?bool
    {
        return $this->isMandatory;
    }

    public function setIsMandatory(bool $isMandatory): static
    {
        $this->isMandatory = $isMandatory;

        return $this;
    }

    public function getRequiresLogin(): ?bool
    {
        return $this->requiresLogin;
    }

    public function setRequiresLogin(bool $requiresLogin): static
    {
        $this->requiresLogin = $requiresLogin;

        return $this;
    }

    public function getIsSequential(): ?bool
    {
        return $this->isSequential;
    }

    public function setIsSequential(bool $isSequential): static
    {
        $this->isSequential = $isSequential;

        return $this;
    }

    public function getTemplateStageId(): ?int
    {
        return $this->templateStageId;
    }

    public function setTemplateStageId(?int $templateStageId): static
    {
        $this->templateStageId = $templateStageId;

        return $this;
    }

    public function getBranch(): ?Branch
    {
        return $this->branch;
    }

    public function setBranch(?Branch $branch): static
    {
        $this->branch = $branch;

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
