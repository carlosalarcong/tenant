<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\MedicalHistoryType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MedicalHistoryType>
 */
class MedicalHistoryTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MedicalHistoryType::class);
    }

    /**
     * @return MedicalHistoryType[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('mht')
            ->where('mht.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('mht.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
