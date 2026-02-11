<?php

namespace App\Service;

use App\Entity\Tenant;
use App\Service\TenantContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Psr\Log\LoggerInterface;

/**
 * Resolvedor dinámico de controladores multi-tenant
 * Maneja la resolución de controladores específicos por tenant con fallbacks
 */
class DynamicControllerResolver
{
    public function __construct(
        private TenantContext $tenantContext,
        private LoggerInterface $logger
    ) {}

    /**
     * Resuelve un controlador según el patrón y el tenant actual
     */
    public function resolve(Request $request): callable
    {
        $route = $request->attributes->get('_route');
        $routeParams = $request->attributes->get('_route_params', []);
        
        // Obtener configuración de la ruta
        $controllerPattern = $routeParams['_controller_pattern'] ?? null;
        $fallbackController = $routeParams['_fallback_controller'] ?? null;
        $tenantSpecific = $routeParams['_tenant_specific'] ?? [];
        
        if (!$controllerPattern) {
            throw new NotFoundHttpException('Patrón de controlador no definido para la ruta: ' . $route);
        }
        
        $tenant = $this->tenantContext->getCurrentTenant();
        $tenantKey = $tenant ? ucfirst($tenant['subdomain']) : 'Default';
        
        $this->logger->debug('Resolviendo controlador', [
            'route' => $route,
            'tenant' => $tenantKey,
            'pattern' => $controllerPattern,
            'fallback' => $fallbackController
        ]);
        
        // Verificar si la ruta es específica para ciertos tenants
        if (!empty($tenantSpecific) && !in_array($tenantKey, $tenantSpecific)) {
            if ($fallbackController) {
                return $this->createCallable($fallbackController);
            }
            throw new NotFoundHttpException('Ruta no disponible para el tenant actual: ' . $tenantKey);
        }
        
        // Intentar resolver el controlador específico del tenant
        $specificController = str_replace('{tenant}', $tenantKey, $controllerPattern);
        
        if ($this->controllerExists($specificController)) {
            $this->logger->debug('Controlador específico encontrado', ['controller' => $specificController]);
            return $this->createCallable($specificController);
        }
        
        // Fallback al controlador por defecto
        if ($fallbackController && $this->controllerExists($fallbackController)) {
            $this->logger->debug('Usando controlador fallback', ['controller' => $fallbackController]);
            return $this->createCallable($fallbackController);
        }
        
        throw new NotFoundHttpException('Controlador no encontrado para la ruta: ' . $route);
    }

    /**
     * Resuelve dinámicamente cualquier controlador basado en tenant y estructura
     * Maneja todos los casos: Dashboard, Mantenedores, etc. de forma unificada
     */
    public function resolveController(string $subdomain, string $controller, string $action = 'index'): string
    {
        $tenantKey = ucfirst($subdomain);
        $controllerType = ucfirst($controller); // Dashboard, Mantenedores, etc.
        
        $this->logger->debug('Iniciando resolución dinámica de controlador', [
            'subdomain' => $subdomain,
            'tenant_key' => $tenantKey,
            'controller_type' => $controllerType,
            'action' => $action
        ]);
        
        // Patrones de búsqueda en orden de prioridad específico para cada tipo
        $searchPatterns = $this->buildSearchPatterns($tenantKey, $controllerType, $action);
        
        // Buscar el primer controlador que exista y tenga el método
        foreach ($searchPatterns as $index => $controllerPath) {
            if ($this->validateController($controllerPath, $action)) {
                $this->logger->info('✅ Controlador dinámico resuelto exitosamente', [
                    'tenant' => $subdomain,
                    'controller_type' => $controllerType,
                    'action' => $action,
                    'resolved_path' => $controllerPath,
                    'pattern_index' => $index + 1,
                    'total_patterns' => count($searchPatterns)
                ]);
                
                return $controllerPath . '::' . $action;
            }
        }
        
        // Si no encuentra nada, usar el fallback más apropiado
        $fallbackController = $this->getFallbackController($controllerType, $action);
        
        $this->logger->error('❌ No se pudo resolver controlador dinámico - usando fallback', [
            'tenant' => $subdomain,
            'controller_type' => $controllerType,
            'action' => $action,
            'patterns_tried' => count($searchPatterns),
            'fallback_used' => $fallbackController
        ]);
        
        return $fallbackController;
    }

