<?php

namespace App\Entity\Tenant;

//use App\Repository\TaxAffectationTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * TaxAffectationType (TipoAfectacionTributaria)
 *
 * Tabla legacy: tipo_afectacion_tributaria
 *
 * Catálogo de tipos de afectación tributaria aplicables a artículos o servicios (ej. Afecto, Exento).
 */
#[ORM\Entity()]
#[ORM\Table(name: '`tax_affectation_type`')]
class TaxAffectationType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(options: ["default" => true])]
    private ?bool $isActive = true;

    /**
     * @var Collection<int, HealthInsurance>
     */
    #[ORM\OneToMany(targetEntity: HealthInsurance::class, mappedBy: 'taxAffectationType')]
    private Collection $healthInsurances;

    public function __construct()
    {
        $this->healthInsurances = new ArrayCollection();
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
     * @return Collection<int, HealthInsurance>
     */
    public function getHealthInsurances(): Collection
    {
        return $this->healthInsurances;
    }

    public function addHealthInsurance(HealthInsurance $healthInsurance): static
    {
        if (!$this->healthInsurances->contains($healthInsurance)) {
            $this->healthInsurances->add($healthInsurance);
            $healthInsurance->setTaxAffectationType($this);
        }

        return $this;
    }

    public function removeHealthInsurance(HealthInsurance $healthInsurance): static
    {
        if ($this->healthInsurances->removeElement($healthInsurance)) {
            // set the owning side to null (unless already changed)
            if ($healthInsurance->getTaxAffectationType() === $this) {
                $healthInsurance->setTaxAffectationType(null);
            }
        }

        return $this;
    }
}
