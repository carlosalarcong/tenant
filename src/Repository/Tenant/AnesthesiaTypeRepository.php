<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\AnesthesiaType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AnesthesiaType>
 */
class AnesthesiaTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnesthesiaType::class);
    }

    /**
     * @return AnesthesiaType[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('a.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
