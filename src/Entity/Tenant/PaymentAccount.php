<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\PaymentAccountRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * PaymentAccount (PagoCuenta)
 *
 * Tabla legacy: pago_cuenta
 *
 * Registro de pago individual asociado a una PatientAccount (cuenta maestra).
 * Una PatientAccount puede tener N PaymentAccount.
 * Regla de negocio: mientras algún PaymentAccount esté pendiente (paymentStatus = pendiente),
 * la PatientAccount padre debe permanecer abierta.
 */
#[ORM\Entity(repositoryClass: PaymentAccountRepository::class)]
#[ORM\Table(name: 'payment_account')]
class PaymentAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Cuenta maestra a la que pertenece este pago.
     * Legacy: ID_CUENTA_PACIENTE → CuentaPaciente
     */
    #[ORM\ManyToOne(targetEntity: PatientAccount::class, inversedBy: 'paymentAccounts')]
    #[ORM\JoinColumn(name: 'patient_account_id', referencedColumnName: 'id', nullable: false)]
    private ?PatientAccount $patientAccount = null;

    /**
     * Registro de paciente asociado a este pago.
     * Legacy: ID_PACIENTE → Paciente
     */
    #[ORM\ManyToOne(targetEntity: Patient::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: true)]
    private ?Patient $patient = null;

    /**
     * Estado actual de este pago individual (pendiente, regularizado, anulado).
     * Legacy: ID_ESTADO_PAGO → EstadoPago
     */
    #[ORM\ManyToOne(targetEntity: PaymentStatus::class)]
    #[ORM\JoinColumn(name: 'payment_status_id', referencedColumnName: 'id', nullable: true)]
    private ?PaymentStatus $paymentStatus = null;

    /**
     * Miembro que registró o procesó este pago.
     * Legacy: ID_USUARIO → UsuariosRebsol
     */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'created_by_member_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $createdByMember = null;

    /**
     * Miembro que anuló este pago (si aplica).
     * Legacy: ID_USUARIO_ANULACION → UsuariosRebsol
     */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'cancelled_by_member_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $cancelledByMember = null;

    /**
     * Caja donde se procesó el pago.
     * Legacy: ID_CAJA → Caja
     */
    #[ORM\ManyToOne(targetEntity: CashRegisterLocation::class)]
    #[ORM\JoinColumn(name: 'cash_register_location_id', referencedColumnName: 'id', nullable: true)]
    private ?CashRegisterLocation $cashRegisterLocation = null;

    /**
     * Sesión de caja abierta que procesó este pago (para nrocaja en DTE).
     * Legacy: ID_CAJA_SESION → CajaAbierta
     */
    #[ORM\ManyToOne(targetEntity: CashRegister::class)]
    #[ORM\JoinColumn(name: 'cash_register_id', referencedColumnName: 'id', nullable: true)]
    private ?CashRegister $cashRegister = null;

    /**
     * Sub-empresa asociada a este pago (para estructuras multi-empresa).
     * Legacy: ID_SUB_EMPRESA → SubEmpresa
     */
    #[ORM\ManyToOne(targetEntity: SubCompany::class)]
    #[ORM\JoinColumn(name: 'sub_company_id', referencedColumnName: 'id', nullable: true)]
    private ?SubCompany $subCompany = null;

    /**
     * Motivo de la diferencia entre el monto facturado y el pagado.
     * Legacy: ID_MOTIVO_DIFERENCIA → MotivoDiferencia
     */
    #[ORM\ManyToOne(targetEntity: DifferenceReason::class)]
    #[ORM\JoinColumn(name: 'difference_reason_id', referencedColumnName: 'id', nullable: true)]
    private ?DifferenceReason $differenceReason = null;

    /** Legacy: FECHA_PAGO */
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $paymentDate = null;

    /** Legacy: NUMERO_DOCUMENTO */
    #[ORM\Column(type: 'bigint', nullable: true)]
    private ?int $documentNumber = null;

    /** Legacy: IMPUESTO */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $tax = null;

    /** Legacy: MONTO */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, nullable: true)]
    private ?string $amount = null;

    /**
     * Código de estado del documento (entero crudo, aún sin FK).
     * Legacy: ID_ESTADO_DOCUMENTO
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $documentStatusId = null;

    /** Identificador de cuota (ej. "1/3"). Legacy: CUOTA */
    #[ORM\Column(length: 11, nullable: true)]
    private ?string $installment = null;

    /** Fecha programada de pago. Legacy: FECHA_A_PAGO */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $scheduledPaymentDate = null;

    /** Legacy: FECHA_ANULACION */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $cancellationDate = null;

    /** Legacy: MOTIVO_ANULACION */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $cancellationReason = null;

    /** Legacy: FECHA_REGULARIZACION */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $regularizationDate = null;

    /** Legacy: OBSERVACION_REGULARIZACION */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $regularizationNotes = null;

    /** Monto de la diferencia entre lo facturado y lo pagado. Legacy: MONTO_DIFERENCIA */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $differenceAmount = null;

    /** Diferencia de precio. Legacy: PRECIO_DIFERENCIA */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $priceVariance = null;

    /** Indica si el pago corresponde a cobranza/recupero. Legacy: ES_COBRANZA */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isCollection = false;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;
        return $this;
    }

    public function getPaymentStatus(): ?PaymentStatus
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(?PaymentStatus $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;
        return $this;
    }

    public function getCreatedByMember(): ?Member
    {
        return $this->createdByMember;
    }

    public function setCreatedByMember(?Member $createdByMember): self
    {
        $this->createdByMember = $createdByMember;
        return $this;
    }

    public function getCancelledByMember(): ?Member
    {
        return $this->cancelledByMember;
    }

    public function setCancelledByMember(?Member $cancelledByMember): self
    {
        $this->cancelledByMember = $cancelledByMember;
        return $this;
    }

    public function getCashRegisterLocation(): ?CashRegisterLocation
    {
        return $this->cashRegisterLocation;
    }

    public function setCashRegisterLocation(?CashRegisterLocation $cashRegisterLocation): self
    {
        $this->cashRegisterLocation = $cashRegisterLocation;
        return $this;
    }

    public function getCashRegister(): ?CashRegister
    {
        return $this->cashRegister;
    }

    public function setCashRegister(?CashRegister $cashRegister): self
    {
        $this->cashRegister = $cashRegister;
        return $this;
    }

    public function getSubCompany(): ?SubCompany
    {
        return $this->subCompany;
    }

    public function setSubCompany(?SubCompany $subCompany): self
    {
        $this->subCompany = $subCompany;
        return $this;
    }

    public function getDifferenceReason(): ?DifferenceReason
    {
        return $this->differenceReason;
    }

    public function setDifferenceReason(?DifferenceReason $differenceReason): self
    {
        $this->differenceReason = $differenceReason;
        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(\DateTimeInterface $paymentDate): self
    {
        $this->paymentDate = $paymentDate;
        return $this;
    }

    public function getDocumentNumber(): ?int
    {
        return $this->documentNumber;
    }

    public function setDocumentNumber(?int $documentNumber): self
    {
        $this->documentNumber = $documentNumber;
        return $this;
    }

    public function getTax(): ?int
    {
        return $this->tax;
    }

    public function setTax(?int $tax): self
    {
        $this->tax = $tax;
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getDocumentStatusId(): ?int
    {
        return $this->documentStatusId;
    }

    public function setDocumentStatusId(?int $documentStatusId): self
    {
        $this->documentStatusId = $documentStatusId;
        return $this;
    }

    public function getInstallment(): ?string
    {
        return $this->installment;
    }

    public function setInstallment(?string $installment): self
    {
        $this->installment = $installment;
        return $this;
    }

    public function getScheduledPaymentDate(): ?\DateTimeInterface
    {
        return $this->scheduledPaymentDate;
    }

    public function setScheduledPaymentDate(?\DateTimeInterface $scheduledPaymentDate): self
    {
        $this->scheduledPaymentDate = $scheduledPaymentDate;
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

    public function getCancellationReason(): ?string
    {
        return $this->cancellationReason;
    }

    public function setCancellationReason(?string $cancellationReason): self
    {
        $this->cancellationReason = $cancellationReason;
        return $this;
    }

    public function getRegularizationDate(): ?\DateTimeInterface
    {
        return $this->regularizationDate;
    }

    public function setRegularizationDate(?\DateTimeInterface $regularizationDate): self
    {
        $this->regularizationDate = $regularizationDate;
        return $this;
    }

    public function getRegularizationNotes(): ?string
    {
        return $this->regularizationNotes;
    }

    public function setRegularizationNotes(?string $regularizationNotes): self
    {
        $this->regularizationNotes = $regularizationNotes;
        return $this;
    }

    public function getDifferenceAmount(): ?string
    {
        return $this->differenceAmount;
    }

    public function setDifferenceAmount(?string $differenceAmount): self
    {
        $this->differenceAmount = $differenceAmount;
        return $this;
    }

    public function getPriceVariance(): ?string
    {
        return $this->priceVariance;
    }

    public function setPriceVariance(?string $priceVariance): self
    {
        $this->priceVariance = $priceVariance;
        return $this;
    }

    public function isCollection(): bool
    {
        return $this->isCollection;
    }

    public function setIsCollection(bool $isCollection): self
    {
        $this->isCollection = $isCollection;
        return $this;
    }
}
