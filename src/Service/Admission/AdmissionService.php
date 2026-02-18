<?php

namespace App\Service\Admission;

use App\Entity\Tenant\AdmissionStatus;
use App\Entity\Tenant\AdmissionRecord;
use App\Entity\Tenant\Agreement;
use App\Entity\Tenant\Bed;
use App\Entity\Tenant\CareType;
use App\Entity\Tenant\Payer;
use App\Entity\Tenant\Patient;
use App\Entity\Tenant\Person;
use App\Entity\Tenant\Service;
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
        int $bedId
    ): ?AdmissionRecord {
        if ($this->findBlockingAdmissionForPerson($personId) instanceof AdmissionRecord) {
            return null;
        }

        if (
            !$this->validateFinancialData($payerId, $agreementId)
            || !$this->validateLocationData($serviceId, $bedId)
        ) {
            return null;
        }

        $person = $this->entityManager->find(Person::class, $personId);
        $payer = $this->entityManager->find(Payer::class, $payerId);
        $agreement = $this->entityManager->find(Agreement::class, $agreementId);
        $service = $this->entityManager->find(Service::class, $serviceId);
        $bed = $this->entityManager->find(Bed::class, $bedId);
        $careType = $this->careTypeRepository->findFirstActive();

        if (
            !$person instanceof Person
            || !$payer instanceof Payer
            || !$agreement instanceof Agreement
            || !$service instanceof Service
            || !$bed instanceof Bed
            || !$careType instanceof CareType
        ) {
            return null;
        }

        $patient = new Patient();
        $patient->setPerson($person);
        $patient->setPayer($payer);
        $patient->setAgreement($agreement);
        $patient->setCareType($careType);

        $record = new AdmissionRecord();
        $record->setPatient($patient);
        $record->setAdmissionType($admissionType);
        $record->setAdmissionStatus($this->resolveAdmissionStatusByPreferredNames(self::DRAFT_STATUS_CANDIDATES));
        $record->setPayer($payer);
        $record->setAgreement($agreement);
        $record->setService($service);
        $record->setBed($bed);

        $this->entityManager->persist($patient);
        $this->entityManager->persist($record);
        $this->entityManager->flush();

        return $record;
    }

    public function getWizardCreationBlockingReason(
        int $personId,
        int $payerId,
        int $agreementId,
        int $serviceId,
        int $bedId
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

        $hasActiveCareType = $this->careTypeRepository->hasAnyActive();

        if (!$hasActiveCareType) {
            return 'No hay Tipo de Atención activo (tabla care_type). Debes crear al menos uno para continuar.';
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
}
