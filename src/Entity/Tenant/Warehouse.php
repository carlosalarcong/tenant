<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\WarehouseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Warehouse - Inventory storage location
 * Legacy table: bodega
 * 
 * Represents warehouse or inventory storage locations
 */
#[ORM\Entity(repositoryClass: WarehouseRepository::class)]
#[ORM\Table(name: 'warehouse')]
#[ORM\HasLifecycleCallbacks]
class Warehouse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shortName = null;

    #[ORM\Column(length: 255)]
    private ?string $code = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $createsArticle = false;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isVirtual = false;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isPharmacy = false;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isAutomaticReception = false;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(name: 'service_id', referencedColumnName: 'id', nullable: true)]
    private ?Service $service = null;

    #[ORM\ManyToOne(targetEntity: Warehouse::class)]
    #[ORM\JoinColumn(name: 'parent_warehouse_id', referencedColumnName: 'id', nullable: true)]
    private ?Warehouse $parentWarehouse = null;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): static
    {
        $this->shortName = $shortName;
        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function createsArticle(): ?bool
    {
        return $this->createsArticle;
    }

    public function setCreatesArticle(bool $createsArticle): static
    {
        $this->createsArticle = $createsArticle;
        return $this;
    }

    public function isVirtual(): ?bool
    {
        return $this->isVirtual;
    }

    public function setIsVirtual(bool $isVirtual): static
    {
        $this->isVirtual = $isVirtual;
        return $this;
    }

    public function isPharmacy(): ?bool
    {
        return $this->isPharmacy;
    }

    public function setIsPharmacy(bool $isPharmacy): static
    {
        $this->isPharmacy = $isPharmacy;
        return $this;
    }

    public function isAutomaticReception(): ?bool
    {
        return $this->isAutomaticReception;
    }

    public function setIsAutomaticReception(bool $isAutomaticReception): static
    {
        $this->isAutomaticReception = $isAutomaticReception;
        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): static
    {
        $this->service = $service;
        return $this;
    }

    public function getParentWarehouse(): ?Warehouse
    {
        return $this->parentWarehouse;
    }

    public function setParentWarehouse(?Warehouse $parentWarehouse): static
    {
        $this->parentWarehouse = $parentWarehouse;
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
        return $this->name ?? '';
    }
}
