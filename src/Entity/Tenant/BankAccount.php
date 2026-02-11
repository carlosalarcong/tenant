<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\BankAccountRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BankAccount (CuentasBancarias)
 * 
 * Mantenedor de cuentas bancarias para liquid

aciones
 */
#[ORM\Entity(repositoryClass: BankAccountRepository::class)]
#[ORM\Table(name: 'bank_account')]
class BankAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'account_number', type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'Account number is required')]
    #[Assert\Length(max: 255)]
    private string $accountNumber;

    #[ORM\ManyToOne(targetEntity: Bank::class)]
    #[ORM\JoinColumn(name: 'bank_id', nullable: true)]
    private ?Bank $bank = null;

    #[ORM\ManyToOne(targetEntity: BankAccountType::class)]
    #[ORM\JoinColumn(name: 'bank_account_type_id', nullable: true)]
    private ?BankAccountType $bankAccountType = null;

    #[ORM\Column(name: 'is_active', type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(name: 'id_estado', type: 'integer')]
    private int $idEstado = 1;

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

    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    public function setAccountNumber(string $accountNumber): self
    {
        $this->accountNumber = $accountNumber;
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

    public function getBankAccountType(): ?BankAccountType
    {
        return $this->bankAccountType;
    }

    public function setBankAccountType(?BankAccountType $bankAccountType): self
    {
        $this->bankAccountType = $bankAccountType;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getIdEstado(): int
    {
        return $this->idEstado;
    }

    public function setIdEstado(int $idEstado): self
    {
        $this->idEstado = $idEstado;
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
