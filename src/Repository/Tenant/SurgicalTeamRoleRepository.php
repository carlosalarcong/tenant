<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\SurgicalTeamRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SurgicalTeamRole>
 *
 * @method SurgicalTeamRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method SurgicalTeamRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method SurgicalTeamRole[]    findAll()
 * @method SurgicalTeamRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SurgicalTeamRoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurgicalTeamRole::class);
    }
}
