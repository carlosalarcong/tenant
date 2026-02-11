<?php

namespace App\Controller;

use App\Entity\Tenant\MaintainerRolePermission;
use App\Form\MaintainerRolePermissionType;
use App\Repository\Tenant\MaintainerRolePermissionRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;

/**
 * Mantenedor CRUD para administrar permisos de roles sobre mantenedores
 * 
 * Este controller permite a los administradores gestionar dinámicamente
 * qué roles tienen qué permisos sobre los mantenedores del sistema.
 * 
 * SEGURIDAD:
 * - Solo accesible por ROLE_ADMIN
 * - Protege eliminación del permiso wildcard de ROLE_ADMIN
 * - Invalida caché automáticamente después de cada operación
 * 
 * CARACTERÍSTICAS:
 * - Gestión completa CRUD de permisos
 * - Invalidación automática de caché (Redis + in-memory)
 * - Logging de operaciones para auditoría
 * - Validación de permisos críticos
 * - Soporte para wildcards (*)
 * - Filtrado por rol, permiso, categoría
 * 
 * @author Melisa Development Team
 * @since Sprint 2 - CRUD UI for permissions (Feb 2026)
 */
#[Route('/admin/maintainer-permissions')]
#[IsGranted('ROLE_ADMIN', message: 'Solo administradores pueden gestionar permisos de mantenedores')]
class MaintainerRolePermissionController extends AbstractMantenedorController
{
    public function __construct(
        private MaintainerRolePermissionRepository $repository,
        private LoggerInterface $logger,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->repository->createQueryBuilder('mrp')
            ->orderBy('mrp.priority', 'DESC')
            ->addOrderBy('mrp.role', 'ASC')
            ->addOrderBy('mrp.permission', 'ASC');
    }

    protected function getColumns(): array
    {
        return [
            'role' => $this->translator->trans('maintainers.columns.role', [], 'maintainers'),
            'permission' => $this->translator->trans('maintainers.columns.permission', [], 'maintainers'),
            'granted' => $this->translator->trans('maintainers.columns.granted', [], 'maintainers'),
            'category' => $this->translator->trans('maintainers.columns.category', [], 'maintainers'),
            'maintainer' => $this->translator->trans('maintainers.columns.maintainer', [], 'maintainers'),
            'priority' => $this->translator->trans('maintainers.columns.priority', [], 'maintainers'),
            'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers'),
        ];
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/maintainer_role_permission/index.html.twig';
    }

    protected function getFormType(): string
    {
        return MaintainerRolePermissionType::class;
    }

    protected function createNewEntity(): object
    {
        $entity = new MaintainerRolePermission();
        $entity->setGranted(true);
        $entity->setIsActive(true);
        $entity->setPriority(0);
        return $entity;
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainer_permission_index';
    }

    protected function getEntityClass(): string
    {
        return MaintainerRolePermission::class;
    }

    /**
     * Hook ejecutado después de guardar una entidad (crear o editar)
     * Invalida el caché de permisos para que los cambios se apliquen inmediatamente
     */
    protected function afterSave(object $entity, Request $request): void
    {
        parent::afterSave($entity, $request);
        
        if ($entity instanceof MaintainerRolePermission) {
            // Invalidar caché de permisos
            $this->repository->invalidateCache();
            
            // Log para auditoría
            $this->logger->info('Permission updated and cache invalidated', [
                'role' => $entity->getRole(),
                'permission' => $entity->getPermission(),
                'granted' => $entity->isGranted(),
                'category' => $entity->getCategory(),
                'maintainer' => $entity->getMaintainer(),
                'user' => $this->getUser()?->getUserIdentifier(),
            ]);
        }
    }

    /**
     * Hook ejecutado antes de eliminar una entidad
     * Previene la eliminación del permiso wildcard de ROLE_ADMIN
     */
    protected function beforeDelete(object $entity, Request $request): void
    {
        parent::beforeDelete($entity, $request);
        
        if ($entity instanceof MaintainerRolePermission) {
            // Protección: no permitir eliminar el permiso wildcard de ROLE_ADMIN
            if ($entity->getRole() === 'ROLE_ADMIN' && $entity->getPermission() === '*') {
                $this->addFlash('error', 
                    'No se puede eliminar el permiso wildcard (*) de ROLE_ADMIN por razones de seguridad. ' .
                    'Este permiso garantiza que los administradores siempre tengan acceso completo al sistema.'
                );
                throw new \RuntimeException('Cannot delete ROLE_ADMIN wildcard permission');
            }
        }
    }

    /**
     * Hook ejecutado después de eliminar una entidad
     * Invalida el caché de permisos
     */
    protected function afterDelete(object $entity, Request $request): void
    {
        parent::afterDelete($entity, $request);
        
        if ($entity instanceof MaintainerRolePermission) {
            // Invalidar caché de permisos
            $this->repository->invalidateCache();
            
            // Log para auditoría
            $this->logger->warning('Permission deleted and cache invalidated', [
                'role' => $entity->getRole(),
                'permission' => $entity->getPermission(),
                'user' => $this->getUser()?->getUserIdentifier(),
            ]);
        }
    }

    protected function findEntity(int $id): ?object
    {
        return $this->repository->find($id);
    }

    protected function getPageTitle(?string $action = null): string
    {
        return match($action) {
            'create' => 'Crear Permiso de Rol',
            'edit' => 'Editar Permiso de Rol',
            default => 'Permisos de Mantenedores',
        };
    }

    #[Route('', name: 'app_maintainer_permission_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainer_permission_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainer_permission_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}', name: 'app_maintainer_permission_delete', methods: ['DELETE'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }

    #[Route('/export', name: 'app_maintainer_permission_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport($request);
    }
}
