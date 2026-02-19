<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\NursingDischargeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NursingDischargeRepository::class)]
#[ORM\Table(name: 'nursing_discharge')]
class NursingDischarge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?AdmissionRecord $admissionRecord = null;

    #[Assert\Choice(choices: ['alta', 'fallecimiento', 'traslado_externo'])]
    #[ORM\Column(length: 30)]
    private string $dischargeType = 'alta';

    #[Assert\Choice(choices: ['bueno', 'regular', 'grave'])]
    #[ORM\Column(length: 20)]
    private string $conditionStatus = 'bueno';

    #[Assert\NotNull]
    #[ORM\Column]
    private \DateTimeImmutable $dischargedAt;

    #[ORM\Column(nullable: true)]
    private ?int $cancelledByUserId = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $cancelledAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    public function __construct()
    {
        $this->dischargedAt = new \DateTimeImmutable();
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

    public function getDischargeType(): string
    {
        return $this->dischargeType;
    }

    public function setDischargeType(string $dischargeType): self
    {
        $this->dischargeType = $dischargeType;
        return $this;
    }

    public function getConditionStatus(): string
    {
        return $this->conditionStatus;
    }

    public function setConditionStatus(string $conditionStatus): self
    {
        $this->conditionStatus = $conditionStatus;
        return $this;
    }

    public function getDischargedAt(): \DateTimeImmutable
    {
        return $this->dischargedAt;
    }

    public function setDischargedAt(\DateTimeImmutable $dischargedAt): self
    {
        $this->dischargedAt = $dischargedAt;
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
