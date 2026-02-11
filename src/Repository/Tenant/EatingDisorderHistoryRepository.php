<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\EatingDisorderHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EatingDisorderHistory>
 */
class EatingDisorderHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EatingDisorderHistory::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('edh')
            ->where('edh.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('edh.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
