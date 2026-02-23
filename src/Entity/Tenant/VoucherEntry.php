<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\VoucherEntryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * VoucherEntry (DetalleTalonario)
 *
 * Tabla legacy: detalle_talonario
 *
 * Consumo de un folio del talonario al procesar un pago.
 * Cada PaymentAccount genera un VoucherEntry que registra
 * el folio asignado, el cajero y la fecha de emisión.
 */
#[ORM\Entity(repositoryClass: VoucherEntryRepository::class)]
#[ORM\Table(name: 'voucher_entry')]
class VoucherEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Talonario del que se consumió el folio.
     * Legacy: ID_TALONARIO → Talonario
     */
    #[ORM\ManyToOne(targetEntity: Voucher::class)]
    #[ORM\JoinColumn(name: 'voucher_id', referencedColumnName: 'id', nullable: false)]
    private ?Voucher $voucher = null;

    /**
     * Pago al que se le emitió este folio.
     * Legacy: ID_PAGO_CUENTA → PagoCuenta
     */
    #[ORM\ManyToOne(targetEntity: PaymentAccount::class)]
    #[ORM\JoinColumn(name: 'payment_account_id', referencedColumnName: 'id', nullable: false)]
    private ?PaymentAccount $paymentAccount = null;

    /**
     * Cajero que emitió el documento.
     * Legacy: ID_USUARIO → UsuariosRebsol
     */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $member = null;

    /** Número de folio asignado. Legacy: NUMERO_FOLIO */
    #[ORM\Column(type: 'integer')]
    private int $folioNumber = 0;

    /** Fecha y hora de emisión del documento. Legacy: FECHA_EMISION */
    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $issuedAt = null;

    /** Indica si el folio fue anulado. Legacy: ES_ANULADO */
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isCancelled = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getVoucher(): ?Voucher
    {
        return $this->voucher;
    }

    public function setVoucher(?Voucher $voucher): self
    {
        $this->voucher = $voucher;
        return $this;
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

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;
        return $this;
    }

    public function getFolioNumber(): int
    {
        return $this->folioNumber;
    }

    public function setFolioNumber(int $folioNumber): self
    {
        $this->folioNumber = $folioNumber;
        return $this;
    }

    public function getIssuedAt(): ?\DateTimeInterface
    {
        return $this->issuedAt;
    }

    public function setIssuedAt(\DateTimeInterface $issuedAt): self
    {
        $this->issuedAt = $issuedAt;
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
}
