<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\GroupPermissionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * GroupPermission (PermisoGrupo)
 *
 * Tabla legacy: (nueva entidad)
 *
 * Permisos asignados a grupos de miembros para el control de acceso.
 */
#[ORM\Entity(repositoryClass: GroupPermissionRepository::class)]
#[ORM\Table(name: 'group_permission')]
#[ORM\Index(columns: ['domain'], name: 'idx_group_permission_domain')]
#[ORM\Index(columns: ['domain', 'resource_id'], name: 'idx_group_permission_resource')]
class GroupPermission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: MemberGroup::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?MemberGroup $group = null;

    #[ORM\Column(length: 100)]
    private string $domain;

    #[ORM\Column(nullable: true)]
    private ?int $resourceId = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $fieldName = null;

    #[ORM\Column(type: 'boolean')]
    private bool $canView = false;

    #[ORM\Column(type: 'boolean')]
    private bool $canEdit = false;

    #[ORM\Column(type: 'boolean')]
    private bool $canDelete = false;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $constraints = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    private ?Member $createdBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGroup(): ?MemberGroup
    {
        return $this->group;
    }

    public function setGroup(MemberGroup $group): self
    {
        $this->group = $group;
        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    public function getResourceId(): ?int
    {
        return $this->resourceId;
    }

    public function setResourceId(?int $resourceId): self
    {
        $this->resourceId = $resourceId;
        return $this;
    }

    public function getFieldName(): ?string
    {
        return $this->fieldName;
    }

    public function setFieldName(?string $fieldName): self
    {
        $this->fieldName = $fieldName;
        return $this;
    }

    public function canView(): bool
    {
        return $this->canView;
    }

    public function setCanView(bool $canView): self
    {
        $this->canView = $canView;
        return $this;
    }

    public function canEdit(): bool
    {
        return $this->canEdit;
    }

    public function setCanEdit(bool $canEdit): self
    {
        $this->canEdit = $canEdit;
        return $this;
    }

    public function canDelete(): bool
    {
        return $this->canDelete;
    }

    public function setCanDelete(bool $canDelete): self
    {
        $this->canDelete = $canDelete;
        return $this;
    }

    public function getConstraints(): ?array
    {
        return $this->constraints;
    }

    public function setConstraints(?array $constraints): self
    {
        $this->constraints = $constraints;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getCreatedBy(): ?Member
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Member $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }
}
