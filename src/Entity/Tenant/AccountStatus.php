<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\AccountStatusRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * AccountStatus (EstadoCuenta)
 *
 * Tabla legacy: estado_cuenta
 *
 * Catálogo de estados de la cuenta maestra del paciente (ej. Abierta, Cerrada, Pagada).
 * PatientAccount transiciona entre estos estados según el estado de regularización
 * de sus registros PaymentAccount asociados.
 */
#[ORM\Entity(repositoryClass: AccountStatusRepository::class)]
#[ORM\Table(name: 'account_status')]
class AccountStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Legacy: NOMBRE */
    #[ORM\Column(length: 60)]
    private string $name = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
