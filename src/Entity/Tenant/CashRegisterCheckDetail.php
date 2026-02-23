<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\CashRegisterCheckDetailRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * CashRegisterCheckDetail (DetalleCajaCheque)
 *
 * Tabla legacy: detalle_caja_cheque
 *
 * Detalle de cheques individuales recibidos durante la jornada de caja.
 * Un cierre puede tener múltiples cheques registrados aquí.
 */
#[ORM\Entity(repositoryClass: CashRegisterCheckDetailRepository::class)]
#[ORM\Table(name: 'cash_register_check_detail')]
class CashRegisterCheckDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Caja a la que pertenece este cheque.
     * Legacy: ID_CAJA → Caja
     */
    #[ORM\ManyToOne(targetEntity: CashRegister::class)]
    #[ORM\JoinColumn(name: 'cash_register_id', referencedColumnName: 'id', nullable: false)]
    private ?CashRegister $cashRegister = null;

    /**
     * Banco emisor del cheque.
     * Legacy: ID_BANCO → Banco
     */
    #[ORM\ManyToOne(targetEntity: Bank::class)]
    #[ORM\JoinColumn(name: 'bank_id', referencedColumnName: 'id', nullable: true)]
    private ?Bank $bank = null;

    /** Número del cheque. Legacy: NUMERO_CHEQUE */
    #[ORM\Column(length: 30, nullable: true)]
    private ?string $checkNumber = null;

    /** Monto del cheque. Legacy: MONTO */
    #[ORM\Column(type: 'decimal', precision: 12, scale: 2, options: ['default' => '0.00'])]
    private string $amount = '0.00';

    /** Fecha del cheque (cheque al día o a fecha). Legacy: FECHA_CHEQUE */
    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $checkDate = null;

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

    public function getBank(): ?Bank
    {
        return $this->bank;
    }

    public function setBank(?Bank $bank): self
    {
        $this->bank = $bank;
        return $this;
    }

    public function getCheckNumber(): ?string
    {
        return $this->checkNumber;
    }

    public function setCheckNumber(?string $checkNumber): self
    {
        $this->checkNumber = $checkNumber;
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

    public function getCheckDate(): ?\DateTimeInterface
    {
        return $this->checkDate;
    }

    public function setCheckDate(?\DateTimeInterface $checkDate): self
    {
        $this->checkDate = $checkDate;
        return $this;
    }
}
