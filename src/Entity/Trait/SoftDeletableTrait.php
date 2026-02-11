<?php

namespace App\Entity\Trait;

use App\Entity\Tenant\Member;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trait SoftDeletableTrait
 * 
 * Proporciona funcionalidad de soft delete para entidades.
 * En lugar de eliminar físicamente registros, se marca con deletedAt y deletedBy.
 * 
 * Uso:
 *   use SoftDeletableTrait;
 *   
 * Campos agregados:
 *   - deletedAt: ?DateTimeInterface - Fecha de eliminación lógica
 *   - deletedBy: ?User - Usuario que eliminó el registro
 */
trait SoftDeletableTrait
{
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $deletedById = null;

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function getDeletedById(): ?int
    {
        return $this->deletedById;
    }

    public function setDeletedById(?int $deletedById): self
    {
        $this->deletedById = $deletedById;
        return $this;
    }

    /**
     * Marca el registro como eliminado (soft delete)
     * @param object|null $deletedBy Usuario que elimina (debe tener método getId())
     */
    public function softDelete(?object $deletedBy = null): self
    {
        $this->deletedAt = new \DateTime();
        $this->deletedById = $deletedBy?->getId();
        return $this;
    }

    /**
     * Restaura un registro eliminado
     */
    public function restore(): self
    {
        $this->deletedAt = null;
        $this->deletedById = null;
        return $this;
    }

    /**
     * Verifica si el registro está eliminado
     */
    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }
}
