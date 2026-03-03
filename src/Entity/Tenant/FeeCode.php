<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\FeeCodeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * FeeCode
 *
 * Tabla legacy: código fijo de guarismo.
 */
#[ORM\Entity(repositoryClass: FeeCodeRepository::class)]
#[ORM\Table(name: 'fee_code')]
class FeeCode
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 45)]
    private ?string $name = null;

    #[ORM\Column(name: 'is_theatre', type: 'boolean')]
    private bool $isTheatre = false;

    #[ORM\Column(name: 'is_zero', type: 'integer')]
    private int $isZero = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
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

    public function isTheatre(): bool
    {
        return $this->isTheatre;
    }

    public function setIsTheatre(bool $isTheatre): self
    {
        $this->isTheatre = $isTheatre;
        return $this;
    }

    public function getIsZero(): int
    {
        return $this->isZero;
    }

    public function setIsZero(int $isZero): self
    {
        $this->isZero = $isZero;
        return $this;
    }
}
