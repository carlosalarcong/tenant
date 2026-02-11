<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\MedicalServiceBedTypeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * MedicalServiceBedType - Relation between medical services and bed types
 * Legacy table: rel_accion_clinica_tipo_cama
 * 
 * Links medical services with bed types they require
 */
#[ORM\Entity(repositoryClass: MedicalServiceBedTypeRepository::class)]
#[ORM\Table(name: 'medical_service_bed_type')]
#[ORM\HasLifecycleCallbacks]
class MedicalServiceBedType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MedicalService::class)]
    #[ORM\JoinColumn(name: 'medical_service_id', referencedColumnName: 'id', nullable: false)]
    private ?MedicalService $medicalService = null;

    #[ORM\ManyToOne(targetEntity: BedType::class)]
    #[ORM\JoinColumn(name: 'bed_type_id', referencedColumnName: 'id', nullable: false)]
    private ?BedType $bedType = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $quantity = 1;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isActive = true;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

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

    public function getMedicalService(): ?MedicalService
    {
        return $this->medicalService;
    }

    public function setMedicalService(?MedicalService $medicalService): static
    {
        $this->medicalService = $medicalService;
        return $this;
    }

    public function getBedType(): ?BedType
    {
        return $this->bedType;
    }

    public function setBedType(?BedType $bedType): static
    {
        $this->bedType = $bedType;
        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
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

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s - %s', 
            $this->medicalService?->getName() ?? '',
            $this->bedType?->getName() ?? ''
        );
    }
}
