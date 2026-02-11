<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\ExamPackageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ExamPackage - Package of medical exams/tests
 * Legacy table: paquete_examen
 * 
 * Represents a bundle of medical exams offered as a package
 */
#[ORM\Entity(repositoryClass: ExamPackageRepository::class)]
#[ORM\Table(name: 'exam_package')]
#[ORM\HasLifecycleCallbacks]
class ExamPackage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $code = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isBillable = false;

    #[ORM\ManyToOne(targetEntity: MedicalService::class)]
    #[ORM\JoinColumn(name: 'medical_service_id', referencedColumnName: 'id', nullable: true)]
    private ?MedicalService $medicalService = null;

    #[ORM\OneToMany(mappedBy: 'examPackage', targetEntity: ExamPackageDetail::class, cascade: ['persist', 'remove'])]
    private Collection $details;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isActive = true;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->details = new ArrayCollection();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function isBillable(): ?bool
    {
        return $this->isBillable;
    }

    public function setIsBillable(bool $isBillable): static
    {
        $this->isBillable = $isBillable;
        return $this;
    }

    public function getMedicalService(): ?MedicalService
    {
        return $this->medicalService;
    }

    public function setMedicalService(?MedicalService $medicalService): static
    {
        $this->medicalService = $medicalService;
        return $this;
    }

    /**
     * @return Collection<int, ExamPackageDetail>
     */
    public function getDetails(): Collection
    {
        return $this->details;
    }

    public function addDetail(ExamPackageDetail $detail): static
    {
        if (!$this->details->contains($detail)) {
            $this->details->add($detail);
            $detail->setExamPackage($this);
        }
        return $this;
    }

    public function removeDetail(ExamPackageDetail $detail): static
    {
        if ($this->details->removeElement($detail)) {
            if ($detail->getExamPackage() === $this) {
                $detail->setExamPackage(null);
            }
        }
        return $this;
    }

    public function isActive(): ?bool
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
        return $this->name ?? '';
    }
}
