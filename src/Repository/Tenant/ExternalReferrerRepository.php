<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ExternalReferrer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExternalReferrer>
 */
class ExternalReferrerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExternalReferrer::class);
    }

    public function findActive(): array
    {
        return $this->createQueryBuilder('er')
            ->where('er.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('er.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findWithAgreement(): array
    {
        return $this->createQueryBuilder('er')
            ->where('er.hasAgreement = :hasAgreement')
            ->andWhere('er.isActive = :active')
            ->setParameter('hasAgreement', true)
            ->setParameter('active', true)
            ->orderBy('er.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('er')
            ->where('er.referrerType = :type')
            ->andWhere('er.isActive = :active')
            ->setParameter('type', $type)
            ->setParameter('active', true)
            ->orderBy('er.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
