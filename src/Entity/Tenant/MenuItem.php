<?php

namespace App\Entity\Tenant;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * MenuItem (ItemMenu)
 *
 * Tabla legacy: item_menu
 *
 * Representa un ítem del menú de navegación configurable por tenant, almacenado en base de datos para configuración dinámica.
 */
#[ORM\Entity(repositoryClass: 'App\Repository\Tenant\MenuItemRepository')]
#[ORM\Table(name: 'menu_items')]
class MenuItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private string $name;

    #[ORM\Column(type: 'string', length: 100)]
    private string $label;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $route = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $module = null;

    #[ORM\ManyToOne(targetEntity: MenuItem::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?MenuItem $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: MenuItem::class, cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $children;

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $enabled = true;

    #[ORM\Column(type: 'boolean')]
    private bool $visibleInSidebar = true;

    #[ORM\Column(type: 'boolean')]
    private bool $requiresAuth = true;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $requiredRoles = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    // Getters y Setters

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

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(?string $route): self
    {
        $this->route = $route;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function getModule(): ?string
    {
        return $this->module;
    }

    public function setModule(?string $module): self
    {
        $this->module = $module;
        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }
        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->removeElement($child)) {
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }
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

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function isVisibleInSidebar(): bool
    {
        return $this->visibleInSidebar;
    }

    public function setVisibleInSidebar(bool $visibleInSidebar): self
    {
        $this->visibleInSidebar = $visibleInSidebar;
        return $this;
    }

    public function isRequiresAuth(): bool
    {
        return $this->requiresAuth;
    }

    public function setRequiresAuth(bool $requiresAuth): self
    {
        $this->requiresAuth = $requiresAuth;
        return $this;
    }

    public function getRequiredRoles(): ?array
    {
        return $this->requiredRoles;
    }

    public function setRequiredRoles(?array $requiredRoles): self
    {
        $this->requiredRoles = $requiredRoles;
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

    /**
     * Convierte el MenuItem a array para usar en MenuDefinition
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'route' => $this->route,
            'icon' => $this->icon,
            'module' => $this->module,
            'children' => $this->children->map(fn(MenuItem $child) => $child->toArray())->toArray(),
        ];
    }
}
