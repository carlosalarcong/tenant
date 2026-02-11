<?php
namespace App\Repository\Tenant;
use App\Entity\Tenant\ExamService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;

class ExamServiceRepository extends ServiceEntityRepository
{
    public function __construct(TenantEntityManager $manager) {
        parent::__construct($manager, ExamService::class);
    }
    public function findAllActive(): array {
        return $this->createQueryBuilder('es')
            ->where('es.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('es.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
