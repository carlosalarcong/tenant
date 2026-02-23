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

    /**
     * Busca pacientes por RUT (identification) o por nombre/apellido.
     * Búsqueda case-insensitive con LIKE.
     *
     * @return Patient[]
     */
    public function searchByQuery(string $query, int $limit = 10): array
    {
        $q = '%' . mb_strtolower(trim($query)) . '%';

        return $this->createQueryBuilder('p')
            ->leftJoin('p.person', 'per')
            ->addSelect('per')
            ->leftJoin('p.payer', 'pay')
            ->addSelect('pay')
            ->leftJoin('p.agreement', 'agr')
            ->addSelect('agr')
            ->leftJoin('p.insurancePlan', 'plan')
            ->addSelect('plan')
            ->where('LOWER(per.identification) LIKE :q')
            ->orWhere('LOWER(per.name) LIKE :q')
            ->orWhere('LOWER(per.lastName) LIKE :q')
            ->setParameter('q', $q)
            ->orderBy('p.admissionDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