    /**
     * Construye los patrones de búsqueda universales para cualquier tipo de controlador
     */
    private function buildSearchPatterns(string $tenantKey, string $controllerType, string $action): array
    {
        $controllerName = $controllerType . 'Controller';
        
        // Patrones universales que funcionan para cualquier tipo de controlador
        return [
            // 1. Controlador específico del tenant en estructura jerárquica tipo-específica
            "App\\Controller\\{$controllerType}\\{$tenantKey}\\DefaultController",
            
            // 2. Controlador específico del tenant en estructura jerárquica con nombre específico
            "App\\Controller\\{$controllerType}\\{$tenantKey}\\{$controllerName}",
            
            // 3. Controlador específico del tenant en carpeta propia
            "App\\Controller\\{$tenantKey}\\{$controllerName}",
            
            // 4. DefaultController del tenant (puede manejar cualquier tipo)
            "App\\Controller\\{$tenantKey}\\DefaultController",
            
            // 5. Controlador default en estructura jerárquica tipo-específica
            "App\\Controller\\{$controllerType}\\Default\\DefaultController",
            
            // 6. Controlador default en estructura jerárquica con nombre específico
            "App\\Controller\\{$controllerType}\\Default\\{$controllerName}",
            
            // 7. Controlador base específico del tipo
            "App\\Controller\\{$controllerName}",
            
            // 8. DefaultController universal (fallback absoluto)
            "App\\Controller\\DefaultController"
        ];
    }

    /**
     * Valida si un controlador existe y tiene el método requerido
     */
    private function validateController(string $controllerPath, string $action): bool
    {
        return class_exists($controllerPath) && method_exists($controllerPath, $action);
    }

    /**
     * Obtiene el controlador fallback más apropiado según el tipo - completamente dinámico
     */
    private function getFallbackController(string $controllerType, string $action): string
    {
        // Intentar fallbacks universales en orden de prioridad
        $universalFallbacks = [
            // 1. DefaultController en estructura jerárquica del tipo
            "App\\Controller\\{$controllerType}\\Default\\DefaultController::{$action}",
            
            // 2. Controlador específico del tipo base
            "App\\Controller\\{$controllerType}Controller::{$action}",
            
            // 3. DefaultController universal
            "App\\Controller\\DefaultController::index"
        ];
        
        foreach ($universalFallbacks as $fallback) {
            [$class, $method] = explode('::', $fallback);
            
            if ($this->validateController($class, $method)) {
                return $fallback;
            }
        }
        
        // Fallback absoluto si nada más funciona
        return 'App\\Controller\\DefaultController::index';
    }

