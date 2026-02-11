<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\PaymentMethodRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PaymentMethodRepository::class)]
#[ORM\Table(name: 'payment_method')]
#[ORM\HasLifecycleCallbacks]
class PaymentMethod
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(message: 'El nombre no puede estar vacío')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'El nombre no puede tener más de {{ limit }} caracteres'
    )]
    private ?string $name = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true)]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    private Collection $children;

    #[ORM\ManyToOne(targetEntity: PaymentMethodType::class)]
    #[ORM\JoinColumn(name: 'payment_method_type_id', referencedColumnName: 'id', nullable: true)]
    private ?PaymentMethodType $paymentMethodType = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $issuesReceipt = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isGuarantee = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isProfessionalPayment = false;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isWebPayment = false;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $documentTypeCode = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $accountingCode = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $visibleInCashRegister = true;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $creditCardPayment = false;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $idEstado = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->children = new ArrayCollection();
    }

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;
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

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children->add($child);
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getPaymentMethodType(): ?PaymentMethodType
    {
        return $this->paymentMethodType;
    }

    public function setPaymentMethodType(?PaymentMethodType $paymentMethodType): self
    {
        $this->paymentMethodType = $paymentMethodType;
        return $this;
    }

    public function isIssuesReceipt(): bool
    {
        return $this->issuesReceipt;
    }

    public function setIssuesReceipt(bool $issuesReceipt): self
    {
        $this->issuesReceipt = $issuesReceipt;
        return $this;
    }

    public function isIsGuarantee(): bool
    {
        return $this->isGuarantee;
    }

    public function setIsGuarantee(bool $isGuarantee): self
    {
        $this->isGuarantee = $isGuarantee;
        return $this;
    }

    public function isIsProfessionalPayment(): bool
    {
        return $this->isProfessionalPayment;
    }

    public function setIsProfessionalPayment(bool $isProfessionalPayment): self
    {
        $this->isProfessionalPayment = $isProfessionalPayment;
        return $this;
    }

    public function isIsWebPayment(): bool
    {
        return $this->isWebPayment;
    }

    public function setIsWebPayment(bool $isWebPayment): self
    {
        $this->isWebPayment = $isWebPayment;
        return $this;
    }

    public function getDocumentTypeCode(): ?string
    {
        return $this->documentTypeCode;
    }

    public function setDocumentTypeCode(?string $documentTypeCode): self
    {
        $this->documentTypeCode = $documentTypeCode;
        return $this;
    }

    public function getAccountingCode(): ?string
    {
        return $this->accountingCode;
    }

    public function setAccountingCode(?string $accountingCode): self
    {
        $this->accountingCode = $accountingCode;
        return $this;
    }

    public function isVisibleInCashRegister(): bool
    {
        return $this->visibleInCashRegister;
    }

    public function setVisibleInCashRegister(bool $visibleInCashRegister): self
    {
        $this->visibleInCashRegister = $visibleInCashRegister;
        return $this;
    }

    public function isCreditCardPayment(): bool
    {
        return $this->creditCardPayment;
    }

    public function setCreditCardPayment(bool $creditCardPayment): self
    {
        $this->creditCardPayment = $creditCardPayment;
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
