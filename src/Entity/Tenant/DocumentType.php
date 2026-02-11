<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\DocumentTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DocumentTypeRepository::class)]
#[ORM\Table(name: 'document_type')]
#[ORM\HasLifecycleCallbacks]
class DocumentType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    private ?string $siiCode = null;

    #[ORM\Column(type: 'string', length: 70)]
    #[Assert\NotBlank(message: 'El nombre no puede estar vacío')]
    #[Assert\Length(
        max: 70,
        maxMessage: 'El nombre no puede tener más de {{ limit }} caracteres'
    )]
    private ?string $name = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isDte = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isLogistics = false;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $idEstado = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSiiCode(): ?string
    {
        return $this->siiCode;
    }

    public function setSiiCode(?string $siiCode): self
    {
        $this->siiCode = $siiCode;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function isIsDte(): bool
    {
        return $this->isDte;
    }

    public function setIsDte(bool $isDte): self
    {
        $this->isDte = $isDte;
        return $this;
    }

    public function isIsLogistics(): bool
    {
        return $this->isLogistics;
    }

    public function setIsLogistics(bool $isLogistics): self
    {
        $this->isLogistics = $isLogistics;
        return $this;
    }

    public function isIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getIdEstado(): ?int
    {
        return $this->idEstado;
    }

    public function setIdEstado(?int $idEstado): self
    {
        $this->idEstado = $idEstado;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
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

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}
