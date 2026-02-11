<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\ArticleWarehouseRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * ArticleWarehouse (ArticuloBodega)
 * 
 * Relación entre artículos y bodegas con gestión de stock
 */
#[ORM\Entity(repositoryClass: ArticleWarehouseRepository::class)]
#[ORM\Table(name: 'article_warehouse')]
class ArticleWarehouse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Article::class)]
    #[ORM\JoinColumn(name: 'article_id', nullable: false)]
    private Article $article;

    #[ORM\ManyToOne(targetEntity: Warehouse::class)]
    #[ORM\JoinColumn(name: 'warehouse_id', nullable: false)]
    private Warehouse $warehouse;

    #[ORM\Column(name: 'min_stock', type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $minStock = null;

    #[ORM\Column(name: 'critical_stock', type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $criticalStock = null;

    #[ORM\Column(name: 'optimal_stock', type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $optimalStock = null;

    #[ORM\Column(name: 'is_critical', type: 'boolean')]
    private bool $isCritical = false;

    #[ORM\Column(name: 'is_active', type: 'boolean')]
    private bool $isActive = true;

    #[ORM\Column(name: 'id_estado', type: 'integer')]
    private int $idEstado = 1;

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

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function setArticle(Article $article): self
    {
        $this->article = $article;
        return $this;
    }

    public function getWarehouse(): Warehouse
    {
        return $this->warehouse;
    }

    public function setWarehouse(Warehouse $warehouse): self
    {
        $this->warehouse = $warehouse;
        return $this;
    }

    public function getMinStock(): ?string
    {
        return $this->minStock;
    }

    public function setMinStock(?string $minStock): self
    {
        $this->minStock = $minStock;
        return $this;
    }

    public function getCriticalStock(): ?string
    {
        return $this->criticalStock;
    }

    public function setCriticalStock(?string $criticalStock): self
    {
        $this->criticalStock = $criticalStock;
        return $this;
    }

    public function getOptimalStock(): ?string
    {
        return $this->optimalStock;
    }

    public function setOptimalStock(?string $optimalStock): self
    {
        $this->optimalStock = $optimalStock;
        return $this;
    }

    public function isCritical(): bool
    {
        return $this->isCritical;
    }

    public function setIsCritical(bool $isCritical): self
    {
        $this->isCritical = $isCritical;
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

    public function getIdEstado(): int
    {
        return $this->idEstado;
    }

    public function setIdEstado(int $idEstado): self
    {
        $this->idEstado = $idEstado;
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

    public function __toString(): string
    {
        return ($this->article ? $this->article->getName() : '') . ' - ' . ($this->warehouse ? $this->warehouse->getName() : '');
    }
}
