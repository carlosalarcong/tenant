<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\NursingChargeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NursingChargeRepository::class)]
#[ORM\Table(name: 'nursing_charge')]
class NursingCharge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?AdmissionRecord $admissionRecord = null;

    #[Assert\Choice(choices: ['article', 'intervention', 'package'])]
    #[ORM\Column(length: 20)]
    private string $chargeType;

    #[ORM\Column(nullable: true)]
    private ?int $referenceId = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $payload = null;

    #[Assert\Choice(choices: ['active', 'cancelled', 'rejected'])]
    #[ORM\Column(length: 20)]
    private string $status = 'active';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->chargeType = 'article';
    }

    public function getId(): ?int { return $this->id; }
    public function getAdmissionRecord(): ?AdmissionRecord { return $this->admissionRecord; }
    public function setAdmissionRecord(?AdmissionRecord $admissionRecord): self { $this->admissionRecord = $admissionRecord; return $this; }
    public function getChargeType(): string { return $this->chargeType; }
    public function setChargeType(string $chargeType): self { $this->chargeType = $chargeType; return $this; }
    public function getReferenceId(): ?int { return $this->referenceId; }
    public function setReferenceId(?int $referenceId): self { $this->referenceId = $referenceId; return $this; }
    public function getPayload(): ?array { return $this->payload; }
    public function setPayload(?array $payload): self { $this->payload = $payload; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function setCreatedAt(\DateTimeImmutable $createdAt): self { $this->createdAt = $createdAt; return $this; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }
}
