<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\PathologyArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * PathologyArticle
 * 
 * Legacy table: articulo_por_patologia
 * Spanish name: Artículo por Patología
 * 
 * Represents articles/supplies associated with specific pathologies:
 * - Medical supplies required for GES pathologies
 * - Treatment materials per pathology
 * - Medication protocols
 * - Resource allocation per pathology
 */
#[ORM\Entity(repositoryClass: PathologyArticleRepository::class)]
#[ORM\Table(name: 'pathology_article')]
class PathologyArticle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GESPathology::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?GESPathology $gesPathology = null;

    #[ORM\Column(length: 200)]
    private ?string $articleName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $articleCode = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 1])]
    private int $quantity = 1;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $unitOfMeasure = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $unitCost = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isMandatory = true;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGesPathology(): ?GESPathology
    {
        return $this->gesPathology;
    }

    public function setGesPathology(?GESPathology $gesPathology): static
    {
        $this->gesPathology = $gesPathology;
        return $this;
    }

    public function getArticleName(): ?string
    {
        return $this->articleName;
    }

    public function setArticleName(string $articleName): static
    {
        $this->articleName = $articleName;
        return $this;
    }

    public function getArticleCode(): ?string
    {
        return $this->articleCode;
    }

    public function setArticleCode(?string $articleCode): static
    {
        $this->articleCode = $articleCode;
        return $this;
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

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getUnitOfMeasure(): ?string
    {
        return $this->unitOfMeasure;
    }

    public function setUnitOfMeasure(?string $unitOfMeasure): static
    {
        $this->unitOfMeasure = $unitOfMeasure;
        return $this;
    }

    public function getUnitCost(): ?string
    {
        return $this->unitCost;
    }

    public function setUnitCost(?string $unitCost): static
    {
        $this->unitCost = $unitCost;
        return $this;
    }

    public function isMandatory(): bool
    {
        return $this->isMandatory;
    }

    public function setIsMandatory(bool $isMandatory): static
    {
        $this->isMandatory = $isMandatory;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s - %s',
            $this->gesPathology?->getName() ?? '',
            $this->articleName ?? ''
        );
    }
}
