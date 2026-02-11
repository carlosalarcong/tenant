<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Setting;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository para Setting usando TenantEntityManager
 */
class SettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Setting::class);
    }

    public function getAllSetting(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = '
            SELECT * FROM setting s
            ORDER BY s.id ASC
            ';

        $resultSet = $conn->executeQuery($sql);
        return $resultSet->fetchAllAssociative();

    }


}
