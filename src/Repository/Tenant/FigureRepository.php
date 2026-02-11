<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Figure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Figure>
 */
class FigureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Figure::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('f.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCode(string $code): ?Figure
    {
        return $this->createQueryBuilder('f')
            ->where('f.code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
