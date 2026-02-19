<?php

namespace App\Entity\Tenant;

//use App\Repository\MunicipalityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Municipality (Comuna)
 *
 * Tabla legacy: comuna
 *
 * Catálogo de comunas (municipios) pertenecientes a una provincia, con código HL7 para interoperabilidad.
 */
#[ORM\Entity()]
#[ORM\Table(name: '`municipality`')]
class Municipality
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $municipalityCodeHl7 = null;

    #[ORM\ManyToOne(inversedBy: 'municipalities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?province $province = null;

    #[ORM\Column(options: ["default" => true])]
    private ?bool $isActive = true;

    /**
     * @var Collection<int, PersonAddress>
     */
    #[ORM\OneToMany(targetEntity: PersonAddress::class, mappedBy: 'municipality')]
    private Collection $personAddresses;

    public function __construct()
    {
        $this->personAddresses = new ArrayCollection();
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

    public function getMunicipalityCodeHl7(): ?string
    {
        return $this->municipalityCodeHl7;
    }

    public function setMunicipalityCodeHl7(?string $municipalityCodeHl7): static
    {
        $this->municipalityCodeHl7 = $municipalityCodeHl7;

        return $this;
    }

    public function getProvince(): ?province
    {
        return $this->province;
    }

    public function setProvince(?province $province): static
    {
        $this->province = $province;

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

    /**
     * @return Collection<int, PersonAddress>
     */
    public function getPersonAddresses(): Collection
    {
        return $this->personAddresses;
    }

    public function addPersonAddress(PersonAddress $personAddress): static
    {
        if (!$this->personAddresses->contains($personAddress)) {
            $this->personAddresses->add($personAddress);
            $personAddress->setMunicipality($this);
        }

        return $this;
    }

    public function removePersonAddress(PersonAddress $personAddress): static
    {
        if ($this->personAddresses->removeElement($personAddress)) {
            // set the owning side to null (unless already changed)
            if ($personAddress->getMunicipality() === $this) {
                $personAddress->setMunicipality(null);
            }
        }

        return $this;
    }
}
