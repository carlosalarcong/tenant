<?php

namespace App\Controller;

use App\Security\Voter\MaintainerContext;
use App\Security\Voter\MaintainerVoter;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Turbo\TurboStreamResponse;

/**
 * Controlador base para Mantenedores (Master Data)
 * 
 * Implementa el patrón Template Method para estandarizar el flujo CRUD
 * de todos los mantenedores del sistema.
 * 
 * Los mantenedores son datos maestros compartidos por todos los tenants
 * (países, géneros, religiones, etc.)
 * 
 * Beneficios:
 * - Reduce código duplicado en ~30%
 * - Flujo consistente en todos los mantenedores
 * - Fácil agregar nuevos mantenedores
 * - Centraliza cambios (modificar una vez, aplica a todos)
 * 
 * Uso:
 * ```php
 * class GenderController extends AbstractMantenedorController
 * {
 *     protected function getData(): array { return $this->repository->findAll(); }
 *     protected function getColumns(): array { return ['name', 'active']; }
 *     protected function getTemplatePath(): string { return 'mantenedores/basic/gender/index.html.twig'; }
 *     protected function getFormType(): string { return GenderType::class; }
 *     protected function createNewEntity(): object { return new Gender(); }
 * }
 * ```
 */
abstract class AbstractMantenedorController extends AbstractTenantAwareController
{
    protected TenantEntityManager $entityManager;
    protected ?ExportService $exportService = null;

    public function __construct(
        TenantEntityManager $entityManager,
        protected TranslatorInterface $translator
    ) {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Inyecta el servicio de exportación (opcional)
     * Se usa setter injection para mantener retrocompatibilidad
     */
    public function setExportService(ExportService $exportService): void
    {
        $this->exportService = $exportService;
    }

    /**
     * Traduce un array de claves de columnas al idioma actual
     * 
     * Patrón funcional para traducir múltiples columnas de forma concisa.
     * Usa el dominio 'maintainers.columns' automáticamente.
     * 
     * @param array<string> $columns Claves de columnas (ej: ['name', 'code', 'is_active'])
     * @return array<string> Array de strings traducidos
     * 
     * @example
     * ```php
     * headers: $this->translateColumns(['code', 'name', 'is_active'])
     * // Resultado: ['Código', 'Nombre', 'Activo']
     * ```
     */
    protected function translateColumns(array $columns): array
    {
        return array_map(
            fn(string $column): string => $this->translator->trans(
                "maintainers.columns.$column",
                [],
                'maintainers'
            ),
            $columns
        );
    }

    /**
     * Detecta si la petición viene de un Turbo Frame
     * 
     * Turbo Frame puede enviar peticiones de dos formas:
     * 1. Con header Turbo-Frame cuando se hace submit de formulario
     * 2. Con Sec-Fetch-Dest: turboframe cuando se carga via src attribute
     */
    protected function isTurboFrameRequest(Request $request): bool
    {
        // Verificar header Turbo-Frame (para submits)
        if ($request->headers->has('Turbo-Frame')) {
            return true;
        }
        
        // Verificar Sec-Fetch-Dest (para lazy loading via src)
        if ($request->headers->get('Sec-Fetch-Dest') === 'turboframe') {
            return true;
        }
        
        return false;
    }

    /**
     * Template method: Define el flujo principal del listado
     * Las subclases llaman a este método desde sus rutas
     */
    protected function handleIndex(Request $request): Response
    {
        $this->beforeIndex($request);
        
        $dataOrQuery = $this->getData($request);
        
        // Auto-detección: QueryBuilder → Paginar | Array → Sin paginación
        if ($dataOrQuery instanceof QueryBuilder) {
            // Aplicar búsqueda y filtros antes de paginar
            $dataOrQuery = $this->applySearchAndFilters($dataOrQuery, $request);
            
            $pagination = $this->paginate($dataOrQuery, $request);
            $data = $pagination['items'];
            $paginationData = [
                'current_page' => $pagination['current_page'],
                'total_pages' => $pagination['total_pages'],
                'total_items' => $pagination['total_items'],
                'items_per_page' => $pagination['items_per_page'],
                'has_previous' => $pagination['has_previous'],
                'has_next' => $pagination['has_next'],
            ];
        } else {
            // Array simple: aplicar filtros en memoria
            $data = $this->filterArrayData($dataOrQuery, $request);
            $paginationData = null;
        }
        
        $processedData = $this->processData($data, $request);
        
        $this->afterIndex($request);
        
        return $this->render($this->getTemplatePath(), [
            'data' => $processedData,
            'columns' => $this->getColumns(),
            'actions' => $this->getActions(),
            'tenant' => $this->getTenant(),
            'page_title' => $this->getPageTitle(),
            'entity_class' => $this->getEntityClass(),
            'create_route' => $this->getCreateRoute(),
            'is_turbo_frame' => $this->isTurboFrameRequest($request),
            'pagination' => $paginationData,
            'search_config' => $this->getSearchConfig(),
            'filter_config' => $this->getFilterConfig(),
            'search' => $request->query->get('search', ''),
            'status' => $request->query->get('status', 'all'),
        ]);
    }

    /**
     * Template method: Define el flujo de creación de entidad
     * Las subclases llaman a este método desde sus rutas
     */
    protected function handleCreate(Request $request): Response
    {
        // Verificar permiso de creación con contexto de categoría (Phase 2)
        $context = new MaintainerContext($this->getEntityClass(), $this->getMaintainerCategory());
        $this->denyAccessUnlessGranted(MaintainerVoter::CREATE, $context);
        
        $this->beforeCreate($request);
        
        $entity = $this->createNewEntity();
        $form = $this->createForm($this->getFormType(), $entity);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->beforeSave($entity, $request);
            $this->save($entity);
            $this->afterSave($entity, $request);
            
            $this->addFlash('success', $this->getSuccessMessage('create'));
            
            // Si es petición Turbo Frame, retornar Turbo Stream para insertar nueva fila
            if ($this->isTurboFrameRequest($request)) {
                return $this->renderTurboStreamCreate($entity);
            }
            
            // Fallback: redirect normal
            return $this->redirectToRoute($this->getIndexRoute());
        }
        
        // Si es una petición Turbo Frame, usar template modal
        $isTurbo = $this->isTurboFrameRequest($request);
        $template = $isTurbo 
            ? $this->getModalFormTemplatePath() 
            : $this->getFormTemplatePath();
        
        return $this->render($template, [
            'form' => $form->createView(),
            'entity' => $entity,
            'page_title' => $this->getPageTitle('create'),
            'cancel_route' => $this->getIndexRoute(),
            'is_turbo_frame' => $isTurbo,
            'action_url' => $this->generateUrl($this->getCreateRoute()),
        ]);
    }

