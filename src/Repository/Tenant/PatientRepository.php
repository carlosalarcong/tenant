<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Patient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Patient>
 */
class PatientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Patient::class);
    }

    public function findByPersonId(int $personId): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.person', 'per')
            ->where('per.id = :personId')
            ->setParameter('personId', $personId)
            ->orderBy('p.admissionDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
