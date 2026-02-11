<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\AgreementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Agreement
 * 
 * Legacy table: convenio
 * Spanish name: Convenio
 * 
 * Represents commercial agreements with entities:
 * - Corporate healthcare agreements
 * - Insurance company contracts
 * - Service level agreements
 * - Pricing and discount terms
 */
#[ORM\Entity(repositoryClass: AgreementRepository::class)]
#[ORM\Table(name: 'agreement')]
class Agreement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: Payer::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Payer $payer = null;

    #[ORM\ManyToOne(targetEntity: RequestingCompany::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?RequestingCompany $requestingCompany = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $agreementType = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $discountPercentage = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $paymentTermDays = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $terms = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $coveredServices = null;

    #[ORM\Column(length: 50)]
    private ?string $status = 'active'; // active, expired, suspended, cancelled

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $autoRenew = false;

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

    public function getPayer(): ?Payer
    {
        return $this->financier;
    }

    public function setPayer(?Payer $payer): static
    {
        $this->financier = $payer;
        return $this;
    }

    public function getRequestingCompany(): ?RequestingCompany
    {
        return $this->requestingCompany;
    }

    public function setRequestingCompany(?RequestingCompany $requestingCompany): static
    {
        $this->requestingCompany = $requestingCompany;
        return $this;
    }

    public function getAgreementType(): ?string
    {
        return $this->agreementType;
    }

    public function setAgreementType(?string $agreementType): static
    {
        $this->agreementType = $agreementType;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getDiscountPercentage(): ?string
    {
        return $this->discountPercentage;
    }

    public function setDiscountPercentage(?string $discountPercentage): static
    {
        $this->discountPercentage = $discountPercentage;
        return $this;
    }

    public function getPaymentTermDays(): ?int
    {
        return $this->paymentTermDays;
    }

    public function setPaymentTermDays(?int $paymentTermDays): static
    {
        $this->paymentTermDays = $paymentTermDays;
        return $this;
    }

    public function getTerms(): ?string
    {
        return $this->terms;
    }

    public function setTerms(?string $terms): static
    {
        $this->terms = $terms;
        return $this;
    }

    public function getCoveredServices(): ?string
    {
        return $this->coveredServices;
    }

    public function setCoveredServices(?string $coveredServices): static
    {
        $this->coveredServices = $coveredServices;
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

    public function isAutoRenew(): bool
    {
        return $this->autoRenew;
    }

    public function setAutoRenew(bool $autoRenew): static
    {
        $this->autoRenew = $autoRenew;
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
