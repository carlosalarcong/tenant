<?php

namespace App\Entity\Tenant;

//use App\Repository\HealthInsuranceRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * HealthInsurance (Prevision)
 *
 * Tabla legacy: prevision
 *
 * Catálogo de previsiones de salud (FONASA, ISAPRE, etc.) con integración HL7 e IMED.
 */
#[ORM\Entity()]
#[ORM\Table(name: '`health_insurance`')]
class HealthInsurance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $interfaceCode = null;

    #[ORM\Column(nullable: true)]
    private ?int $idImed = null;

    #[ORM\Column(length: 45)]
    private ?string $abbreviatedName = null;

    #[ORM\Column(options: ["default" => true])]
    private ?bool $isDefaultValue = true;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $healthcarePrevitionHl7 = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $icon = null;

    #[ORM\ManyToOne(inversedBy: 'healthInsurances')]
    private ?HealthInsuranceType $healthInsuranceType = null;

    #[ORM\ManyToOne(inversedBy: 'healthInsurances')]
    private ?TaxAffectationType $taxAffectationType = null;

    #[ORM\Column(options: ["default" => true])]
    private ?bool $isActive = true;

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

    public function getInterfaceCode(): ?string
    {
        return $this->interfaceCode;
    }

    public function setInterfaceCode(?string $interfaceCode): static
    {
        $this->interfaceCode = $interfaceCode;

        return $this;
    }

    public function getIdImed(): ?int
    {
        return $this->idImed;
    }

    public function setIdImed(?int $idImed): static
    {
        $this->idImed = $idImed;

        return $this;
    }

    public function getAbbreviatedName(): ?string
    {
        return $this->abbreviatedName;
    }

    public function setAbbreviatedName(string $abbreviatedName): static
    {
        $this->abbreviatedName = $abbreviatedName;

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

    public function getHealthcarePrevitionHl7(): ?string
    {
        return $this->healthcarePrevitionHl7;
    }

    public function setHealthcarePrevitionHl7(?string $healthcarePrevitionHl7): static
    {
        $this->healthcarePrevitionHl7 = $healthcarePrevitionHl7;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

        return $this;
    }

    public function getHealthInsuranceType(): ?HealthInsuranceType
    {
        return $this->healthInsuranceType;
    }

    public function setHealthInsuranceType(?HealthInsuranceType $healthInsuranceType): static
    {
        $this->healthInsuranceType = $healthInsuranceType;

        return $this;
    }

    public function getTaxAffectationType(): ?TaxAffectationType
    {
        return $this->taxAffectationType;
    }

    public function setTaxAffectationType(?TaxAffectationType $taxAffectationType): static
    {
        $this->taxAffectationType = $taxAffectationType;

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
}
