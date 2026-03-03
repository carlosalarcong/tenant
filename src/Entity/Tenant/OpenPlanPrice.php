<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\OpenPlanPriceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OpenPlanPriceRepository::class)]
#[ORM\Table(name: 'open_plan_price')]
class OpenPlanPrice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'unit_price', type: 'decimal', precision: 10, scale: 2)]
    private ?string $unitPrice = null;

    #[ORM\Column(name: 'copay_amount', type: 'decimal', precision: 10, scale: 2)]
    private ?string $copayAmount = null;

    #[ORM\Column(name: 'theatre_amount', type: 'decimal', precision: 10, scale: 2)]
    private ?string $theatreAmount = null;

    #[ORM\Column(name: 'effective_date', type: 'datetime')]
    private ?\DateTimeInterface $effectiveDate = null;

    #[ORM\Column(name: 'cancellation_date', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $cancellationDate = null;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\ManyToOne(targetEntity: InsurancePlan::class)]
    #[ORM\JoinColumn(name: 'plan_id', referencedColumnName: 'id', nullable: false)]
    private ?InsurancePlan $plan = null;

    #[ORM\ManyToOne(targetEntity: BillingItem::class)]
    #[ORM\JoinColumn(name: 'billing_item_id', referencedColumnName: 'id', nullable: false)]
    private ?BillingItem $billingItem = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'cancellation_user_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $cancellationUser = null;

    /**
     * @var Collection<int, OpenPlanDistribution>
     */
    #[ORM\OneToMany(mappedBy: 'openPlanPrice', targetEntity: OpenPlanDistribution::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $distributions;

    public function __construct()
    {
        $this->distributions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUnitPrice(): ?string
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(string $unitPrice): self
    {
        $this->unitPrice = $unitPrice;
        return $this;
    }

    public function getCopayAmount(): ?string
    {
        return $this->copayAmount;
    }

    public function setCopayAmount(string $copayAmount): self
    {
        $this->copayAmount = $copayAmount;
        return $this;
    }

    public function getTheatreAmount(): ?string
    {
        return $this->theatreAmount;
    }

    public function setTheatreAmount(string $theatreAmount): self
    {
        $this->theatreAmount = $theatreAmount;
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

    public function getPlan(): ?InsurancePlan
    {
        return $this->plan;
    }

    public function setPlan(?InsurancePlan $plan): self
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
     * @return Collection<int, OpenPlanDistribution>
     */
    public function getDistributions(): Collection
    {
        return $this->distributions;
    }

    public function addDistribution(OpenPlanDistribution $distribution): self
    {
        if (!$this->distributions->contains($distribution)) {
            $this->distributions->add($distribution);
            $distribution->setOpenPlanPrice($this);
        }

        return $this;
    }

    public function removeDistribution(OpenPlanDistribution $distribution): self
    {
        if ($this->distributions->removeElement($distribution) && $distribution->getOpenPlanPrice() === $this) {
            $distribution->setOpenPlanPrice(null);
        }

        return $this;
    }
}
