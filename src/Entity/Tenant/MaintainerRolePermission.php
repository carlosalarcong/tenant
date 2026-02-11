<?php

namespace App\Entity\Tenant;

use Doctrine\ORM\Mapping as ORM;

/**
 * Matriz de permisos para mantenedores basada en roles
 * 
 * Esta entidad almacena qué permisos tiene cada rol sobre los mantenedores.
 * Permite administrar la matriz de permisos dinámicamente sin modificar código.
 * 
 * Ejemplo de registros:
 * | role                    | permission | granted |
 * |-------------------------|------------|---------|
 * | ROLE_ADMIN              | *          | true    |
 * | ROLE_MAINTAINER_MANAGER | CREATE     | true    |
 * | ROLE_MAINTAINER_MANAGER | READ       | true    |
 * | ROLE_MAINTAINER_MANAGER | UPDATE     | true    |
 * | ROLE_MAINTAINER_MANAGER | DELETE     | true    |
 * | ROLE_MAINTAINER_MANAGER | EXPORT     | true    |
 * | ROLE_MAINTAINER_USER    | READ       | true    |
 * 
 * @author Melisa Development Team
 * @since Sprint 1.5 - Database-driven permissions (Feb 2026)
 */
#[ORM\Entity(repositoryClass: 'App\Repository\Tenant\MaintainerRolePermissionRepository')]
#[ORM\Table(name: 'maintainer_role_permission')]
#[ORM\UniqueConstraint(name: 'unique_role_permission', columns: ['role', 'permission'])]
#[ORM\Index(name: 'idx_role', columns: ['role'])]
#[ORM\Index(name: 'idx_permission', columns: ['permission'])]
class MaintainerRolePermission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Nombre del rol Symfony
     * Ejemplos: ROLE_ADMIN, ROLE_MAINTAINER_MANAGER, ROLE_MAINTAINER_USER
     */
    #[ORM\Column(type: 'string', length: 100)]
    private string $role;

    /**
     * Permiso del mantenedor
     * Valores: CREATE, READ, UPDATE, DELETE, EXPORT, * (wildcard = todos)
     */
    #[ORM\Column(type: 'string', length: 50)]
    private string $permission;

    /**
     * Si el permiso está concedido (true) o denegado (false)
     */
    #[ORM\Column(type: 'boolean')]
    private bool $granted = true;

    /**
     * Categoría de mantenedor (opcional, para Fase 2)
     * Valores: null (todos), 'basic', 'clinical', 'commercial', 'hospital', 'human'
     */
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $category = null;

    /**
     * Mantenedor específico (opcional, para Fase 3)
     * Valor: null (todos), o nombre de entidad específica (ej: 'Gender', 'Disease')
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $maintainer = null;

    /**
     * Descripción del permiso (para UI del mantenedor)
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $description = null;

    /**
     * Prioridad para resolver conflictos (mayor = más prioritario)
     * Útil cuando un usuario tiene múltiples roles
     */
    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $priority = 0;

    /**
     * Si está activo o no
     */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    /**
     * Fecha de creación
     */
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    /**
     * Fecha de última actualización
     */
    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // ===== GETTERS & SETTERS =====

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getPermission(): string
    {
        return $this->permission;
    }

    public function setPermission(string $permission): self
    {
        $this->permission = $permission;
        return $this;
    }

    public function isGranted(): bool
    {
        return $this->granted;
    }

    public function setGranted(bool $granted): self
    {
        $this->granted = $granted;
        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function getMaintainer(): ?string
    {
        return $this->maintainer;
    }

    public function setMaintainer(?string $maintainer): self
    {
        $this->maintainer = $maintainer;
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

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Hook que se ejecuta antes de actualizar
     */
    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Verifica si este permiso aplica al contexto dado
     * 
     * @param string|null $category Categoría del mantenedor
     * @param string|null $maintainer Nombre del mantenedor específico
     * @return bool True si este permiso aplica al contexto
     */
    public function appliesTo(?string $category = null, ?string $maintainer = null): bool
    {
        // Si no está activo, no aplica
        if (!$this->isActive) {
            return false;
        }

        // Si tiene mantenedor específico, debe coincidir
        if ($this->maintainer !== null && $this->maintainer !== $maintainer) {
            return false;
        }

        // Si tiene categoría específica, debe coincidir
        if ($this->category !== null && $this->category !== $category) {
            return false;
        }

        // Si pasó todos los filtros, aplica
        return true;
    }

    /**
     * Verifica si es un permiso wildcard (*)
     */
    public function isWildcard(): bool
    {
        return $this->permission === '*';
    }
}
