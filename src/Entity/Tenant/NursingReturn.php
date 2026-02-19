<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\NursingReturnRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NursingReturnRepository::class)]
#[ORM\Table(name: 'nursing_return')]
class NursingReturn
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?AdmissionRecord $admissionRecord = null;

    #[Assert\NotBlank]
    #[ORM\Column(length: 120)]
    private string $itemName;

    #[Assert\Positive]
    #[ORM\Column]
    private int $quantity = 1;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    #[Assert\Choice(choices: ['active', 'cancelled'])]
    #[ORM\Column(length: 20)]
    private string $status = 'active';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $cancelledAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->itemName = '';
    }

    public function getId(): ?int { return $this->id; }
    public function getAdmissionRecord(): ?AdmissionRecord { return $this->admissionRecord; }
    public function setAdmissionRecord(?AdmissionRecord $admissionRecord): self { $this->admissionRecord = $admissionRecord; return $this; }
    public function getItemName(): string { return $this->itemName; }
    public function setItemName(string $itemName): self { $this->itemName = $itemName; return $this; }
    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $quantity): self { $this->quantity = $quantity; return $this; }
    public function getReason(): ?string { return $this->reason; }
    public function setReason(?string $reason): self { $this->reason = $reason; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }
    public function getCancelledAt(): ?\DateTimeImmutable { return $this->cancelledAt; }
    public function setCancelledAt(?\DateTimeImmutable $cancelledAt): self { $this->cancelledAt = $cancelledAt; return $this; }
}
