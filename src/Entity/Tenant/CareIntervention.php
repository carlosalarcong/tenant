<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\CareInterventionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CareIntervention
 * 
 * Intervenciones de cuidado relacionadas con categorías de cuidado
 */
#[ORM\Entity(repositoryClass: CareInterventionRepository::class)]
#[ORM\Table(name: 'care_intervention')]
class CareIntervention
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Description is required')]
    #[Assert\Length(max: 255)]
    private string $description;

    #[ORM\ManyToOne(targetEntity: CareCategory::class)]
    #[ORM\JoinColumn(name: 'care_category_id', nullable: true)]
    private ?CareCategory $careCategory = null;

    #[ORM\Column(name: 'is_active', type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(name: 'id_estado', type: 'integer')]
    private int $idEstado = 1;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getCareCategory(): ?CareCategory
    {
        return $this->careCategory;
    }

    public function setCareCategory(?CareCategory $careCategory): self
    {
        $this->careCategory = $careCategory;
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

    public function getIdEstado(): int
    {
        return $this->idEstado;
    }

    public function setIdEstado(int $idEstado): self
    {
        $this->idEstado = $idEstado;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
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

    public function __toString(): string
    {
        return $this->description;
    }
}
