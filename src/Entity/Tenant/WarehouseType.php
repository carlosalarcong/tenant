<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\WarehouseTypeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * WarehouseType (TipoBodega)
 *
 * Legacy table: tipo_bodega
 * Spanish name: Tipo de Bodega
 *
 * Catalog of warehouse types (e.g. central, secondary, pharmacy).
 */
#[ORM\Entity(repositoryClass: WarehouseTypeRepository::class)]
#[ORM\Table(name: 'warehouse_type')]
class WarehouseType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Legacy: NOMBRE */
    #[ORM\Column(length: 255)]
    private string $name;

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

    public function __toString(): string
    {
        return $this->name;
    }
}
