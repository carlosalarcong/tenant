<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\TenantModulePermissionOverrideRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * TenantModulePermissionOverride (AnulacionPermisoModuloTenant)
 *
 * Tabla legacy: (nueva entidad)
 *
 * Override de permisos por módulo para configuraciones custom.
 * 
 * Permite definir roles específicos requeridos para acceder a cada módulo
 * cuando el tenant usa un perfil custom o necesita sobrescribir defaults.
 * 
 * Ejemplos:
 * - moduleName: 'patients', requiredRoles: ["ROLE_ADMIN", "ROLE_DOCTOR"]
 * - moduleName: 'appointments', requiredRoles: ["ROLE_ADMIN", "ROLE_SECRETARY"]
 */
#[ORM\Entity(repositoryClass: TenantModulePermissionOverrideRepository::class)]
#[ORM\Table(name: 'tenant_module_permission_override')]
#[ORM\UniqueConstraint(name: 'unique_module_permission', columns: ['module_name'])]
#[ORM\Index(columns: ['module_name'])]
class TenantModulePermissionOverride
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $moduleName;

    #[ORM\Column(type: 'json')]
    private array $requiredRoles = [];

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function setModuleName(string $moduleName): self
    {
        $this->moduleName = $moduleName;
        
        return $this;
    }

    public function getRequiredRoles(): array
    {
        return $this->requiredRoles;
    }

    public function setRequiredRoles(array $requiredRoles): self
    {
        $this->requiredRoles = $requiredRoles;
        $this->updatedAt = new \DateTimeImmutable();
        
        return $this;
    }

    public function addRequiredRole(string $role): self
    {
        if (!in_array($role, $this->requiredRoles)) {
            $this->requiredRoles[] = $role;
            $this->updatedAt = new \DateTimeImmutable();
        }
        
        return $this;
    }

    public function removeRequiredRole(string $role): self
    {
        $key = array_search($role, $this->requiredRoles);
        if ($key !== false) {
            unset($this->requiredRoles[$key]);
            $this->requiredRoles = array_values($this->requiredRoles); // Reindex
            $this->updatedAt = new \DateTimeImmutable();
        }
        
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

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        $this->updatedAt = new \DateTimeImmutable();
        
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
}
