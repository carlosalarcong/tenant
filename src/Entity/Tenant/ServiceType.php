<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\ServiceTypeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * ServiceType
 * 
 * Legacy table: tipo_prestacion
 * Spanish name: Tipo Prestación
 * 
 * Represents the types of medical services/procedures offered:
 * - Consultation
 * - Hospitalization
 * - Surgery
 * - Emergency
 * - Laboratory
 * - Imaging
 * - Therapy
 * - etc.
 */
#[ORM\Entity(repositoryClass: ServiceTypeRepository::class)]
#[ORM\Table(name: 'service_type')]
class ServiceType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $requiresAuthorization = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $requiresBedAssignment = false;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $defaultDuration = null;

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

    public function isRequiresAuthorization(): bool
    {
        return $this->requiresAuthorization;
    }

    public function setRequiresAuthorization(bool $requiresAuthorization): static
    {
        $this->requiresAuthorization = $requiresAuthorization;
        return $this;
    }

    public function isRequiresBedAssignment(): bool
    {
        return $this->requiresBedAssignment;
    }

    public function setRequiresBedAssignment(bool $requiresBedAssignment): static
    {
        $this->requiresBedAssignment = $requiresBedAssignment;
        return $this;
    }

    public function getDefaultDuration(): ?int
    {
        return $this->defaultDuration;
    }

    public function setDefaultDuration(?int $defaultDuration): static
    {
        $this->defaultDuration = $defaultDuration;
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
