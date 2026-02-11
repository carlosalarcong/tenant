<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\AppointmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Appointment
 * 
 * Legacy table: cita
 * Spanish name: Cita
 * 
 * Represents medical appointments:
 * - Patient appointments with professionals
 * - Scheduled consultations
 * - Status tracking
 * - Cancellation management
 */
#[ORM\Entity(repositoryClass: AppointmentRepository::class)]
#[ORM\Table(name: 'appointment')]
class Appointment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $appointmentNumber = null;

    #[ORM\ManyToOne(targetEntity: Schedule::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Schedule $schedule = null;

    #[ORM\ManyToOne(targetEntity: Professional::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Professional $professional = null;

    #[ORM\Column(length: 100)]
    private ?string $patientName = null;

    #[ORM\Column(length: 20)]
    private ?string $patientRut = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $patientEmail = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $patientPhone = null;

    #[ORM\ManyToOne(targetEntity: ConsultationType::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?ConsultationType $consultationType = null;

    #[ORM\ManyToOne(targetEntity: Payer::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Payer $payer = null;

    #[ORM\ManyToOne(targetEntity: Authorization::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Authorization $authorization = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $appointmentDateTime = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 30])]
    private int $durationMinutes = 30;

    #[ORM\Column(length: 50)]
    private ?string $status = 'scheduled'; // scheduled, confirmed, arrived, in_progress, completed, cancelled, no_show

    #[ORM\ManyToOne(targetEntity: CancellationType::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?CancellationType $cancellationType = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $cancellationDate = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $cancellationReason = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observations = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isFirstTime = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $reminderSent = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->generateAppointmentNumber();
    }

    private function generateAppointmentNumber(): void
    {
        $this->appointmentNumber = 'CITA-' . date('YmdHis') . '-' . substr(uniqid(), -4);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAppointmentNumber(): ?string
    {
        return $this->appointmentNumber;
    }

    public function setAppointmentNumber(string $appointmentNumber): static
    {
        $this->appointmentNumber = $appointmentNumber;
        return $this;
    }

    public function getSchedule(): ?Schedule
    {
        return $this->schedule;
    }

    public function setSchedule(?Schedule $schedule): static
    {
        $this->schedule = $schedule;
        return $this;
    }

    public function getProfessional(): ?Professional
    {
        return $this->professional;
    }

    public function setProfessional(?Professional $professional): static
    {
        $this->professional = $professional;
        return $this;
    }

    public function getPatientName(): ?string
    {
        return $this->patientName;
    }

    public function setPatientName(string $patientName): static
    {
        $this->patientName = $patientName;
        return $this;
    }

    public function getPatientRut(): ?string
    {
        return $this->patientRut;
    }

    public function setPatientRut(string $patientRut): static
    {
        $this->patientRut = $patientRut;
        return $this;
    }

    public function getPatientEmail(): ?string
    {
        return $this->patientEmail;
    }

    public function setPatientEmail(?string $patientEmail): static
    {
        $this->patientEmail = $patientEmail;
        return $this;
    }

    public function getPatientPhone(): ?string
    {
        return $this->patientPhone;
    }

    public function setPatientPhone(?string $patientPhone): static
    {
        $this->patientPhone = $patientPhone;
        return $this;
    }

    public function getConsultationType(): ?ConsultationType
    {
        return $this->consultationType;
    }

    public function setConsultationType(?ConsultationType $consultationType): static
    {
        $this->consultationType = $consultationType;
        return $this;
    }

    public function getPayer(): ?Payer
    {
        return $this->financier;
    }

    public function setPayer(?Payer $payer): static
    {
        $this->financier = $payer;
        return $this;
    }

    public function getAuthorization(): ?Authorization
    {
        return $this->authorization;
    }

    public function setAuthorization(?Authorization $authorization): static
    {
        $this->authorization = $authorization;
        return $this;
    }

    public function getAppointmentDateTime(): ?\DateTimeInterface
    {
        return $this->appointmentDateTime;
    }

    public function setAppointmentDateTime(\DateTimeInterface $appointmentDateTime): static
    {
        $this->appointmentDateTime = $appointmentDateTime;
        return $this;
    }

    public function getDurationMinutes(): int
    {
        return $this->durationMinutes;
    }

    public function setDurationMinutes(int $durationMinutes): static
    {
        $this->durationMinutes = $durationMinutes;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCancellationType(): ?CancellationType
    {
        return $this->cancellationType;
    }

    public function setCancellationType(?CancellationType $cancellationType): static
    {
        $this->cancellationType = $cancellationType;
        return $this;
    }

    public function getCancellationDate(): ?\DateTimeInterface
    {
        return $this->cancellationDate;
    }

    public function setCancellationDate(?\DateTimeInterface $cancellationDate): static
    {
        $this->cancellationDate = $cancellationDate;
        return $this;
    }

    public function getCancellationReason(): ?string
    {
        return $this->cancellationReason;
    }

    public function setCancellationReason(?string $cancellationReason): static
    {
        $this->cancellationReason = $cancellationReason;
        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): static
    {
        $this->reason = $reason;
        return $this;
    }

    public function getObservations(): ?string
    {
        return $this->observations;
    }

    public function setObservations(?string $observations): static
    {
        $this->observations = $observations;
        return $this;
    }

    public function isFirstTime(): bool
    {
        return $this->isFirstTime;
    }

    public function setIsFirstTime(bool $isFirstTime): static
    {
        $this->isFirstTime = $isFirstTime;
        return $this;
    }

    public function isReminderSent(): bool
    {
        return $this->reminderSent;
    }

    public function setReminderSent(bool $reminderSent): static
    {
        $this->reminderSent = $reminderSent;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function __toString(): string
    {
        return $this->appointmentNumber ?? '';
    }
}