    /**
     * Template method: Define el flujo de edición de entidad
     * Las subclases llaman a este método desde sus rutas
     */
    protected function handleEdit(Request $request, int $id): Response
    {
        $entity = $this->findEntity($id);
        
        if (!$entity) {
            $this->addFlash('error', $this->getErrorMessage('not_found'));
            return $this->redirectToRoute($this->getIndexRoute());
        }
        
        // Verificar permiso de actualización con contexto de categoría (Phase 2)
        $context = new MaintainerContext($entity, $this->getMaintainerCategory());
        $this->denyAccessUnlessGranted(MaintainerVoter::UPDATE, $context);
        
        $this->beforeEdit($entity, $request);
        
        $form = $this->createForm($this->getFormType(), $entity);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->beforeSave($entity, $request);
            $this->save($entity);
            $this->afterSave($entity, $request);
            
            $this->addFlash('success', $this->getSuccessMessage('edit'));
            
            // Si es petición Turbo Frame, retornar Turbo Stream para reemplazar fila
            if ($this->isTurboFrameRequest($request)) {
                return $this->renderTurboStreamUpdate($entity);
            }
            
            // Fallback: redirect normal
            return $this->redirectToRoute($this->getIndexRoute());
        }
        
        // Si es una petición Turbo Frame, usar template modal
        $template = $this->isTurboFrameRequest($request) 
            ? $this->getModalFormTemplatePath() 
            : $this->getFormTemplatePath();
        
