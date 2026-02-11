<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\PrescriptionRuleDetailRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * PrescriptionRuleDetail
 * 
 * Detalle de reglas de prescripción médica
 */
#[ORM\Entity(repositoryClass: PrescriptionRuleDetailRepository::class)]
#[ORM\Table(name: 'prescription_rule_detail')]
class PrescriptionRuleDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Intervals is required')]
    #[Assert\Length(max: 255)]
    private string $intervals;

    #[ORM\Column(name: 'daily_quantity', type: 'integer')]
    #[Assert\NotBlank(message: 'Daily quantity is required')]
    private int $dailyQuantity;

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

    public function getIntervals(): string
    {
        return $this->intervals;
    }

    public function setIntervals(string $intervals): self
    {
        $this->intervals = $intervals;
        return $this;
    }

    public function getDailyQuantity(): int
    {
        return $this->dailyQuantity;
    }

    public function setDailyQuantity(int $dailyQuantity): self
    {
        $this->dailyQuantity = $dailyQuantity;
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
        return $this->intervals;
    }
}
