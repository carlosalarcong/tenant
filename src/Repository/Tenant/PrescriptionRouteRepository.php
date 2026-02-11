<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PrescriptionRoute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrescriptionRoute>
 */
class PrescriptionRouteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrescriptionRoute::class);
    }

    /**
     * Encuentra todas las vías de prescripción activas
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('pr')
            ->where('pr.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pr.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
