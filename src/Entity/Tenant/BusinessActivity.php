<?php

namespace App\Entity\Tenant;

//use App\Repository\BusinessActivityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * BusinessActivity (RubroEmpresa)
 *
 * Tabla legacy: rubro_empresa
 *
 * Catálogo de rubros o actividades económicas de empresas, utilizado en convenios y facturación.
 */
#[ORM\Entity()]
#[ORM\Table(name: '`business_activity`')]
class BusinessActivity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

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
