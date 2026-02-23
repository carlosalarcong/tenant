<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\PaymentAccountDetailRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * PaymentAccountDetail (DetallePagoCuenta / PagoCuentaDetalle)
 *
 * Tabla legacy: detalle_pago_cuenta
 *
 * Línea de detalle de un pago por forma de pago.
 * Un PaymentAccount puede pagarse con múltiples formas de pago
 * (efectivo + tarjeta, por ejemplo); cada una genera un PaymentAccountDetail.
 * Si el detalle usa un voucher/folio, se vincula a un VoucherEntry.
 */
#[ORM\Entity(repositoryClass: PaymentAccountDetailRepository::class)]
#[ORM\Table(name: 'payment_account_detail')]
class PaymentAccountDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Pago al que pertenece este detalle.
     * Legacy: ID_PAGO_CUENTA → PagoCuenta
     */
    #[ORM\ManyToOne(targetEntity: PaymentAccount::class)]
    #[ORM\JoinColumn(name: 'payment_account_id', referencedColumnName: 'id', nullable: false)]
    private ?PaymentAccount $paymentAccount = null;

    /**
     * Forma de pago usada en este detalle.
     * Legacy: ID_FORMA_PAGO → FormaPago
     */
    #[ORM\ManyToOne(targetEntity: PaymentMethod::class)]
    #[ORM\JoinColumn(name: 'payment_method_id', referencedColumnName: 'id', nullable: false)]
    private ?PaymentMethod $paymentMethod = null;

    /**
     * Folio de talonario emitido para este detalle (si aplica).
     * Legacy: ID_DETALLE_TALONARIO → DetalleTalonario
     */
    #[ORM\ManyToOne(targetEntity: VoucherEntry::class)]
    #[ORM\JoinColumn(name: 'voucher_entry_id', referencedColumnName: 'id', nullable: true)]
    private ?VoucherEntry $voucherEntry = null;

    /** Monto pagado con esta forma de pago. Legacy: MONTO */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, options: ['default' => '0.00'])]
    private string $amount = '0.00';

    /**
     * Número de autorización o referencia del medio de pago
     * (código de transacción tarjeta, número de cheque, etc.).
     * Legacy: NUMERO_REFERENCIA / CODIGO_AUTORIZACION
     */
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $referenceNumber = null;

    /**
     * Últimos 4 dígitos de la tarjeta (si aplica).
     * Legacy: ULTIMOS_DIGITOS
     */
    #[ORM\Column(length: 4, nullable: true)]
    private ?string $cardLastDigits = null;

    /** Cuotas (si es tarjeta de crédito en cuotas). Legacy: CUOTAS */
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $installments = null;

    /** Indica si este detalle fue anulado. Legacy: ES_ANULADO */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isCancelled = false;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPaymentAccount(): ?PaymentAccount
    {
        return $this->paymentAccount;
    }

    public function setPaymentAccount(?PaymentAccount $paymentAccount): self
    {
        $this->paymentAccount = $paymentAccount;
        return $this;
    }

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?PaymentMethod $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getVoucherEntry(): ?VoucherEntry
    {
        return $this->voucherEntry;
    }

    public function setVoucherEntry(?VoucherEntry $voucherEntry): self
    {
        $this->voucherEntry = $voucherEntry;
        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getReferenceNumber(): ?string
    {
        return $this->referenceNumber;
    }

    public function setReferenceNumber(?string $referenceNumber): self
    {
        $this->referenceNumber = $referenceNumber;
        return $this;
    }

    public function getCardLastDigits(): ?string
    {
        return $this->cardLastDigits;
    }

    public function setCardLastDigits(?string $cardLastDigits): self
    {
        $this->cardLastDigits = $cardLastDigits;
        return $this;
    }

    public function getInstallments(): ?int
    {
        return $this->installments;
    }

    public function setInstallments(?int $installments): self
    {
        $this->installments = $installments;
        return $this;
    }

    public function isCancelled(): bool
    {
        return $this->isCancelled;
    }

    public function setIsCancelled(bool $isCancelled): self
    {
        $this->isCancelled = $isCancelled;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
