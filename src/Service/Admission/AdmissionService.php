<?php

namespace App\Service\Admission;

use App\Entity\Tenant\AdmissionStatus;
use App\Entity\Tenant\AdmissionRecord;
use App\Entity\Tenant\Agreement;
use App\Entity\Tenant\Branch;
use App\Entity\Tenant\Bed;
use App\Entity\Tenant\CareType;
use App\Entity\Tenant\InsurancePlan;
use App\Entity\Tenant\Origin;
use App\Entity\Tenant\Payer;
use App\Entity\Tenant\Patient;
use App\Entity\Tenant\Person;
use App\Entity\Tenant\Professional;
use App\Entity\Tenant\Service;
use App\Entity\Tenant\Specialty;
use App\Repository\Tenant\AdmissionRecordRepository;
use App\Repository\Tenant\AdmissionStatusRepository;
use App\Repository\Tenant\AgreementRepository;
use App\Repository\Tenant\CareTypeRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;

class AdmissionService
{
    private const DRAFT_STATUS_CANDIDATES = ['pre-admisión'];
    private const ADMITTED_STATUS_CANDIDATES = ['admitido'];
    private const BLOCKING_ADMISSION_STATUSES = [
        'admitido',
        'hospitalizado',
        'preadmision',
    ];

    public function __construct(
        private TenantEntityManager $entityManager,
        private AgreementRepository $agreementRepository,
        private CareTypeRepository $careTypeRepository,
        private AdmissionRecordRepository $admissionRecordRepository,
        private AdmissionStatusRepository $admissionStatusRepository
    ) {}

    public function createDraftAdmission(Patient $patient, string $admissionType): AdmissionRecord
    {
        $record = new AdmissionRecord();
        $record->setPatient($patient);
        $record->setAdmissionType($admissionType);
        $record->setAdmissionStatus($this->resolveAdmissionStatusByPreferredNames(self::DRAFT_STATUS_CANDIDATES));

        $this->entityManager->persist($record);
        $this->entityManager->flush();

        return $record;
    }

    public function validateFinancialData(int $payerId, int $agreementId): bool
    {
        $payer = $this->entityManager->find(Payer::class, $payerId);
        if (!$payer instanceof Payer || !$payer->isActive()) {
            return false;
        }

        $agreement = $this->entityManager->find(Agreement::class, $agreementId);

        return $agreement instanceof Agreement
            && $agreement->isActive()
            && $this->agreementRepository->isActiveForPayer($agreementId, $payerId);
    }

    public function validateLocationData(int $serviceId, int $bedId): bool
    {
        $service = $this->entityManager->find(Service::class, $serviceId);
        if (!$service instanceof Service || !$service->isActive()) {
            return false;
        }

        $bed = $this->entityManager->find(Bed::class, $bedId);

        return $bed instanceof Bed && $bed->isActive();
    }

    public function assignFinancialData(AdmissionRecord $record, int $payerId, int $agreementId): bool
    {
        if (!$this->validateFinancialData($payerId, $agreementId)) {
            return false;
        }

        $payer = $this->entityManager->find(Payer::class, $payerId);
        $agreement = $this->entityManager->find(Agreement::class, $agreementId);
        if (!$payer instanceof Payer || !$agreement instanceof Agreement) {
            return false;
        }

        $record->setPayer($payer);
        $record->setAgreement($agreement);
        $this->entityManager->flush();

        return true;
    }

    public function assignLocationData(AdmissionRecord $record, int $serviceId, int $bedId): bool
    {
        if (!$this->validateLocationData($serviceId, $bedId)) {
            return false;
        }

        $service = $this->entityManager->find(Service::class, $serviceId);
        $bed = $this->entityManager->find(Bed::class, $bedId);
        if (!$service instanceof Service || !$bed instanceof Bed) {
            return false;
        }

        $record->setService($service);
        $record->setBed($bed);
        $this->entityManager->flush();

        return true;
    }

