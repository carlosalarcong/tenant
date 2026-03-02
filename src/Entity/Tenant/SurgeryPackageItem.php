<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\SurgeryPackageItemRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * SurgeryPackageItem (ItemPaqueteCirugia)
 *
 * Tabla legacy: item_paquete_cirugia
 *
 * Ítem valorizable perteneciente a un plan paquetizado de cirugía.
 */
#[ORM\Entity(repositoryClass: SurgeryPackageItemRepository::class)]
#[ORM\Table(name: 'surgery_package_item')]
class SurgeryPackageItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 200)]
    private ?string $name = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(length: 30)]
    private ?string $itemType = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: MedicalService::class)]
    #[ORM\JoinColumn(name: 'medical_service_id', referencedColumnName: 'id', nullable: true)]
    private ?MedicalService $medicalService = null;

    #[ORM\ManyToOne(targetEntity: SurgeryPackagePlan::class)]
    #[ORM\JoinColumn(name: 'surgery_package_plan_id', referencedColumnName: 'id', nullable: true)]
    private ?SurgeryPackagePlan $surgeryPackagePlan = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getItemType(): ?string
    {
        return $this->itemType;
    }

    public function setItemType(string $itemType): self
    {
        $this->itemType = $itemType;
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

    public function getMedicalService(): ?MedicalService
    {
        return $this->medicalService;
    }

    public function setMedicalService(?MedicalService $medicalService): self
    {
        $this->medicalService = $medicalService;
        return $this;
    }

    public function getSurgeryPackagePlan(): ?SurgeryPackagePlan
    {
        return $this->surgeryPackagePlan;
    }

    public function setSurgeryPackagePlan(?SurgeryPackagePlan $surgeryPackagePlan): self
    {
        $this->surgeryPackagePlan = $surgeryPackagePlan;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
