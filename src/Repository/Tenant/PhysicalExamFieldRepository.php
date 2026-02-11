<?php
namespace App\Repository\Tenant;
use App\Entity\Tenant\PhysicalExamField;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;

class PhysicalExamFieldRepository extends ServiceEntityRepository
{
    public function __construct(TenantEntityManager $manager) {
        parent::__construct($manager, PhysicalExamField::class);
    }
    public function findAllActive(): array {
        return $this->createQueryBuilder('pef')
            ->where('pef.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pef.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
