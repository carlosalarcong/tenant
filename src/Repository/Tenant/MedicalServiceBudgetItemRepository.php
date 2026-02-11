<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\MedicalServiceBudgetItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MedicalServiceBudgetItem>
 */
class MedicalServiceBudgetItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MedicalServiceBudgetItem::class);
    }

    public function findByMedicalService(int $medicalServiceId): array
    {
        return $this->createQueryBuilder('msbi')
            ->where('msbi.medicalService = :medicalServiceId')
            ->setParameter('medicalServiceId', $medicalServiceId)
            ->getQuery()
            ->getResult();
    }

    public function findBySurgeryItem(int $surgeryItemId): array
    {
        return $this->createQueryBuilder('msbi')
            ->where('msbi.surgeryItem = :surgeryItemId')
            ->setParameter('surgeryItemId', $surgeryItemId)
            ->getQuery()
            ->getResult();
    }
}
