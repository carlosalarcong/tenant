<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\SettingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
#[ORM\Table(name: '`setting`')]
class Setting
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 32)]
    private ?string $fieldFormat = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $fieldAvailableValue = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $fieldDefaultValue = null;

    #[ORM\Column(nullable: true)]
    private ?bool $fieldRequired = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $fieldValue = null;

    #[ORM\Column(nullable: true)]
    private ?array $specialAttributes = [];

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getFieldFormat(): ?string
    {
        return $this->fieldFormat;
    }

    public function setFieldFormat(string $fieldFormat): static
    {
        $this->fieldFormat = $fieldFormat;

        return $this;
    }

    public function getFieldAvailableValue(): ?string
    {
        return $this->fieldAvailableValue;
    }

    public function setFieldAvailableValue(?string $fieldAvailableValue): static
    {
        $this->fieldAvailableValue = $fieldAvailableValue;

        return $this;
    }

    /**
     * Set parent
     *
     * @param Setting $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Get parent
     * @return Setting
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children
     *
     * @param Setting $children
     */
    public function addSetting(Setting $children)
    {
        $this->children[] = $children;
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getFieldDefaultValue(): ?string
    {
        return $this->fieldDefaultValue;
    }

    public function setFieldDefaultValue(?string $fieldDefaultValue): static
    {
        $this->fieldDefaultValue = $fieldDefaultValue;

        return $this;
    }

    public function isFieldRequired(): ?bool
    {
        return $this->fieldRequired;
    }

    public function setFieldRequired(?bool $fieldRequired): static
    {
        $this->fieldRequired = $fieldRequired;

        return $this;
    }

    public function getFieldValue(): ?string
    {
        return $this->fieldValue;
    }

    public function setFieldValue(?string $fieldValue): static
    {
        $this->fieldValue = $fieldValue;

        return $this;
    }

    public function getSpecialAttributes(): ?array
    {
        return $this->specialAttributes;
    }

    public function setSpecialAttributes(?array $specialAttributes): static
    {
        $this->specialAttributes = $specialAttributes;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}