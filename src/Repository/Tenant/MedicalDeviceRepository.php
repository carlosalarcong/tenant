<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\MedicalDevice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MedicalDevice>
 */
class MedicalDeviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MedicalDevice::class);
    }

    public function findAllActive(): array
    {
        return $this->createQueryBuilder('md')
            ->where('md.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('md.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