    public function createAdmissionFromWizard(
        int $personId,
        string $admissionType,
        int $payerId,
        int $agreementId,
        int $serviceId,
        int $bedId,
        array $wizardData = []
    ): ?AdmissionRecord {
        if ($this->findBlockingAdmissionForPerson($personId) instanceof AdmissionRecord) {
            return null;
        }

        $careTypeId = (int) ($wizardData['careType'] ?? 0);

        if (
            $careTypeId <= 0
            || !$this->isValidCareType($careTypeId)
            || !$this->entityManager->find(CareType::class, $careTypeId) instanceof CareType
            || !$this->entityManager->find(Branch::class, (int) ($wizardData['branch'] ?? 0)) instanceof Branch
            || !$this->validateFinancialData($payerId, $agreementId)
            || !$this->validateLocationData($serviceId, $bedId)
        ) {
            return null;
        }

        $person = $this->entityManager->find(Person::class, $personId);
        $payer = $this->entityManager->find(Payer::class, $payerId);
        $agreement = $this->entityManager->find(Agreement::class, $agreementId);
        $service = $this->entityManager->find(Service::class, $serviceId);
        $bed = $this->entityManager->find(Bed::class, $bedId);
        $careType = $this->entityManager->find(CareType::class, $careTypeId);
        $branch = $this->entityManager->find(Branch::class, (int) ($wizardData['branch'] ?? 0));
        $professional = $this->entityManager->find(Professional::class, (int) ($wizardData['professional'] ?? 0));
        $specialty = $this->entityManager->find(Specialty::class, (int) ($wizardData['specialty'] ?? 0));
        $origin = $this->entityManager->find(Origin::class, (int) ($wizardData['origin'] ?? 0));
        $insurancePlan = $this->entityManager->find(InsurancePlan::class, (int) ($wizardData['insurancePlan'] ?? 0));

        if (
            !$person instanceof Person
            || !$payer instanceof Payer
            || !$agreement instanceof Agreement
            || !$service instanceof Service
            || !$bed instanceof Bed
            || !$careType instanceof CareType
            || !$branch instanceof Branch
        ) {
            return null;
        }

        $patient = new Patient();
        $patient->setPerson($person);
        $patient->setPayer($payer);
        $patient->setAgreement($agreement);
        $patient->setCareType($careType);
        $patient->setInsurancePlan($insurancePlan instanceof InsurancePlan ? $insurancePlan : null);
        $patient->setOrigin($origin instanceof Origin ? $origin : null);
        $patient->setProfessional($professional instanceof Professional ? $professional : null);
        $person->setNumberOfChildren($this->normalizeNullablePositiveInt($wizardData['childrenCount'] ?? null));

        $record = new AdmissionRecord();
        $record->setPatient($patient);
        $record->setAdmissionType($admissionType);
        $record->setAdmissionStatus($this->resolveAdmissionStatusByPreferredNames(self::DRAFT_STATUS_CANDIDATES));
        $record->setPayer($payer);
        $record->setAgreement($agreement);
        $record->setService($service);
        $record->setBed($bed);
        $record->setBranch($branch);
        $record->setProfessional($professional instanceof Professional ? $professional : null);
        $record->setSpecialty($specialty instanceof Specialty ? $specialty : null);
        $record->setOrigin($origin instanceof Origin ? $origin : null);
        $record->setInsurancePlan($insurancePlan instanceof InsurancePlan ? $insurancePlan : null);
        $record->setReferringDoctor($this->truncate((string) ($wizardData['referralDoctor'] ?? ''), 255));
        $record->setEmergencyNotice($this->truncate((string) ($wizardData['emergencyContact'] ?? ''), 100));
        $record->setEmergencyPhone($this->truncate((string) ($wizardData['emergencyPhone'] ?? ''), 10));
        $record->setNotes($this->truncate((string) ($wizardData['observations'] ?? ''), 240));
        $record->setHasMedicalOrder(filter_var($wizardData['medicalOrder'] ?? false, FILTER_VALIDATE_BOOL));
        $record->setOtherOrigin(
            filter_var($wizardData['otherOriginEnabled'] ?? false, FILTER_VALIDATE_BOOL)
                ? $this->truncate((string) ($wizardData['otherOrigin'] ?? ''), 255)
                : null
        );

        $this->entityManager->persist($patient);
        $this->entityManager->persist($person);
        $this->entityManager->persist($record);
        $this->entityManager->flush();

        return $record;
    }

    public function getWizardCreationBlockingReason(
        int $personId,
        int $payerId,
        int $agreementId,
        int $serviceId,
        int $bedId,
        int $careTypeId
    ): ?string {
        $person = $this->entityManager->find(Person::class, $personId);
        if (!$person instanceof Person) {
            return 'La persona seleccionada no existe.';
        }

        $blockingAdmission = $this->findBlockingAdmissionForPerson($personId);
        if ($blockingAdmission instanceof AdmissionRecord) {
            return sprintf(
                'La persona ya tiene una admisión activa (#%d). Debes revisar ese ingreso antes de admitir nuevamente.',
                (int) $blockingAdmission->getId()
            );
        }

        if (!$this->validateFinancialData($payerId, $agreementId)) {
            return 'El financiador o convenio no son válidos para esta admisión.';
        }

        if (!$this->validateLocationData($serviceId, $bedId)) {
            return 'El servicio o la cama no son válidos o no están activos.';
        }

        if (!$this->isValidCareType($careTypeId)) {
            return 'El tipo de atención no es válido o está inactivo.';
        }

        return null;
    }

