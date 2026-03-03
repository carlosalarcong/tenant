<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\OpenPlanDistributionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OpenPlanDistributionRepository::class)]
#[ORM\Table(name: 'open_plan_distribution')]
class OpenPlanDistribution
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\ManyToOne(targetEntity: OpenPlanPrice::class, inversedBy: 'distributions')]
    #[ORM\JoinColumn(name: 'open_plan_price_id', referencedColumnName: 'id', nullable: false)]
    private ?OpenPlanPrice $openPlanPrice = null;

    #[ORM\ManyToOne(targetEntity: SurgeryFeeItem::class)]
    #[ORM\JoinColumn(name: 'surgery_fee_item_id', referencedColumnName: 'id', nullable: false)]
    private ?SurgeryFeeItem $surgeryFeeItem = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;
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

    public function getOpenPlanPrice(): ?OpenPlanPrice
    {
        return $this->openPlanPrice;
    }

    public function setOpenPlanPrice(?OpenPlanPrice $openPlanPrice): self
    {
        $this->openPlanPrice = $openPlanPrice;
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
