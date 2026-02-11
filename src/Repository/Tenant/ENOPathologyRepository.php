<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ENOPathology;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ENOPathology>
 */
class ENOPathologyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ENOPathology::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('ep')
            ->where('ep.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('ep.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByIcd10Code(string $code): ?ENOPathology
    {
        return $this->createQueryBuilder('ep')
            ->where('ep.icd10Code = :code')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findChronic(): array
    {
        return $this->createQueryBuilder('ep')
            ->where('ep.isChronic = :chronic')
            ->andWhere('ep.isActive = :active')
            ->setParameter('chronic', true)
            ->setParameter('active', true)
            ->orderBy('ep.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
