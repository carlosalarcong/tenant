<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\PayerTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * PayerType (HL7 standard)
 * 
 * Legacy table: tipo_prevision
 * Spanish name: Tipo Prestador / Tipo Previsión / Tipo Financiador
 * 
 * Represents the types of healthcare payers in the system:
 * - FONASA
 * - ISAPRE
 * - Private
 * - Insurance companies
 * - Other agreements
 */
#[ORM\Entity(repositoryClass: PayerTypeRepository::class)]
#[ORM\Table(name: 'payer_type')]
class PayerType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isAgreement = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isDefault = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, Payer>
     */
    #[ORM\OneToMany(targetEntity: Payer::class, mappedBy: 'payerType')]
    private Collection $payers;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->payers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): static
    {
        $this->code = $code;
        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function isAgreement(): bool
    {
        return $this->isAgreement;
    }

    public function setIsAgreement(bool $isAgreement): static
    {
        $this->isAgreement = $isAgreement;
        return $this;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): static
    {
        $this->isDefault = $isDefault;
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

    /**
     * @return Collection<int, Payer>
     */
    public function getPayers(): Collection
    {
        return $this->payers;
    }

    public function addPayer(Payer $payer): static
    {
        if (!$this->payers->contains($payer)) {
            $this->payers->add($payer);
            $payer->setPayerType($this);
        }
        return $this;
    }

    public function removePayer(Payer $payer): static
    {
        if ($this->payers->removeElement($payer)) {
            if ($payer->getPayerType() === $this) {
                $payer->setPayerType(null);
            }
        }
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
