<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\ClinicalActionQuestionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ClinicalActionQuestion
 * 
 * Preguntas de acciones clínicas relacionadas con categorías de acciones clínicas
 */
#[ORM\Entity(repositoryClass: ClinicalActionQuestionRepository::class)]
#[ORM\Table(name: 'clinical_action_question')]
class ClinicalActionQuestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    #[Assert\NotBlank(message: 'Name is required')]
    #[Assert\Length(max: 100)]
    private string $name;

    #[ORM\Column(name: 'sort_order', type: 'integer')]
    private int $sortOrder = 0;

    #[ORM\Column(name: 'range_min', type: 'integer')]
    private int $rangeMin = 0;

    #[ORM\Column(name: 'range_max', type: 'integer', nullable: true)]
    private ?int $rangeMax = null;

    #[ORM\Column(name: 'age_min', type: 'integer', nullable: true)]
    private ?int $ageMin = null;

    #[ORM\Column(name: 'age_max', type: 'integer', nullable: true)]
    private ?int $ageMax = null;

    #[ORM\Column(name: 'help_text', type: 'string', length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $helpText = null;

    #[ORM\Column(name: 'is_multiple', type: 'boolean')]
    private bool $isMultiple = false;

    #[ORM\Column(name: 'is_extended', type: 'boolean')]
    private bool $isExtended = false;

    #[ORM\Column(name: 'is_required', type: 'boolean')]
    private bool $isRequired = false;

    #[ORM\Column(name: 'field_type', type: 'string', length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $fieldType = null;

    #[ORM\ManyToOne(targetEntity: ClinicalActionCategory::class)]
    #[ORM\JoinColumn(name: 'clinical_action_category_id', nullable: true)]
    private ?ClinicalActionCategory $clinicalActionCategory = null;

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getRangeMin(): int
    {
        return $this->rangeMin;
    }

    public function setRangeMin(int $rangeMin): self
    {
        $this->rangeMin = $rangeMin;
        return $this;
    }

    public function getRangeMax(): ?int
    {
        return $this->rangeMax;
    }

    public function setRangeMax(?int $rangeMax): self
    {
        $this->rangeMax = $rangeMax;
        return $this;
    }

    public function getAgeMin(): ?int
    {
        return $this->ageMin;
    }

    public function setAgeMin(?int $ageMin): self
    {
        $this->ageMin = $ageMin;
        return $this;
    }

    public function getAgeMax(): ?int
    {
        return $this->ageMax;
    }

    public function setAgeMax(?int $ageMax): self
    {
        $this->ageMax = $ageMax;
        return $this;
    }

    public function getHelpText(): ?string
    {
        return $this->helpText;
    }

    public function setHelpText(?string $helpText): self
    {
        $this->helpText = $helpText;
        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->isMultiple;
    }

    public function setIsMultiple(bool $isMultiple): self
    {
        $this->isMultiple = $isMultiple;
        return $this;
    }

    public function isExtended(): bool
    {
        return $this->isExtended;
    }

    public function setIsExtended(bool $isExtended): self
    {
        $this->isExtended = $isExtended;
        return $this;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): self
    {
        $this->isRequired = $isRequired;
        return $this;
    }

    public function getFieldType(): ?string
    {
        return $this->fieldType;
    }

    public function setFieldType(?string $fieldType): self
    {
        $this->fieldType = $fieldType;
        return $this;
    }

    public function getClinicalActionCategory(): ?ClinicalActionCategory
    {
        return $this->clinicalActionCategory;
    }

    public function setClinicalActionCategory(?ClinicalActionCategory $clinicalActionCategory): self
    {
        $this->clinicalActionCategory = $clinicalActionCategory;
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
