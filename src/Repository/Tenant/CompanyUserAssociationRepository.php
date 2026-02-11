<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\CompanyUserAssociation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompanyUserAssociation>
 */
class CompanyUserAssociationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyUserAssociation::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('cua')
            ->where('cua.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('cua.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
