<?php

namespace App\Entity\Tenant;

//use App\Repository\HealthInsuranceTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * HealthInsuranceType (TipoPrevision)
 *
 * Tabla legacy: tipo_prevision
 *
 * Catálogo de tipos de previsión de salud (ej. FONASA, ISAPRE, Particular).
 */
#[ORM\Entity()]
#[ORM\Table(name: "health_insurance_type")]
class HealthInsuranceType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(options: ["default" => true])]
    private ?bool $isDefaultValue = true;

    #[ORM\Column(options: ["default" => true])]
    private ?bool $isAgreement = true;

    #[ORM\Column(options: ["default" => true])]
    private ?bool $isActive = true;

    /**
     * @var Collection<int, HealthInsurance>
     */
    #[ORM\OneToMany(targetEntity: HealthInsurance::class, mappedBy: 'healthInsuranceType')]
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

    public function isDefaultValue(): ?bool
    {
        return $this->isDefaultValue;
    }

    public function setIsDefaultValue(bool $isDefaultValue): static
    {
        $this->isDefaultValue = $isDefaultValue;

        return $this;
    }

    public function isAgreement(): ?bool
    {
        return $this->isAgreement;
    }

    public function setIsAgreement(bool $isAgreement): static
    {
        $this->isAgreement = $isAgreement;

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
            $healthInsurance->setHealthInsuranceType($this);
        }

        return $this;
    }

    public function removeHealthInsurance(HealthInsurance $healthInsurance): static
    {
        if ($this->healthInsurances->removeElement($healthInsurance)) {
            // set the owning side to null (unless already changed)
            if ($healthInsurance->getHealthInsuranceType() === $this) {
                $healthInsurance->setHealthInsuranceType(null);
            }
        }

        return $this;
    }
}
