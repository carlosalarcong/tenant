<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\MenuItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * MenuItemRepository: Consultas para items del menú configurable.
 */
class MenuItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuItem::class);
    }

    /**
     * Obtiene la estructura completa del menú habilitado, ordenado jerárquicamente.
     * Solo retorna items habilitados y visibles en sidebar.
     * 
     * @return MenuItem[]
     */
    public function getMenuStructure(): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.parent IS NULL')
            ->andWhere('m.enabled = :enabled')
            ->andWhere('m.visibleInSidebar = :visible')
            ->setParameter('enabled', true)
            ->setParameter('visible', true)
            ->orderBy('m.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtiene items del menú con sus hijos de forma eficiente (eager loading).
     * Carga hasta 4 niveles de profundidad para soportar menús complejos.
     * 
     * @return MenuItem[]
     */
    public function getMenuWithChildren(): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.children', 'c')
            ->addSelect('c')
            ->leftJoin('c.children', 'cc')
            ->addSelect('cc')
            ->leftJoin('cc.children', 'ccc')
            ->addSelect('ccc')
            ->where('m.parent IS NULL')
            ->andWhere('m.enabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('m.position', 'ASC')
            ->addOrderBy('c.position', 'ASC')
            ->addOrderBy('cc.position', 'ASC')
            ->addOrderBy('ccc.position', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * Obtiene todos los items del menú para administración (incluye deshabilitados).
     * Carga toda la jerarquía con sus hijos (eager loading) para evitar N+1 queries.
     * 
     * @return MenuItem[]
     */
    public function getAllForAdmin(): array
    {
        return $this->createQueryBuilder('m')
            ->leftJoin('m.children', 'c')
            ->addSelect('c')
            ->leftJoin('c.children', 'cc')
            ->addSelect('cc')
            ->leftJoin('cc.children', 'ccc')
            ->addSelect('ccc')
            ->leftJoin('ccc.children', 'cccc')
            ->addSelect('cccc')
            ->where('m.parent IS NULL')
            ->orderBy('m.position', 'ASC')
            ->addOrderBy('c.position', 'ASC')
            ->addOrderBy('cc.position', 'ASC')
            ->addOrderBy('ccc.position', 'ASC')
            ->addOrderBy('cccc.position', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra un item por su nombre único.
     */
    public function findOneByName(string $name): ?MenuItem
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * Obtiene el siguiente número de posición para items del mismo nivel.
     */
    public function getNextPosition(?MenuItem $parent = null): int
    {
        $qb = $this->createQueryBuilder('m')
            ->select('MAX(m.position)')
            ->where('m.parent ' . ($parent ? '= :parent' : 'IS NULL'));

        if ($parent) {
            $qb->setParameter('parent', $parent);
        }

        $maxPosition = $qb->getQuery()->getSingleScalarResult();

        return ($maxPosition ?? -1) + 1;
    }

    /**
     * Reordena items después de eliminar uno.
     */
    public function reorderAfterDelete(int $deletedPosition, ?MenuItem $parent = null): void
    {
        $qb = $this->createQueryBuilder('m')
            ->update()
            ->set('m.position', 'm.position - 1')
            ->where('m.position > :deletedPosition')
            ->setParameter('deletedPosition', $deletedPosition);

        if ($parent) {
            $qb->andWhere('m.parent = :parent')
               ->setParameter('parent', $parent);
        } else {
            $qb->andWhere('m.parent IS NULL');
        }

        $qb->getQuery()->execute();
    }
}
