<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\AdmissionRecordRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdmissionRecordRepository::class)]
#[ORM\Table(name: 'admission_record')]
#[ORM\HasLifecycleCallbacks]
class AdmissionRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Person::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Person $person = null;

    #[ORM\Column(length: 30)]
    private string $admissionType = 'hospitalaria';

    #[ORM\Column(length: 30)]
    private string $status = 'draft';

    #[ORM\ManyToOne(targetEntity: Payer::class)]
    #[ORM\JoinColumn(name: 'payer_id', referencedColumnName: 'id', nullable: true)]
    private ?Payer $payer = null;

    #[ORM\ManyToOne(targetEntity: Agreement::class)]
    #[ORM\JoinColumn(name: 'agreement_id', referencedColumnName: 'id', nullable: true)]
    private ?Agreement $agreement = null;

    #[ORM\ManyToOne(targetEntity: Service::class)]
    #[ORM\JoinColumn(name: 'service_id', referencedColumnName: 'id', nullable: true)]
    private ?Service $service = null;

    #[ORM\ManyToOne(targetEntity: Bed::class)]
    #[ORM\JoinColumn(name: 'bed_id', referencedColumnName: 'id', nullable: true)]
    private ?Bed $bed = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $triage = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $consultationReason = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(Person $person): self
    {
        $this->person = $person;
        return $this;
    }

    public function getAdmissionType(): string
    {
        return $this->admissionType;
    }

    public function setAdmissionType(string $admissionType): self
    {
        $this->admissionType = $admissionType;
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

    public function getPayer(): ?Payer
    {
        return $this->payer;
    }

    public function setPayer(?Payer $payer): self
    {
        $this->payer = $payer;
        return $this;
    }

    public function getAgreement(): ?Agreement
    {
        return $this->agreement;
    }

    public function setAgreement(?Agreement $agreement): self
    {
        $this->agreement = $agreement;
        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;
        return $this;
    }

    public function getBed(): ?Bed
    {
        return $this->bed;
    }

    public function setBed(?Bed $bed): self
    {
        $this->bed = $bed;
        return $this;
    }

    public function getTriage(): ?string
    {
        return $this->triage;
    }

    public function setTriage(?string $triage): self
    {
        $this->triage = $triage;
        return $this;
    }

    public function getConsultationReason(): ?string
    {
        return $this->consultationReason;
    }

    public function setConsultationReason(?string $consultationReason): self
    {
        $this->consultationReason = $consultationReason;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
