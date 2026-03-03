<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\FeeCodePriceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * FeeCodePrice
 *
 * Precio vigente por código de guarismo y relación sucursal-financiador.
 */
#[ORM\Entity(repositoryClass: FeeCodePriceRepository::class)]
#[ORM\Table(name: 'fee_code_price')]
#[ORM\HasLifecycleCallbacks]
class FeeCodePrice
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'effective_date', type: 'datetime')]
    private ?\DateTimeInterface $effectiveDate = null;

    #[ORM\Column(name: 'modified_at', type: 'datetime')]
    private ?\DateTimeInterface $modifiedAt = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $amount = null;

    #[ORM\ManyToOne(targetEntity: FeeCode::class)]
    #[ORM\JoinColumn(name: 'fee_code_id', referencedColumnName: 'id', nullable: false)]
    private ?FeeCode $feeCode = null;

    #[ORM\ManyToOne(targetEntity: BranchPayer::class)]
    #[ORM\JoinColumn(name: 'branch_payer_id', referencedColumnName: 'id', nullable: false)]
    private ?BranchPayer $branchPayer = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'modified_by_id', referencedColumnName: 'id', nullable: true)]
    private ?Member $modifiedBy = null;

    public function __construct()
    {
        $this->modifiedAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setModifiedAtValue(): void
    {
        $this->modifiedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEffectiveDate(): ?\DateTimeInterface
    {
        return $this->effectiveDate;
    }

    public function setEffectiveDate(\DateTimeInterface $effectiveDate): self
    {
        $this->effectiveDate = $effectiveDate;
        return $this;
    }

    public function getModifiedAt(): ?\DateTimeInterface
    {
        return $this->modifiedAt;
    }

    public function setModifiedAt(\DateTimeInterface $modifiedAt): self
    {
        $this->modifiedAt = $modifiedAt;
        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getFeeCode(): ?FeeCode
    {
        return $this->feeCode;
    }

    public function setFeeCode(?FeeCode $feeCode): self
    {
        $this->feeCode = $feeCode;
        return $this;
    }

    public function getBranchPayer(): ?BranchPayer
    {
        return $this->branchPayer;
    }

    public function setBranchPayer(?BranchPayer $branchPayer): self
    {
        $this->branchPayer = $branchPayer;
        return $this;
    }

    public function getModifiedBy(): ?Member
    {
        return $this->modifiedBy;
    }

    public function setModifiedBy(?Member $modifiedBy): self
    {
        $this->modifiedBy = $modifiedBy;
        return $this;
    }
}
