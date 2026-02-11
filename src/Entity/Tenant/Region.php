<?php

namespace App\Entity\Tenant;

//use App\Repository\RegionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\Table(name: '`region`')]
class Region
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $addressStateHl7 = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $regionCodeHl7 = null;

    #[ORM\ManyToOne(inversedBy: 'regions')]
    private ?Country $country = null;

    #[ORM\Column(options: ["default" => true])]
    private ?bool $isActive = true;

    /**
     * @var Collection<int, Province>
     */
    #[ORM\OneToMany(targetEntity: Province::class, mappedBy: 'region')]
    private Collection $provinces;

    public function __construct()
    {
        $this->provinces = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getAddressStateHl7(): ?string
    {
        return $this->addressStateHl7;
    }

    public function setAddressStateHl7(string $addressStateHl7): static
    {
        $this->addressStateHl7 = $addressStateHl7;

        return $this;
    }

    public function getRegionCodeHl7(): ?string
    {
        return $this->regionCodeHl7;
    }

    public function setRegionCodeHl7(?string $regionCodeHl7): static
    {
        $this->regionCodeHl7 = $regionCodeHl7;

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

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): static
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection<int, Province>
     */
    public function getProvinces(): Collection
    {
        return $this->provinces;
    }

    public function addProvince(Province $province): static
    {
        if (!$this->provinces->contains($province)) {
            $this->provinces->add($province);
            $province->setRegion($this);
        }

        return $this;
    }

    public function removeProvince(Province $province): static
    {
        if ($this->provinces->removeElement($province)) {
            // set the owning side to null (unless already changed)
            if ($province->getRegion() === $this) {
                $province->setRegion(null);
            }
        }

        return $this;
    }

}
