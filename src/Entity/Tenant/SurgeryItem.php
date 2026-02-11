<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\SurgeryItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * SurgeryItem
 * 
 * Legacy table: item_cirugia
 * Spanish name: Item Cirugía
 * 
 * Represents surgical procedure items:
 * - Surgical supplies
 * - Surgical instruments
 * - Disposable items for surgery
 * - Operating room materials
 */
#[ORM\Entity(repositoryClass: SurgeryItemRepository::class)]
#[ORM\Table(name: 'surgery_item')]
class SurgeryItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(length: 200)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $unitOfMeasure = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $unitCost = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isSterile = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isDisposable = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;
        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;
        return $this;
    }

    public function getUnitOfMeasure(): ?string
    {
        return $this->unitOfMeasure;
    }

    public function setUnitOfMeasure(?string $unitOfMeasure): static
    {
        $this->unitOfMeasure = $unitOfMeasure;
        return $this;
    }

    public function getUnitCost(): ?string
    {
        return $this->unitCost;
    }

    public function setUnitCost(?string $unitCost): static
    {
        $this->unitCost = $unitCost;
        return $this;
    }

    public function isSterile(): bool
    {
        return $this->isSterile;
    }

    public function setIsSterile(bool $isSterile): static
    {
        $this->isSterile = $isSterile;
        return $this;
    }

    public function isDisposable(): bool
    {
        return $this->isDisposable;
    }

    public function setIsDisposable(bool $isDisposable): static
    {
        $this->isDisposable = $isDisposable;
        return $this;
    }

    public function isActive(): bool
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
