<?php
namespace App\Repository\Tenant;
use App\Entity\Tenant\ExamServiceType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;

class ExamServiceTypeRepository extends ServiceEntityRepository
{
    public function __construct(TenantEntityManager $manager) {
        parent::__construct($manager, ExamServiceType::class);
    }
    public function findAllActive(): array {
        return $this->createQueryBuilder('est')
            ->where('est.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('est.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
