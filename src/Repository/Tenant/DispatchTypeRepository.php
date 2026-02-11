<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\DispatchType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DispatchType>
 */
class DispatchTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DispatchType::class);
    }

    /**
     * Encuentra todos los tipos de despacho activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('dt')
            ->where('dt.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('dt.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
