<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\AdmissionRecordRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * AdmissionRecord (DatoIngreso)
 *
 * Tabla legacy: dato_ingreso
 *
 * Representa el registro de admisión o ingreso de un paciente a la clínica.
 */
#[ORM\Entity(repositoryClass: AdmissionRecordRepository::class)]
#[ORM\Table(name: 'admission_record')]
#[ORM\HasLifecycleCallbacks]
class AdmissionRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The patient visit record this admission is linked to.
     * Legacy: idPaciente → Paciente
     */
    #[ORM\OneToOne(targetEntity: Patient::class)]
    #[ORM\JoinColumn(nullable: false, unique: true)]
    private ?Patient $patient = null;

    #[ORM\Column(length: 30)]
    private string $admissionType = 'hospitalaria';

    /** Legacy: ID_ESTADO_INGRESO */
    #[ORM\ManyToOne(targetEntity: AdmissionStatus::class)]
    #[ORM\JoinColumn(name: 'admission_status_id', referencedColumnName: 'id', nullable: true)]
    private ?AdmissionStatus $admissionStatus = null;

    #[ORM\ManyToOne(targetEntity: Payer::class)]
    #[ORM\JoinColumn(name: 'payer_id', referencedColumnName: 'id', nullable: true)]
    private ?Payer $payer = null;

    #[ORM\ManyToOne(targetEntity: Agreement::class)]
    #[ORM\JoinColumn(name: 'agreement_id', referencedColumnName: 'id', nullable: true)]
    private ?Agreement $agreement = null;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(name: 'service_id', referencedColumnName: 'id', nullable: true)]
    private ?Service $service = null;

    #[ORM\ManyToOne(targetEntity: Bed::class)]
    #[ORM\JoinColumn(name: 'bed_id', referencedColumnName: 'id', nullable: true)]
    private ?Bed $bed = null;

    /** Legacy: ID_SUCURSAL */
    #[ORM\ManyToOne(targetEntity: Branch::class)]
    #[ORM\JoinColumn(name: 'branch_id', referencedColumnName: 'id', nullable: true)]
    private ?Branch $branch = null;

    /** Legacy: ID_PROFESIONAL */
    #[ORM\ManyToOne(targetEntity: Professional::class)]
    #[ORM\JoinColumn(name: 'professional_id', referencedColumnName: 'id', nullable: true)]
    private ?Professional $professional = null;

    /** Legacy: ID_MOTIVO_ANULACION_INGRESO */
    #[ORM\ManyToOne(targetEntity: CancellationReason::class)]
    #[ORM\JoinColumn(name: 'cancellation_reason_id', referencedColumnName: 'id', nullable: true)]
    private ?CancellationReason $cancellationReason = null;

    /** Legacy: ID_ESPECIALIDAD_MEDICA */
    #[ORM\ManyToOne(targetEntity: Specialty::class)]
    #[ORM\JoinColumn(name: 'specialty_id', referencedColumnName: 'id', nullable: true)]
    private ?Specialty $specialty = null;

    /** Legacy: ID_PR_PLAN */
    #[ORM\ManyToOne(targetEntity: InsurancePlan::class)]
    #[ORM\JoinColumn(name: 'insurance_plan_id', referencedColumnName: 'id', nullable: true)]
    private ?InsurancePlan $insurancePlan = null;

    /** Legacy: ID_ORIGEN */
    #[ORM\ManyToOne(targetEntity: Origin::class)]
    #[ORM\JoinColumn(name: 'origin_id', referencedColumnName: 'id', nullable: true)]
    private ?Origin $origin = null;

    /** Legacy: ID_TIPO_CUENTA */
    #[ORM\ManyToOne(targetEntity: AccountType::class)]
    #[ORM\JoinColumn(name: 'account_type_id', referencedColumnName: 'id', nullable: true)]
    private ?AccountType $accountType = null;

    /** Legacy: ID_REL_CAMA_PACIENTE */
    #[ORM\ManyToOne(targetEntity: BedPatientAssignment::class)]
    #[ORM\JoinColumn(name: 'bed_patient_assignment_id', referencedColumnName: 'id', nullable: true)]
    private ?BedPatientAssignment $bedAssignment = null;

    /**
     * Cuenta maestra de facturación de este ingreso.
     * Se crea al confirmar el ingreso y permanece abierta mientras haya pagos pendientes.
     * Legacy: sin FK directa — CuentaPaciente.idPaciente vinculada vía Paciente
     */
    #[ORM\OneToOne(targetEntity: PatientAccount::class)]
    #[ORM\JoinColumn(name: 'patient_account_id', referencedColumnName: 'id', nullable: true)]
    private ?PatientAccount $patientAccount = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $triage = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $consultationReason = null;

    /** Legacy: FECHA_INGRESO */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $admissionDate = null;

    /** Legacy: FECHA_PREADMISION */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $preAdmissionDate = null;

    /** Legacy: FECHA_ANULACION */
    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $cancellationDate = null;

    /** Legacy: NUMERO */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $number = null;

    /** Legacy: INGRESO_QUIRURGICO */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isSurgicalAdmission = false;

    /** Legacy: ORDEN_MEDICA */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $hasMedicalOrder = false;

    /** Legacy: OBSERVACION */
    #[ORM\Column(length: 240, nullable: true)]
    private ?string $notes = null;

    /** Legacy: EMERGENCIA_AVISO */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $emergencyNotice = null;

    /** Legacy: EMERGENCIA_TELEFONO */
    #[ORM\Column(length: 10, nullable: true)]
    private ?string $emergencyPhone = null;

    /** Legacy: NOMBRE_ARCHIVO_ORDEN_MEDICA */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $medicalOrderFile = null;

    /** Legacy: OTRO_ORIGEN */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $otherOrigin = null;

    /** Legacy: OBSERVACION_ANULACION */
    #[ORM\Column(length: 2000, nullable: true)]
    private ?string $cancellationNotes = null;

    /** Legacy: CON_HONORARIOS */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $withFees = true;

    /** Legacy: DAU (Días de Atención Urgencia) */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $dau = null;

    /** Legacy: MEDICO_DERIVADOR */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $referringDoctor = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
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

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(Patient $patient): self
    {
        $this->patient = $patient;
        return $this;
    }

    public function getAdmissionType(): string
    {
        return $this->admissionType;
    }

    public function setAdmissionType(string $admissionType): self
    {
        $this->admissionType = $admissionType;
        return $this;
    }

    public function getAdmissionStatus(): ?AdmissionStatus
    {
        return $this->admissionStatus;
    }

    public function setAdmissionStatus(?AdmissionStatus $admissionStatus): self
    {
        $this->admissionStatus = $admissionStatus;
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

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;
        return $this;
    }

    public function getBed(): ?Bed
    {
        return $this->bed;
    }

    public function setBed(?Bed $bed): self
    {
        $this->bed = $bed;
        return $this;
    }

    public function getTriage(): ?string
    {
        return $this->triage;
    }

    public function setTriage(?string $triage): self
    {
        $this->triage = $triage;
        return $this;
    }

    public function getConsultationReason(): ?string
    {
        return $this->consultationReason;
    }

    public function setConsultationReason(?string $consultationReason): self
    {
        $this->consultationReason = $consultationReason;
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

    public function getCancellationReason(): ?CancellationReason
    {
        return $this->cancellationReason;
    }

    public function setCancellationReason(?CancellationReason $cancellationReason): self
    {
        $this->cancellationReason = $cancellationReason;
        return $this;
    }

    public function getSpecialty(): ?Specialty
    {
        return $this->specialty;
    }

    public function setSpecialty(?Specialty $specialty): self
    {
        $this->specialty = $specialty;
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

    public function getOrigin(): ?Origin
    {
        return $this->origin;
    }

    public function setOrigin(?Origin $origin): self
    {
        $this->origin = $origin;
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

    public function getPreAdmissionDate(): ?\DateTimeInterface
    {
        return $this->preAdmissionDate;
    }

    public function setPreAdmissionDate(?\DateTimeInterface $preAdmissionDate): self
    {
        $this->preAdmissionDate = $preAdmissionDate;
        return $this;
    }

    public function getCancellationDate(): ?\DateTimeInterface
    {
        return $this->cancellationDate;
    }

    public function setCancellationDate(?\DateTimeInterface $cancellationDate): self
    {
        $this->cancellationDate = $cancellationDate;
        return $this;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): self
    {
        $this->number = $number;
        return $this;
    }

    public function isSurgicalAdmission(): bool
    {
        return $this->isSurgicalAdmission;
    }

    public function setIsSurgicalAdmission(bool $isSurgicalAdmission): self
    {
        $this->isSurgicalAdmission = $isSurgicalAdmission;
        return $this;
    }

    public function isHasMedicalOrder(): bool
    {
        return $this->hasMedicalOrder;
    }

    public function setHasMedicalOrder(bool $hasMedicalOrder): self
    {
        $this->hasMedicalOrder = $hasMedicalOrder;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getEmergencyNotice(): ?string
    {
        return $this->emergencyNotice;
    }

    public function setEmergencyNotice(?string $emergencyNotice): self
    {
        $this->emergencyNotice = $emergencyNotice;
        return $this;
    }

    public function getEmergencyPhone(): ?string
    {
        return $this->emergencyPhone;
    }

    public function setEmergencyPhone(?string $emergencyPhone): self
    {
        $this->emergencyPhone = $emergencyPhone;
        return $this;
    }

    public function getMedicalOrderFile(): ?string
    {
        return $this->medicalOrderFile;
    }

    public function setMedicalOrderFile(?string $medicalOrderFile): self
    {
        $this->medicalOrderFile = $medicalOrderFile;
        return $this;
    }

    public function getOtherOrigin(): ?string
    {
        return $this->otherOrigin;
    }

    public function setOtherOrigin(?string $otherOrigin): self
    {
        $this->otherOrigin = $otherOrigin;
        return $this;
    }

    public function getCancellationNotes(): ?string
    {
        return $this->cancellationNotes;
    }

    public function setCancellationNotes(?string $cancellationNotes): self
    {
        $this->cancellationNotes = $cancellationNotes;
        return $this;
    }

    public function isWithFees(): bool
    {
        return $this->withFees;
    }

    public function setWithFees(bool $withFees): self
    {
        $this->withFees = $withFees;
        return $this;
    }

    public function getDau(): ?int
    {
        return $this->dau;
    }

    public function setDau(?int $dau): self
    {
        $this->dau = $dau;
        return $this;
    }

    public function getReferringDoctor(): ?string
    {
        return $this->referringDoctor;
    }

    public function setReferringDoctor(?string $referringDoctor): self
    {
        $this->referringDoctor = $referringDoctor;
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

    public function getBedAssignment(): ?BedPatientAssignment
    {
        return $this->bedAssignment;
    }

    public function setBedAssignment(?BedPatientAssignment $bedAssignment): self
    {
        $this->bedAssignment = $bedAssignment;
        return $this;
    }

    public function getPatientAccount(): ?PatientAccount
    {
        return $this->patientAccount;
    }

    public function setPatientAccount(?PatientAccount $patientAccount): self
    {
        $this->patientAccount = $patientAccount;
        return $this;
    }
}
