<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\SurgeryPatientStatusConfigRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SurgeryPatientStatusConfigRepository::class)]
#[ORM\Table(name: 'surgery_patient_status_config')]
class SurgeryPatientStatusConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    #[Assert\Length(max: 20, maxMessage: 'El color no puede exceder {{ limit }} caracteres')]
    private ?string $color = null;

    #[ORM\ManyToOne(targetEntity: SurgeryPatientStatus::class)]
    #[ORM\JoinColumn(name: 'surgery_patient_status_id', nullable: true)]
    private ?SurgeryPatientStatus $surgeryPatientStatus = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'integer')]
    private int $idEstado = 1;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;
        return $this;
    }

    public function getSurgeryPatientStatus(): ?SurgeryPatientStatus
    {
        return $this->surgeryPatientStatus;
    }

    public function setSurgeryPatientStatus(?SurgeryPatientStatus $surgeryPatientStatus): self
    {
        $this->surgeryPatientStatus = $surgeryPatientStatus;
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
}
