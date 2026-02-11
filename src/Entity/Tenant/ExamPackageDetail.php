<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\ExamPackageDetailRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * ExamPackageDetail - Details of exams in a package
 * Legacy table: paquete_examen_detalle
 * 
 * Represents individual clinical actions/exams within an exam package
 */
#[ORM\Entity(repositoryClass: ExamPackageDetailRepository::class)]
#[ORM\Table(name: 'exam_package_detail')]
#[ORM\HasLifecycleCallbacks]
class ExamPackageDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: ExamPackage::class, inversedBy: 'details')]
    #[ORM\JoinColumn(name: 'exam_package_id', referencedColumnName: 'id', nullable: false)]
    private ?ExamPackage $examPackage = null;

    #[ORM\ManyToOne(targetEntity: MedicalService::class)]
    #[ORM\JoinColumn(name: 'medical_service_id', referencedColumnName: 'id', nullable: false)]
    private ?MedicalService $medicalService = null;

    #[ORM\Column(type: 'boolean')]
    private ?bool $isActive = true;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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

    public function getExamPackage(): ?ExamPackage
    {
        return $this->examPackage;
    }

    public function setExamPackage(?ExamPackage $examPackage): static
    {
        $this->examPackage = $examPackage;
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
        return $this->medicalService?->getName() ?? '';
    }
}
