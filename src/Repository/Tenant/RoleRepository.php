<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repositorio para la entidad Role
 * 
 * @extends ServiceEntityRepository<Role>
 * @method Role|null find($id, $lockMode = null, $lockVersion = null)
 * @method Role|null findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    /**
     * Obtiene todos los roles activos ordenados por posición
     * Este método se usa para poblar dropdowns
     * 
     * @return Role[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('r.position', 'ASC')
            ->addOrderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra un rol por su código (ej: ROLE_ADMIN)
     */
    public function findOneByCode(string $code): ?Role
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * Obtiene roles del sistema (no pueden eliminarse)
     * 
     * @return Role[]
     */
    public function findSystemRoles(): array
    {
        return $this->findBy(['isSystem' => true], ['position' => 'ASC']);
    }

    public function save(Role $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Role $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
