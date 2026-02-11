<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\FigureRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Figure - Billing codes and pricing figures
 * Legacy table: guarismo
 */
#[ORM\Entity(repositoryClass: FigureRepository::class)]
#[ORM\Table(name: 'figure')]
#[ORM\HasLifecycleCallbacks]
class Figure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 45)]
    private ?string $name = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isSurgicalRoom = false;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isZero = false;

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

    public function isSurgicalRoom(): ?bool
    {
        return $this->isSurgicalRoom;
    }

    public function setIsSurgicalRoom(bool $isSurgicalRoom): static
    {
        $this->isSurgicalRoom = $isSurgicalRoom;
        return $this;
    }

    public function isZero(): ?bool
    {
        return $this->isZero;
    }

    public function setIsZero(bool $isZero): static
    {
        $this->isZero = $isZero;
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
