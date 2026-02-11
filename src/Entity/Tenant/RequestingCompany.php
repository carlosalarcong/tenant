<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\RequestingCompanyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * RequestingCompany
 * 
 * Legacy table: empresa_solicitante
 * Spanish name: Empresa Solicitante
 * 
 * Represents companies that request medical services:
 * - Corporate clients
 * - Organizations with health agreements
 * - Companies with employee healthcare plans
 * - Business partners
 */
#[ORM\Entity(repositoryClass: RequestingCompanyRepository::class)]
#[ORM\Table(name: 'requesting_company')]
class RequestingCompany
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(length: 150)]
    private ?string $businessName = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $tradeName = null;

    #[ORM\Column(length: 20)]
    private ?string $rut = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $contactPerson = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $industry = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $numberOfEmployees = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $discountPercentage = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $paymentTermDays = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $hasAgreement = false;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $agreementStartDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $agreementEndDate = null;

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

    public function getBusinessName(): ?string
    {
        return $this->businessName;
    }

    public function setBusinessName(string $businessName): static
    {
        $this->businessName = $businessName;
        return $this;
    }

    public function getTradeName(): ?string
    {
        return $this->tradeName;
    }

    public function setTradeName(?string $tradeName): static
    {
        $this->tradeName = $tradeName;
        return $this;
    }

    public function getRut(): ?string
    {
        return $this->rut;
    }

    public function setRut(string $rut): static
    {
        $this->rut = $rut;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getContactPerson(): ?string
    {
        return $this->contactPerson;
    }

    public function setContactPerson(?string $contactPerson): static
    {
        $this->contactPerson = $contactPerson;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getIndustry(): ?string
    {
        return $this->industry;
    }

    public function setIndustry(?string $industry): static
    {
        $this->industry = $industry;
        return $this;
    }

    public function getNumberOfEmployees(): ?int
    {
        return $this->numberOfEmployees;
    }

    public function setNumberOfEmployees(?int $numberOfEmployees): static
    {
        $this->numberOfEmployees = $numberOfEmployees;
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

    public function isHasAgreement(): bool
    {
        return $this->hasAgreement;
    }

    public function setHasAgreement(bool $hasAgreement): static
    {
        $this->hasAgreement = $hasAgreement;
        return $this;
    }

    public function getAgreementStartDate(): ?\DateTimeInterface
    {
        return $this->agreementStartDate;
    }

    public function setAgreementStartDate(?\DateTimeInterface $agreementStartDate): static
    {
        $this->agreementStartDate = $agreementStartDate;
        return $this;
    }

    public function getAgreementEndDate(): ?\DateTimeInterface
    {
        return $this->agreementEndDate;
    }

    public function setAgreementEndDate(?\DateTimeInterface $agreementEndDate): static
    {
        $this->agreementEndDate = $agreementEndDate;
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
        return $this->businessName ?? '';
    }
}
