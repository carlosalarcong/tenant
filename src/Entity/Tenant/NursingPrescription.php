<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\NursingPrescriptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NursingPrescriptionRepository::class)]
#[ORM\Table(name: 'nursing_prescription')]
class NursingPrescription
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
    private ?Person $prescribedBy = null;

    #[Assert\NotNull]
    #[ORM\Column]
    private \DateTimeImmutable $prescribedAt;

    #[Assert\Choice(choices: ['active', 'completed', 'cancelled'])]
    #[ORM\Column(length: 20)]
    private string $status = 'active';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $complement = null;

    /** @var Collection<int, NursingPrescriptionItem> */
    #[ORM\OneToMany(mappedBy: 'prescription', targetEntity: NursingPrescriptionItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $items;

    public function __construct()
    {
        $this->prescribedAt = new \DateTimeImmutable();
        $this->items = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }
    public function getAdmissionRecord(): ?AdmissionRecord { return $this->admissionRecord; }
    public function setAdmissionRecord(?AdmissionRecord $admissionRecord): self { $this->admissionRecord = $admissionRecord; return $this; }
    public function getPrescribedBy(): ?Person { return $this->prescribedBy; }
    public function setPrescribedBy(?Person $prescribedBy): self { $this->prescribedBy = $prescribedBy; return $this; }
    public function getPrescribedAt(): \DateTimeImmutable { return $this->prescribedAt; }
    public function setPrescribedAt(\DateTimeImmutable $prescribedAt): self { $this->prescribedAt = $prescribedAt; return $this; }
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function getComplement(): ?string { return $this->complement; }
    public function setComplement(?string $complement): self { $this->complement = $complement; return $this; }

    /** @return Collection<int, NursingPrescriptionItem> */
    public function getItems(): Collection { return $this->items; }

    public function addItem(NursingPrescriptionItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setPrescription($this);
        }

        return $this;
    }

    public function removeItem(NursingPrescriptionItem $item): self
    {
        if ($this->items->removeElement($item) && $item->getPrescription() === $this) {
            $item->setPrescription(null);
        }

        return $this;
    }
}
