<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\BonoWebVoucherDetailRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * BonoWebVoucherDetail
 *
 * Línea de prestación dentro de un BonoWebVoucher.
 * Cada prestación enviada a Snabb genera un detalle con su copago
 * y bonificación calculados por FONASA.
 */
#[ORM\Entity(repositoryClass: BonoWebVoucherDetailRepository::class)]
#[ORM\Table(name: 'bono_web_voucher_detail')]
class BonoWebVoucherDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /** Voucher al que pertenece este detalle. */
    #[ORM\ManyToOne(targetEntity: BonoWebVoucher::class, inversedBy: 'details')]
    #[ORM\JoinColumn(name: 'bono_web_voucher_id', referencedColumnName: 'id', nullable: false)]
    private ?BonoWebVoucher $bonoWebVoucher = null;

    /**
     * Ítem de facturación del catálogo local (si corresponde).
     * Nullable: pueden enviarse prestaciones sin BillingItem local.
     */
    #[ORM\ManyToOne(targetEntity: BillingItem::class)]
    #[ORM\JoinColumn(name: 'billing_item_id', referencedColumnName: 'id', nullable: true)]
    private ?BillingItem $billingItem = null;

    /** Nombre de la prestación tal como se envía a Snabb. */
    #[ORM\Column(length: 200)]
    private string $serviceName = '';

    /** Código FONASA de la prestación (ej: P0301). */
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $serviceCode = null;

    /** Monto copago del paciente para esta prestación. */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, options: ['default' => '0.00'])]
    private string $copago = '0.00';

    /** Monto bonificado por FONASA para esta prestación. */
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, options: ['default' => '0.00'])]
    private string $bonificacion = '0.00';

    /** Cantidad de unidades de la prestación. */
    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $quantity = 1;

    public function getId(): ?int { return $this->id; }

    public function getBonoWebVoucher(): ?BonoWebVoucher { return $this->bonoWebVoucher; }
    public function setBonoWebVoucher(?BonoWebVoucher $v): self { $this->bonoWebVoucher = $v; return $this; }

    public function getBillingItem(): ?BillingItem { return $this->billingItem; }
    public function setBillingItem(?BillingItem $v): self { $this->billingItem = $v; return $this; }

    public function getServiceName(): string { return $this->serviceName; }
    public function setServiceName(string $v): self { $this->serviceName = $v; return $this; }

    public function getServiceCode(): ?string { return $this->serviceCode; }
    public function setServiceCode(?string $v): self { $this->serviceCode = $v; return $this; }

    public function getCopago(): string { return $this->copago; }
    public function setCopago(string $v): self { $this->copago = $v; return $this; }

    public function getBonificacion(): string { return $this->bonificacion; }
    public function setBonificacion(string $v): self { $this->bonificacion = $v; return $this; }

    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $v): self { $this->quantity = $v; return $this; }
}
