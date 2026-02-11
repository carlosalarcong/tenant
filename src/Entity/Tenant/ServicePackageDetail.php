<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\ServicePackageDetailRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * ServicePackageDetail - Details of services in a package
 * Legacy table: paquete_prestacion_detalle
 * 
 * Represents individual clinical actions/services within a service package
 */
#[ORM\Entity(repositoryClass: ServicePackageDetailRepository::class)]
#[ORM\Table(name: 'service_package_detail')]
#[ORM\HasLifecycleCallbacks]
class ServicePackageDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ServicePackage::class, inversedBy: 'details')]
    #[ORM\JoinColumn(name: 'service_package_id', referencedColumnName: 'id', nullable: false)]
    private ?ServicePackage $servicePackage = null;

    #[ORM\ManyToOne(targetEntity: MedicalService::class)]
    #[ORM\JoinColumn(name: 'medical_service_id', referencedColumnName: 'id', nullable: false)]
    private ?MedicalService $medicalService = null;

    #[ORM\Column(type: 'integer')]
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

    public function getServicePackage(): ?ServicePackage
    {
        return $this->servicePackage;
    }

    public function setServicePackage(?ServicePackage $servicePackage): static
    {
        $this->servicePackage = $servicePackage;
        return $this;
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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
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
        return $this->medicalService?->getName() ?? '';
    }
}
