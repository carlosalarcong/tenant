<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\NursingConcurrency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NursingConcurrency>
 */
class NursingConcurrencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NursingConcurrency::class);
    }

    public function acquireLock(int $admissionRecordId, int $userId, string $sessionId): bool
    {
        $now = new \DateTimeImmutable();
        $existingLock = $this->findActiveLock($admissionRecordId);

        if ($existingLock instanceof NursingConcurrency) {
            if (
                $existingLock->getUserId() === $userId
                && $existingLock->getSessionId() === $sessionId
            ) {
                $existingLock->setLockedAt($now);
                $this->getEntityManager()->flush();

                return true;
            }

            return false;
        }

        $lock = (new NursingConcurrency())
            ->setAdmissionRecordId($admissionRecordId)
            ->setUserId($userId)
            ->setSessionId($sessionId)
            ->setLockedAt($now);

        $this->getEntityManager()->persist($lock);
        $this->getEntityManager()->flush();

        return true;
    }

    public function releaseLock(int $admissionRecordId, string $sessionId): void
    {
        $this->createQueryBuilder('nc')
            ->delete()
            ->where('nc.admissionRecordId = :admissionRecordId')
            ->andWhere('nc.sessionId = :sessionId')
            ->setParameter('admissionRecordId', $admissionRecordId)
            ->setParameter('sessionId', $sessionId)
            ->getQuery()
            ->execute();
    }

    public function findActiveLock(int $admissionRecordId): ?NursingConcurrency
    {
        return $this->createQueryBuilder('nc')
            ->where('nc.admissionRecordId = :admissionRecordId')
            ->setParameter('admissionRecordId', $admissionRecordId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function cleanStaleLocks(int $minutesOld = 30): int
    {
        $threshold = (new \DateTimeImmutable())->modify(sprintf('-%d minutes', max(1, $minutesOld)));

        return $this->createQueryBuilder('nc')
            ->delete()
            ->where('nc.lockedAt < :threshold')
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->execute();
    }
}
