<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\OpenPlanDistributionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * OpenPlanDistribution (AbiertaDistribucion)
 *
 * Tabla legacy: abierta_distribucion
 *
 * Distribución de montos abiertos entre honorarios, clínico y valorizable.
 */
#[ORM\Entity(repositoryClass: OpenPlanDistributionRepository::class)]
#[ORM\Table(name: 'open_plan_distribution')]
class OpenPlanDistribution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['default' => '0.00'])]
    private string $honorariumAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['default' => '0.00'])]
    private string $clinicalAmount = '0.00';

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, options: ['default' => '0.00'])]
    private string $valuableAmount = '0.00';

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $effectiveDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expirationDate = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: MedicalService::class)]
    #[ORM\JoinColumn(name: 'medical_service_id', referencedColumnName: 'id', nullable: false)]
    private ?MedicalService $medicalService = null;

    #[ORM\ManyToOne(targetEntity: InsurancePlan::class)]
    #[ORM\JoinColumn(name: 'insurance_plan_id', referencedColumnName: 'id', nullable: false)]
    private ?InsurancePlan $insurancePlan = null;

    #[ORM\ManyToOne(targetEntity: BranchPayer::class)]
    #[ORM\JoinColumn(name: 'branch_payer_id', referencedColumnName: 'id', nullable: true)]
    private ?BranchPayer $branchPayer = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHonorariumAmount(): string
    {
        return $this->honorariumAmount;
    }

    public function setHonorariumAmount(string $honorariumAmount): self
    {
        $this->honorariumAmount = $honorariumAmount;
        return $this;
    }

    public function getClinicalAmount(): string
    {
        return $this->clinicalAmount;
    }

    public function setClinicalAmount(string $clinicalAmount): self
    {
        $this->clinicalAmount = $clinicalAmount;
        return $this;
    }

    public function getValuableAmount(): string
    {
        return $this->valuableAmount;
    }

    public function setValuableAmount(string $valuableAmount): self
    {
        $this->valuableAmount = $valuableAmount;
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

    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?\DateTimeInterface $expirationDate): self
    {
        $this->expirationDate = $expirationDate;
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

    public function getInsurancePlan(): ?InsurancePlan
    {
        return $this->insurancePlan;
    }

    public function setInsurancePlan(?InsurancePlan $insurancePlan): self
    {
        $this->insurancePlan = $insurancePlan;
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
