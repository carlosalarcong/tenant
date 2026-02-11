<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\CareClosureDestination;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CareClosureDestination>
 */
class CareClosureDestinationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CareClosureDestination::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('ccd')
            ->where('ccd.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('ccd.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
