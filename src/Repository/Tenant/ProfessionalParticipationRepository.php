<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\ProfessionalParticipation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProfessionalParticipation>
 */
class ProfessionalParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfessionalParticipation::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('pp')
            ->where('pp.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pp.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
