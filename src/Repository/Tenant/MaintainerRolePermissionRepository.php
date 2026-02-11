<?php

namespace App\Repository\Tenant;

use App\Entity\Tenant\MaintainerRolePermission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Repositorio para MaintainerRolePermission con cache in-memory y Redis
 * 
 * Optimizaciones:
 * - Cache in-memory por request (evita queries repetidas en mismo request)
 * - Cache en Redis (evita queries entre requests)
 * - TTL de 1 hora (balance entre freshness y performance)
 * - Invalidación automática al guardar/eliminar
 * 
 * Performance esperado:
 * - Request 1: 1 query a DB + cache
 * - Request 2-N: 0 queries (lee de cache)
 * 
 * @author Melisa Development Team
 * @since Sprint 1.5 - Database-driven permissions (Feb 2026)
 */
class MaintainerRolePermissionRepository extends ServiceEntityRepository
{
    /**
     * Cache in-memory indexado por rol
     * Formato: ['ROLE_ADMIN' => [MaintainerRolePermission, ...], ...]
     */
    private array $inMemoryCache = [];

    /**
     * Flag para saber si ya cargamos todos los permisos en memoria
     */
    private bool $allPermissionsLoaded = false;

    private const CACHE_KEY = 'maintainer_role_permissions';
    private const CACHE_TTL = 3600; // 1 hora

    public function __construct(
        ManagerRegistry $registry,
        private readonly ?CacheItemPoolInterface $cache = null
    ) {
        parent::__construct($registry, MaintainerRolePermission::class);
    }

    /**
     * Obtiene todos los permisos activos para un rol específico
     * 
     * @param string $role Nombre del rol (ej: ROLE_ADMIN)
     * @return array<MaintainerRolePermission>
     */
    public function findByRole(string $role): array
    {
        // 1. Verificar cache in-memory
        if (isset($this->inMemoryCache[$role])) {
            return $this->inMemoryCache[$role];
        }

        // 2. Verificar cache Redis si está disponible
        if ($this->cache) {
            $cacheKey = self::CACHE_KEY . '_' . md5($role);
            $cacheItem = $this->cache->getItem($cacheKey);
            
            if ($cacheItem->isHit()) {
                $permissions = $cacheItem->get();
                $this->inMemoryCache[$role] = $permissions;
                return $permissions;
            }
        }

        // 3. Query a base de datos
        $permissions = $this->createQueryBuilder('p')
            ->where('p.role = :role')
            ->andWhere('p.isActive = true')
            ->setParameter('role', $role)
            ->orderBy('p.priority', 'DESC')
            ->getQuery()
            ->getResult();

        // 4. Guardar en ambos caches
        $this->inMemoryCache[$role] = $permissions;
        
        if ($this->cache && isset($cacheItem)) {
            $cacheItem->set($permissions);
            $cacheItem->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cacheItem);
        }

