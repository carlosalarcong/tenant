<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\PermissionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Permission (Permiso)
 *
 * Tabla legacy: (nueva entidad)
 *
 * Representa un permiso individual asignado directamente a un usuario, con control granular de acceso a dominios, recursos y campos específicos.
 */
#[ORM\Entity(repositoryClass: PermissionRepository::class)]
#[ORM\Table(name: 'permission')]
#[ORM\Index(columns: ['user_id', 'domain'])]
#[ORM\Index(columns: ['domain', 'resource_id'])]
#[ORM\UniqueConstraint(name: 'unique_user_permission', columns: ['user_id', 'domain', 'resource_id', 'field_name'])]
class Permission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Member $user;

    #[ORM\Column(type: 'string', length: 100)]
    private string $domain;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $resourceId = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $fieldName = null;

    #[ORM\Column(type: 'boolean')]
    private bool $canView = false;

    #[ORM\Column(type: 'boolean')]
    private bool $canEdit = false;

    #[ORM\Column(type: 'boolean')]
    private bool $canDelete = false;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $constraints = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(nullable: true)]
    private ?Member $createdBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): Member
    {
        return $this->user;
    }

    public function setUser(Member $user): self
    {
        $this->user = $user;
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

    public function getCreatedAt(): \DateTimeImmutable
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
