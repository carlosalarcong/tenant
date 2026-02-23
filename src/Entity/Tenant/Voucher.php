<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\VoucherRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Voucher (Talonario)
 *
 * Tabla legacy: talonario
 *
 * Stock de folios/boletas asignado a una ubicación de caja.
 * Cada pago consume un folio del talonario activo mediante VoucherEntry.
 * Un talonario tiene un rango de folios (desde–hasta) y un folio actual.
 */
#[ORM\Entity(repositoryClass: VoucherRepository::class)]
#[ORM\Table(name: 'voucher')]
class Voucher
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * Ubicación de caja a la que pertenece este talonario.
     * Legacy: ID_CAJA → UbicacionCaja
     */
    #[ORM\ManyToOne(targetEntity: CashRegisterLocation::class)]
    #[ORM\JoinColumn(name: 'cash_register_location_id', referencedColumnName: 'id', nullable: false)]
    private ?CashRegisterLocation $cashRegisterLocation = null;

    /**
     * Sub-empresa emisora del talonario (para estructuras multi-empresa).
     * Legacy: ID_SUB_EMPRESA → SubEmpresa
     */
    #[ORM\ManyToOne(targetEntity: SubCompany::class)]
    #[ORM\JoinColumn(name: 'sub_company_id', referencedColumnName: 'id', nullable: true)]
    private ?SubCompany $subCompany = null;

    /** Folio inicial del rango. Legacy: FOLIO_DESDE */
    #[ORM\Column(type: 'integer')]
    private int $folioFrom = 0;

    /** Folio final del rango. Legacy: FOLIO_HASTA */
    #[ORM\Column(type: 'integer')]
    private int $folioTo = 0;

    /** Folio actual (próximo a usar). Legacy: FOLIO_ACTUAL */
    #[ORM\Column(type: 'integer')]
    private int $currentFolio = 0;

    /** Indica si el talonario está activo y disponible para uso. Legacy: ES_ACTIVO */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $isActive = true;

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

    public function getCashRegisterLocation(): ?CashRegisterLocation
    {
        return $this->cashRegisterLocation;
    }

    public function setCashRegisterLocation(?CashRegisterLocation $cashRegisterLocation): self
    {
        $this->cashRegisterLocation = $cashRegisterLocation;
        return $this;
    }

    public function getSubCompany(): ?SubCompany
    {
        return $this->subCompany;
    }

    public function setSubCompany(?SubCompany $subCompany): self
    {
        $this->subCompany = $subCompany;
        return $this;
    }

    public function getFolioFrom(): int
    {
        return $this->folioFrom;
    }

    public function setFolioFrom(int $folioFrom): self
    {
        $this->folioFrom = $folioFrom;
        return $this;
    }

    public function getFolioTo(): int
    {
        return $this->folioTo;
    }

    public function setFolioTo(int $folioTo): self
    {
        $this->folioTo = $folioTo;
        return $this;
    }

    public function getCurrentFolio(): int
    {
        return $this->currentFolio;
    }

    public function setCurrentFolio(int $currentFolio): self
    {
        $this->currentFolio = $currentFolio;
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

    public function getCreatedAt(): \DateTimeInterface
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

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /** Retorna true si aún quedan folios disponibles. */
    public function hasAvailableFolios(): bool
    {
        return $this->isActive && $this->currentFolio <= $this->folioTo;
    }
}
