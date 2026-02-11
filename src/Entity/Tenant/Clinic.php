<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\ClinicRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Clinic
 * 
 * Legacy table: clinica
 * Spanish name: Clínica
 * 
 * Represents clinic locations or facilities:
 * - Main hospital
 * - Branch clinics
 * - Satellite locations
 * - Medical centers
 */
#[ORM\Entity(repositoryClass: ClinicRepository::class)]
#[ORM\Table(name: 'clinic')]
class Clinic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $code = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $shortName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $rut = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $address = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $region = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $director = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $totalBeds = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $hasEmergency = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $hasICU = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private bool $isMainFacility = false;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    private bool $isActive = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
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

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(?string $shortName): static
    {
        $this->shortName = $shortName;
        return $this;
    }

    public function getRut(): ?string
    {
        return $this->rut;
    }

    public function setRut(?string $rut): static
    {
        $this->rut = $rut;
        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(?string $region): static
    {
        $this->region = $region;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getDirector(): ?string
    {
        return $this->director;
    }

    public function setDirector(?string $director): static
    {
        $this->director = $director;
        return $this;
    }

    public function getTotalBeds(): ?int
    {
        return $this->totalBeds;
    }

    public function setTotalBeds(?int $totalBeds): static
    {
        $this->totalBeds = $totalBeds;
        return $this;
    }

    public function isHasEmergency(): bool
    {
        return $this->hasEmergency;
    }

    public function setHasEmergency(bool $hasEmergency): static
    {
        $this->hasEmergency = $hasEmergency;
        return $this;
    }

    public function isHasICU(): bool
    {
        return $this->hasICU;
    }

    public function setHasICU(bool $hasICU): static
    {
        $this->hasICU = $hasICU;
        return $this;
    }

    public function isMainFacility(): bool
    {
        return $this->isMainFacility;
    }

    public function setIsMainFacility(bool $isMainFacility): static
    {
        $this->isMainFacility = $isMainFacility;
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

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
