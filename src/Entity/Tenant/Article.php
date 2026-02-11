<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\ArticleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Article (Articulo)
 * 
 * Entidad central de logística que representa artículos del inventario
 */
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\Table(name: 'article')]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $code;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 2000, nullable: true)]
    #[Assert\Length(max: 2000)]
    private ?string $description = null;

    #[ORM\Column(name: 'account_group_code', type: 'string', length: 255, nullable: true)]
    private ?string $accountGroupCode = null;

    #[ORM\Column(name: 'is_consignment', type: 'boolean')]
    private bool $isConsignment = false;

    #[ORM\Column(name: 'is_controlled', type: 'boolean')]
    private bool $isControlled = false;

    #[ORM\Column(name: 'has_expiration_date', type: 'boolean')]
    private bool $hasExpirationDate = false;

    #[ORM\Column(name: 'photo_name', type: 'string', length: 255, nullable: true)]
    private ?string $photoName = null;

    #[ORM\Column(name: 'min_stock', type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $minStock = null;

    #[ORM\Column(name: 'critical_stock', type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $criticalStock = null;

    #[ORM\Column(name: 'optimal_stock', type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $optimalStock = null;

    #[ORM\Column(name: 'max_stock', type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $maxStock = null;

    #[ORM\Column(name: 'is_critical', type: 'boolean')]
    private bool $isCritical = false;

    #[ORM\Column(name: 'is_generic', type: 'boolean')]
    private bool $isGeneric = false;

    #[ORM\Column(name: 'is_resterilizable', type: 'boolean')]
    private bool $isResterilizable = false;

    #[ORM\Column(name: 'is_for_sale', type: 'boolean')]
    private bool $isForSale = false;

    #[ORM\Column(name: 'is_billable', type: 'boolean')]
    private bool $isBillable = false;

    #[ORM\Column(name: 'is_first_aid_deduction', type: 'boolean')]
    private bool $isFirstAidDeduction = false;

    #[ORM\Column(name: 'generic_name', type: 'string', length: 100, nullable: true)]
    private ?string $genericName = null;

    #[ORM\Column(name: 'short_name', type: 'string', length: 100, nullable: true)]
    private ?string $shortName = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $margin = null;

    #[ORM\Column(name: 'icon_code', type: 'string', length: 255, nullable: true)]
    private ?string $iconCode = null;

    #[ORM\Column(name: 'cenabast_code', type: 'string', length: 255, nullable: true)]
    private ?string $cenabastCode = null;

    #[ORM\ManyToOne(targetEntity: ArticleType::class)]
    #[ORM\JoinColumn(name: 'article_type_id', nullable: true)]
    private ?ArticleType $articleType = null;

    #[ORM\ManyToOne(targetEntity: SubCompany::class)]
    #[ORM\JoinColumn(name: 'billing_sub_company_id', nullable: true)]
    private ?SubCompany $billingSubCompany = null;

    #[ORM\ManyToOne(targetEntity: SubCompany::class)]
    #[ORM\JoinColumn(name: 'sub_company_id', nullable: true)]
    private ?SubCompany $subCompany = null;

    #[ORM\ManyToOne(targetEntity: BudgetItem::class)]
    #[ORM\JoinColumn(name: 'budget_item_id', nullable: true)]
    private ?BudgetItem $budgetItem = null;

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

    public function getAccountGroupCode(): ?string
    {
        return $this->accountGroupCode;
    }

    public function setAccountGroupCode(?string $accountGroupCode): self
    {
        $this->accountGroupCode = $accountGroupCode;
        return $this;
    }

    public function isConsignment(): bool
    {
        return $this->isConsignment;
    }

    public function setIsConsignment(bool $isConsignment): self
    {
        $this->isConsignment = $isConsignment;
        return $this;
    }

    public function isControlled(): bool
    {
        return $this->isControlled;
    }

    public function setIsControlled(bool $isControlled): self
    {
        $this->isControlled = $isControlled;
        return $this;
    }

    public function hasExpirationDate(): bool
    {
        return $this->hasExpirationDate;
    }

    public function setHasExpirationDate(bool $hasExpirationDate): self
    {
        $this->hasExpirationDate = $hasExpirationDate;
        return $this;
    }

    public function getPhotoName(): ?string
    {
        return $this->photoName;
    }

    public function setPhotoName(?string $photoName): self
    {
        $this->photoName = $photoName;
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

    public function getMaxStock(): ?string
    {
        return $this->maxStock;
    }

    public function setMaxStock(?string $maxStock): self
    {
        $this->maxStock = $maxStock;
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

    public function isGeneric(): bool
    {
        return $this->isGeneric;
    }

    public function setIsGeneric(bool $isGeneric): self
    {
        $this->isGeneric = $isGeneric;
        return $this;
    }

    public function isResterilizable(): bool
    {
        return $this->isResterilizable;
    }

    public function setIsResterilizable(bool $isResterilizable): self
    {
        $this->isResterilizable = $isResterilizable;
        return $this;
    }

    public function isForSale(): bool
    {
        return $this->isForSale;
    }

    public function setIsForSale(bool $isForSale): self
    {
        $this->isForSale = $isForSale;
        return $this;
    }

    public function isBillable(): bool
    {
        return $this->isBillable;
    }

    public function setIsBillable(bool $isBillable): self
    {
        $this->isBillable = $isBillable;
        return $this;
    }

    public function isFirstAidDeduction(): bool
    {
        return $this->isFirstAidDeduction;
    }

    public function setIsFirstAidDeduction(bool $isFirstAidDeduction): self
    {
        $this->isFirstAidDeduction = $isFirstAidDeduction;
        return $this;
    }

    public function getGenericName(): ?string
    {
        return $this->genericName;
    }

    public function setGenericName(?string $genericName): self
    {
        $this->genericName = $genericName;
        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;
        return $this;
    }

    public function getMargin(): ?string
    {
        return $this->margin;
    }

    public function setMargin(?string $margin): self
    {
        $this->margin = $margin;
        return $this;
    }

    public function getIconCode(): ?string
    {
        return $this->iconCode;
    }

    public function setIconCode(?string $iconCode): self
    {
        $this->iconCode = $iconCode;
        return $this;
    }

    public function getCenabastCode(): ?string
    {
        return $this->cenabastCode;
    }

    public function setCenabastCode(?string $cenabastCode): self
    {
        $this->cenabastCode = $cenabastCode;
        return $this;
    }

    public function getArticleType(): ?ArticleType
    {
        return $this->articleType;
    }

    public function setArticleType(?ArticleType $articleType): self
    {
        $this->articleType = $articleType;
        return $this;
    }

    public function getBillingSubCompany(): ?SubCompany
    {
        return $this->billingSubCompany;
    }

    public function setBillingSubCompany(?SubCompany $billingSubCompany): self
    {
        $this->billingSubCompany = $billingSubCompany;
        return $this;
    }

    public function getSubCompany(): ?SubCompany
    {
        return $this->subCompany;
    }

    public function setSubCompany(?SubCompany $subCompany): self
    {
        $this->subCompany = $subCompany;
        return $this;
    }

    public function getBudgetItem(): ?BudgetItem
    {
        return $this->budgetItem;
    }

    public function setBudgetItem(?BudgetItem $budgetItem): self
    {
        $this->budgetItem = $budgetItem;
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
        return $this->name;
    }
}