    /**
     * Resuelve controlador dinámicamente como lo haría un ControllerSubscriber
     * Permite resolución automática basada en el tenant sin configuración manual
     */
    public function resolveControllerFromRoute(string $originalController, string $tenantSubdomain): string
    {
        // Analizar el controlador original
        if (strpos($originalController, '::') === false) {
            return $originalController; // No es un controlador válido
        }
        
        [$originalClass, $method] = explode('::', $originalController, 2);
        
        // Descomponer el namespace del controlador original
        $classParts = explode('\\', $originalClass);
        
        if (count($classParts) < 3) {
            return $originalController; // Estructura no válida
        }
        
        // Extraer información base
        $baseNamespace = $classParts[0] . '\\' . $classParts[1]; // App\Controller
        $controllerType = $classParts[2] ?? 'Default'; // Dashboard, Mantenedores, etc.
        $controllerName = end($classParts); // DefaultController, PacientesController, etc.
        
        $tenantKey = ucfirst($tenantSubdomain);
        
        // Generar patrones de búsqueda dinámicos
        $dynamicPatterns = [
            // 1. Inyectar tenant en la posición 3 (después de Controller)
            // App\Controller\Dashboard\Melisahospital\DefaultController
            "{$baseNamespace}\\{$controllerType}\\{$tenantKey}\\{$controllerName}",
            
            // 2. Reemplazar la posición 3 completamente por el tenant
            // App\Controller\Melisahospital\DefaultController
            "{$baseNamespace}\\{$tenantKey}\\{$controllerName}",
            
            // 3. Mantener estructura pero cambiar a Default si el tenant no existe
            // App\Controller\Dashboard\Default\DefaultController
            "{$baseNamespace}\\{$controllerType}\\Default\\{$controllerName}",
            
            // 4. Controlador original sin modificar
            $originalClass
        ];
        
        // Buscar el primer patrón que funcione
        foreach ($dynamicPatterns as $pattern) {
            if (class_exists($pattern) && method_exists($pattern, $method)) {
                $this->logger->debug('Controlador resuelto dinámicamente desde ruta', [
                    'original' => $originalController,
                    'tenant' => $tenantSubdomain,
                    'resolved' => $pattern . '::' . $method,
                    'pattern' => $pattern
                ]);
                
                return $pattern . '::' . $method;
            }
        }
        
        $this->logger->warning('No se pudo resolver controlador dinámico desde ruta', [
            'original' => $originalController,
            'tenant' => $tenantSubdomain,
            'patterns_tried' => $dynamicPatterns
        ]);
        
        // Fallback al controlador original
        return $originalController;
    }

    /**
     * Crea un callable desde un string de controlador
     */
    private function createCallable(string $controllerString): callable
    {
        if (strpos($controllerString, '::') === false) {
            throw new \InvalidArgumentException('Formato de controlador inválido: ' . $controllerString);
        }
        
        [$class, $method] = explode('::', $controllerString, 2);
        
        if (!class_exists($class)) {
            throw new NotFoundHttpException('Clase de controlador no encontrada: ' . $class);
        }
        
        if (!method_exists($class, $method)) {
            throw new NotFoundHttpException('Método no encontrado: ' . $controllerString);
        }
        
        return [$class, $method];
    }

    /**
     * Verifica si un controlador existe
     */
    private function controllerExists(string $controllerString): bool
    {
        if (strpos($controllerString, '::') === false) {
            return false;
        }
        
        [$class, $method] = explode('::', $controllerString, 2);
        
        return class_exists($class) && method_exists($class, $method);
    }
    
    /**
     * Obtiene controladores disponibles para un subdomain
     */
    public function getAvailableControllers(string $subdomain): array
    {
        $controllersPath = __DIR__ . '/../Controller/' . ucfirst($subdomain);
        
        if (!is_dir($controllersPath)) {
            return ['default']; // Solo DefaultController disponible
        }
        
        $controllers = [];
        $files = glob($controllersPath . '/*Controller.php');
        
        foreach ($files as $file) {
            $controllerName = basename($file, 'Controller.php');
            $controllers[] = strtolower($controllerName);
        }
        
        return $controllers;
    }

    /**
     * Verifica si un controlador específico existe para un tenant
     */
    public function controllerExistsForTenant(string $subdomain, string $controller): bool
    {
        $tenantNamespace = ucfirst($subdomain);
        $controllerClass = ucfirst($controller) . 'Controller';
        
        $customControllerPath = sprintf(
            'App\\Controller\\%s\\%s',
            $tenantNamespace,
            $controllerClass
        );
        
        return class_exists($customControllerPath);
    }
    
    /**
     * Obtiene la ruta de un controlador
     */
    public function getControllerPath(string $subdomain, string $controller): string
    {
        $tenantNamespace = ucfirst($subdomain);
        $controllerClass = ucfirst($controller) . 'Controller';
        
        return sprintf('App\\Controller\\%s\\%s', $tenantNamespace, $controllerClass);
    }

    /**
     * Obtiene información de depuración sobre la resolución de controladores
     */
    public function getDebugInfo(string $subdomain): array
    {
        return [
            'tenant' => $subdomain,
            'tenant_key' => ucfirst($subdomain),
            'available_controllers' => $this->getAvailableControllers($subdomain),
            'dashboard_controller_exists' => $this->controllerExistsForTenant($subdomain, 'dashboard'),
        ];
    }

