<?php

namespace App\Service\Admission;

use App\Entity\Tenant\AdmissionRecord;
use App\Entity\Tenant\Agreement;
use App\Entity\Tenant\Bed;
use App\Entity\Tenant\Payer;
use App\Entity\Tenant\Person;
use App\Entity\Tenant\Service;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;

class AdmissionService
{
    public function __construct(
        private TenantEntityManager $entityManager
    ) {}

    public function createDraftAdmission(Person $patient, string $admissionType): AdmissionRecord
    {
        $record = new AdmissionRecord();
        $record->setPerson($patient);
        $record->setAdmissionType($admissionType);
        $record->setStatus('draft');

        $this->entityManager->persist($record);
        $this->entityManager->flush();

        return $record;
    }

    public function assignFinancialData(AdmissionRecord $record, int $payerId, int $agreementId): bool
    {
        $payer = $this->entityManager->find(Payer::class, $payerId);
        if (!$payer instanceof Payer || !$payer->isActive()) {
            return false;
        }

        $connection = $this->entityManager->getConnection();
        $agreementMatchesPayer = $connection->fetchOne(
            'SELECT id FROM agreement WHERE id = :id AND is_active = true AND payer_id = :payer',
            ['id' => $agreementId, 'payer' => $payerId]
        );
        $agreement = $this->entityManager->find(Agreement::class, $agreementId);
        if (!$agreementMatchesPayer || !$agreement instanceof Agreement || !$agreement->isActive()) {
            return false;
        }

        $record->setPayer($payer);
        $record->setAgreement($agreement);
        $this->entityManager->flush();

        return true;
    }

    public function assignLocationData(AdmissionRecord $record, int $serviceId, int $bedId): bool
    {
        $service = $this->entityManager->find(Service::class, $serviceId);
        if (!$service instanceof Service || !$service->isActive()) {
            return false;
        }

        $bed = $this->entityManager->find(Bed::class, $bedId);
        if (!$bed instanceof Bed || !$bed->isActive()) {
            return false;
        }

        $record->setService($service);
        $record->setBed($bed);
        $record->setStatus('completed');
        $this->entityManager->flush();

        return true;
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
}
