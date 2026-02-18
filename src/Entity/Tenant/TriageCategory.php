<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\TriageCategoryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * TriageCategory (Categorizacion)
 *
 * Legacy table: categorizacion
 * Spanish name: Categorización / Nivel de Triage
 *
 * Catalog of triage levels used in emergency admissions
 * (e.g. Rojo, Naranja, Amarillo, Verde, Azul — Manchester scale).
 */
#[ORM\Entity(repositoryClass: TriageCategoryRepository::class)]
#[ORM\Table(name: 'triage_category')]
class TriageCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** Legacy: NOMBRE */
    #[ORM\Column(length: 45)]
    private string $name;

    /** Legacy: COLOR (hex or css color name) */
    #[ORM\Column(length: 45)]
    private string $color;

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

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): self
    {
        $this->color = $color;
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
