<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\NursingConcurrencyRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NursingConcurrencyRepository::class)]
#[ORM\Table(name: 'nursing_concurrency')]
#[ORM\UniqueConstraint(name: 'uniq_nursing_concurrency_admission', columns: ['admission_record_id'])]
#[ORM\Index(name: 'idx_nursing_concurrency_locked_at', columns: ['locked_at'])]
class NursingConcurrency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotNull]
    #[Assert\Positive]
    #[ORM\Column(name: 'admission_record_id')]
    private int $admissionRecordId;

    #[Assert\NotNull]
    #[Assert\Positive]
    #[ORM\Column(name: 'user_id')]
    private int $userId;

    #[Assert\NotBlank]
    #[Assert\Length(max: 128)]
    #[ORM\Column(name: 'session_id', length: 128)]
    private string $sessionId;

    #[Assert\NotNull]
    #[ORM\Column(name: 'locked_at')]
    private \DateTimeImmutable $lockedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdmissionRecordId(): int
    {
        return $this->admissionRecordId;
    }

    public function setAdmissionRecordId(int $admissionRecordId): self
    {
        $this->admissionRecordId = $admissionRecordId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): self
    {
        $this->sessionId = $sessionId;

        return $this;
    }

    public function getLockedAt(): \DateTimeImmutable
    {
        return $this->lockedAt;
    }

    public function setLockedAt(\DateTimeImmutable $lockedAt): self
    {
        $this->lockedAt = $lockedAt;

        return $this;
    }
}
