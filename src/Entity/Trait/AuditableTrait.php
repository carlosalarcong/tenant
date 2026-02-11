<?php

namespace App\Entity\Trait;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trait AuditableTrait
 * 
 * Proporciona funcionalidad de auditoría completa para entidades.
 * Registra quién y cuándo creó/modificó/eliminó cada registro.
 * 
 * Uso:
 *   use AuditableTrait;
 *   use SoftDeletableTrait; // opcional, para deletedBy
 *   
 * Campos agregados:
 *   - createdAt: DateTimeInterface - Fecha de creación (requerido)
 *   - createdBy: ?int - ID del usuario que creó (opcional)
 *   - updatedAt: ?DateTimeInterface - Fecha de última modificación
 *   - updatedBy: ?int - ID del usuario que modificó
 * 
 * NOTA: Los campos *By son INT sin FK porque Member está en tenant DB.
 * El listener AuditTrailListener se encarga de llenar estos campos automáticamente.
 */
trait AuditableTrait
{
    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $createdBy = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $updatedBy = null;

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?int $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
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

    public function getUpdatedBy(): ?int
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?int $updatedBy): self
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }

    /**
     * Marca cuándo y quién creó el registro
     * Este método es llamado automáticamente por AuditTrailListener
     * 
     * @param object|int|null $createdBy Usuario (objeto con getId()) o ID directo
     */
    public function markCreated(object|int|null $createdBy = null): self
    {
        if (!isset($this->createdAt)) {
            $this->createdAt = new \DateTime();
        }
        $this->createdBy = is_object($createdBy) ? $createdBy->getId() : $createdBy;
        return $this;
    }

    /**
     * Marca cuándo y quién modificó el registro
     * Este método es llamado automáticamente por AuditTrailListener
     * 
     * @param object|int|null $updatedBy Usuario (objeto con getId()) o ID directo
     */
    public function markUpdated(object|int|null $updatedBy = null): self
    {
        $this->updatedAt = new \DateTime();
        $this->updatedBy = is_object($updatedBy) ? $updatedBy->getId() : $updatedBy;
        return $this;
    }
}
