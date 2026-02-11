<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\ClinicalActionAnswerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ClinicalActionAnswer
 * 
 * Respuestas de preguntas de acciones clínicas
 */
#[ORM\Entity(repositoryClass: ClinicalActionAnswerRepository::class)]
#[ORM\Table(name: 'clinical_action_answer')]
class ClinicalActionAnswer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'sort_order', type: 'integer')]
    private int $sortOrder = 0;

    #[ORM\Column(name: 'pre_text', type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $preText = null;

    #[ORM\Column(name: 'post_text', type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $postText = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $placeholder = null;

    #[ORM\Column(name: 'default_value', type: 'string', length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $defaultValue = null;

    #[ORM\Column(name: 'entity_response', type: 'string', length: 100, nullable: true)]
    #[Assert\Length(max: 100)]
    private ?string $entityResponse = null;

    #[ORM\Column(name: 'is_checked', type: 'boolean')]
    private bool $isChecked = false;

    #[ORM\ManyToOne(targetEntity: ClinicalActionQuestion::class)]
    #[ORM\JoinColumn(name: 'clinical_action_question_id', nullable: false)]
    #[Assert\NotNull(message: 'Clinical action question is required')]
    private ClinicalActionQuestion $clinicalActionQuestion;

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

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;
        return $this;
    }

    public function getPreText(): ?string
    {
        return $this->preText;
    }

    public function setPreText(?string $preText): self
    {
        $this->preText = $preText;
        return $this;
    }

    public function getPostText(): ?string
    {
        return $this->postText;
    }

    public function setPostText(?string $postText): self
    {
        $this->postText = $postText;
        return $this;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function setPlaceholder(?string $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function getDefaultValue(): ?string
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(?string $defaultValue): self
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function getEntityResponse(): ?string
    {
        return $this->entityResponse;
    }

    public function setEntityResponse(?string $entityResponse): self
    {
        $this->entityResponse = $entityResponse;
        return $this;
    }

    public function isChecked(): bool
    {
        return $this->isChecked;
    }

    public function setIsChecked(bool $isChecked): self
    {
        $this->isChecked = $isChecked;
        return $this;
    }

    public function getClinicalActionQuestion(): ClinicalActionQuestion
    {
        return $this->clinicalActionQuestion;
    }

    public function setClinicalActionQuestion(ClinicalActionQuestion $clinicalActionQuestion): self
    {
        $this->clinicalActionQuestion = $clinicalActionQuestion;
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
        return (string) $this->id;
    }
}
