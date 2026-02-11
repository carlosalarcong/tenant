<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\PrescriptionRuleDetail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrescriptionRuleDetail>
 */
class PrescriptionRuleDetailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrescriptionRuleDetail::class);
    }

    /**
     * Encuentra todos los detalles de regla de prescripción activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('prd')
            ->where('prd.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('prd.dailyQuantity', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
