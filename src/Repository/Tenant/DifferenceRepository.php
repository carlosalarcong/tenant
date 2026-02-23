<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Difference;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Difference>
 */
class DifferenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Difference::class);
    }

    /**
     * Carga una Difference con sus asociaciones más usadas en un único query.
     */
    public function findWithDetailsById(int $id): ?Difference
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.requestedByMember', 'rbm')->addSelect('rbm')
            ->leftJoin('d.differenceType', 'dt')->addSelect('dt')
            ->leftJoin('d.differenceReason', 'dr')->addSelect('dr')
            ->leftJoin('d.differenceDirection', 'dd')->addSelect('dd')
            ->leftJoin('d.patientAccount', 'pa')->addSelect('pa')
            ->where('d.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
