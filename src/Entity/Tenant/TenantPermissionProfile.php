<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\TenantPermissionProfileRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * TenantPermissionProfile (PerfilPermisoTenant)
 *
 * Tabla legacy: (nueva entidad)
 *
 * Perfil de permisos del tenant.
 * 
 * Define el tipo de estrategia de permisos que usa el tenant:
 * - 'collaborative': Múltiples roles pueden acceder (clínicas grandes)
 * - 'restrictive': Solo administradores (clínicas pequeñas)
 * - 'custom': Configuración personalizada por módulo
 */
#[ORM\Entity(repositoryClass: TenantPermissionProfileRepository::class)]
#[ORM\Table(name: 'tenant_permission_profile')]
class TenantPermissionProfile
{
    public const PROFILE_COLLABORATIVE = 'collaborative';
    public const PROFILE_RESTRICTIVE = 'restrictive';
    public const PROFILE_CUSTOM = 'custom';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $profileType;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->profileType = self::PROFILE_COLLABORATIVE; // Default
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProfileType(): string
    {
        return $this->profileType;
    }

    public function setProfileType(string $profileType): self
    {
        if (!in_array($profileType, [self::PROFILE_COLLABORATIVE, self::PROFILE_RESTRICTIVE, self::PROFILE_CUSTOM])) {
            throw new \InvalidArgumentException("Invalid profile type: {$profileType}");
        }
        
        $this->profileType = $profileType;
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

    public function isCollaborative(): bool
    {
        return $this->profileType === self::PROFILE_COLLABORATIVE;
    }

    public function isRestrictive(): bool
    {
        return $this->profileType === self::PROFILE_RESTRICTIVE;
    }

    public function isCustom(): bool
    {
        return $this->profileType === self::PROFILE_CUSTOM;
    }
}
