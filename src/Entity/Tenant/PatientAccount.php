<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\PatientAccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * PatientAccount (CuentaPaciente)
 *
 * Tabla legacy: cuenta_paciente
 *
 * Cuenta maestra de facturación de un ingreso. Vinculada 1:1 con AdmissionRecord.
 * Permanece abierta mientras alguno de sus PaymentAccount esté pendiente.
 * Solo cierra cuando todos los PaymentAccount quedan regularizados.
 */
#[ORM\Entity(repositoryClass: PatientAccountRepository::class)]
#[ORM\Table(name: 'patient_account')]
class PatientAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Paciente al que pertenece esta cuenta.
     * Legacy: ID_PACIENTE → Paciente
     */
    #[ORM\ManyToOne(targetEntity: Patient::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private ?Patient $patient = null;

    /**
     * Estado actual de la cuenta maestra (abierta / cerrada / pagada).
     * Legacy: ID_ESTADO_CUENTA → EstadoCuenta
     */
    #[ORM\ManyToOne(targetEntity: AccountStatus::class)]
    #[ORM\JoinColumn(name: 'account_status_id', referencedColumnName: 'id', nullable: true)]
    private ?AccountStatus $accountStatus = null;

    /**
     * Miembro que realizó la última modificación en esta cuenta.
     * Legacy: ID_USUARIO_MODIFICACION → UsuariosRebsol
     */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'modified_by_member_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $modifiedByMember = null;

    /**
     * Registros de pago individuales que componen esta cuenta.
     * Regla de negocio: la cuenta permanece abierta mientras algún pago esté pendiente.
     * Legacy: lado inverso de PagoCuenta.idCuentaPaciente
     */
    #[ORM\OneToMany(targetEntity: PaymentAccount::class, mappedBy: 'patientAccount', cascade: ['persist'], orphanRemoval: true)]
    private Collection $paymentAccounts;

    /** Legacy: TOTAL_CUENTA */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $totalAccount = null;

    /**
     * Monto afecto a honorarios.
     * Legacy: AFECTO_CUENTA
     */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $affectedAccount = null;

    /** Legacy: PREGUNTA_UNO */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $questionOne = null;

    /** Legacy: PREGUNTA_DOS */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $questionTwo = null;

    /** Legacy: TOTAL_PRECUENTA */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $totalPreAccount = null;

    /** Legacy: NUMERO_PRECUENTA */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $preAccountNumber = null;

    /** Legacy: TOTAL_DESCUENTO */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $totalDiscount = null;

    /** RUT del funcionario que corrigió el saldo. Legacy: RUT_CORRIGE_SALDO */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $balanceCorrectionRut = null;

    /** Legacy: SALDO_CUENTA */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $accountBalance = null;

    /** Total de prestaciones paquetizadas. Legacy: TOTAL_CUENTA_PAQUETIZADO */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $totalPackagedAccount = null;

    /** Legacy: FECHA_MODIFICACION */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $modifiedAt = null;

    public function __construct()
    {
        $this->paymentAccounts = new ArrayCollection();
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

    public function getAccountStatus(): ?AccountStatus
    {
        return $this->accountStatus;
    }

    public function setAccountStatus(?AccountStatus $accountStatus): self
    {
        $this->accountStatus = $accountStatus;
        return $this;
    }

    public function getModifiedByMember(): ?Member
    {
        return $this->modifiedByMember;
    }

    public function setModifiedByMember(?Member $modifiedByMember): self
    {
        $this->modifiedByMember = $modifiedByMember;
        return $this;
    }

    /** @return Collection<int, PaymentAccount> */
    public function getPaymentAccounts(): Collection
    {
        return $this->paymentAccounts;
    }

    public function addPaymentAccount(PaymentAccount $paymentAccount): self
    {
        if (!$this->paymentAccounts->contains($paymentAccount)) {
            $this->paymentAccounts->add($paymentAccount);
            $paymentAccount->setPatientAccount($this);
        }
        return $this;
    }

    public function removePaymentAccount(PaymentAccount $paymentAccount): self
    {
        if ($this->paymentAccounts->removeElement($paymentAccount)) {
            if ($paymentAccount->getPatientAccount() === $this) {
                $paymentAccount->setPatientAccount(null);
            }
        }
        return $this;
    }

    public function getTotalAccount(): ?string
    {
        return $this->totalAccount;
    }

    public function setTotalAccount(?string $totalAccount): self
    {
        $this->totalAccount = $totalAccount;
        return $this;
    }

    public function getAffectedAccount(): ?string
    {
        return $this->affectedAccount;
    }

    public function setAffectedAccount(?string $affectedAccount): self
    {
        $this->affectedAccount = $affectedAccount;
        return $this;
    }

    public function getQuestionOne(): ?int
    {
        return $this->questionOne;
    }

    public function setQuestionOne(?int $questionOne): self
    {
        $this->questionOne = $questionOne;
        return $this;
    }

    public function getQuestionTwo(): ?int
    {
        return $this->questionTwo;
    }

    public function setQuestionTwo(?int $questionTwo): self
    {
        $this->questionTwo = $questionTwo;
        return $this;
    }

    public function getTotalPreAccount(): ?string
    {
        return $this->totalPreAccount;
    }

    public function setTotalPreAccount(?string $totalPreAccount): self
    {
        $this->totalPreAccount = $totalPreAccount;
        return $this;
    }

    public function getPreAccountNumber(): ?int
    {
        return $this->preAccountNumber;
    }

    public function setPreAccountNumber(?int $preAccountNumber): self
    {
        $this->preAccountNumber = $preAccountNumber;
        return $this;
    }

    public function getTotalDiscount(): ?string
    {
        return $this->totalDiscount;
    }

    public function setTotalDiscount(?string $totalDiscount): self
    {
        $this->totalDiscount = $totalDiscount;
        return $this;
    }

    public function getBalanceCorrectionRut(): ?int
    {
        return $this->balanceCorrectionRut;
    }

    public function setBalanceCorrectionRut(?int $balanceCorrectionRut): self
    {
        $this->balanceCorrectionRut = $balanceCorrectionRut;
        return $this;
    }

    public function getAccountBalance(): ?string
    {
        return $this->accountBalance;
    }

    public function setAccountBalance(?string $accountBalance): self
    {
        $this->accountBalance = $accountBalance;
        return $this;
    }

    public function getTotalPackagedAccount(): ?string
    {
        return $this->totalPackagedAccount;
    }

    public function setTotalPackagedAccount(?string $totalPackagedAccount): self
    {
        $this->totalPackagedAccount = $totalPackagedAccount;
        return $this;
    }

    public function getModifiedAt(): ?\DateTimeInterface
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(?\DateTimeInterface $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;
        return $this;
    }
}
