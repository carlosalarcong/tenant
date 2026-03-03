<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\BudgetObservationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * BudgetObservation (PresupuestoObservacion)
 *
 * Tabla legacy: presupuesto_observacion
 *
 * Observación registrada sobre un presupuesto.
 */
#[ORM\Entity(repositoryClass: BudgetObservationRepository::class)]
#[ORM\Table(name: 'budget_observation')]
class BudgetObservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'is_email_sent', type: 'boolean', options: ['default' => false])]
    private bool $isEmailSent = false;

    #[ORM\Column(type: 'string', length: 2000)]
    private ?string $observation = null;

    #[ORM\ManyToOne(targetEntity: Budget::class, inversedBy: 'observations')]
    #[ORM\JoinColumn(name: 'budget_id', nullable: false)]
    private ?Budget $budget = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'member_id', nullable: false)]
    private ?Member $member = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function isEmailSent(): bool
    {
        return $this->isEmailSent;
    }

    public function setIsEmailSent(bool $isEmailSent): self
    {
        $this->isEmailSent = $isEmailSent;
        return $this;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(string $observation): self
    {
        $this->observation = $observation;
        return $this;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): self
    {
        $this->budget = $budget;
        return $this;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;
        return $this;
    }
}
