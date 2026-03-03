<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\BudgetSurgeryFeePriceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * BudgetSurgeryFeePrice (GuarismoPrecio)
 *
 * Tabla legacy: guarismo_precio
 *
 * Precio del pabellón quirúrgico para una MedicalService dado un convenio.
 * Es el valor usado para aplicar la regla 50/100%.
 */
#[ORM\Entity(repositoryClass: BudgetSurgeryFeePriceRepository::class)]
#[ORM\Table(name: 'budget_surgery_fee_price')]
class BudgetSurgeryFeePrice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'theatre_amount', type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $theatreAmount = null;

    #[ORM\Column(name: 'effective_date', type: 'datetime')]
    private ?\DateTimeInterface $effectiveDate = null;

    #[ORM\Column(name: 'expires_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $expiresAt = null;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: MedicalService::class)]
    #[ORM\JoinColumn(name: 'medical_service_id', nullable: false)]
    private ?MedicalService $medicalService = null;

    #[ORM\ManyToOne(targetEntity: BranchPayer::class)]
    #[ORM\JoinColumn(name: 'branch_payer_id', nullable: true)]
    private ?BranchPayer $branchPayer = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTheatreAmount(): ?string
    {
        return $this->theatreAmount;
    }

    public function setTheatreAmount(string $theatreAmount): self
    {
        $this->theatreAmount = $theatreAmount;
        return $this;
    }

    public function getEffectiveDate(): ?\DateTimeInterface
    {
        return $this->effectiveDate;
    }

    public function setEffectiveDate(\DateTimeInterface $effectiveDate): self
    {
        $this->effectiveDate = $effectiveDate;
        return $this;
    }

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getMedicalService(): ?MedicalService
    {
        return $this->medicalService;
    }

    public function setMedicalService(?MedicalService $medicalService): self
    {
        $this->medicalService = $medicalService;
        return $this;
    }

    public function getBranchPayer(): ?BranchPayer
    {
        return $this->branchPayer;
    }

    public function setBranchPayer(?BranchPayer $branchPayer): self
    {
        $this->branchPayer = $branchPayer;
        return $this;
    }
}
