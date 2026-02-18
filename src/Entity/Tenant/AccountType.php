<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\AccountTypeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * AccountType (TipoCuenta)
 *
 * Legacy table: tipo_cuenta
 * Spanish name: Tipo de Cuenta
 *
 * Catalog of hospitalization account types used in admission records
 * (e.g. Hospitalización, Cirugía, Urgencia, Día Hospital).
 */
#[ORM\Entity(repositoryClass: AccountTypeRepository::class)]
#[ORM\Table(name: 'account_type')]
class AccountType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Legacy: NOMBRE */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
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

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
