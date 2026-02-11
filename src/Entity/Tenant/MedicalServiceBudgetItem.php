<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\MedicalServiceBudgetItemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * MedicalServiceBudgetItem - Relation between medical services and budget items
 * Legacy table: rel_accion_clinica_item
 * 
 * Links medical services with budget items
 */
#[ORM\Entity(repositoryClass: MedicalServiceBudgetItemRepository::class)]
#[ORM\Table(name: 'medical_service_budget_item')]
#[ORM\HasLifecycleCallbacks]
class MedicalServiceBudgetItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MedicalService::class)]
    #[ORM\JoinColumn(name: 'medical_service_id', referencedColumnName: 'id', nullable: false)]
    private ?MedicalService $medicalService = null;

    #[ORM\ManyToOne(targetEntity: SurgeryItem::class)]
    #[ORM\JoinColumn(name: 'surgery_item_id', referencedColumnName: 'id', nullable: false)]
    private ?SurgeryItem $surgeryItem = null;

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

    public function getSurgeryItem(): ?SurgeryItem
    {
        return $this->surgeryItem;
    }

    public function setSurgeryItem(?SurgeryItem $surgeryItem): static
    {
        $this->surgeryItem = $surgeryItem;
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
            $this->surgeryItem?->getName() ?? ''
        );
    }
}
