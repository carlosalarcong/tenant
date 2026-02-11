<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\TreatmentRegimen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TreatmentRegimen>
 *
 * @method TreatmentRegimen|null find($id, $lockMode = null, $lockVersion = null)
 * @method TreatmentRegimen|null findOneBy(array $criteria, array $orderBy = null)
 * @method TreatmentRegimen[]    findAll()
 * @method TreatmentRegimen[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TreatmentRegimenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TreatmentRegimen::class);
    }
}
