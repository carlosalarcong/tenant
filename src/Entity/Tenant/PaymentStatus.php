<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\PaymentStatusRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * PaymentStatus (EstadoPago)
 *
 * Tabla legacy: estado_pago
 *
 * Catálogo de estados de los pagos individuales (ej. Pendiente, Regularizado, Anulado).
 * Usado por PaymentAccount para determinar si la CuentaPaciente padre puede cerrarse:
 * todos los PaymentAccount deben estar regularizados primero.
 */
#[ORM\Entity(repositoryClass: PaymentStatusRepository::class)]
#[ORM\Table(name: 'payment_status')]
class PaymentStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Legacy: NOMBRE_ESTADO_PAGO */
    #[ORM\Column(length: 45)]
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