    /**
     * Obtiene datos del tenant con fallbacks robustos
     * Centraliza toda la lógica de obtención de tenant para evitar repetición
     */
    public function getCurrentTenantWithFallback(): ?array
    {
        // Primero intentar desde TenantContext
        $tenant = $this->tenantContext->getCurrentTenant();
        
        if ($tenant && isset($tenant['name'])) {
            return $tenant;
        }
        
        // Si TenantContext no tiene datos, intentar reconstruir desde request/sesión
        // Esto se maneja automáticamente en TenantContext::getCurrentTenant()
        // ya que tiene la lógica de fallback mejorada
        
        return $tenant;
    }

    /**
     * Obtiene datos del tenant con garantía de valores válidos
     * Nunca retorna null, siempre tiene fallbacks por defecto
     */
    public function getGuaranteedTenant(): array
    {
        $tenant = $this->getCurrentTenantWithFallback();
        
        // Si aún es null o no tiene datos mínimos, usar valores por defecto
        if (!$tenant || !isset($tenant['name'])) {
            return [
                'id' => 1,
                'name' => 'Melisa Clinic',
                'subdomain' => 'default',
                'database_name' => '',
                'rut_empresa' => null,
                'host' => 'localhost',
                'host_port' => 3306,
                'db_user' => $_ENV['TENANT_DB_USER'] ?? 'tenant',
                'db_password' => $_ENV['TENANT_DB_PASSWORD'] ?? ''
            ];
        }
        
        return $tenant;
    }

    /**
     * Genera la ruta apropiada para redirección - completamente dinámico
     * Funciona para dashboard, mantenedores, reportes, etc.
     */
    public function generateRedirectRoute(string $subdomain, string $controllerType = 'dashboard'): string
    {
        $tenantKey = ucfirst($subdomain);
        $controllerPath = $this->resolveController($subdomain, $controllerType, 'index');
        
        // Extraer solo la clase sin el método
        [$class] = explode('::', $controllerPath);
        
        $this->logger->debug('Generando ruta de redirección universal', [
            'subdomain' => $subdomain,
            'controller_type' => $controllerType,
            'resolved_class' => $class,
            'full_controller' => $controllerPath
        ]);
        
        // Generar nombre de ruta dinámicamente basado en la estructura del controlador
        $routeName = $this->generateRouteNameFromController($class, $subdomain, $controllerType);
        
        $this->logger->debug('Ruta generada dinámicamente', [
            'route_name' => $routeName,
            'controller_class' => $class
        ]);
        
        return $routeName;
    }

    /**
     * Genera nombre de ruta dinámicamente basado en el controlador resuelto
     */
    private function generateRouteNameFromController(string $controllerClass, string $subdomain, string $controllerType): string
    {
        $controllerTypeLower = strtolower($controllerType);
        $subdomainLower = strtolower($subdomain);
        
        // Analizar la estructura del controlador para determinar la ruta apropiada
        $classParts = explode('\\', $controllerClass);
        
        // Si es un controlador Default en estructura jerárquica del tipo
        if (in_array('Default', $classParts) && in_array(ucfirst($controllerType), $classParts)) {
            return "app_{$controllerTypeLower}_default";
        }
        
        // Si contiene el tenant en la estructura jerárquica
        if (in_array(ucfirst($subdomain), $classParts)) {
            return "app_{$controllerTypeLower}_{$subdomainLower}";
        }
        
        // Si es un controlador específico del tenant
        if (str_contains($controllerClass, ucfirst($subdomain))) {
            return "app_{$controllerTypeLower}_{$subdomainLower}";
        }
        
        // Fallback: intentar ruta específica del tenant
        $specificRoute = "app_{$controllerTypeLower}_{$subdomainLower}";
        
        // Si no existe, usar ruta default del tipo
        return "app_{$controllerTypeLower}_default";
    }


}