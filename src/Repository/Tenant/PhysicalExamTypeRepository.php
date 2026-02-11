<?php
namespace App\Repository\Tenant;
use App\Entity\Tenant\PhysicalExamType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;

class PhysicalExamTypeRepository extends ServiceEntityRepository
{
    public function __construct(TenantEntityManager $manager) {
        parent::__construct($manager, PhysicalExamType::class);
    }
    public function findAllActive(): array {
        return $this->createQueryBuilder('pet')
            ->where('pet.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pet.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
