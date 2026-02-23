<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\BonoWebVoucherRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * BonoWebVoucher
 *
 * Voucher de BonoWeb/Snabb (bono FONASA electrónico).
 * Representa una solicitud de bono enviada a la API de Snabb para que
 * el paciente pague su copago y FONASA bonifique el resto.
 *
 * Ciclo de vida (estados Snabb):
 *   Created → Verified → Waiting for Payment → Paid → Done
 *                                            ↘ Canceled
 */
#[ORM\Entity(repositoryClass: BonoWebVoucherRepository::class)]
#[ORM\Table(name: 'bono_web_voucher')]
class BonoWebVoucher
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Detalle de pago vinculado a este voucher (se asigna al confirmar el pago).
     * Inicialmente null — se completa cuando el cajero confirma con confirmDone().
     */
    #[ORM\OneToOne(targetEntity: PaymentAccountDetail::class)]
    #[ORM\JoinColumn(name: 'payment_account_detail_id', referencedColumnName: 'id', nullable: true)]
    private ?PaymentAccountDetail $paymentAccountDetail = null;

    /**
     * UUID del voucher en el sistema Snabb.
     * Es el identificador externo primario para llamadas a la API.
     */
    #[ORM\Column(length: 100, unique: true)]
    private string $voucherId = '';

    /** URL de pago generada por Snabb para redirigir al paciente. */
    #[ORM\Column(length: 500, nullable: true)]
    private ?string $voucherUrl = null;

    /**
     * Estado del voucher en Snabb.
     * Valores: Created | Verified | Waiting for Payment | Paid | Done | Canceled
     */
    #[ORM\Column(length: 30, options: ['default' => 'Created'])]
    private string $status = 'Created';

    /** Total copago del paciente según FONASA (en pesos CLP). */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, options: ['default' => '0.00'])]
    private string $copagoTotal = '0.00';

    /** Total bonificado por FONASA (en pesos CLP). */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, options: ['default' => '0.00'])]
    private string $bonificacionTotal = '0.00';

    /** @var Collection<int, BonoWebVoucherDetail> */
    #[ORM\OneToMany(targetEntity: BonoWebVoucherDetail::class, mappedBy: 'bonoWebVoucher', cascade: ['persist', 'remove'])]
    private Collection $details;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->details   = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getPaymentAccountDetail(): ?PaymentAccountDetail { return $this->paymentAccountDetail; }
    public function setPaymentAccountDetail(?PaymentAccountDetail $v): self { $this->paymentAccountDetail = $v; return $this; }

    public function getVoucherId(): string { return $this->voucherId; }
    public function setVoucherId(string $v): self { $this->voucherId = $v; return $this; }

    public function getVoucherUrl(): ?string { return $this->voucherUrl; }
    public function setVoucherUrl(?string $v): self { $this->voucherUrl = $v; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $v): self { $this->status = $v; return $this; }

    public function getCopagoTotal(): string { return $this->copagoTotal; }
    public function setCopagoTotal(string $v): self { $this->copagoTotal = $v; return $this; }

    public function getBonificacionTotal(): string { return $this->bonificacionTotal; }
    public function setBonificacionTotal(string $v): self { $this->bonificacionTotal = $v; return $this; }

    /** @return Collection<int, BonoWebVoucherDetail> */
    public function getDetails(): Collection { return $this->details; }

    public function addDetail(BonoWebVoucherDetail $detail): self
    {
        if (!$this->details->contains($detail)) {
            $this->details->add($detail);
            $detail->setBonoWebVoucher($this);
        }
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }

    public function getUpdatedAt(): ?\DateTime { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTime $v): self { $this->updatedAt = $v; return $this; }

    // ── Helpers de estado ────────────────────────────────────────────────────

    public function isPaid(): bool    { return $this->status === 'Paid'; }
    public function isDone(): bool    { return $this->status === 'Done'; }
    public function isCanceled(): bool { return $this->status === 'Canceled'; }
    public function isPending(): bool  { return !in_array($this->status, ['Done', 'Canceled'], true); }
}
