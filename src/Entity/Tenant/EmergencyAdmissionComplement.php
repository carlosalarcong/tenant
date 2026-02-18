<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\EmergencyAdmissionComplementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * EmergencyAdmissionComplement (DatoIngresoComplementoUrgencia)
 *
 * Legacy table: dato_ingreso_complemento_urgencia
 * Spanish name: Dato Ingreso Complemento Urgencia
 *
 * Stores emergency-specific data associated with an admission record.
 * Includes triage information, discharge data, and accident details.
 */
#[ORM\Entity(repositoryClass: EmergencyAdmissionComplementRepository::class)]
#[ORM\Table(name: 'emergency_admission_complement')]
#[ORM\HasLifecycleCallbacks]
class EmergencyAdmissionComplement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Legacy: ID_DATO_INGRESO */
    #[ORM\ManyToOne(targetEntity: AdmissionRecord::class)]
    #[ORM\JoinColumn(name: 'admission_record_id', referencedColumnName: 'id', nullable: false)]
    private ?AdmissionRecord $admissionRecord = null;

    /** Legacy: ID_TIPO_CONSULTA_URGENCIA */
    #[ORM\ManyToOne(targetEntity: EmergencyConsultationType::class)]
    #[ORM\JoinColumn(name: 'emergency_consultation_type_id', referencedColumnName: 'id', nullable: true)]
    private ?EmergencyConsultationType $emergencyConsultationType = null;

    /** Legacy: ID_CONVENIO_EMPRESA */
    #[ORM\ManyToOne(targetEntity: CompanyAgreement::class)]
    #[ORM\JoinColumn(name: 'company_agreement_id', referencedColumnName: 'id', nullable: true)]
    private ?CompanyAgreement $companyAgreement = null;

    /** Legacy: ID_CATEGORIZACION */
    #[ORM\ManyToOne(targetEntity: TriageCategory::class)]
    #[ORM\JoinColumn(name: 'triage_category_id', referencedColumnName: 'id', nullable: true)]
    private ?TriageCategory $triageCategory = null;

    /** Legacy: AVISO_CARABINEROS */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $noticePolice = false;

    /** Legacy: ACOMPANIADO_POR */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $accompaniedBy = null;

    /** Legacy: LLEGO_EN */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $arrivedBy = null;

    /** Legacy: LUGAR_ACCIDENTE */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $accidentLocation = null;

    /** Legacy: TIPO_ACCIDENTE */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $accidentType = null;

    /** Legacy: OTRO_TIPO_CONSULTA */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $otherConsultationType = null;

    /** Legacy: FECHA_CATEGORIZACION */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $triageDate = null;

    /** Legacy: CLAVE_AZUL */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $codeBlue = false;

    /** Legacy: OBSERVACION_ALTA */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $dischargeNotes = null;

    /** Legacy: FECHA_ALTA */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dischargeDate = null;

    /** Legacy: NOMBRE_ROL_USUARIO_ALTA */
    #[ORM\Column(length: 150, nullable: true)]
    private ?string $dischargeUserRole = null;

    /** Legacy: FECHA_ALTA_ACTUALIZACION */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $dischargeUpdateDate = null;

    /** Legacy: NOMBRE_ROL_USUARIO_ALTA_ACTUALIZACION */
    #[ORM\Column(length: 150, nullable: true)]
    private ?string $dischargeUpdateUserRole = null;

    #[ORM\OneToMany(mappedBy: 'emergencyAdmissionComplement', targetEntity: EmergencyAdmissionComplementDetail::class, cascade: ['persist', 'remove'])]
    private Collection $details;

    public function __construct()
    {
        $this->details = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdmissionRecord(): ?AdmissionRecord
    {
        return $this->admissionRecord;
    }

    public function setAdmissionRecord(?AdmissionRecord $admissionRecord): self
    {
        $this->admissionRecord = $admissionRecord;
        return $this;
    }

    public function getEmergencyConsultationType(): ?EmergencyConsultationType
    {
        return $this->emergencyConsultationType;
    }

    public function setEmergencyConsultationType(?EmergencyConsultationType $emergencyConsultationType): self
    {
        $this->emergencyConsultationType = $emergencyConsultationType;
        return $this;
    }

    public function getCompanyAgreement(): ?CompanyAgreement
    {
        return $this->companyAgreement;
    }

    public function setCompanyAgreement(?CompanyAgreement $companyAgreement): self
    {
        $this->companyAgreement = $companyAgreement;
        return $this;
    }

    public function getTriageCategory(): ?TriageCategory
    {
        return $this->triageCategory;
    }

    public function setTriageCategory(?TriageCategory $triageCategory): self
    {
        $this->triageCategory = $triageCategory;
        return $this;
    }

    public function isNoticePolice(): bool
    {
        return $this->noticePolice;
    }

    public function setNoticePolice(bool $noticePolice): self
    {
        $this->noticePolice = $noticePolice;
        return $this;
    }

    public function getAccompaniedBy(): ?string
    {
        return $this->accompaniedBy;
    }

    public function setAccompaniedBy(?string $accompaniedBy): self
    {
        $this->accompaniedBy = $accompaniedBy;
        return $this;
    }

    public function getArrivedBy(): ?string
    {
        return $this->arrivedBy;
    }

    public function setArrivedBy(?string $arrivedBy): self
    {
        $this->arrivedBy = $arrivedBy;
        return $this;
    }

    public function getAccidentLocation(): ?string
    {
        return $this->accidentLocation;
    }

    public function setAccidentLocation(?string $accidentLocation): self
    {
        $this->accidentLocation = $accidentLocation;
        return $this;
    }

    public function getAccidentType(): ?string
    {
        return $this->accidentType;
    }

    public function setAccidentType(?string $accidentType): self
    {
        $this->accidentType = $accidentType;
        return $this;
    }

    public function getOtherConsultationType(): ?string
    {
        return $this->otherConsultationType;
    }

    public function setOtherConsultationType(?string $otherConsultationType): self
    {
        $this->otherConsultationType = $otherConsultationType;
        return $this;
    }

    public function getTriageDate(): ?\DateTimeInterface
    {
        return $this->triageDate;
    }

    public function setTriageDate(?\DateTimeInterface $triageDate): self
    {
        $this->triageDate = $triageDate;
        return $this;
    }

    public function isCodeBlue(): bool
    {
        return $this->codeBlue;
    }

    public function setCodeBlue(bool $codeBlue): self
    {
        $this->codeBlue = $codeBlue;
        return $this;
    }

    public function getDischargeNotes(): ?string
    {
        return $this->dischargeNotes;
    }

    public function setDischargeNotes(?string $dischargeNotes): self
    {
        $this->dischargeNotes = $dischargeNotes;
        return $this;
    }

    public function getDischargeDate(): ?\DateTimeInterface
    {
        return $this->dischargeDate;
    }

    public function setDischargeDate(?\DateTimeInterface $dischargeDate): self
    {
        $this->dischargeDate = $dischargeDate;
        return $this;
    }

    public function getDischargeUserRole(): ?string
    {
        return $this->dischargeUserRole;
    }

    public function setDischargeUserRole(?string $dischargeUserRole): self
    {
        $this->dischargeUserRole = $dischargeUserRole;
        return $this;
    }

    public function getDischargeUpdateDate(): ?\DateTimeInterface
    {
        return $this->dischargeUpdateDate;
    }

    public function setDischargeUpdateDate(?\DateTimeInterface $dischargeUpdateDate): self
    {
        $this->dischargeUpdateDate = $dischargeUpdateDate;
        return $this;
    }

    public function getDischargeUpdateUserRole(): ?string
    {
        return $this->dischargeUpdateUserRole;
    }

    public function setDischargeUpdateUserRole(?string $dischargeUpdateUserRole): self
    {
        $this->dischargeUpdateUserRole = $dischargeUpdateUserRole;
        return $this;
    }

    /** @return Collection<int, EmergencyAdmissionComplementDetail> */
    public function getDetails(): Collection
    {
        return $this->details;
    }

    public function addDetail(EmergencyAdmissionComplementDetail $detail): self
    {
        if (!$this->details->contains($detail)) {
            $this->details->add($detail);
            $detail->setEmergencyAdmissionComplement($this);
        }
        return $this;
    }

    public function removeDetail(EmergencyAdmissionComplementDetail $detail): self
    {
        if ($this->details->removeElement($detail)) {
            if ($detail->getEmergencyAdmissionComplement() === $this) {
                $detail->setEmergencyAdmissionComplement(null);
            }
        }
        return $this;
    }
}
