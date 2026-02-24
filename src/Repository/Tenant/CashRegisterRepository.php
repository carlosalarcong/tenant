<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\CashRegister;
use App\Entity\Tenant\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CashRegister>
 */
class CashRegisterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CashRegister::class);
    }

    /**
     * Encuentra la caja abierta (status='abierta') del cajero, cargando la ubicación.
     * Retorna null si no hay ninguna caja abierta para ese miembro.
     */
    public function findOpenByMember(Member $member): ?CashRegister
    {
        return $this->createQueryBuilder('cr')
            ->leftJoin('cr.cashRegisterLocation', 'loc')
            ->addSelect('loc')
            ->where('cr.member = :member')
            ->andWhere('cr.status = :status')
            ->setParameter('member', $member)
            ->setParameter('status', 'abierta')
            ->orderBy('cr.openedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Encuentra la última caja cerrada del cajero.
     * Se usa para mostrar la sección "Caja Anterior" en Gestión Caja cuando
     * el cajero no tiene ninguna caja abierta actualmente.
     */
    public function findLastClosedByMember(Member $member): ?CashRegister
    {
        return $this->createQueryBuilder('cr')
            ->leftJoin('cr.cashRegisterLocation', 'loc')
            ->addSelect('loc')
            ->where('cr.member = :member')
            ->andWhere('cr.status = :status')
            ->setParameter('member', $member)
            ->setParameter('status', 'cerrada')
            ->orderBy('cr.closedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Encuentra una caja por ID con su ubicación y detalles de cierre cargados
     * en la misma query (evita N+1 al renderizar el reporte).
     */
    public function findWithDetailsById(int $id): ?CashRegister
    {
        return $this->createQueryBuilder('cr')
            ->leftJoin('cr.cashRegisterLocation', 'loc')
            ->leftJoin('cr.member', 'mbr')
            ->leftJoin('cr.branch', 'br')
            ->addSelect('loc', 'mbr', 'br')
            ->where('cr.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
