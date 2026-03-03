<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\SurgeryPackageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SurgeryPackageRepository::class)]
#[ORM\Table(name: 'surgery_package')]
class SurgeryPackage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(name: 'adjustment_percentage', type: 'decimal', precision: 10, scale: 2, options: ['default' => 0])]
    private string $adjustmentPercentage = '0.00';

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'cancellation_date', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $cancellationDate = null;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\ManyToOne(targetEntity: SurgeryPackagePlan::class, inversedBy: 'packages')]
    #[ORM\JoinColumn(name: 'plan_id', referencedColumnName: 'id', nullable: false)]
    private ?SurgeryPackagePlan $plan = null;

    #[ORM\ManyToOne(targetEntity: BillingItem::class)]
    #[ORM\JoinColumn(name: 'billing_item_id', referencedColumnName: 'id', nullable: false)]
    private ?BillingItem $billingItem = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', nullable: false)]
    private ?Member $createdBy = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'cancellation_user_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $cancellationUser = null;

    /**
     * @var Collection<int, SurgeryPackagePrice>
     */
    #[ORM\OneToMany(mappedBy: 'package', targetEntity: SurgeryPackagePrice::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $prices;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->prices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getAdjustmentPercentage(): string
    {
        return $this->adjustmentPercentage;
    }

    public function setAdjustmentPercentage(string $adjustmentPercentage): self
    {
        $this->adjustmentPercentage = $adjustmentPercentage;
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

    public function getCancellationDate(): ?\DateTimeInterface
    {
        return $this->cancellationDate;
    }

    public function setCancellationDate(?\DateTimeInterface $cancellationDate): self
    {
        $this->cancellationDate = $cancellationDate;
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

    public function getPlan(): ?SurgeryPackagePlan
    {
        return $this->plan;
    }

    public function setPlan(?SurgeryPackagePlan $plan): self
    {
        $this->plan = $plan;
        return $this;
    }

    public function getBillingItem(): ?BillingItem
    {
        return $this->billingItem;
    }

    public function setBillingItem(?BillingItem $billingItem): self
    {
        $this->billingItem = $billingItem;
        return $this;
    }

    public function getCreatedBy(): ?Member
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Member $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getCancellationUser(): ?Member
    {
        return $this->cancellationUser;
    }

    public function setCancellationUser(?Member $cancellationUser): self
    {
        $this->cancellationUser = $cancellationUser;
        return $this;
    }

    /**
     * @return Collection<int, SurgeryPackagePrice>
     */
    public function getPrices(): Collection
    {
        return $this->prices;
    }

    public function addPrice(SurgeryPackagePrice $price): self
    {
        if (!$this->prices->contains($price)) {
            $this->prices->add($price);
            $price->setPackage($this);
        }

        return $this;
    }

    public function removePrice(SurgeryPackagePrice $price): self
    {
        if ($this->prices->removeElement($price) && $price->getPackage() === $this) {
            $price->setPackage(null);
        }

        return $this;
    }
}
