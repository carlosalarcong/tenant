<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\ClinicalActionPatientRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * ClinicalActionPatient (AccionClinicaPaciente)
 *
 * Tabla legacy: accion_clinica_paciente
 *
 * Prestación clínica cobrada a un paciente dentro de un pago.
 * Vincula un ítem de facturación (BillingItem) con su PaymentAccount,
 * el profesional que la realizó, y opcionalmente una diferencia aplicada.
 */
#[ORM\Entity(repositoryClass: ClinicalActionPatientRepository::class)]
#[ORM\Table(name: 'clinical_action_patient')]
class ClinicalActionPatient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Pago al que pertenece esta prestación.
     * Legacy: ID_PAGO_CUENTA → PagoCuenta
     */
    #[ORM\ManyToOne(targetEntity: PaymentAccount::class)]
    #[ORM\JoinColumn(name: 'payment_account_id', referencedColumnName: 'id', nullable: false)]
    private ?PaymentAccount $paymentAccount = null;

    /**
     * Ítem de facturación (prestación/servicio) cobrado.
     * Legacy: ID_ITEM_FACTURACION → ItemFacturacion
     */
    #[ORM\ManyToOne(targetEntity: BillingItem::class)]
    #[ORM\JoinColumn(name: 'billing_item_id', referencedColumnName: 'id', nullable: true)]
    private ?BillingItem $billingItem = null;

    /**
     * Diferencia/descuento aplicado a esta prestación (si aplica).
     * Legacy: ID_DIFERENCIA → Diferencia
     */
    #[ORM\ManyToOne(targetEntity: Difference::class)]
    #[ORM\JoinColumn(name: 'difference_id', referencedColumnName: 'id', nullable: true)]
    private ?Difference $difference = null;

    /**
     * Profesional que realizó la prestación.
     * Legacy: ID_PROFESIONAL → Profesional / RolProfesional
     */
    #[ORM\ManyToOne(targetEntity: Professional::class)]
    #[ORM\JoinColumn(name: 'professional_id', referencedColumnName: 'id', nullable: true)]
    private ?Professional $professional = null;

    /** Cantidad de unidades de la prestación. Legacy: CANTIDAD */
    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $quantity = 1;

    /** Precio unitario de la prestación. Legacy: PRECIO_UNITARIO */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, options: ['default' => '0.00'])]
    private string $unitPrice = '0.00';

    /** Monto total de la prestación (quantity × unitPrice). Legacy: MONTO_TOTAL */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, options: ['default' => '0.00'])]
    private string $totalAmount = '0.00';

    /** Monto de descuento aplicado. Legacy: MONTO_DESCUENTO */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, options: ['default' => '0.00'])]
    private string $discountAmount = '0.00';

    /** Indica si la prestación fue anulada. Legacy: ES_ANULADO */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isCancelled = false;

    /** Observaciones adicionales sobre la prestación. Legacy: OBSERVACION */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

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

    public function getBillingItem(): ?BillingItem
    {
        return $this->billingItem;
    }

    public function setBillingItem(?BillingItem $billingItem): self
    {
        $this->billingItem = $billingItem;
        return $this;
    }

    public function getDifference(): ?Difference
    {
        return $this->difference;
    }

    public function setDifference(?Difference $difference): self
    {
        $this->difference = $difference;
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

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getUnitPrice(): string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): self
    {
        $this->unitPrice = $unitPrice;
        return $this;
    }

    public function getTotalAmount(): string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): self
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    public function getDiscountAmount(): string
    {
        return $this->discountAmount;
    }

    public function setDiscountAmount(string $discountAmount): self
    {
        $this->discountAmount = $discountAmount;
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

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
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
