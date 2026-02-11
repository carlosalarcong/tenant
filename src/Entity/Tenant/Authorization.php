<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\AuthorizationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Authorization
 * 
 * Legacy table: autorizacion
 * Spanish name: Autorización
 * 
 * Represents medical service authorizations:
 * - Insurance company approvals
 * - Pre-authorization for procedures
 * - Authorization codes and validity
 * - Status tracking
 */
#[ORM\Entity(repositoryClass: AuthorizationRepository::class)]
#[ORM\Table(name: 'authorization')]
class Authorization
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Payer::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Payer $payer = null;

    #[ORM\ManyToOne(targetEntity: ServiceType::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?ServiceType $serviceType = null;

    #[ORM\Column(length: 50)]
    private ?string $authorizationCode = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $patientName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $patientRut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $authorizationDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expirationDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $authorizedServices = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $authorizedAmount = null;

    #[ORM\Column(length: 50)]
    private ?string $status = 'pending'; // pending, approved, rejected, expired, used

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observations = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $authorizedBy = null;

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

    public function getPayer(): ?Payer
    {
        return $this->financier;
    }

    public function setPayer(?Payer $payer): static
    {
        $this->financier = $payer;
        return $this;
    }

    public function getServiceType(): ?ServiceType
    {
        return $this->serviceType;
    }

    public function setServiceType(?ServiceType $serviceType): static
    {
        $this->serviceType = $serviceType;
        return $this;
    }

    public function getAuthorizationCode(): ?string
    {
        return $this->authorizationCode;
    }

    public function setAuthorizationCode(string $authorizationCode): static
    {
        $this->authorizationCode = $authorizationCode;
        return $this;
    }

    public function getPatientName(): ?string
    {
        return $this->patientName;
    }

    public function setPatientName(?string $patientName): static
    {
        $this->patientName = $patientName;
        return $this;
    }

    public function getPatientRut(): ?string
    {
        return $this->patientRut;
    }

    public function setPatientRut(?string $patientRut): static
    {
        $this->patientRut = $patientRut;
        return $this;
    }

    public function getAuthorizationDate(): ?\DateTimeInterface
    {
        return $this->authorizationDate;
    }

    public function setAuthorizationDate(\DateTimeInterface $authorizationDate): static
    {
        $this->authorizationDate = $authorizationDate;
        return $this;
    }

    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?\DateTimeInterface $expirationDate): static
    {
        $this->expirationDate = $expirationDate;
        return $this;
    }

    public function getAuthorizedServices(): ?string
    {
        return $this->authorizedServices;
    }

    public function setAuthorizedServices(?string $authorizedServices): static
    {
        $this->authorizedServices = $authorizedServices;
        return $this;
    }

    public function getAuthorizedAmount(): ?string
    {
        return $this->authorizedAmount;
    }

    public function setAuthorizedAmount(?string $authorizedAmount): static
    {
        $this->authorizedAmount = $authorizedAmount;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): static
    {
        $this->observations = $observations;
        return $this;
    }

    public function getAuthorizedBy(): ?string
    {
        return $this->authorizedBy;
    }

    public function setAuthorizedBy(?string $authorizedBy): static
    {
        $this->authorizedBy = $authorizedBy;
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
        return $this->authorizationCode ?? '';
    }
}
