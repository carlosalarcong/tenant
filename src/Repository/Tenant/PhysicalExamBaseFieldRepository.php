<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PhysicalExamBaseField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PhysicalExamBaseField>
 */
class PhysicalExamBaseFieldRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PhysicalExamBaseField::class);
    }

    /**
     * Encuentra todos los campos base de examen físico activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('pebf')
            ->where('pebf.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pebf.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
