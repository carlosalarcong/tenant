<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\SurgicalTeamRoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * SurgicalTeamRole (Rol de Equipo Quirúrgico)
 * 
 * Represents a role in the surgical team
 */
#[ORM\Entity(repositoryClass: SurgicalTeamRoleRepository::class)]
#[ORM\Table(name: 'surgical_team_role')]
#[ORM\HasLifecycleCallbacks]
class SurgicalTeamRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'sort_order', type: 'integer')]
    #[Assert\NotBlank(message: 'Sort order is required')]
    private int $sortOrder;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Name is required')]
    #[Assert\Length(max: 100, maxMessage: 'Name cannot exceed {{ limit }} characters')]
    private string $name;

    #[ORM\ManyToOne(targetEntity: SurgeryItem::class)]
    #[ORM\JoinColumn(name: 'surgery_item_id', nullable: true)]
    private ?SurgeryItem $surgeryItem = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $idEstado = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->sortOrder = 0;
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

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
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

    public function getSurgeryItem(): ?SurgeryItem
    {
        return $this->surgeryItem;
    }

    public function setSurgeryItem(?SurgeryItem $surgeryItem): self
    {
        $this->surgeryItem = $surgeryItem;
        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getIdEstado(): ?string
    {
        return $this->idEstado;
    }

    public function setIdEstado(?string $idEstado): self
    {
        $this->idEstado = $idEstado;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
