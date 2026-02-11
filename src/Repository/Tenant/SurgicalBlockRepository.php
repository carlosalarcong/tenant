<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\SurgicalBlock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SurgicalBlock>
 *
 * @method SurgicalBlock|null find($id, $lockMode = null, $lockVersion = null)
 * @method SurgicalBlock|null findOneBy(array $criteria, array $orderBy = null)
 * @method SurgicalBlock[]    findAll()
 * @method SurgicalBlock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SurgicalBlockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurgicalBlock::class);
    }
}
