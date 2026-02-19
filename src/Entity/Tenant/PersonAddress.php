<?php

namespace App\Entity\Tenant;

//use App\Repository\PersonAddressRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * PersonAddress (DireccionPersona)
 *
 * Tabla legacy: direccion_persona
 *
 * Dirección domiciliaria de una persona, con calle, número, detalles, comuna y país.
 */
#[ORM\Entity()]
#[ORM\Table(name: "person_address")]
class PersonAddress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'personAddresses')]
    private ?Person $idPerson = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 10)]
    private ?string $streetNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $addressDetails = null;

    #[ORM\ManyToOne(inversedBy: 'personAddresses')]
    private ?Municipality $municipality = null;

    #[ORM\ManyToOne(inversedBy: 'personAddresses')]
    private ?Country $country = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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

    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    public function setStreetNumber(string $streetNumber): static
    {
        $this->streetNumber = $streetNumber;

        return $this;
    }

    public function getAddressDetails(): ?string
    {
        return $this->addressDetails;
    }

    public function setAddressDetails(string $addressDetails): static
    {
        $this->addressDetails = $addressDetails;

        return $this;
    }

    public function getMunicipality(): ?Municipality
    {
        return $this->municipality;
    }

    public function setMunicipality(?Municipality $municipality): static
    {
        $this->municipality = $municipality;

        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getIdPerson(): ?Person
    {
        return $this->idPerson;
    }

    public function setIdPerson(?Person $idPerson): static
    {
        $this->idPerson = $idPerson;

        return $this;
    }
}
