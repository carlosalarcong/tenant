<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\SurgeryPackagePriceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * SurgeryPackagePrice (PqPrecioPaqueteCirugia)
 *
 * Tabla legacy: pq_precio_paquete_cirugia
 *
 * Precio vigente de un ítem o plan paquetizado por sucursal/financiador.
 */
#[ORM\Entity(repositoryClass: SurgeryPackagePriceRepository::class)]
#[ORM\Table(name: 'surgery_package_price')]
class SurgeryPackagePrice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $priceIsapre = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $priceFonasa = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $effectiveDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expirationDate = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: SurgeryPackageItem::class)]
    #[ORM\JoinColumn(name: 'surgery_package_item_id', referencedColumnName: 'id', nullable: false)]
    private ?SurgeryPackageItem $surgeryPackageItem = null;

    #[ORM\ManyToOne(targetEntity: SurgeryPackagePlan::class)]
    #[ORM\JoinColumn(name: 'surgery_package_plan_id', referencedColumnName: 'id', nullable: false)]
    private ?SurgeryPackagePlan $surgeryPackagePlan = null;

    #[ORM\ManyToOne(targetEntity: BranchPayer::class)]
    #[ORM\JoinColumn(name: 'branch_payer_id', referencedColumnName: 'id', nullable: true)]
    private ?BranchPayer $branchPayer = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPriceIsapre(): ?string
    {
        return $this->priceIsapre;
    }

    public function setPriceIsapre(string $priceIsapre): self
    {
        $this->priceIsapre = $priceIsapre;
        return $this;
    }

    public function getPriceFonasa(): ?string
    {
        return $this->priceFonasa;
    }

    public function setPriceFonasa(?string $priceFonasa): self
    {
        $this->priceFonasa = $priceFonasa;
        return $this;
    }

    public function getEffectiveDate(): ?\DateTimeInterface
    {
        return $this->effectiveDate;
    }

    public function setEffectiveDate(\DateTimeInterface $effectiveDate): self
    {
        $this->effectiveDate = $effectiveDate;
        return $this;
    }

    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(?\DateTimeInterface $expirationDate): self
    {
        $this->expirationDate = $expirationDate;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
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

    public function getSurgeryPackageItem(): ?SurgeryPackageItem
    {
        return $this->surgeryPackageItem;
    }

    public function setSurgeryPackageItem(?SurgeryPackageItem $surgeryPackageItem): self
    {
        $this->surgeryPackageItem = $surgeryPackageItem;
        return $this;
    }

    public function getSurgeryPackagePlan(): ?SurgeryPackagePlan
    {
        return $this->surgeryPackagePlan;
    }

    public function setSurgeryPackagePlan(?SurgeryPackagePlan $surgeryPackagePlan): self
    {
        $this->surgeryPackagePlan = $surgeryPackagePlan;
        return $this;
    }

    public function getBranchPayer(): ?BranchPayer
    {
        return $this->branchPayer;
    }

    public function setBranchPayer(?BranchPayer $branchPayer): self
    {
        $this->branchPayer = $branchPayer;
        return $this;
    }
}
