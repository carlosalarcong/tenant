<?php

namespace App\Controller\Admission;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\Country;
use App\Entity\Tenant\EducationLevel;
use App\Entity\Tenant\Gender;
use App\Entity\Tenant\IdentificationType;
use App\Entity\Tenant\MaritalStatus;
use App\Entity\Tenant\Occupation;
use App\Entity\Tenant\Person;
use App\Entity\Tenant\Religion;
use App\Form\Admission\ForeignPatientType;
use App\Form\Admission\PatientRegistrationType;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admission/patient', name: 'app_admission_patient_')]
class PatientRegistrationController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager
    ) {}

    #[Route('/register', name: 'register', methods: ['GET', 'POST'])]
    public function register(Request $request): Response
    {
        return $this->handleRegistration($request, false);
    }

    #[Route('/register/foreign', name: 'register_foreign', methods: ['GET', 'POST'])]
    public function registerForeign(Request $request): Response
    {
        return $this->handleRegistration($request, true);
    }

    private function handleRegistration(Request $request, bool $foreignMode): Response
    {
        $rawIdentificationType = (string) $request->query->get('identification_type', '');
        $initialIdentificationType = ctype_digit($rawIdentificationType) ? (int) $rawIdentificationType : 0;

        $identificationTypes = $this->entityManager->getRepository(IdentificationType::class)->findBy([], ['name' => 'ASC']);
        $genders = $this->entityManager->getRepository(Gender::class)->findBy([], ['name' => 'ASC']);
        $countries = $this->entityManager->getRepository(Country::class)->findBy([], ['name' => 'ASC']);
        $maritalStatuses = $this->entityManager->getRepository(MaritalStatus::class)->findBy([], ['name' => 'ASC']);
        $occupations = $this->entityManager->getRepository(Occupation::class)->findBy([], ['name' => 'ASC']);
        $religions = $this->entityManager->getRepository(Religion::class)->findBy([], ['name' => 'ASC']);
        $educationLevels = $this->entityManager->getRepository(EducationLevel::class)->findBy([], ['name' => 'ASC']);

        $formClass = $foreignMode ? ForeignPatientType::class : PatientRegistrationType::class;
        $form = $this->createForm($formClass, [
            'identification_type' => $initialIdentificationType ?: null,
            'identification' => (string) $request->query->get('identification', ''),
            'admission_type' => (string) $request->query->get('admission_type', 'hospitalaria'),
            'birth_date' => '',
            'name' => '',
            'last_name' => '',
            'middle_name' => '',
            'mobile_phone' => '',
            'home_phone' => '',
            'work_phone' => '',
            'email' => '',
            'secondary_email' => '',
            'social_name' => '',
            'number_of_children' => null,
            'gender' => null,
            'country' => null,
            'marital_status' => null,
            'occupation' => null,
            'religion' => null,
            'education_level' => null,
        ], [
            'identification_choices' => $this->toChoiceMap($identificationTypes),
            'gender_choices' => $this->toChoiceMap($genders),
            'country_choices' => $this->toChoiceMap($countries),
            'marital_status_choices' => $this->toChoiceMap($maritalStatuses),
            'occupation_choices' => $this->toChoiceMap($occupations),
            'religion_choices' => $this->toChoiceMap($religions),
            'education_level_choices' => $this->toChoiceMap($educationLevels),
            'foreign_mode' => $foreignMode,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array<string,mixed> $formData */
            $formData = $form->getData();

            if ($this->nullIfEmpty($formData['identification'] ?? null) !== null) {
                $exists = $this->entityManager->createQueryBuilder()
                    ->select('p.id')
                    ->from(Person::class, 'p')
                    ->leftJoin('p.identificationType', 'it')
                    ->where('p.identification = :identification')
                    ->setParameter('identification', (string) $formData['identification']);

                if ((int) ($formData['identification_type'] ?? 0) > 0) {
                    $exists->andWhere('it.id = :typeId')->setParameter('typeId', (int) $formData['identification_type']);
                }

                if (!empty($exists->getQuery()->getScalarResult())) {
                    $this->addFlash('warning', 'Ya existe un paciente con esa identificación.');
                }
            }

            if (count($request->getSession()->getFlashBag()->peek('warning')) > 0) {
                return $this->render('admission/patient/register.html.twig', [
                    'page_title' => $foreignMode ? 'Registro de paciente extranjero' : 'Registro de paciente',
                    'form' => $form->createView(),
                    'foreign_mode' => $foreignMode,
                ]);
            }

            try {
                $birthDateAt = new \DateTimeImmutable((string) $formData['birth_date']);
            } catch (\Exception) {
                $this->addFlash('danger', 'La fecha de nacimiento no es válida.');

                return $this->render('admission/patient/register.html.twig', [
                    'page_title' => $foreignMode ? 'Registro de paciente extranjero' : 'Registro de paciente',
                    'form' => $form->createView(),
                    'foreign_mode' => $foreignMode,
                ]);
            }

            $person = new Person();
            $identification = $this->nullIfEmpty($formData['identification'] ?? null);
            if ($identification === null) {
                $identification = $this->generateForeignIdentifier();
            }

            $person->setIdentification($identification);
            $person->setName((string) $formData['name']);
            $person->setLastName((string) $formData['last_name']);
            $person->setMiddleName($this->nullIfEmpty($formData['middle_name'] ?? null));
            $person->setMobilePhone((string) $formData['mobile_phone']);
            $person->setHomePhone($this->nullIfEmpty($formData['home_phone'] ?? null));
            $person->setWorkPhone($this->nullIfEmpty($formData['work_phone'] ?? null));
            $person->setEmail($this->nullIfEmpty($formData['email'] ?? null));
            $person->setSecondaryEmail($this->nullIfEmpty($formData['secondary_email'] ?? null));
            $person->setSocialName($this->nullIfEmpty($formData['social_name'] ?? null));
            $person->setNumberOfChildren($formData['number_of_children'] === null ? null : (int) $formData['number_of_children']);
            $person->setCreatedAt(new \DateTimeImmutable());
            $person->setUpdatedAt(new \DateTimeImmutable());
            $person->setBirthDateAt($birthDateAt);

            $this->assignRelation($person, IdentificationType::class, 'setIdentificationType', (int) ($formData['identification_type'] ?? 0));
            $this->assignRelation($person, Gender::class, 'setGender', (int) ($formData['gender'] ?? 0));
            $this->assignRelation($person, Country::class, 'setNacionality', (int) ($formData['country'] ?? 0));
            $this->assignRelation($person, MaritalStatus::class, 'setMaritalStatus', (int) ($formData['marital_status'] ?? 0));
            $this->assignRelation($person, Occupation::class, 'setOccupation', (int) ($formData['occupation'] ?? 0));
            $this->assignRelation($person, Religion::class, 'setReligion', (int) ($formData['religion'] ?? 0));
            $this->assignRelation($person, EducationLevel::class, 'setEducationLevel', (int) ($formData['education_level'] ?? 0));

            $this->entityManager->persist($person);
            $this->entityManager->flush();

            $this->addFlash('success', 'Persona creada. Continúa con la admisión.');

            return $this->redirectToRoute('app_admission_wizard_step1', [
                'patientId' => $person->getId(),
                'type' => ((string) ($formData['admission_type'] ?? 'hospitalaria')) === 'pre' ? 'pre' : 'hospitalaria',
            ]);
        }

        return $this->render('admission/patient/register.html.twig', [
            'page_title' => $foreignMode ? 'Registro de paciente extranjero' : 'Registro de paciente',
            'form' => $form->createView(),
            'foreign_mode' => $foreignMode,
        ]);
    }

    private function generateForeignIdentifier(): string
    {
        do {
            $candidate = 'EXT-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
            $exists = $this->entityManager->createQueryBuilder()
                ->select('p.id')
                ->from(Person::class, 'p')
                ->where('p.identification = :identification')
                ->setParameter('identification', $candidate)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        } while ($exists !== null);

        return $candidate;
    }

    private function assignRelation(Person $person, string $className, string $setter, int $id): void
    {
        if ($id <= 0) {
            return;
        }

        $entity = $this->entityManager->find($className, $id);
        if ($entity !== null) {
            $person->{$setter}($entity);
        }
    }

    private function toChoiceMap(array $items): array
    {
        $choices = [];
        foreach ($items as $item) {
            $choices[$item->getName()] = $item->getId();
        }

        return $choices;
    }

    private function nullIfEmpty(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));
        return $text === '' ? null : $text;
    }
}
