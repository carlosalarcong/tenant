<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\EmergencyConsultationType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmergencyConsultationType>
 */
class EmergencyConsultationTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmergencyConsultationType::class);
    }

    /**
     * Encuentra todos los tipos de consulta activos
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('ect')
            ->where('ect.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('ect.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
