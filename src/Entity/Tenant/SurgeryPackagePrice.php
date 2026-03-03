<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\SurgeryPackagePriceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SurgeryPackagePriceRepository::class)]
#[ORM\Table(name: 'surgery_package_price')]
class SurgeryPackagePrice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'payer_price', type: 'decimal', precision: 10, scale: 2)]
    private ?string $payerPrice = null;

    #[ORM\Column(name: 'clinic_price', type: 'decimal', precision: 10, scale: 2)]
    private ?string $clinicPrice = null;

    #[ORM\Column(name: 'effective_date', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $effectiveDate = null;

    #[ORM\Column(name: 'is_in_use', type: 'boolean', options: ['default' => false])]
    private bool $isInUse = false;

    #[ORM\ManyToOne(targetEntity: SurgeryPackage::class, inversedBy: 'prices')]
    #[ORM\JoinColumn(name: 'package_id', referencedColumnName: 'id', nullable: false)]
    private ?SurgeryPackage $package = null;

    #[ORM\ManyToOne(targetEntity: SurgeryFeeItem::class)]
    #[ORM\JoinColumn(name: 'surgery_fee_item_id', referencedColumnName: 'id', nullable: false)]
    private ?SurgeryFeeItem $surgeryFeeItem = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPayerPrice(): ?string
    {
        return $this->payerPrice;
    }

    public function setPayerPrice(string $payerPrice): self
    {
        $this->payerPrice = $payerPrice;
        return $this;
    }

    public function getClinicPrice(): ?string
    {
        return $this->clinicPrice;
    }

    public function setClinicPrice(string $clinicPrice): self
    {
        $this->clinicPrice = $clinicPrice;
        return $this;
    }

    public function getEffectiveDate(): ?\DateTimeInterface
    {
        return $this->effectiveDate;
    }

    public function setEffectiveDate(?\DateTimeInterface $effectiveDate): self
    {
        $this->effectiveDate = $effectiveDate;
        return $this;
    }

    public function isInUse(): bool
    {
        return $this->isInUse;
    }

    public function setIsInUse(bool $isInUse): self
    {
        $this->isInUse = $isInUse;
        return $this;
    }

    public function getPackage(): ?SurgeryPackage
    {
        return $this->package;
    }

    public function setPackage(?SurgeryPackage $package): self
    {
        $this->package = $package;
        return $this;
    }

    public function getSurgeryFeeItem(): ?SurgeryFeeItem
    {
        return $this->surgeryFeeItem;
    }

    public function setSurgeryFeeItem(?SurgeryFeeItem $surgeryFeeItem): self
    {
        $this->surgeryFeeItem = $surgeryFeeItem;
        return $this;
    }
}
