<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\SurgeryPackagePlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SurgeryPackagePlanRepository::class)]
#[ORM\Table(name: 'surgery_package_plan')]
class SurgeryPackagePlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'cancellation_date', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $cancellationDate = null;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\ManyToOne(targetEntity: BranchPayer::class)]
    #[ORM\JoinColumn(name: 'branch_payer_id', referencedColumnName: 'id', nullable: false)]
    private ?BranchPayer $branchPayer = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', nullable: false)]
    private ?Member $createdBy = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'cancellation_user_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $cancellationUser = null;

    /**
     * @var Collection<int, SurgeryPackage>
     */
    #[ORM\OneToMany(mappedBy: 'plan', targetEntity: SurgeryPackage::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $packages;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->packages = new ArrayCollection();
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

    public function getBranchPayer(): ?BranchPayer
    {
        return $this->branchPayer;
    }

    public function setBranchPayer(?BranchPayer $branchPayer): self
    {
        $this->branchPayer = $branchPayer;
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
     * @return Collection<int, SurgeryPackage>
     */
    public function getPackages(): Collection
    {
        return $this->packages;
    }

    public function addPackage(SurgeryPackage $package): self
    {
        if (!$this->packages->contains($package)) {
            $this->packages->add($package);
            $package->setPlan($this);
        }

        return $this;
    }

    public function removePackage(SurgeryPackage $package): self
    {
        if ($this->packages->removeElement($package) && $package->getPlan() === $this) {
            $package->setPlan(null);
        }

        return $this;
    }
}
