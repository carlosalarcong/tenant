<?php
namespace App\Repository\Tenant;
use App\Entity\Tenant\PhysicalExamGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;

class PhysicalExamGroupRepository extends ServiceEntityRepository
{
    public function __construct(TenantEntityManager $manager) {
        parent::__construct($manager, PhysicalExamGroup::class);
    }
    public function findAllActive(): array {
        return $this->createQueryBuilder('peg')
            ->where('peg.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('peg.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
