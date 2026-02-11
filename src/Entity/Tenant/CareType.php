<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\CareTypeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * CareType (TipoAtencionFc)
 *
 * Legacy table: tipo_atencion_fc
 * Spanish name: Tipo de Atención de Facturación / Cuenta
 *
 * Catalog of care/billing account types used in admissions
 * (e.g. Hospitalaria, Urgencia, Ambulatoria, Quirúrgica).
 */
#[ORM\Entity(repositoryClass: CareTypeRepository::class)]
#[ORM\Table(name: 'care_type')]
class CareType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $route = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(?string $route): self
    {
        $this->route = $route;
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

    public function __toString(): string
    {
        return $this->name;
    }
}
