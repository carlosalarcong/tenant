<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\ConsultationTypeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * ConsultationType
 * 
 * Legacy table: tipo_consulta
 * Spanish name: Tipo Consulta
 * 
 * Represents the types of medical consultations:
 * - First consultation
 * - Follow-up
 * - Emergency
 * - Preventive
 * - Specialty consultation
 * - Home visit
 * - Telemedicine
 * - etc.
 */
#[ORM\Entity(repositoryClass: ConsultationTypeRepository::class)]
#[ORM\Table(name: 'consultation_type')]
class ConsultationType
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

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $defaultDurationMinutes = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $requiresPriorAppointment = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isEmergency = false;

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

    public function getDefaultDurationMinutes(): ?int
    {
        return $this->defaultDurationMinutes;
    }

    public function setDefaultDurationMinutes(?int $defaultDurationMinutes): static
    {
        $this->defaultDurationMinutes = $defaultDurationMinutes;
        return $this;
    }

    public function isRequiresPriorAppointment(): bool
    {
        return $this->requiresPriorAppointment;
    }

    public function setRequiresPriorAppointment(bool $requiresPriorAppointment): static
    {
        $this->requiresPriorAppointment = $requiresPriorAppointment;
        return $this;
    }

    public function isEmergency(): bool
    {
        return $this->isEmergency;
    }

    public function setIsEmergency(bool $isEmergency): static
    {
        $this->isEmergency = $isEmergency;
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
