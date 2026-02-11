<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\PatientRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Patient (Paciente)
 *
 * Legacy table: paciente
 * Spanish name: Paciente / Registro de Atención
 *
 * Represents a patient visit/admission event linking a Person to
 * an admission encounter with financial and clinical context.
 * Each record corresponds to one care event (numero_atencion).
 */
#[ORM\Entity(repositoryClass: PatientRepository::class)]
#[ORM\Table(name: 'patient')]
#[ORM\HasLifecycleCallbacks]
class Patient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The person (patient) receiving care.
     * Legacy: idPnatural
     */
    #[ORM\ManyToOne(targetEntity: Person::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    /**
     * The person acting as legal guardian/tutor (e.g. for minors).
     * Legacy: idTutor
     */
    #[ORM\ManyToOne(targetEntity: Person::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Person $tutor = null;

    /**
     * Financial guarantor / healthcare payer (financiador).
     * Legacy: idFinanciador → Prevision
     */
    #[ORM\ManyToOne(targetEntity: Payer::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Payer $payer = null;

    /**
     * Specific insurance agreement/plan (convenio).
     * Legacy: idConvenio → Prevision
     */
    #[ORM\ManyToOne(targetEntity: Agreement::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Agreement $agreement = null;

    /**
     * Insurance plan under the agreement.
     * Legacy: idPlan → PrPlan
     */
    #[ORM\ManyToOne(targetEntity: InsurancePlan::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?InsurancePlan $insurancePlan = null;

    /**
     * Type of billing/care account.
     * Legacy: idTipoAtencionFc → TipoAtencionFc
     */
    #[ORM\ManyToOne(targetEntity: CareType::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?CareType $careType = null;

    /**
     * Origin of the patient referral.
     * Legacy: idOrigen → Origen
     */
    #[ORM\ManyToOne(targetEntity: Origin::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Origin $origin = null;

    /**
     * External referrer (derivador externo).
     * Legacy: idDerivadorExterno → DerivadorExterno
     */
    #[ORM\ManyToOne(targetEntity: ExternalReferrer::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?ExternalReferrer $externalReferrer = null;

    /**
     * Attending professional.
     * Legacy: idProfesional → UsuariosRebsol
     */
    #[ORM\ManyToOne(targetEntity: Professional::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Professional $professional = null;

    /**
     * Requesting company (empresa solicitante).
     * Legacy: idEmpresaSolicitante → EmpresaSolicitante
     */
    #[ORM\ManyToOne(targetEntity: RequestingCompany::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?RequestingCompany $requestingCompany = null;

    /**
     * Internal event identifier (correlativo de evento).
     * Legacy: evento
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $eventNumber = null;

    /**
     * Sequential care number (número de atención).
     * Legacy: numeroAtencion
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $careNumber = null;

    /**
     * Date and time of admission.
     * Legacy: fechaIngreso
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $admissionDate = null;

    /**
     * Whether patient was referred from outside the facility.
     * Legacy: esExterno (int used as boolean)
     */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isExternal = false;

    /**
     * Name of external professional (when isExternal = true).
     * Legacy: profesionalExterno
     */
    #[ORM\Column(length: 150, nullable: true)]
    private ?string $externalProfessional = null;

    /**
     * Exam/procedure order reference.
     * Legacy: ordenExamen
     */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $examOrder = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(Person $person): self
    {
        $this->person = $person;
        return $this;
    }

    public function getTutor(): ?Person
    {
        return $this->tutor;
    }

    public function setTutor(?Person $tutor): self
    {
        $this->tutor = $tutor;
        return $this;
    }

    public function getPayer(): ?Payer
    {
        return $this->payer;
    }

    public function setPayer(Payer $payer): self
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

    public function getCareType(): ?CareType
    {
        return $this->careType;
    }

    public function setCareType(CareType $careType): self
    {
        $this->careType = $careType;
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

    public function getExternalReferrer(): ?ExternalReferrer
    {
        return $this->externalReferrer;
    }

    public function setExternalReferrer(?ExternalReferrer $externalReferrer): self
    {
        $this->externalReferrer = $externalReferrer;
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

    public function getRequestingCompany(): ?RequestingCompany
    {
        return $this->requestingCompany;
    }

    public function setRequestingCompany(?RequestingCompany $requestingCompany): self
    {
        $this->requestingCompany = $requestingCompany;
        return $this;
    }

    public function getEventNumber(): ?int
    {
        return $this->eventNumber;
    }

    public function setEventNumber(?int $eventNumber): self
    {
        $this->eventNumber = $eventNumber;
        return $this;
    }

    public function getCareNumber(): ?int
    {
        return $this->careNumber;
    }

    public function setCareNumber(?int $careNumber): self
    {
        $this->careNumber = $careNumber;
        return $this;
    }

    public function getAdmissionDate(): ?\DateTimeInterface
    {
        return $this->admissionDate;
    }

    public function setAdmissionDate(?\DateTimeInterface $admissionDate): self
    {
        $this->admissionDate = $admissionDate;
        return $this;
    }

    public function isExternal(): bool
    {
        return $this->isExternal;
    }

    public function setIsExternal(bool $isExternal): self
    {
        $this->isExternal = $isExternal;
        return $this;
    }

    public function getExternalProfessional(): ?string
    {
        return $this->externalProfessional;
    }

    public function setExternalProfessional(?string $externalProfessional): self
    {
        $this->externalProfessional = $externalProfessional;
        return $this;
    }

    public function getExamOrder(): ?string
    {
        return $this->examOrder;
    }

    public function setExamOrder(?string $examOrder): self
    {
        $this->examOrder = $examOrder;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
