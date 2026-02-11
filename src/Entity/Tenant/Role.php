<?php

namespace App\Entity\Tenant;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\Tenant\RoleRepository;

/**
 * Entidad para gestionar roles del sistema de forma dinámica
 * 
 * Esta tabla centraliza todos los roles disponibles en el sistema,
 * permitiendo administrarlos desde la UI y usarlos en dropdowns.
 * 
 * BENEFICIOS:
 * - Roles configurables sin modificar código
 * - Nombres traducibles/personalizables por tenant
 * - Control de activación/desactivación de roles
 * - Ordenamiento personalizable
 * 
 * @author Melisa Development Team
 * @since Sprint 2 - Dynamic Role Management (Feb 2026)
 */
#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ORM\Table(name: 'role')]
#[ORM\HasLifecycleCallbacks]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Código interno del rol (ej: ROLE_ADMIN, ROLE_DOCTOR)
     * Este es el valor que se usa en Symfony Security
     */
    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $code;

    /**
     * Nombre legible del rol (ej: "Administrador", "Doctor/Médico")
     * Este es el valor que se muestra en la UI
     */
    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    /**
     * Descripción opcional del rol y sus responsabilidades
     */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    /**
     * Posición para ordenar en dropdowns y listados
     * Menor número = mayor prioridad (aparece primero)
     */
    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    /**
     * Si está activo, el rol aparece en dropdowns y puede asignarse
     */
    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    /**
     * Si es un rol del sistema (no puede eliminarse)
     */
    #[ORM\Column(type: 'boolean')]
    private bool $isSystem = false;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;
        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;
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

    public function isSystem(): bool
    {
        return $this->isSystem;
    }

    public function setIsSystem(bool $isSystem): self
    {
        $this->isSystem = $isSystem;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
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

    /**
     * Para usar en dropdowns de formularios
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
