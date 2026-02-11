<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\DifferenceReasonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * DifferenceReason (MotivoDiferencia)
 * 
 * Mantenedor de motivos de diferencia del sistema
 */
#[ORM\Entity(repositoryClass: DifferenceReasonRepository::class)]
#[ORM\Table(name: 'difference_reason')]
class DifferenceReason
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    #[Assert\NotBlank(message: 'Name is required')]
    #[Assert\Length(max: 50)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: DifferenceDirection::class)]
    #[ORM\JoinColumn(name: 'difference_direction_id', nullable: true)]
    private ?DifferenceDirection $differenceDirection = null;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDifferenceDirection(): ?DifferenceDirection
    {
        return $this->differenceDirection;
    }

    public function setDifferenceDirection(?DifferenceDirection $differenceDirection): self
    {
        $this->differenceDirection = $differenceDirection;
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
}
