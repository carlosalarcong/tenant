<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\EducationLevelDetailRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * EducationLevelDetail (DetalleNivelEducacional)
 *
 * Tabla legacy: detalle_nivel_educacional
 *
 * Catálogo de detalles específicos asociados a cada nivel educacional.
 */
#[ORM\Entity(repositoryClass: EducationLevelDetailRepository::class)]
#[ORM\Table(name: 'maintainer_education_level_detail')]
class EducationLevelDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $code = null;

    #[ORM\ManyToOne(targetEntity: EducationLevel::class)]
    #[ORM\JoinColumn(nullable: false)]
    private EducationLevel $educationLevel;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ["default" => true])]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function getEducationLevel(): EducationLevel
    {
        return $this->educationLevel;
    }

    public function setEducationLevel(EducationLevel $educationLevel): self
    {
        $this->educationLevel = $educationLevel;
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
    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
    public function __toString(): string
    {
        return $this->name;
    }
}