        return $permissions;
    }

    /**
     * Obtiene todos los permisos activos (para cualquier rol)
     * Útil para cargar todo de una vez
     * 
     * @return array<MaintainerRolePermission>
     */
    public function findAllActive(): array
    {
        // Verificar si ya cargamos todo
        if ($this->allPermissionsLoaded) {
            return array_merge(...array_values($this->inMemoryCache));
        }

        // Verificar cache Redis
        if ($this->cache) {
            $cacheKey = self::CACHE_KEY . '_all';
            $cacheItem = $this->cache->getItem($cacheKey);
            
            if ($cacheItem->isHit()) {
                $permissions = $cacheItem->get();
                $this->indexPermissionsByRole($permissions);
                $this->allPermissionsLoaded = true;
                return $permissions;
            }
        }

        // Query a base de datos
        $permissions = $this->createQueryBuilder('p')
            ->where('p.isActive = true')
            ->orderBy('p.role', 'ASC')
            ->addOrderBy('p.priority', 'DESC')
            ->getQuery()
            ->getResult();

        // Indexar por rol en memoria
        $this->indexPermissionsByRole($permissions);
        $this->allPermissionsLoaded = true;

        // Guardar en cache Redis
        if ($this->cache && isset($cacheItem)) {
            $cacheItem->set($permissions);
            $cacheItem->expiresAfter(self::CACHE_TTL);
            $this->cache->save($cacheItem);
        }

        return $permissions;
    }

    /**
     * Verifica si un rol tiene un permiso específico
     * 
     * @param string $role Rol del usuario
     * @param string $permission Permiso a verificar (CREATE, READ, UPDATE, DELETE, EXPORT)
     * @param string|null $category Categoría del mantenedor (opcional)
     * @param string|null $maintainer Mantenedor específico (opcional)
     * @return bool True si tiene el permiso
     */
    public function hasPermission(
        string $role,
        string $permission,
        ?string $category = null,
        ?string $maintainer = null
    ): bool {
        $permissions = $this->findByRole($role);

        foreach ($permissions as $perm) {
            // Verificar si aplica al contexto
            if (!$perm->appliesTo($category, $maintainer)) {
                continue;
            }

            // Wildcard (*) concede todos los permisos
            if ($perm->isWildcard() && $perm->isGranted()) {
                return true;
            }

            // Permiso específico
            if ($perm->getPermission() === $permission) {
                return $perm->isGranted();
            }
        }

        // Por defecto, denegar
        return false;
    }

    /**
     * Invalida el cache (útil al guardar/eliminar permisos)
     */
    public function invalidateCache(): void
    {
        // Limpiar cache in-memory
        $this->inMemoryCache = [];
        $this->allPermissionsLoaded = false;

        // Limpiar cache Redis
        if ($this->cache) {
            $this->cache->clear();
        }
    }

    /**
     * Indexa permisos por rol en cache in-memory
     * 
     * @param array<MaintainerRolePermission> $permissions
     */
    private function indexPermissionsByRole(array $permissions): void
    {
        $this->inMemoryCache = [];
        
        foreach ($permissions as $permission) {
            $role = $permission->getRole();
            if (!isset($this->inMemoryCache[$role])) {
                $this->inMemoryCache[$role] = [];
            }
            $this->inMemoryCache[$role][] = $permission;
        }
    }

    /**
     * Override save para invalidar cache
     */
    public function save(MaintainerRolePermission $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        
        if ($flush) {
            $this->getEntityManager()->flush();
            $this->invalidateCache();
        }
    }

    /**
     * Override remove para invalidar cache
     */
    public function remove(MaintainerRolePermission $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);
        
        if ($flush) {
            $this->getEntityManager()->flush();
            $this->invalidateCache();
        }
    }

    /**
     * Obtiene todos los roles únicos en el sistema
     * 
     * @return array<string>
     */
    public function findAllRoles(): array
    {
        $result = $this->createQueryBuilder('p')
            ->select('DISTINCT p.role')
            ->where('p.isActive = true')
            ->orderBy('p.role', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'role');
    }

    /**
     * Obtiene estadísticas de permisos (útil para dashboard del mantenedor)
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('p');
        
        return [
            'total' => (int) $qb->select('COUNT(p.id)')->getQuery()->getSingleScalarResult(),
            'active' => (int) $qb->select('COUNT(p.id)')->where('p.isActive = true')->getQuery()->getSingleScalarResult(),
            'inactive' => (int) $qb->select('COUNT(p.id)')->where('p.isActive = false')->getQuery()->getSingleScalarResult(),
            'by_role' => $this->countByRole(),
            'by_permission' => $this->countByPermission(),
        ];
    }

    private function countByRole(): array
    {
        $result = $this->createQueryBuilder('p')
            ->select('p.role, COUNT(p.id) as count')
            ->where('p.isActive = true')
            ->groupBy('p.role')
            ->getQuery()
            ->getArrayResult();

        return array_column($result, 'count', 'role');
    }

    private function countByPermission(): array
    {
        $result = $this->createQueryBuilder('p')
            ->select('p.permission, COUNT(p.id) as count')
            ->where('p.isActive = true')
            ->groupBy('p.permission')
            ->getQuery()
            ->getArrayResult();

        return array_column($result, 'count', 'permission');
    }
}
