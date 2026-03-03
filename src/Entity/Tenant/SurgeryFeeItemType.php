<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\SurgeryFeeItemTypeRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * SurgeryFeeItemType
 *
 * Tipo fijo legacy para ítems de guarismo quirúrgico.
 */
#[ORM\Entity(repositoryClass: SurgeryFeeItemTypeRepository::class)]
#[ORM\Table(name: 'surgery_fee_item_type')]
class SurgeryFeeItemType
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $name = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }
}
