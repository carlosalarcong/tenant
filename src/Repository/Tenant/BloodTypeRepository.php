<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\BloodType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BloodType>
 */
class BloodTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BloodType::class);
    }

    /**
     * @return BloodType[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('b.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
