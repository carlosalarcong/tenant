<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\BudgetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Budget (Presupuesto)
 *
 * Tabla legacy: presupuesto
 *
 * Presupuesto médico generado para un paciente.
 */
#[ORM\Entity(repositoryClass: BudgetRepository::class)]
#[ORM\Table(name: 'budget')]
class Budget
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'number', type: 'integer')]
    private ?int $number = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'expires_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $expiresAt = null;

    #[ORM\Column(name: 'footer_text', type: Types::TEXT, nullable: true)]
    private ?string $footerText = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observation = null;

    #[ORM\Column(name: 'is_ambulatory', type: 'boolean', options: ['default' => false])]
    private bool $isAmbulatory = false;

    #[ORM\Column(name: 'includes_honorariums', type: 'boolean', options: ['default' => true])]
    private bool $includesHonorariums = true;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'active'])]
    private string $status = 'active';

    #[ORM\ManyToOne(targetEntity: Person::class)]
    #[ORM\JoinColumn(name: 'person_id', nullable: false)]
    private ?Person $person = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'member_id', nullable: false)]
    private ?Member $member = null;

    #[ORM\ManyToOne(targetEntity: Branch::class)]
    #[ORM\JoinColumn(name: 'branch_id', nullable: false)]
    private ?Branch $branch = null;

    #[ORM\ManyToOne(targetEntity: Professional::class)]
    #[ORM\JoinColumn(name: 'professional_id', nullable: true)]
    private ?Professional $professional = null;

    #[ORM\ManyToOne(targetEntity: Payer::class)]
    #[ORM\JoinColumn(name: 'payer_id', nullable: true)]
    private ?Payer $payer = null;

    #[ORM\ManyToOne(targetEntity: Agreement::class)]
    #[ORM\JoinColumn(name: 'agreement_id', nullable: true)]
    private ?Agreement $agreement = null;

    #[ORM\ManyToOne(targetEntity: InsurancePlan::class)]
    #[ORM\JoinColumn(name: 'insurance_plan_id', nullable: true)]
    private ?InsurancePlan $insurancePlan = null;

    #[ORM\ManyToOne(targetEntity: SurgeryPackagePlan::class)]
    #[ORM\JoinColumn(name: 'surgery_package_plan_id', nullable: true)]
    private ?SurgeryPackagePlan $surgeryPackagePlan = null;

    #[ORM\ManyToOne(targetEntity: CareType::class)]
    #[ORM\JoinColumn(name: 'care_type_id', nullable: true)]
    private ?CareType $careType = null;

    #[ORM\ManyToOne(targetEntity: AccountType::class)]
    #[ORM\JoinColumn(name: 'account_type_id', nullable: true)]
    private ?AccountType $accountType = null;

    #[ORM\ManyToOne(targetEntity: Origin::class)]
    #[ORM\JoinColumn(name: 'origin_id', nullable: true)]
    private ?Origin $origin = null;

    /**
     * @var Collection<int, BudgetDetail>
     */
    #[ORM\OneToMany(mappedBy: 'budget', targetEntity: BudgetDetail::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $details;

    /**
     * @var Collection<int, BudgetObservation>
     */
    #[ORM\OneToMany(mappedBy: 'budget', targetEntity: BudgetObservation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $observations;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->details = new ArrayCollection();
        $this->observations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;
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

    public function getExpiresAt(): ?\DateTimeInterface
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeInterface $expiresAt): self
    {
        $this->expiresAt = $expiresAt;
        return $this;
    }

    public function getFooterText(): ?string
    {
        return $this->footerText;
    }

    public function setFooterText(?string $footerText): self
    {
        $this->footerText = $footerText;
        return $this;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(?string $observation): self
    {
        $this->observation = $observation;
        return $this;
    }

    public function isAmbulatory(): bool
    {
        return $this->isAmbulatory;
    }

    public function setIsAmbulatory(bool $isAmbulatory): self
    {
        $this->isAmbulatory = $isAmbulatory;
        return $this;
    }

    public function isIncludesHonorariums(): bool
    {
        return $this->includesHonorariums;
    }

    public function setIncludesHonorariums(bool $includesHonorariums): self
    {
        $this->includesHonorariums = $includesHonorariums;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;
        return $this;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;
        return $this;
    }

    public function getBranch(): ?Branch
    {
        return $this->branch;
    }

    public function setBranch(?Branch $branch): self
    {
        $this->branch = $branch;
        return $this;
    }

    public function getProfessional(): ?Professional
    {
        return $this->professional;
    }

    public function setProfessional(?Professional $professional): self
    {
        $this->professional = $professional;
        return $this;
    }

    public function getPayer(): ?Payer
    {
        return $this->payer;
    }

    public function setPayer(?Payer $payer): self
    {
        $this->payer = $payer;
        return $this;
    }

    public function getAgreement(): ?Agreement
    {
        return $this->agreement;
    }

    public function setAgreement(?Agreement $agreement): self
    {
        $this->agreement = $agreement;
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

    public function getSurgeryPackagePlan(): ?SurgeryPackagePlan
    {
        return $this->surgeryPackagePlan;
    }

    public function setSurgeryPackagePlan(?SurgeryPackagePlan $surgeryPackagePlan): self
    {
        $this->surgeryPackagePlan = $surgeryPackagePlan;
        return $this;
    }

    public function getCareType(): ?CareType
    {
        return $this->careType;
    }

    public function setCareType(?CareType $careType): self
    {
        $this->careType = $careType;
        return $this;
    }

    public function getAccountType(): ?AccountType
    {
        return $this->accountType;
    }

    public function setAccountType(?AccountType $accountType): self
    {
        $this->accountType = $accountType;
        return $this;
    }

    public function getOrigin(): ?Origin
    {
        return $this->origin;
    }

    public function setOrigin(?Origin $origin): self
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * @return Collection<int, BudgetDetail>
     */
    public function getDetails(): Collection
    {
        return $this->details;
    }

    public function addDetail(BudgetDetail $detail): self
    {
        if (!$this->details->contains($detail)) {
            $this->details->add($detail);
            $detail->setBudget($this);
        }
        return $this;
    }

    public function removeDetail(BudgetDetail $detail): self
    {
        if ($this->details->removeElement($detail) && $detail->getBudget() === $this) {
            $detail->setBudget(null);
        }
        return $this;
    }

    /**
     * @return Collection<int, BudgetObservation>
     */
    public function getObservations(): Collection
    {
        return $this->observations;
    }

    public function addObservation(BudgetObservation $observation): self
    {
        if (!$this->observations->contains($observation)) {
            $this->observations->add($observation);
            $observation->setBudget($this);
        }
        return $this;
    }

    public function removeObservation(BudgetObservation $observation): self
    {
        if ($this->observations->removeElement($observation) && $observation->getBudget() === $this) {
            $observation->setBudget(null);
        }
        return $this;
    }
}
