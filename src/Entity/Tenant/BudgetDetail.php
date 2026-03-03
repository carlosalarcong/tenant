<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\BudgetDetailRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * BudgetDetail (PresupuestoDetalle)
 *
 * Tabla legacy: presupuesto_detalle
 *
 * Línea de detalle de un presupuesto médico.
 */
#[ORM\Entity(repositoryClass: BudgetDetailRepository::class)]
#[ORM\Table(name: 'budget_detail')]
class BudgetDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $quantity = 1;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $amount = null;

    #[ORM\Column(name: 'is_estimated', type: 'boolean', options: ['default' => false])]
    private bool $isEstimated = false;

    #[ORM\Column(name: 'item_type', type: 'string', length: 30)]
    private ?string $itemType = null;

    #[ORM\ManyToOne(targetEntity: Budget::class, inversedBy: 'details')]
    #[ORM\JoinColumn(name: 'budget_id', nullable: false)]
    private ?Budget $budget = null;

    #[ORM\ManyToOne(targetEntity: MedicalService::class)]
    #[ORM\JoinColumn(name: 'medical_service_id', nullable: true)]
    private ?MedicalService $medicalService = null;

    #[ORM\ManyToOne(targetEntity: SurgeryPackageItem::class)]
    #[ORM\JoinColumn(name: 'surgery_package_item_id', nullable: true)]
    private ?SurgeryPackageItem $surgeryPackageItem = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;
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

    public function isEstimated(): bool
    {
        return $this->isEstimated;
    }

    public function setIsEstimated(bool $isEstimated): self
    {
        $this->isEstimated = $isEstimated;
        return $this;
    }

    public function getItemType(): ?string
    {
        return $this->itemType;
    }

    public function setItemType(string $itemType): self
    {
        $this->itemType = $itemType;
        return $this;
    }

    public function getBudget(): ?Budget
    {
        return $this->budget;
    }

    public function setBudget(?Budget $budget): self
    {
        $this->budget = $budget;
        return $this;
    }

    public function getMedicalService(): ?MedicalService
    {
        return $this->medicalService;
    }

    public function setMedicalService(?MedicalService $medicalService): self
    {
        $this->medicalService = $medicalService;
        return $this;
    }

    public function getSurgeryPackageItem(): ?SurgeryPackageItem
    {
        return $this->surgeryPackageItem;
    }

    public function setSurgeryPackageItem(?SurgeryPackageItem $surgeryPackageItem): self
    {
        $this->surgeryPackageItem = $surgeryPackageItem;
        return $this;
    }
}
