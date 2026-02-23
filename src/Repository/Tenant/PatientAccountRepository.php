<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PatientAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PatientAccount>
 */
class PatientAccountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PatientAccount::class);
    }

    /**
     * Find the PatientAccount for a given Patient, eagerly loading
     * accountStatus, patient, patient.person, patient.payer and patient.agreement.
     */
    public function findByPatientId(int $patientId): ?PatientAccount
    {
        return $this->createQueryBuilder('pa')
            ->leftJoin('pa.accountStatus', 'acst')
            ->leftJoin('pa.patient', 'p')
            ->leftJoin('p.person', 'per')
            ->leftJoin('p.payer', 'payer')
            ->leftJoin('p.agreement', 'agr')
            ->leftJoin('p.insurancePlan', 'plan')
            ->addSelect('acst', 'p', 'per', 'payer', 'agr', 'plan')
            ->where('pa.patient = :patientId')
            ->setParameter('patientId', $patientId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find PatientAccounts for all patients whose legal guardian (tutor)
     * is the given Person. Used to show co-responsible accounts in the summary.
     *
     * @return list<PatientAccount>
     */
    public function findByTutorPersonId(int $tutorPersonId): array
    {
        /** @var list<PatientAccount> $results */
        $results = $this->createQueryBuilder('pa')
            ->leftJoin('pa.accountStatus', 'acst')
            ->leftJoin('pa.patient', 'p')
            ->leftJoin('p.person', 'per')
            ->leftJoin('p.tutor', 'tut')
            ->addSelect('acst', 'p', 'per', 'tut')
            ->where('tut.id = :tutorPersonId')
            ->setParameter('tutorPersonId', $tutorPersonId)
            ->getQuery()
            ->getResult();

        return $results;
    }
}
