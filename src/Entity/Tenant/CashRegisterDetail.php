<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\CashRegisterDetailRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * CashRegisterDetail (DetalleCaja)
 *
 * Tabla legacy: detalle_caja
 *
 * Línea de detalle del cierre de caja por forma de pago.
 * Al cerrar la caja el cajero ingresa el monto real por cada PaymentMethod.
 */
#[ORM\Entity(repositoryClass: CashRegisterDetailRepository::class)]
#[ORM\Table(name: 'cash_register_detail')]
class CashRegisterDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Caja a la que pertenece este detalle.
     * Legacy: ID_CAJA → Caja
     */
    #[ORM\ManyToOne(targetEntity: CashRegister::class)]
    #[ORM\JoinColumn(name: 'cash_register_id', referencedColumnName: 'id', nullable: false)]
    private ?CashRegister $cashRegister = null;

    /**
     * Forma de pago a la que corresponde el monto.
     * Legacy: ID_FORMA_PAGO → FormaPago
     */
    #[ORM\ManyToOne(targetEntity: PaymentMethod::class)]
    #[ORM\JoinColumn(name: 'payment_method_id', referencedColumnName: 'id', nullable: false)]
    private ?PaymentMethod $paymentMethod = null;

    /**
     * Banco asociado (aplica a cheques y transferencias).
     * Legacy: ID_BANCO → Banco
     */
    #[ORM\ManyToOne(targetEntity: Bank::class)]
    #[ORM\JoinColumn(name: 'bank_id', referencedColumnName: 'id', nullable: true)]
    private ?Bank $bank = null;

    /** Monto real contabilizado para esta forma de pago al cierre. Legacy: MONTO */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, options: ['default' => '0.00'])]
    private string $amount = '0.00';

    /** Número de depósito asociado (para transferencias/cheques). Legacy: NUMERO_DEPOSITO */
    #[ORM\Column(length: 50, nullable: true)]
    private ?string $depositNumber = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getPaymentMethod(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(?PaymentMethod $paymentMethod): self
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }

    public function getBank(): ?Bank
    {
        return $this->bank;
    }

    public function setBank(?Bank $bank): self
    {
        $this->bank = $bank;
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

    public function getDepositNumber(): ?string
    {
        return $this->depositNumber;
    }

    public function setDepositNumber(?string $depositNumber): self
    {
        $this->depositNumber = $depositNumber;
        return $this;
    }
}
