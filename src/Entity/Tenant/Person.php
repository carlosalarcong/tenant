<?php

namespace App\Entity\Tenant;

use App\Repository\Tenant\PersonRepository;
use App\Security\SecuredResourceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Person (Pnatural)
 *
 * Tabla legacy: pnatural
 *
 * Representa a una persona física con sus datos demográficos, de contacto e identificación.
 * Equivalente a la tabla pnatural del legacy, con arquitectura normalizada y soporte multi-tenant.
 */
#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[ORM\Table(name: '`person`')]
class Person implements SecuredResourceInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'people')]
    private ?IdentificationType $identificationType = null;

    #[ORM\Column(length: 100)]
    private ?string $identification = null;

    #[ORM\Column(length: 60)]
    private ?string $name = null;

    #[ORM\Column(length: 45)]
    private ?string $lastName = null;

    #[ORM\Column(length: 45, nullable: true)]
    private ?string $middleName = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $birthDateAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deathDateAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $nonMedicalNote = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photoPath = null;

    #[ORM\Column(options: ["default" => true])]
    private ?bool $isRecordVisibility = true;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $recordViewedDateAt = null;

    #[ORM\ManyToOne(inversedBy: 'people')]
    private ?EducationLevel $educationLevel = null;

    #[ORM\ManyToOne(inversedBy: 'people')]
    private ?MaritalStatus $maritalStatus = null;

    #[ORM\ManyToOne(inversedBy: 'people')]
    private ?Occupation $occupation = null;

    #[ORM\ManyToOne(inversedBy: 'people')]
    private ?EthnicGroup $ethnicGroup = null;

    #[ORM\ManyToOne(inversedBy: 'people')]
    private ?Religion $religion = null;

    #[ORM\ManyToOne(inversedBy: 'people')]
    private ?Gender $gender = null;

    #[ORM\ManyToOne(inversedBy: 'people')]
    private ?Country $nacionality = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $socialName = null;

    #[ORM\Column(nullable: true)]
    private ?int $number_of_children = null;

    #[ORM\Column(length: 20)]
    private ?string $mobilePhone = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $workPhone = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $homePhone = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $secondaryEmail = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $contactMethod = null;

    #[ORM\Column(nullable: true)]
    private ?int $twinNumber = null;

    /**
     * @var Collection<int, PersonAddress>
     */
    #[ORM\OneToMany(targetEntity: PersonAddress::class, mappedBy: 'idPerson')]
    private Collection $personAddresses;

    public function __construct()
    {
        $this->personAddresses = new ArrayCollection();
    }

    // Este método se ejecuta justo antes del primer INSERT
    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $now = new \DateTimeImmutable();
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    // Este método se ejecuta justo antes de cada UPDATE
    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getIdentificationType(): ?IdentificationType
    {
        return $this->identificationType;
    }

    public function setIdentificationType(?IdentificationType $identificationType): static
    {
        $this->identificationType = $identificationType;

        return $this;
    }

    public function getIdentification(): ?string
    {
        return $this->identification;
    }

    public function setIdentification(string $identification): static
    {
        $this->identification = $identification;

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

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getMiddleName(): ?string
    {
        return $this->middleName;
    }

    public function setMiddleName(?string $middleName): static
    {
        $this->middleName = $middleName;

        return $this;
    }

    public function getBirthDateAt(): ?\DateTimeImmutable
    {
        return $this->birthDateAt;
    }

    public function setBirthDateAt(\DateTimeImmutable $birthDateAt): static
    {
        $this->birthDateAt = $birthDateAt;

        return $this;
    }

    public function getDeathDateAt(): ?\DateTimeImmutable
    {
        return $this->deathDateAt;
    }

    public function setDeathDateAt(?\DateTimeImmutable $deathDateAt): static
    {
        $this->deathDateAt = $deathDateAt;

        return $this;
    }

    public function getNonMedicalNote(): ?string
    {
        return $this->nonMedicalNote;
    }

    public function setNonMedicalNote(?string $nonMedicalNote): static
    {
        $this->nonMedicalNote = $nonMedicalNote;

        return $this;
    }

    public function getPhotoPath(): ?string
    {
        return $this->photoPath;
    }

    public function setPhotoPath(?string $photoPath): static
    {
        $this->photoPath = $photoPath;

        return $this;
    }

    public function isRecordVisibility(): ?bool
    {
        return $this->isRecordVisibility;
    }

    public function setIsRecordVisibility(bool $isRecordVisibility): static
    {
        $this->isRecordVisibility = $isRecordVisibility;

        return $this;
    }

    public function getRecordViewedDateAt(): ?\DateTimeImmutable
    {
        return $this->recordViewedDateAt;
    }

    public function setRecordViewedDateAt(?\DateTimeImmutable $recordViewedDateAt): static
    {
        $this->recordViewedDateAt = $recordViewedDateAt;

        return $this;
    }

    public function getEducationLevel(): ?EducationLevel
    {
        return $this->educationLevel;
    }

    public function setEducationLevel(?EducationLevel $educationLevel): static
    {
        $this->educationLevel = $educationLevel;

        return $this;
    }

    public function getMaritalStatus(): ?MaritalStatus
    {
        return $this->maritalStatus;
    }

    public function setMaritalStatus(?MaritalStatus $maritalStatus): static
    {
        $this->maritalStatus = $maritalStatus;

        return $this;
    }

    public function getOccupation(): ?Occupation
    {
        return $this->occupation;
    }

    public function setOccupation(?Occupation $occupation): static
    {
        $this->occupation = $occupation;

        return $this;
    }

    public function getEthnicGroup(): ?EthnicGroup
    {
        return $this->ethnicGroup;
    }

    public function setEthnicGroup(?EthnicGroup $ethnicGroup): static
    {
        $this->ethnicGroup = $ethnicGroup;

        return $this;
    }

    public function getReligion(): ?Religion
    {
        return $this->religion;
    }

    public function setReligion(?Religion $religion): static
    {
        $this->religion = $religion;

        return $this;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function setGender(?Gender $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getNacionality(): ?Country
    {
        return $this->nacionality;
    }

    public function setNacionality(?Country $nacionality): static
    {
        $this->nacionality = $nacionality;

        return $this;
    }

    public function getSocialName(): ?string
    {
        return $this->socialName;
    }

    public function setSocialName(?string $socialName): static
    {
        $this->socialName = $socialName;

        return $this;
    }

    public function getNumberOfChildren(): ?int
    {
        return $this->number_of_children;
    }

    public function setNumberOfChildren(?int $number_of_children): static
    {
        $this->number_of_children = $number_of_children;

        return $this;
    }

    public function getMobilePhone(): ?string
    {
        return $this->mobilePhone;
    }

    public function setMobilePhone(string $mobilePhone): static
    {
        $this->mobilePhone = $mobilePhone;

        return $this;
    }

    public function getWorkPhone(): ?string
    {
        return $this->workPhone;
    }

    public function setWorkPhone(?string $workPhone): static
    {
        $this->workPhone = $workPhone;

        return $this;
    }

    public function getHomePhone(): ?string
    {
        return $this->homePhone;
    }

    public function setHomePhone(?string $homePhone): static
    {
        $this->homePhone = $homePhone;

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

    public function getSecondaryEmail(): ?string
    {
        return $this->secondaryEmail;
    }

    public function setSecondaryEmail(?string $secondaryEmail): static
    {
        $this->secondaryEmail = $secondaryEmail;

        return $this;
    }

    public function getContactMethod(): ?string
    {
        return $this->contactMethod;
    }

    public function setContactMethod(?string $contactMethod): static
    {
        $this->contactMethod = $contactMethod;

        return $this;
    }

    public function getTwinNumber(): ?int
    {
        return $this->twinNumber;
    }

    public function setTwinNumber(?int $twinNumber): static
    {
        $this->twinNumber = $twinNumber;

        return $this;
    }

    /**
     * @return Collection<int, PersonAddress>
     */
    public function getPersonAddresses(): Collection
    {
        return $this->personAddresses;
    }

    public function addPersonAddress(PersonAddress $personAddress): static
    {
        if (!$this->personAddresses->contains($personAddress)) {
            $this->personAddresses->add($personAddress);
            $personAddress->setIdPerson($this);
        }

        return $this;
    }

    public function removePersonAddress(PersonAddress $personAddress): static
    {
        if ($this->personAddresses->removeElement($personAddress)) {
            // set the owning side to null (unless already changed)
            if ($personAddress->getIdPerson() === $this) {
                $personAddress->setIdPerson(null);
            }
        }

        return $this;
    }

    // ===== SecuredResourceInterface Implementation =====

    public function getPermissionDomain(): string
    {
        return 'person';
    }

    public function getPermissionId(): int|string
    {
        return $this->id;
    }
}
