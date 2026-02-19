<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\NursingOrderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NursingOrderRepository::class)]
#[ORM\Table(name: 'nursing_order')]
class NursingOrder
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
    private ?Person $orderedBy = null;

    #[Assert\NotNull]
    #[ORM\Column]
    private \DateTimeImmutable $orderedAt;

    #[Assert\Choice(choices: ['farmacologica', 'procedimiento'])]
    #[ORM\Column(length: 30)]
    private string $orderType = 'procedimiento';

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    #[Assert\Choice(choices: ['active', 'completed', 'cancelled'])]
    #[ORM\Column(length: 20)]
    private string $status = 'active';

    public function __construct()
    {
        $this->orderedAt = new \DateTimeImmutable();
        $this->description = '';
    }

    public function getId(): ?int { return $this->id; }
    public function getAdmissionRecord(): ?AdmissionRecord { return $this->admissionRecord; }
    public function setAdmissionRecord(?AdmissionRecord $admissionRecord): self { $this->admissionRecord = $admissionRecord; return $this; }
    public function getOrderedBy(): ?Person { return $this->orderedBy; }
    public function setOrderedBy(?Person $orderedBy): self { $this->orderedBy = $orderedBy; return $this; }
    public function getOrderedAt(): \DateTimeImmutable { return $this->orderedAt; }
    public function setOrderedAt(\DateTimeImmutable $orderedAt): self { $this->orderedAt = $orderedAt; return $this; }
    public function getOrderType(): string { return $this->orderType; }
    public function setOrderType(string $orderType): self { $this->orderType = $orderType; return $this; }
    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
}
