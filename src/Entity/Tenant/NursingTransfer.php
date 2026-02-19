<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\NursingTransferRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NursingTransferRepository::class)]
#[ORM\Table(name: 'nursing_transfer')]
class NursingTransfer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?AdmissionRecord $admissionRecord = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Service $originService = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Service $destinationService = null;

    #[Assert\Positive]
    #[ORM\Column]
    private int $requestedByUserId;

    #[Assert\NotNull]
    #[ORM\Column]
    private \DateTimeImmutable $requestedAt;

    #[ORM\Column(nullable: true)]
    private ?int $cancelledByUserId = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $cancelledAt = null;

    #[Assert\Choice(choices: ['pending', 'confirmed', 'cancelled'])]
    #[ORM\Column(length: 20)]
    private string $status = 'pending';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->requestedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdmissionRecord(): ?AdmissionRecord
    {
        return $this->admissionRecord;
    }

    public function setAdmissionRecord(?AdmissionRecord $admissionRecord): self
    {
        $this->admissionRecord = $admissionRecord;
        return $this;
    }

    public function getOriginService(): ?Service
    {
        return $this->originService;
    }

    public function setOriginService(?Service $originService): self
    {
        $this->originService = $originService;
        return $this;
    }

    public function getDestinationService(): ?Service
    {
        return $this->destinationService;
    }

    public function setDestinationService(?Service $destinationService): self
    {
        $this->destinationService = $destinationService;
        return $this;
    }

    public function getRequestedByUserId(): int
    {
        return $this->requestedByUserId;
    }

    public function setRequestedByUserId(int $requestedByUserId): self
    {
        $this->requestedByUserId = $requestedByUserId;
        return $this;
    }

    public function getRequestedAt(): \DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(\DateTimeImmutable $requestedAt): self
    {
        $this->requestedAt = $requestedAt;
        return $this;
    }

    public function getCancelledByUserId(): ?int
    {
        return $this->cancelledByUserId;
    }

    public function setCancelledByUserId(?int $cancelledByUserId): self
    {
        $this->cancelledByUserId = $cancelledByUserId;
        return $this;
    }

    public function getCancelledAt(): ?\DateTimeImmutable
    {
        return $this->cancelledAt;
    }

    public function setCancelledAt(?\DateTimeImmutable $cancelledAt): self
    {
        $this->cancelledAt = $cancelledAt;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }
}
