<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controlador base que automáticamente tiene acceso al contexto del tenant
 * Sin necesidad de inyectar nada en el constructor
 * 
 * El TenantContextInjector se encarga de inyectar automáticamente:
 * - $this->tenant (array con datos del tenant)
 * - $this->tenantSubdomain (string)
 * - $this->tenantName (string)
 */
abstract class AbstractTenantAwareController extends AbstractController
{
    /**
     * Datos completos del tenant actual
     * Inyectado automáticamente por TenantContextInjector
     */
    protected ?array $tenant = null;

    /**
     * Subdomain del tenant actual (ej: "hospital")
     * Inyectado automáticamente por TenantContextInjector
     */
    protected ?string $tenantSubdomain = null;

    /**
     * Nombre del tenant actual (ej: "Melisa Hospital")
     * Inyectado automáticamente por TenantContextInjector
     */
    protected ?string $tenantName = null;

    /**
     * Obtiene los datos del tenant actual
     * Garantizado que siempre retorna un array con valores por defecto si no hay tenant
     */
    protected function getTenant(): array
    {
        return $this->tenant ?? [
            'id' => 1,
            'name' => 'Default Tenant',
            'subdomain' => 'default',
            'database_name' => '',
            'is_active' => 1
        ];
    }

    /**
     * Obtiene el subdomain del tenant actual
     */
    protected function getTenantSubdomain(): string
    {
        return $this->tenantSubdomain ?? 'default';
    }

    /**
     * Obtiene el nombre del tenant actual
     */
    protected function getTenantName(): string
    {
        return $this->tenantName ?? 'Default Tenant';
    }

    /**
     * Verifica si hay un tenant válido cargado
     */
    protected function hasTenant(): bool
    {
        return $this->tenant !== null && isset($this->tenant['id']);
    }

    /**
     * Obtiene el directorio de templates del tenant actual
     * Útil para renderizado dinámico de plantillas específicas por tenant
     */
    protected function getTenantTemplateDirectory(): string
    {
        return strtolower($this->tenantSubdomain ?? 'default');
    }

    /**
     * Renderiza una plantilla buscando primero versión específica del tenant
     * con fallback automático a versión default
     * 
     * Ejemplo: renderTenantTemplate('dashboard/index.html.twig', [...])
     * Busca: 1) hospital/dashboard/index.html.twig
     *        2) default/dashboard/index.html.twig
     */
    protected function renderTenantTemplate(string $template, array $parameters = []): Response
    {
        $tenantDir = $this->getTenantTemplateDirectory();
        $tenantTemplate = $tenantDir . '/' . $template;
        
        // Si existe plantilla personalizada del tenant, usarla
        if ($this->container->get('twig')->getLoader()->exists($tenantTemplate)) {
            return $this->render($tenantTemplate, $parameters);
        }
        
        // Fallback a plantilla por defecto
        return $this->render('default/' . $template, $parameters);
    }

    /**
     * Renderiza una plantilla inyectando automáticamente datos del tenant
     * Combina renderTenantTemplate + datos del tenant automáticos
     * 
     * El desarrollador no necesita pasar tenant, tenant_name, subdomain manualmente
     */
    protected function renderWithTenant(string $template, array $parameters = []): Response
    {
        // Agregar automáticamente datos del tenant a los parámetros
        $parameters['tenant'] = $this->getTenant();
        $parameters['tenant_name'] = $this->getTenantName();
        $parameters['subdomain'] = $this->getTenantSubdomain();
        
        return $this->renderTenantTemplate($template, $parameters);
    }
}