        return $this->render($template, [
            'form' => $form->createView(),
            'entity' => $entity,
            'page_title' => $this->getPageTitle('edit'),
            'cancel_route' => $this->getIndexRoute(),
            'is_turbo_frame' => $this->isTurboFrameRequest($request),
            'action_url' => $this->generateUrl(str_replace('_index', '_edit', $this->getIndexRoute()), ['id' => $id]),
        ]);
    }

    /**
     * Template method: Define el flujo de eliminación de entidad
     * Las subclases llaman a este método desde sus rutas
     */
    protected function handleDelete(Request $request, int $id): Response
    {
        $entity = $this->findEntity($id);
        
        if (!$entity) {
            $this->addFlash('error', $this->getErrorMessage('not_found'));
            return $this->redirectToRoute($this->getIndexRoute());
        }
        
        // Verificar permiso de eliminación con contexto de categoría (Phase 2)
        $context = new MaintainerContext($entity, $this->getMaintainerCategory());
        $this->denyAccessUnlessGranted(MaintainerVoter::DELETE, $context);
        
        if ($this->canDelete($entity)) {
            $entityId = $entity->getId();
            $this->beforeDelete($entity, $request);
            $this->remove($entity);
            $this->afterDelete($entity, $request);
            
            $this->addFlash('success', $this->getSuccessMessage('delete'));
            
            // Si es petición Turbo Frame, retornar Turbo Stream para remover fila
            if ($this->isTurboFrameRequest($request)) {
                return $this->renderTurboStreamDelete($entityId);
            }
        } else {
            $this->addFlash('error', $this->getErrorMessage('cannot_delete'));
        }
        
        return $this->redirectToRoute($this->getIndexRoute());
    }
    
    /**
     * Template method: Maneja la restauración de registros eliminados (soft delete)
     * Solo disponible para entidades con SoftDeletableTrait
     */
    protected function handleRestore(Request $request, int $id): Response
    {
        // Buscar incluyendo eliminados
        $entity = $this->findEntityIncludingDeleted($id);
        
        if (!$entity) {
            $this->addFlash('error', $this->getErrorMessage('not_found'));
            return $this->redirectToRoute($this->getIndexRoute());
        }
        
        if (!method_exists($entity, 'restore')) {
            $this->addFlash('error', 'Esta entidad no soporta restauración');
            return $this->redirectToRoute($this->getIndexRoute());
        }
        
        // Verificar permiso de creación (restore = recrear)
        $context = new MaintainerContext($entity, $this->getMaintainerCategory());
        $this->denyAccessUnlessGranted(MaintainerVoter::CREATE, $context);
        
        $entity->restore();
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
        
        $this->addFlash('success', 'Registro restaurado exitosamente');
        return $this->redirectToRoute($this->getIndexRoute());
    }
    
    /**
     * Template method: Maneja la exportación de datos a CSV
     * 
     * Características:
     * - Alta performance con streaming (procesa chunks de 1000 registros)
     * - Bajo consumo de memoria (no carga todo en RAM)
     * - Reutilizable en todos los mantenedores
     * - Respeta filtros y ordenamiento actuales
     * 
     * Uso en controlador hijo:
     * ```php
     * #[Route('/export', name: 'app_maintainers_cost_center_export', methods: ['GET'])]
     * public function export(Request $request): Response
     * {
     *     return $this->handleExport($request);
     * }
     * ```
     * 
     * O personalizar columnas/headers:
     * ```php
     * return $this->handleExport(
     *     request: $request,
     *     columns: ['id', 'name', 'code'],
     *     headers: ['ID', 'Nombre', 'Código'],
     *     filename: 'centros_costo.csv'
     * );
     * ```
     */
    protected function handleExport(
        Request $request,
        ?array $columns = null,
        ?array $headers = null,
        ?string $filename = null
    ): Response {
        // Verificar permiso de exportación con contexto de categoría (Phase 2)
        $context = new MaintainerContext($this->getEntityClass(), $this->getMaintainerCategory());
        $this->denyAccessUnlessGranted(MaintainerVoter::EXPORT, $context);
        
        if (!$this->exportService) {
            throw new \RuntimeException('ExportService not injected. Add #[Autowire] or setter injection.');
        }
        
        // Obtener datos de la misma forma que el index (respeta filtros)
        $dataOrQuery = $this->getData($request);
        
        // Si es QueryBuilder, asegurar que excluimos registros eliminados
        if ($dataOrQuery instanceof QueryBuilder) {
            $alias = $dataOrQuery->getRootAliases()[0];
            $metadata = $dataOrQuery->getEntityManager()->getClassMetadata($dataOrQuery->getRootEntities()[0]);
            
            if ($metadata->hasField('deletedAt')) {
                $dataOrQuery->andWhere("$alias.deletedAt IS NULL");
            }
        }
        
        // Columnas por defecto desde getColumns()
        $columns = $columns ?? $this->getColumns();
        
        // Headers por defecto: capitalizar nombres de columnas
        $headers = $headers ?? array_map(fn($col) => ucfirst(str_replace('_', ' ', $col)), $columns);
        
        // Filename por defecto desde page title
        $filename = $filename ?? $this->sanitizeFilename($this->getPageTitle()) . '_' . date('Y-m-d') . '.csv';
        
        // Si es QueryBuilder, exportar con streaming
        if ($dataOrQuery instanceof QueryBuilder) {
            return $this->exportService->exportToCsv(
                queryBuilder: $dataOrQuery,
                columns: $columns,
                headers: $headers,
                filename: $filename
            );
        }
        
        // Si es array, exportar directamente
        return $this->exportService->exportArrayToCsv(
            data: $dataOrQuery,
            columns: $columns,
            headers: $headers,
            filename: $filename
        );
    }
    
    /**
     * Sanitiza nombre de archivo para export
     */
    private function sanitizeFilename(string $name): string
    {
        // Remover acentos
        $name = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
        // Reemplazar espacios y caracteres especiales
        $name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
        // Remover guiones bajos múltiples
        $name = preg_replace('/_+/', '_', $name);
        return strtolower(trim($name, '_'));
    }

    // ========================================================================
    // HOOKS: Métodos que las subclases pueden sobreescribir
    // ========================================================================

    /**
     * Hook: Se ejecuta antes de listar
     */
    protected function beforeIndex(Request $request): void {}

    /**
     * Hook: Se ejecuta después de listar
     */
    protected function afterIndex(Request $request): void {}

    /**
     * Hook: Se ejecuta antes de crear
     */
    protected function beforeCreate(Request $request): void {}

    /**
     * Hook: Se ejecuta antes de editar
     */
    protected function beforeEdit(object $entity, Request $request): void {}

    /**
     * Hook: Se ejecuta antes de guardar (create y edit)
     */
    protected function beforeSave(object $entity, Request $request): void {}

    /**
     * Hook: Se ejecuta después de guardar (create y edit)
     */
    protected function afterSave(object $entity, Request $request): void {}

    /**
     * Hook: Se ejecuta antes de eliminar
     */
    protected function beforeDelete(object $entity, Request $request): void {}

    /**
     * Hook: Se ejecuta después de eliminar
     */
    protected function afterDelete(object $entity, Request $request): void {}

    // ========================================================================
    // MÉTODOS ABSTRACTOS: Las subclases DEBEN implementarlos
    // ========================================================================

    /**
     * Obtiene los datos a mostrar en el listado
     * 
     * Retornar QueryBuilder para paginación automática:
     *   return $this->repository->createQueryBuilder('e');
     * 
     * Retornar array para sin paginación:
     *   return $this->repository->findAll();
     * 
     * @return array|QueryBuilder Array de entidades o QueryBuilder para paginación
     */
    abstract protected function getData(Request $request): array|QueryBuilder;

    /**
     * Define las columnas a mostrar en la tabla
     * 
     * @return array Ejemplo: ['name', 'description', 'active']
     */
    abstract protected function getColumns(): array;

    /**
     * Ruta de la plantilla del listado
     * 
     * @return string Ejemplo: 'mantenedores/basic/gender/index.html.twig'
     */
    abstract protected function getTemplatePath(): string;

    /**
     * Tipo de formulario a usar
     * 
     * @return string Ejemplo: GenderType::class
     */
    abstract protected function getFormType(): string;

    /**
     * Crea una nueva instancia de la entidad
     * 
     * @return object Nueva instancia de la entidad
     */
    abstract protected function createNewEntity(): object;

    /**
     * Nombre de la ruta del index
     * 
     * @return string Ejemplo: 'app_maintainers_gender_index'
     */
    abstract protected function getIndexRoute(): string;

    /**
     * Título de la página usando traducciones automáticas por entidad.
     *
     * Genera claves de traducción basadas en el namespace y nombre de clase:
     * - maintainers.entities.{module}.{entity_key}.title
     * - maintainers.entities.{module}.{entity_key}.create_title
     * - maintainers.entities.{module}.{entity_key}.edit_title
     *
     * Ejemplo: CreditCardController en Treasury → maintainers.entities.treasury.credit_card.title
     */
    protected function getPageTitle(string $action = 'index'): string
    {
        $entityKey = $this->getEntityTranslationKey();

        return match($action) {
            'create' => $this->translator->trans(
                "maintainers.entities.{$entityKey}.create_title",
                [],
                'maintainers'
            ),
            'edit' => $this->translator->trans(
                "maintainers.entities.{$entityKey}.edit_title",
                [],
                'maintainers'
            ),
            default => $this->translator->trans(
                "maintainers.entities.{$entityKey}.title",
                [],
                'maintainers'
            )
        };
    }

    /**
     * Genera la clave de traducción de la entidad incluyendo el módulo.
     *
     * Ejemplo: App\Controller\Maintainers\Treasury\CreditCardController → treasury.credit_card
     */
    protected function getEntityTranslationKey(): string
    {
        $reflection = new \ReflectionClass($this);
        $namespace = $reflection->getNamespaceName();
        $className = $reflection->getShortName();

        // Extraer módulo del namespace: App\Controller\Maintainers\Treasury → treasury
        $module = '';
        if (preg_match('/Controller\\\\Maintainers\\\\(\w+)/', $namespace, $matches)) {
            $module = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $matches[1]));
        }

        // Extraer nombre de entidad: CreditCardController → credit_card
        $entityName = str_replace('Controller', '', $className);
        $entityKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $entityName));

        return $module ? "{$module}.{$entityKey}" : $entityKey;
    }

    // ========================================================================
    // MÉTODOS CON IMPLEMENTACIÓN POR DEFECTO: Pueden sobreescribirse
    // ========================================================================

    /**
     * Aplica paginación a un QueryBuilder
     * 
     * @param QueryBuilder $queryBuilder Query a paginar
     * @param Request $request Request con parámetro 'page'
     * @return array ['items' => array, 'current_page' => int, 'total_pages' => int, ...]
     */
    protected function paginate(QueryBuilder $queryBuilder, Request $request): array
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $itemsPerPage = $this->getItemsPerPage();
        
        $queryBuilder
            ->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);
        
        $paginator = new Paginator($queryBuilder, fetchJoinCollection: true);
        $totalItems = count($paginator);
        $totalPages = max(1, (int) ceil($totalItems / $itemsPerPage));
        
        // Si la página solicitada excede el total, ajustar
        if ($page > $totalPages) {
            $page = $totalPages;
            $queryBuilder->setFirstResult(($page - 1) * $itemsPerPage);
            $paginator = new Paginator($queryBuilder, fetchJoinCollection: true);
        }
        
        return [
            'items' => iterator_to_array($paginator),
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'items_per_page' => $itemsPerPage,
            'has_previous' => $page > 1,
            'has_next' => $page < $totalPages,
        ];
    }

    /**
     * Cantidad de items por página
     * Subclases pueden sobreescribir
     */
    protected function getItemsPerPage(): int
    {
        return 10;
    }

    /**
     * Procesa los datos antes de enviarlos a la vista
     * Por defecto no hace procesamiento
     */
    protected function processData(array $data, Request $request): array
    {
        return $data;
    }

    /**
     * Acciones disponibles en la tabla
     * Por defecto: view, edit, delete
     */
    protected function getActions(): array
    {
        return ['edit', 'delete'];
    }

    /**
     * Ruta de la plantilla del formulario
     * Por defecto usa el template base reutilizable
     */
    protected function getFormTemplatePath(): string
    {
        return 'maintainers/_base_form.html.twig';
    }

    /**
     * Ruta de la plantilla del formulario para modal
     * Se usa cuando la petición es un Turbo Frame
     */
    protected function getModalFormTemplatePath(): string
    {
        return 'maintainers/_modal_form.html.twig';
    }

    /**
     * Ruta para crear nueva entidad
     * Por defecto deriva del index route
     */
    protected function getCreateRoute(): string
    {
        return str_replace('_index', '_create', $this->getIndexRoute());
    }

    /**
     * Busca una entidad por ID
     * Subclases pueden sobreescribir para usar repository específico
     */
    protected function findEntity(int $id): ?object
    {
        $entityClass = $this->getEntityClass();
        return $this->entityManager->getRepository($entityClass)->find($id);
    }
    
    /**
     * Busca una entidad por ID incluyendo registros eliminados (soft delete)
     * Por defecto usa find(), subclases pueden sobreescribir para queries custom
     */
    protected function findEntityIncludingDeleted(int $id): ?object
    {
        $entityClass = $this->getEntityClass();
        return $this->entityManager->getRepository($entityClass)->find($id);
    }

    /**
     * Obtiene la clase de la entidad
     * Subclases deben sobreescribir si necesitan lógica custom
     */

    /**
     * Guarda la entidad
     */
    protected function save(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * Elimina la entidad (soft delete)
     * Si la entidad tiene el trait SoftDeletableTrait, hace soft delete.
     * Si no, hace delete físico (backward compatibility)
     */
    protected function remove(object $entity): void
    {
        // Soft delete si la entidad lo soporta
        if (method_exists($entity, 'softDelete')) {
            $entity->softDelete($this->getUser());
            $this->entityManager->persist($entity);
        } else {
            // Fallback: delete físico para entidades sin trait
            $this->entityManager->remove($entity);
        }
        $this->entityManager->flush();
    }

    /**
     * Verifica si se puede eliminar la entidad
     * Por defecto siempre permite, subclases pueden sobreescribir
     */
    protected function canDelete(object $entity): bool
    {
        return true;
    }

    /**
     * Mensajes de éxito
     */
    protected function getSuccessMessage(string $action): string
    {
        return match($action) {
            'create' => $this->translator->trans('maintainers.common.success_create', [], 'maintainers'),
            'edit' => $this->translator->trans('maintainers.common.success_update', [], 'maintainers'),
            'delete' => $this->translator->trans('maintainers.common.success_delete', [], 'maintainers'),
            default => $this->translator->trans('maintainers.common.success_create', [], 'maintainers')
        };
    }

    /**
     * Mensajes de error
     */
    protected function getErrorMessage(string $type): string
    {
        return match($type) {
            'not_found' => $this->translator->trans('maintainers.common.error_update', [], 'maintainers'),
            'cannot_delete' => $this->translator->trans('maintainers.common.error_delete', [], 'maintainers'),
            default => $this->translator->trans('maintainers.common.error_create', [], 'maintainers')
        };
    }

    /**
     * Aplica búsqueda y filtros al QueryBuilder
     * 
     * @param QueryBuilder $qb Query builder base
     * @param Request $request Request con parámetros de búsqueda
     * @return QueryBuilder Query builder con filtros aplicados
     */
    protected function applySearchAndFilters(QueryBuilder $qb, Request $request): QueryBuilder
    {
        $alias = $qb->getRootAliases()[0];
        
        // Búsqueda por columnas configurables (case-insensitive)
        if ($search = $request->query->get('search')) {
            $searchColumns = $this->getSearchableColumns();
            
            if (!empty($searchColumns)) {
                $orX = $qb->expr()->orX();
                foreach ($searchColumns as $column) {
                    // Búsqueda case-insensitive usando LOWER en ambos lados
                    $orX->add(
                        $qb->expr()->like(
                            $qb->expr()->lower("$alias.$column"),
                            ':search'
                        )
                    );
                }
                $qb->andWhere($orX)
                   ->setParameter('search', '%' . strtolower($search) . '%');
            }
        }
        
        // Filtro soft delete: excluir registros eliminados por defecto
        $metadata = $qb->getEntityManager()->getClassMetadata($qb->getRootEntities()[0]);
        
        if ($metadata->hasField('deletedAt')) {
            $includeDeleted = $request->query->get('include_deleted', 'false');
            if ($includeDeleted !== 'true') {
                $qb->andWhere("$alias.deletedAt IS NULL");
            }
        }
        
        // Filtro por estado (isActive) si existe en la entidad
        if ($metadata->hasField('isActive')) {
            $status = $request->query->get('status', 'all');
            
            if ($status === 'active') {
                $qb->andWhere("$alias.isActive = true");
            } elseif ($status === 'inactive') {
                $qb->andWhere("$alias.isActive = false");
            }
            // 'all' = sin filtro
        }
        
        // Hook para filtros custom por controller
        $this->applyCustomFilters($qb, $request);
        
        return $qb;
    }

    /**
     * Filtra datos en Array (para mantenedores sin paginación)
     * 
     * @param array $data Datos originales
     * @param Request $request Request con parámetros
     * @return array Datos filtrados
     */
    protected function filterArrayData(array $data, Request $request): array
    {
        $search = $request->query->get('search');
        $status = $request->query->get('status', 'all');
        
        if (!$search && $status === 'all') {
            return $data; // Sin filtros activos
        }
        
        return array_filter($data, function($item) use ($search, $status) {
            // Búsqueda case-insensitive en propiedades configurables
            if ($search) {
                $found = false;
                $searchLower = strtolower($search);
                
                foreach ($this->getSearchableColumns() as $column) {
                    $getter = 'get' . ucfirst($column);
                    if (method_exists($item, $getter)) {
                        $value = $item->$getter();
                        if (stripos(strtolower((string)$value), $searchLower) !== false) {
                            $found = true;
                            break;
                        }
                    }
                }
                if (!$found) return false;
            }
            
            // Filtro por estado
            if ($status !== 'all' && method_exists($item, 'getIsActive')) {
                $isActive = $item->getIsActive();
                if ($status === 'active' && !$isActive) return false;
                if ($status === 'inactive' && $isActive) return false;
            }
            
            return true;
        });
    }

    /**
     * Obtiene la categoría del mantenedor para Phase 2 de permisos granulares
     * 
     * Este método puede ser sobrescrito por las subclases para especificar
     * explicitamente su categoría, activando así los permisos granulares por categoría.
     * 
     * Si retorna null (default), la categoría se extraerá automáticamente del namespace.
     * Si retorna un string, se usará esa categoría explícita.
     * 
     * Categorías válidas: 'basic', 'clinical', 'commercial', 'hospital', 'human',
     * 'workshop', 'settlements', 'insurance', 'budget'
     * 
     * Ejemplo de uso:
     * ```php
     * // En TreatmentTypeController:
     * protected function getMaintainerCategory(): ?string
     * {
     *     return 'commercial'; // Este mantenedor pertenece a categoría commercial
     * }
     * ```
     * 
     * @return string|null Categoría explícita o null para auto-detección
     * @since Phase 2 - Category Granularity (Sprint 3)
     */
    protected function getMaintainerCategory(): ?string
    {
        return null; // Por defecto, usar namespace para extraer categoría
    }

    /**
     * Obtiene el nombre de la clase de la entidad gestionada
     * 
     * Este método es usado por el voter de permisos y por otros componentes
     * que necesitan conocer qué entidad gestiona este controller.
     * 
     * Las subclases deben sobrescribir este método para retornar el FQCN
     * de su entidad.
     * 
     * Ejemplo:
     * ```php
     * protected function getEntityClass(): string
     * {
     *     return Gender::class;
     * }
     * ```
     * 
     * @return string FQCN de la entidad (Fully Qualified Class Name)
     */
    protected function getEntityClass(): string
    {
        // Por defecto, intentar extraer del createNewEntity()
        // Esto funciona en la mayoría de casos pero puede no ser lo más eficiente
        $entity = $this->createNewEntity();
        return get_class($entity);
    }

    /**
     * Define las columnas en las que se puede buscar
     * 
     * Por defecto busca en 'name'. Sobrescribir para personalizar.
     * 
     * @return array Lista de nombres de columnas
     */
    protected function getSearchableColumns(): array
    {
        return ['name']; // Default: buscar en columna 'name'
    }

    /**
     * Configuración de la UI de búsqueda
     * 
     * @return array Configuración de búsqueda
     */
    protected function getSearchConfig(): array
    {
        return [
            'enabled' => true,
            'placeholder' => $this->translator->trans('maintainers.search.placeholder', [], 'maintainers'),
            'label' => $this->translator->trans('maintainers.search.label', [], 'maintainers'),
        ];
    }

    /**
     * Configuración de filtros disponibles
     * 
     * @return array Configuración de filtros
     */
    protected function getFilterConfig(): array
    {
        return [
            'status' => [
                'enabled' => true,
                'label' => $this->translator->trans('maintainers.filter.status', [], 'maintainers'),
                'options' => [
                    'all' => $this->translator->trans('maintainers.filter.all', [], 'maintainers'),
                    'active' => $this->translator->trans('maintainers.filter.active', [], 'maintainers'),
                    'inactive' => $this->translator->trans('maintainers.filter.inactive', [], 'maintainers'),
                ]
            ]
        ];
    }

    /**
     * Hook para agregar filtros custom específicos por controller
     * 
     * Ejemplo de uso en controller hijo:
     * ```php
     * protected function applyCustomFilters(QueryBuilder $qb, Request $request): void
     * {
     *     if ($branchId = $request->query->get('branch')) {
     *         $qb->andWhere('e.branch = :branch')
     *            ->setParameter('branch', $branchId);
     *     }
     * }
     * ```
     * 
     * @param QueryBuilder $qb Query builder
     * @param Request $request Request con parámetros
     */
    protected function applyCustomFilters(QueryBuilder $qb, Request $request): void
    {
        // Override en controllers específicos para filtros adicionales
    }

    // ========================================
    // TURBO STREAMS METHODS
    // ========================================

    /**
     * Renderiza respuesta Turbo Stream para CREATE (prepend nueva fila)
     * 
     * @param object $entity Entidad recién creada
     * @return Response Respuesta Turbo Stream
     */
    protected function renderTurboStreamCreate(object $entity): Response
    {
        $rowHtml = $this->renderView($this->getTableRowTemplatePath(), [
            'entity' => $entity,
            'columns' => $this->getColumns(),
        ]);

        $flashHtml = $this->renderView('components/_flash_messages.html.twig');

        return (new TurboStreamResponse())
            ->prepend('maintainer-table-body', $rowHtml)
            ->update('flash-messages', $flashHtml);
    }

    /**
     * Renderiza respuesta Turbo Stream para UPDATE (replace fila existente)
     * 
     * @param object $entity Entidad actualizada
     * @return Response Respuesta Turbo Stream
     */
    protected function renderTurboStreamUpdate(object $entity): Response
    {
        $rowHtml = $this->renderView($this->getTableRowTemplatePath(), [
            'entity' => $entity,
            'columns' => $this->getColumns(),
        ]);

        $flashHtml = $this->renderView('components/_flash_messages.html.twig');

        return (new TurboStreamResponse())
            ->replace('maintainer-row-' . $entity->getId(), $rowHtml)
            ->update('flash-messages', $flashHtml);
    }

    /**
     * Renderiza respuesta Turbo Stream para DELETE (remove fila)
     * 
     * @param int $entityId ID de la entidad eliminada
     * @return Response Respuesta Turbo Stream
     */
    protected function renderTurboStreamDelete(int $entityId): Response
    {
        $flashHtml = $this->renderView('components/_flash_messages.html.twig');

        return (new TurboStreamResponse())
            ->remove('maintainer-row-' . $entityId)
            ->update('flash-messages', $flashHtml);
    }

    /**
     * Retorna la ruta al template de fila de tabla para Turbo Streams
     * 
     * Override en controllers específicos si necesitan template custom
     * 
     * @return string Ruta al template
     */
    protected function getTableRowTemplatePath(): string
    {
        return 'maintainers/_table_row.html.twig';
    }
}
