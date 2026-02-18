<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\BedAssignmentStatusRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * BedAssignmentStatus (EstadoRelCamaPaciente)
 *
 * Legacy table: estado_rel_cama_paciente
 * Spanish name: Estado de Relación Cama-Paciente
 *
 * Catalog of statuses for bed-patient assignments
 * (e.g. Ocupada, Libre, En limpieza, Reservada).
 */
#[ORM\Entity(repositoryClass: BedAssignmentStatusRepository::class)]
#[ORM\Table(name: 'bed_assignment_status')]
class BedAssignmentStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Legacy: NOMBRE */
    #[ORM\Column(length: 45)]
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