    public function finalizeAdmission(AdmissionRecord $record): void
    {
        $record->setAdmissionStatus($this->resolveAdmissionStatusByPreferredNames(self::ADMITTED_STATUS_CANDIDATES));
        $this->entityManager->flush();
    }

    public function resolveAdmissionLookups(AdmissionRecord $record): array
    {
        $bedName = null;
        if ($record->getBed()) {
            $bedName = sprintf(
                'Cama %s (Piso %s)',
                $record->getBed()->getBedNumber() ?? '-',
                $record->getBed()->getFloor() ?? '-'
            );
        }

        return [
            'payer_name' => $record->getPayer()?->getName(),
            'agreement_name' => $record->getAgreement()?->getName(),
            'service_name' => $record->getService()?->getName(),
            'bed_name' => $bedName,
        ];
    }

    public function findBlockingAdmissionForPerson(int $personId): ?AdmissionRecord
    {
        if ($personId <= 0) {
            return null;
        }

        $records = $this->admissionRecordRepository->findRecentByPersonId($personId);

        foreach ($records as $record) {
            if ($this->isBlockingAdmissionStatus($this->getEffectiveAdmissionStatusName($record))) {
                return $record;
            }
        }

        return null;
    }

    public function isBlockingAdmissionStatus(?string $status): bool
    {
        if ($status === null) {
            return false;
        }

        return in_array($this->normalizeAdmissionStatus($status), self::BLOCKING_ADMISSION_STATUSES, true);
    }

    public function getEffectiveAdmissionStatusName(AdmissionRecord $record): ?string
    {
        $directStatus = trim((string) ($record->getAdmissionStatus()?->getName() ?? ''));
        if ($directStatus !== '') {
            return $directStatus;
        }

        $admissionType = mb_strtolower(trim((string) $record->getAdmissionType()));
        if ($admissionType === 'pre') {
            return 'Pre-admisión';
        }

        // Fallback para registros legacy sin FK de estado poblada.
        return 'Admitido';
    }

    private function normalizeAdmissionStatus(string $status): string
    {
        $value = mb_strtolower(trim($status));
        $value = strtr($value, [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
        ]);
        $value = str_replace([' ', '-', '_'], '', $value);

        return $value;
    }

    /**
     * @param list<int> $personIds
     * @return array<int, list<AdmissionRecord>>
     */
    public function getAdmissionsByPersonIds(array $personIds): array
    {
        $personIds = array_values(array_unique(array_map('intval', $personIds)));
        if ($personIds === []) {
            return [];
        }

        $records = $this->admissionRecordRepository->findRecentByPersonIds($personIds);

        $grouped = [];
        foreach ($records as $record) {
            $personId = $record->getPatient()?->getPerson()?->getId();
            if ($personId === null) {
                continue;
            }
            if (!isset($grouped[$personId])) {
                $grouped[$personId] = [];
            }
            $grouped[$personId][] = $record;
        }

        return $grouped;
    }

    public function resolveAdmissionStatusByPreferredNames(array $preferredNames): ?AdmissionStatus
    {
        if ($preferredNames === []) {
            return null;
        }

        $statuses = $this->admissionStatusRepository->findAllForResolution();

        if ($statuses === []) {
            return null;
        }

        $indexedByNormalizedName = [];
        foreach ($statuses as $status) {
            $indexedByNormalizedName[$this->normalizeAdmissionStatus($status->getName())] = $status;
        }

        foreach ($preferredNames as $preferredName) {
            $normalized = $this->normalizeAdmissionStatus((string) $preferredName);
            if (isset($indexedByNormalizedName[$normalized])) {
                return $indexedByNormalizedName[$normalized];
            }
        }

        return null;
    }

    private function isValidCareType(int $careTypeId): bool
    {
        if ($careTypeId <= 0) {
            return false;
        }

        $careType = $this->entityManager->find(CareType::class, $careTypeId);

        return $careType instanceof CareType && $careType->isActive();
    }

    private function normalizeNullablePositiveInt(mixed $value): ?int
    {
        $intValue = (int) $value;
        return $intValue >= 0 ? $intValue : null;
    }

    private function truncate(string $value, int $maxLength): ?string
    {
        $normalized = trim($value);
        if ($normalized === '') {
            return null;
        }

        if (mb_strlen($normalized) <= $maxLength) {
            return $normalized;
        }

        return mb_substr($normalized, 0, $maxLength);
    }
}
