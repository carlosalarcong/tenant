<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\AdmissionStatusRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * AdmissionStatus (EstadoIngreso)
 *
 * Legacy table: estado_ingreso
 * Spanish name: Estado de Ingreso
 *
 * Catalog of admission record statuses
 * (e.g. Pre-admitido, Admitido, Anulado, Dado de alta).
 */
#[ORM\Entity(repositoryClass: AdmissionStatusRepository::class)]
#[ORM\Table(name: 'admission_status')]
class AdmissionStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Legacy: NOMBRE */
    #[ORM\Column(length: 60)]
    private string $name;

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
