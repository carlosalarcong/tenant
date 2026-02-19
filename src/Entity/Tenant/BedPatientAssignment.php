<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\BedPatientAssignmentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * BedPatientAssignment (RelCamaPaciente)
 *
 * Tabla legacy: rel_cama_paciente
 * Spanish name: Relación Cama-Paciente
 *
 * Tracks bed occupancy over time, linking a patient to a specific bed
 * with a start and end date. Used during hospitalization admissions.
 */
#[ORM\Entity(repositoryClass: BedPatientAssignmentRepository::class)]
#[ORM\Table(name: 'bed_patient_assignment')]
#[ORM\HasLifecycleCallbacks]
class BedPatientAssignment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Legacy: ID_PACIENTE */
    #[ORM\ManyToOne(targetEntity: Patient::class)]
    #[ORM\JoinColumn(name: 'patient_id', referencedColumnName: 'id', nullable: false)]
    private ?Patient $patient = null;

    /** Legacy: ID_CAMA */
    #[ORM\ManyToOne(targetEntity: Bed::class)]
    #[ORM\JoinColumn(name: 'bed_id', referencedColumnName: 'id', nullable: false)]
    private ?Bed $bed = null;

    /** Legacy: ID_ESTADO_REL_CAMA_PACIENTE */
    #[ORM\ManyToOne(targetEntity: BedAssignmentStatus::class)]
    #[ORM\JoinColumn(name: 'bed_assignment_status_id', referencedColumnName: 'id', nullable: true)]
    private ?BedAssignmentStatus $status = null;

    /** Legacy: FECHA_INICIO */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $startDate = null;

    /** Legacy: FECHA_FIN */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $endDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;
        return $this;
    }

    public function getBed(): ?Bed
    {
        return $this->bed;
    }

    public function setBed(?Bed $bed): self
    {
        $this->bed = $bed;
        return $this;
    }

    public function getStatus(): ?BedAssignmentStatus
    {
        return $this->status;
    }

    public function setStatus(?BedAssignmentStatus $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;
        return $this;
    }
}
