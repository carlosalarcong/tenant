<?php

namespace App\Service\Menu;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Servicio que construye el menú de navegación del sidebar
 *
 * Obtiene la estructura del menú desde MenuDefinition y determina qué items
 * deben estar expandidos basándose en la ruta actual.
 */
class MenuBuilder
{
    public function __construct(
        private RequestStack $requestStack,
        private MenuDefinition $menuDefinition,
        private ?LoggerInterface $logger = null
    ) {}

    /**
     * Construye el menú completo del sistema
     */
    public function buildMenu(): array
    {
        // Obtener tenant ID desde la sesión o request
        $tenantId = $this->getTenantId();
        return $this->menuDefinition->getMenuStructure($tenantId);
    }

    /**
     * Obtiene el tenant ID desde la sesión actual.
     */
    private function getTenantId(): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return 'default';
        }

        $session = $request->getSession();
        return (string) ($session->get('tenant_id') ?? 'default');
    }

    /**
     * Determina si un item debe estar expandido basándose en la ruta actual
     */
    public function shouldExpand(array $item): bool
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return false;
        }

        $currentPath = $request->getPathInfo();
        $currentRoute = $request->attributes->get('_route');

        // Expandir si la ruta actual coincide
        if (isset($item['route']) && $currentRoute === $item['route']) {
            return true;
        }

        // Expandir si algún hijo debe estar expandido
        if (!empty($item['children'])) {
            foreach ($item['children'] as $child) {
                if ($this->shouldExpand($child)) {
                    return true;
                }
            }
        }

        // Expandir mantenedores si estamos en cualquier ruta de maintenance
        if ($item['name'] === 'mantenedores' && str_contains($currentPath, '/maintainers')) {
            return true;
        }

        // Expandir subcategorías de maintenance
        if (isset($item['name']) && in_array($item['name'], ['maintenance_basic', 'maintenance_clinical', 'maintenance_geographic', 'maintenance_structure'])) {
            if (str_contains($currentPath, '/maintainers')) {
                // Verificar si algún hijo tiene la ruta activa
                foreach ($item['children'] ?? [] as $child) {
                    if (isset($child['route']) && $currentRoute === $child['route']) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Marca el menú con información de expansión y activación
     */
    public function enrichMenu(array $menu): array
    {
        $request = $this->requestStack->getCurrentRequest();
        $currentRoute = $request?->attributes->get('_route');

        return array_map(function($item) use ($currentRoute) {
            $item['is_active'] = isset($item['route']) && $item['route'] === $currentRoute;
            $item['should_expand'] = $this->shouldExpand($item);

            if (!empty($item['children'])) {
                $item['children'] = $this->enrichMenu($item['children']);
            }

            return $item;
        }, $menu);
    }

    /**
     * Método seguro para construir el menú con manejo de excepciones.
     * Usado por MenuExtension para lazy loading (DESPUÉS de que el tenant esté establecido).
     *
     * @return array Menú enriquecido con información de estado
     */
    public function buildMenuSafe(): array
    {
        try {
            $tenantId = $this->getTenantId();

            $this->logger?->info('🔧 MenuBuilder: Construyendo menú', [
                'tenant_id' => $tenantId,
                'has_request' => $this->requestStack->getCurrentRequest() !== null
            ]);

            // Construir menú desde BD (con cache)
            $menu = $this->menuDefinition->getMenuStructure($tenantId);

            // Enriquecer con información de estado (active, expand)
            $enrichedMenu = $this->enrichMenu($menu);

            $this->logger?->info('✅ MenuBuilder: Menú construido exitosamente', [
                'items_count' => count($enrichedMenu)
            ]);

            return $enrichedMenu;

        } catch (\Exception $e) {
            $this->logger?->error('❌ MenuBuilder: Error construyendo menú, usando hardcoded', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Fallback a menú hardcoded (sin enriquecer)
            // Mejor mostrar un menú básico que fallar completamente
            return $this->menuDefinition->getMenuStructure('default');
        }
    }
}
