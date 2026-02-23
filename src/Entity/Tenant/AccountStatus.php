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

    /**
     * Machine-readable slug identifying this status in business logic.
     * Example values: 'cerrada_pendiente_pago', 'abierta_pendiente_pago'.
     * Used instead of normalizing getName() at runtime.
     */
    #[ORM\Column(length: 60, options: ['default' => ''])]
    private string $code = '';

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

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
