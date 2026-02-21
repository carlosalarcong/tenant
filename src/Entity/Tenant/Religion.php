<?php

namespace App\Entity\Tenant;

//use App\Repository\ReligionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Religion (Religion)
 *
 * Tabla legacy: religion
 *
 * Catálogo de religiones o creencias asignables a personas, con código HL7 para interoperabilidad.
 */
#[ORM\Entity()]
#[ORM\Table(name: '`religion`')]
class Religion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(options: ["default" => true])]
    private ?bool $isDefaultValue = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $religionCodeHl7 = null;

    #[ORM\Column(name: 'is_active', type: 'boolean', options: ["default" => true])]
    private ?bool $isActive = true;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @var Collection<int, Person>
     */
    #[ORM\OneToMany(targetEntity: Person::class, mappedBy: 'religion')]
    private Collection $people;

    public function __construct()
    {
        $this->people = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function isDefaultValue(): ?bool
    {
        return $this->isDefaultValue;
    }

    public function setIsDefaultValue(bool $isDefaultValue): static
    {
        $this->isDefaultValue = $isDefaultValue;

        return $this;
    }

    public function getReligionCodeHl7(): ?string
    {
        return $this->religionCodeHl7;
    }

    public function setReligionCodeHl7(string $religionCodeHl7): static
    {
        $this->religionCodeHl7 = $religionCodeHl7;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
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

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, Person>
     */
    public function getPeople(): Collection
    {
        return $this->people;
    }

    public function addPerson(Person $person): static
    {
        if (!$this->people->contains($person)) {
            $this->people->add($person);
            $person->setReligion($this);
        }

        return $this;
    }

    public function removePerson(Person $person): static
    {
        if ($this->people->removeElement($person)) {
            // set the owning side to null (unless already changed)
            if ($person->getReligion() === $this) {
                $person->setReligion(null);
            }
        }

        return $this;
    }
}
