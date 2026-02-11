<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\ScheduleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Schedule
 * 
 * Legacy table: agenda
 * Spanish name: Agenda
 * 
 * Represents professional schedules and availability:
 * - Doctor appointment slots
 * - Operating room schedules
 * - Resource availability
 * - Time blocks and recurring patterns
 */
#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
#[ORM\Table(name: 'schedule')]
class Schedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Professional::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Professional $professional = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $scheduleDate = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $endTime = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 30])]
    private int $slotDurationMinutes = 30;

    #[ORM\ManyToOne(targetEntity: ConsultationType::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?ConsultationType $consultationType = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $location = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 1])]
    private int $maxAppointments = 1;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isBlocked = false;

    #[ORM\ManyToOne(targetEntity: BlockingType::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?BlockingType $blockingType = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $observations = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getScheduleDate(): ?\DateTimeInterface
    {
        return $this->scheduleDate;
    }

    public function setScheduleDate(\DateTimeInterface $scheduleDate): static
    {
        $this->scheduleDate = $scheduleDate;
        return $this;
    }

    public function getStartTime(): ?\DateTimeInterface
    {
        return $this->startTime;
    }

    public function setStartTime(\DateTimeInterface $startTime): static
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getEndTime(): ?\DateTimeInterface
    {
        return $this->endTime;
    }

    public function setEndTime(\DateTimeInterface $endTime): static
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getSlotDurationMinutes(): int
    {
        return $this->slotDurationMinutes;
    }

    public function setSlotDurationMinutes(int $slotDurationMinutes): static
    {
        $this->slotDurationMinutes = $slotDurationMinutes;
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

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): static
    {
        $this->location = $location;
        return $this;
    }

    public function getMaxAppointments(): int
    {
        return $this->maxAppointments;
    }

    public function setMaxAppointments(int $maxAppointments): static
    {
        $this->maxAppointments = $maxAppointments;
        return $this;
    }

    public function isBlocked(): bool
    {
        return $this->isBlocked;
    }

    public function setIsBlocked(bool $isBlocked): static
    {
        $this->isBlocked = $isBlocked;
        return $this;
    }

    public function getBlockingType(): ?BlockingType
    {
        return $this->blockingType;
    }

    public function setBlockingType(?BlockingType $blockingType): static
    {
        $this->blockingType = $blockingType;
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

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
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
        return sprintf(
            '%s - %s',
            $this->professional?->getFullName() ?? '',
            $this->scheduleDate?->format('Y-m-d') ?? ''
        );
    }
}
