<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\NursingPrescriptionItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: NursingPrescriptionItemRepository::class)]
#[ORM\Table(name: 'nursing_prescription_item')]
class NursingPrescriptionItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotNull]
    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private ?NursingPrescription $prescription = null;

    #[Assert\NotBlank]
    #[ORM\Column(length: 60)]
    private string $articleCode;

    #[Assert\NotBlank]
    #[ORM\Column(length: 255)]
    private string $articleName;

    #[Assert\NotBlank]
    #[ORM\Column(length: 100)]
    private string $dose;

    #[Assert\NotBlank]
    #[ORM\Column(length: 100)]
    private string $route;

    #[Assert\NotBlank]
    #[ORM\Column(length: 100)]
    private string $frequency;

    #[Assert\Positive]
    #[ORM\Column]
    private int $quantity = 1;

    public function getId(): ?int { return $this->id; }
    public function getPrescription(): ?NursingPrescription { return $this->prescription; }
    public function setPrescription(?NursingPrescription $prescription): self { $this->prescription = $prescription; return $this; }
    public function getArticleCode(): string { return $this->articleCode; }
    public function setArticleCode(string $articleCode): self { $this->articleCode = $articleCode; return $this; }
    public function getArticleName(): string { return $this->articleName; }
    public function setArticleName(string $articleName): self { $this->articleName = $articleName; return $this; }
    public function getDose(): string { return $this->dose; }
    public function setDose(string $dose): self { $this->dose = $dose; return $this; }
    public function getRoute(): string { return $this->route; }
    public function setRoute(string $route): self { $this->route = $route; return $this; }
    public function getFrequency(): string { return $this->frequency; }
    public function setFrequency(string $frequency): self { $this->frequency = $frequency; return $this; }
    public function getQuantity(): int { return $this->quantity; }
    public function setQuantity(int $quantity): self { $this->quantity = $quantity; return $this; }
}
