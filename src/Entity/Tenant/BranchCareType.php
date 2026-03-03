<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\BranchCareTypeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * BranchCareType
 *
 * Relación entre sucursal y tipo de atención habilitado.
 */
#[ORM\Entity(repositoryClass: BranchCareTypeRepository::class)]
#[ORM\Table(name: 'branch_care_type')]
class BranchCareType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Branch::class)]
    #[ORM\JoinColumn(name: 'branch_id', referencedColumnName: 'id', nullable: false)]
    private ?Branch $branch = null;

    #[ORM\ManyToOne(targetEntity: CareType::class)]
    #[ORM\JoinColumn(name: 'care_type_id', referencedColumnName: 'id', nullable: false)]
    private ?CareType $careType = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getBranch(): ?Branch
    {
        return $this->branch;
    }

    public function setBranch(?Branch $branch): self
    {
        $this->branch = $branch;
        return $this;
    }

    public function getCareType(): ?CareType
    {
        return $this->careType;
    }

    public function setCareType(?CareType $careType): self
    {
        $this->careType = $careType;
        return $this;
    }
}
