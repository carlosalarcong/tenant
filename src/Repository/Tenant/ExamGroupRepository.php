<?php
namespace App\Repository\Tenant;
use App\Entity\Tenant\ExamGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;

class ExamGroupRepository extends ServiceEntityRepository
{
    public function __construct(TenantEntityManager $manager) {
        parent::__construct($manager, ExamGroup::class);
    }
    public function findAllActive(): array {
        return $this->createQueryBuilder('eg')
            ->where('eg.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('eg.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
