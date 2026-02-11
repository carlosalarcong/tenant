<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\MedicalServiceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * MedicalService - Medical services and clinical procedures
 * Legacy table: accion_clinica
 * 
 * Represents medical services, procedures, and clinical actions that can be provided
 */
#[ORM\Entity(repositoryClass: MedicalServiceRepository::class)]
#[ORM\Table(name: 'medical_service')]
#[ORM\HasLifecycleCallbacks]
class MedicalService
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $code = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $fonasaCode = null;

    #[ORM\Column(type: 'text')]
    private ?string $name = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $shortName = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $accountingCodeErp = null;

    #[ORM\Column(type: 'time', nullable: true)]
    private ?\DateTimeInterface $duration = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $appliesSurcharge = false;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isImed = false;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $imedCode = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $interfaceCode = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $participation = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isTaxed = true;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isProcedure = false;

    #[ORM\ManyToOne(targetEntity: Figure::class)]
    #[ORM\JoinColumn(name: 'figure_id', referencedColumnName: 'id', nullable: true)]
    private ?Figure $figure = null;

    #[ORM\ManyToOne(targetEntity: BudgetItem::class)]
    #[ORM\JoinColumn(name: 'budget_item_id', referencedColumnName: 'id', nullable: true)]
    private ?BudgetItem $budgetItem = null;

    #[ORM\ManyToOne(targetEntity: ServiceType::class)]
    #[ORM\JoinColumn(name: 'service_type_id', referencedColumnName: 'id', nullable: true)]
    private ?ServiceType $serviceType = null;

    #[ORM\ManyToOne(targetEntity: SubCompany::class)]
    #[ORM\JoinColumn(name: 'sub_company_id', referencedColumnName: 'id', nullable: true)]
    private ?SubCompany $subCompany = null;

    #[ORM\ManyToOne(targetEntity: SubCompany::class)]
    #[ORM\JoinColumn(name: 'billing_sub_company_id', referencedColumnName: 'id', nullable: true)]
    private ?SubCompany $billingSubCompany = null;

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function getFonasaCode(): ?string
    {
        return $this->fonasaCode;
    }

    public function setFonasaCode(?string $fonasaCode): static
    {
        $this->fonasaCode = $fonasaCode;
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

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): static
    {
        $this->shortName = $shortName;
        return $this;
    }

    public function getAccountingCodeErp(): ?int
    {
        return $this->accountingCodeErp;
    }

    public function setAccountingCodeErp(?int $accountingCodeErp): static
    {
        $this->accountingCodeErp = $accountingCodeErp;
        return $this;
    }

    public function getDuration(): ?\DateTimeInterface
    {
        return $this->duration;
    }

    public function setDuration(?\DateTimeInterface $duration): static
    {
        $this->duration = $duration;
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

    public function appliesSurcharge(): ?bool
    {
        return $this->appliesSurcharge;
    }

    public function setAppliesSurcharge(bool $appliesSurcharge): static
    {
        $this->appliesSurcharge = $appliesSurcharge;
        return $this;
    }

    public function isImed(): ?bool
    {
        return $this->isImed;
    }

    public function setIsImed(bool $isImed): static
    {
        $this->isImed = $isImed;
        return $this;
    }

    public function getImedCode(): ?string
    {
        return $this->imedCode;
    }

    public function setImedCode(?string $imedCode): static
    {
        $this->imedCode = $imedCode;
        return $this;
    }

    public function getInterfaceCode(): ?string
    {
        return $this->interfaceCode;
    }

    public function setInterfaceCode(?string $interfaceCode): static
    {
        $this->interfaceCode = $interfaceCode;
        return $this;
    }

    public function getParticipation(): ?int
    {
        return $this->participation;
    }

    public function setParticipation(?int $participation): static
    {
        $this->participation = $participation;
        return $this;
    }

    public function isTaxed(): ?bool
    {
        return $this->isTaxed;
    }

    public function setIsTaxed(bool $isTaxed): static
    {
        $this->isTaxed = $isTaxed;
        return $this;
    }

    public function isProcedure(): ?bool
    {
        return $this->isProcedure;
    }

    public function setIsProcedure(bool $isProcedure): static
    {
        $this->isProcedure = $isProcedure;
        return $this;
    }

    public function getFigure(): ?Figure
    {
        return $this->figure;
    }

    public function setFigure(?Figure $figure): static
    {
        $this->figure = $figure;
        return $this;
    }

    public function getBudgetItem(): ?BudgetItem
    {
        return $this->budgetItem;
    }

    public function setBudgetItem(?BudgetItem $budgetItem): static
    {
        $this->budgetItem = $budgetItem;
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

    public function getSubCompany(): ?SubCompany
    {
        return $this->subCompany;
    }

    public function setSubCompany(?SubCompany $subCompany): static
    {
        $this->subCompany = $subCompany;
        return $this;
    }

    public function getBillingSubCompany(): ?SubCompany
    {
        return $this->billingSubCompany;
    }

    public function setBillingSubCompany(?SubCompany $billingSubCompany): static
    {
        $this->billingSubCompany = $billingSubCompany;
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
        return $this->shortName ?? $this->name ?? '';
    }
}
