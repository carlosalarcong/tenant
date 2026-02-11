<?php

namespace App\Controller\Admin;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\MenuItem;
use App\Repository\Tenant\MenuItemRepository;
use App\Service\Menu\MenuDefinition;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/menu-config', name: 'admin_menu_config_')]
class MenuConfigController extends AbstractTenantAwareController
{
    public function __construct(
        #[Autowire(service: 'doctrine.orm.tenant_entity_manager')]
        private EntityManagerInterface $entityManager,
        private MenuItemRepository $menuRepository,
        private MenuDefinition $menuDefinition,
        private LoggerInterface $logger
    ) {}

    /**
     * Lista todos los items del menú para administración
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $menuItems = $this->menuRepository->getAllForAdmin();

        return $this->render('admin/menu_config/index.html.twig', [
            'menu_items' => $menuItems,
            'tenant_name' => $this->getTenantName(),
        ]);
    }

    /**
     * Formulario para crear nuevo item del menú
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $menuItem = new MenuItem();
        
        if ($request->isMethod('POST')) {
            $this->processForm($menuItem, $request);
            
            $this->entityManager->persist($menuItem);
            $this->entityManager->flush();
            
            // Invalidar caché
            $this->menuDefinition->invalidateCache($this->getTenantId($request));
            
            $this->addFlash('success', 'Item del menú creado exitosamente');
            return $this->redirectToRoute('admin_menu_config_index');
        }

        $allItems = $this->menuRepository->getAllForAdmin();
        
        return $this->render('admin/menu_config/new.html.twig', [
            'menu_item' => $menuItem,
            'all_items' => $allItems,
            'tenant_name' => $this->getTenantName(),
        ]);
    }

    /**
     * Formulario para editar item del menú existente
     */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MenuItem $menuItem): Response
    {
        if ($request->isMethod('POST')) {
            $this->processForm($menuItem, $request);
            
            $menuItem->setUpdatedAt(new \DateTime());
            $this->entityManager->flush();
            
            // Invalidar caché
            $this->menuDefinition->invalidateCache($this->getTenantId($request));
            
            $this->addFlash('success', 'Item del menú actualizado exitosamente');
            return $this->redirectToRoute('admin_menu_config_index');
        }

        $allItems = $this->menuRepository->getAllForAdmin();
        
        return $this->render('admin/menu_config/edit.html.twig', [
            'menu_item' => $menuItem,
            'all_items' => $allItems,
            'tenant_name' => $this->getTenantName(),
        ]);
    }

    /**
     * Eliminar item del menú
     */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, MenuItem $menuItem): Response
    {
        $position = $menuItem->getPosition();
        $parent = $menuItem->getParent();
        
        $this->entityManager->remove($menuItem);
        $this->entityManager->flush();
        
        // Reordenar items restantes
        $this->menuRepository->reorderAfterDelete($position, $parent);
        
        // Invalidar caché
        $this->menuDefinition->invalidateCache($this->getTenantId($request));
        
        $this->addFlash('success', 'Item del menú eliminado exitosamente');
        return $this->redirectToRoute('admin_menu_config_index');
    }

    /**
     * Toggle habilitado/deshabilitado
     */
    #[Route('/{id}/toggle', name: 'toggle', methods: ['POST'])]
    public function toggle(Request $request, MenuItem $menuItem): Response
    {
        $menuItem->setEnabled(!$menuItem->isEnabled());
        $menuItem->setUpdatedAt(new \DateTime());
        
        $this->entityManager->flush();
        
        // Invalidar caché
        $this->menuDefinition->invalidateCache($this->getTenantId($request));
        
        $this->addFlash('success', 'Estado del item actualizado');
        return $this->redirectToRoute('admin_menu_config_index');
    }

    /**
     * Invalida manualmente el caché del menú
     */
    #[Route('/clear-cache', name: 'clear_cache', methods: ['POST'])]
    public function clearCache(Request $request): Response
    {
        $this->menuDefinition->invalidateCache($this->getTenantId($request));
        
        $this->addFlash('success', 'Caché del menú invalidado exitosamente');
        return $this->redirectToRoute('admin_menu_config_index');
    }

    /**
     * Actualiza la jerarquía del menú vía AJAX (drag & drop)
     */
    #[Route('/{id}/update-hierarchy', name: 'update_hierarchy', methods: ['POST'])]
    public function updateHierarchy(Request $request, MenuItem $menuItem): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return new JsonResponse(['success' => false, 'error' => 'Datos inválidos'], 400);
            }

            $newParentId = $data['parent_id'] ?? null;
            $newPosition = $data['position'] ?? 0;
            
            // Obtener el nuevo padre si existe
            $newParent = null;
            if ($newParentId) {
                $newParent = $this->menuRepository->find($newParentId);
                if (!$newParent) {
                    return new JsonResponse(['success' => false, 'error' => 'Padre no encontrado'], 404);
                }
            }
            
            // Validar referencias circulares
            if ($this->isCircularReference($menuItem, $newParent)) {
                return new JsonResponse([
                    'success' => false, 
                    'error' => 'No se puede crear una referencia circular'
                ], 400);
            }
            
            // Actualizar parent_id y position
            $menuItem->setParent($newParent);
            $menuItem->setPosition($newPosition);
            $menuItem->setUpdatedAt(new \DateTime());
            
            // Forzar que Doctrine detecte los cambios
            $this->entityManager->persist($menuItem);
            $this->entityManager->flush();

            // Reordenar hermanos (items con mismo padre)
            $siblings = $newParent
                ? $newParent->getChildren()->toArray()
                : $this->menuRepository->findBy(['parent' => null]);

            usort($siblings, fn($a, $b) => $a->getPosition() <=> $b->getPosition());

            $position = 0;
            foreach ($siblings as $sibling) {
                $sibling->setPosition($position++);
            }

            $this->entityManager->flush();

            // Invalidar caché
            $this->menuDefinition->invalidateCache($this->getTenantId($request));

            return new JsonResponse(['success' => true]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false, 
                'error' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene la estructura completa del menú como JSON
     */
    #[Route('/api/tree', name: 'api_tree', methods: ['GET'])]
    public function getMenuTree(): JsonResponse
    {
        try {
            $menuItems = $this->menuRepository->getMenuWithChildren();
            
            $tree = [];
            foreach ($menuItems as $item) {
                if ($item->getParent() === null) {
                    $tree[] = $this->serializeMenuItem($item, 1);
                }
            }
            
            return new JsonResponse($tree);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false, 
                'error' => 'Error al obtener árbol: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agrega un hijo directo a un item
     */
    #[Route('/{id}/add-child', name: 'add_child', methods: ['POST'])]
    public function addChild(Request $request, MenuItem $menuItem): JsonResponse
    {
        try {
            $timestamp = time();
            
            // Obtener siguiente posición disponible
            $children = $menuItem->getChildren();
            $nextPosition = $children->count();
            
            // Crear nuevo item
            $newItem = new MenuItem();
            $newItem->setName('nuevo_item_' . $timestamp);
            $newItem->setLabel('Nuevo Item');
            $newItem->setParent($menuItem);
            $newItem->setPosition($nextPosition);
            $newItem->setEnabled(true);
            $newItem->setVisibleInSidebar(true);
            $newItem->setRequiresAuth(true);
            
            $this->entityManager->persist($newItem);
            $this->entityManager->flush();
            
            // Invalidar caché
            $this->menuDefinition->invalidateCache($this->getTenantId($request));
            
            return new JsonResponse([
                'success' => true,
                'item' => [
                    'id' => $newItem->getId(),
                    'name' => $newItem->getName(),
                    'label' => $newItem->getLabel(),
                    'position' => $newItem->getPosition()
                ]
            ]);
            
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Error al crear hijo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesa el formulario y actualiza el MenuItem
     */
    private function processForm(MenuItem $menuItem, Request $request): void
    {
        $menuItem->setName($request->request->get('name'));
        $menuItem->setLabel($request->request->get('label'));
        $menuItem->setRoute($request->request->get('route') ?: null);
        $menuItem->setIcon($request->request->get('icon') ?: null);
        $menuItem->setModule($request->request->get('module') ?: null);
        $menuItem->setPosition((int) $request->request->get('position', 0));
        $menuItem->setEnabled($request->request->has('enabled'));
        $menuItem->setVisibleInSidebar($request->request->has('visible_in_sidebar'));
        $menuItem->setRequiresAuth($request->request->has('requires_auth'));
        
        // Parent
        $parentId = $request->request->get('parent_id');
        if ($parentId) {
            $parent = $this->menuRepository->find($parentId);
            $menuItem->setParent($parent);
        } else {
            $menuItem->setParent(null);
        }
        
        // Required roles (JSON)
        $rolesInput = $request->request->get('required_roles');
        if ($rolesInput) {
            $roles = array_filter(array_map('trim', explode(',', $rolesInput)));
            $menuItem->setRequiredRoles($roles);
        } else {
            $menuItem->setRequiredRoles(null);
        }
    }

    /**
     * Obtiene el tenant ID desde la sesión
     */
    private function getTenantId(Request $request): string
    {
        return (string) ($request->getSession()->get('tenant_id') ?? 'default');
    }

    /**
     * Valida referencias circulares
     */
    private function isCircularReference(MenuItem $item, ?MenuItem $newParent): bool
    {
        // Si no hay nuevo padre, está OK (se mueve a raíz)
        if ($newParent === null) {
            return false;
        }
        
        // No puede ser padre de sí mismo
        if ($newParent->getId() === $item->getId()) {
            return true;
        }
        
        // No puede ser hijo de ninguno de sus descendientes
        return $this->isDescendantOf($newParent, $item);
    }

    /**
     * Verifica si un item es descendiente de otro (recursivo)
     */
    private function isDescendantOf(MenuItem $item, MenuItem $ancestor): bool
    {
        $parent = $item->getParent();
        
        if ($parent === null) {
            return false;
        }
        
        if ($parent->getId() === $ancestor->getId()) {
            return true;
        }
        
        return $this->isDescendantOf($parent, $ancestor);
    }

    /**
     * Serializa un MenuItem a array (recursivo)
     */
    private function serializeMenuItem(MenuItem $item, int $level = 1): array
    {
        $children = $item->getChildren()->toArray();
        
        // Ordenar hijos por position
        usort($children, fn($a, $b) => $a->getPosition() <=> $b->getPosition());
        
        return [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'label' => $item->getLabel(),
            'route' => $item->getRoute(),
            'icon' => $item->getIcon(),
            'module' => $item->getModule(),
            'position' => $item->getPosition(),
            'enabled' => $item->isEnabled(),
            'level' => $level,
            'hasChildren' => count($children) > 0,
            'childrenCount' => count($children),
            'children' => array_map(
                fn($child) => $this->serializeMenuItem($child, $level + 1),
                $children
            )
        ];
    }
}
