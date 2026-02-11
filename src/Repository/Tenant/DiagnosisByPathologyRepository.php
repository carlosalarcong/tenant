<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\DiagnosisByPathology;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DiagnosisByPathology>
 */
class DiagnosisByPathologyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiagnosisByPathology::class);
    }

    /**
     * @return DiagnosisByPathology[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('dbp')
            ->where('dbp.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('dbp.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
