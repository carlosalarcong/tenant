<?php

namespace App\EventListener;

use App\Service\TenantResolver;
use App\Service\TenantContext;
use Hakam\MultiTenancyBundle\Event\SwitchDbEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Psr\Log\LoggerInterface;

/**
 * Listener mejorado que usa SwitchDbEvent del bundle
 * 
 * Flujo:
 * 1. Detecta tenant desde subdomain (usa TenantResolver)
 * 2. Guarda en TenantContext
 * 3. Dispara SwitchDbEvent (el bundle hace el cambio de conexión)
 */
class TenantDatabaseSwitchListener implements EventSubscriberInterface
{
    private ?string $currentTenantId = null;

    public function __construct(
        private TenantResolver $tenantResolver,
        private TenantContext $tenantContext,
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            // Alta prioridad para ejecutar antes que otros listeners
            KernelEvents::REQUEST => ['onKernelRequest', 1000],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $host = $request->getHost();
        
        $this->logger->info('🔍 TenantDatabaseSwitchListener: Detectando tenant', ['host' => $host]);

        // Extraer subdomain del host
        $subdomain = $this->extractSubdomainFromHost($host);
        
        if (!$subdomain) {
            $this->logger->warning('⚠️ No se pudo extraer subdomain del host', ['host' => $host]);
            return;
        }

        $this->logger->info('📍 Subdomain detectado', ['subdomain' => $subdomain]);

        // Resolver tenant usando TenantResolver (lee de tenant_central)
        try {
            $tenant = $this->tenantResolver->getTenantBySlug($subdomain);
            
            if (!$tenant) {
                $this->logger->warning('⚠️ Tenant no encontrado en tenant_central', ['subdomain' => $subdomain]);
                return;
            }

            $this->logger->info('✅ Tenant resuelto desde tenant_central', [
                'subdomain' => $subdomain,
                'database' => $tenant['database_name'] ?? 'unknown'
            ]);

            // Guardar en TenantContext (para uso en controladores)
            $this->tenantContext->setCurrentTenant($tenant);

            // Solo cambiar DB si es diferente al actual
            $tenantId = (string)($tenant['id'] ?? $subdomain);
            if ($tenantId !== $this->currentTenantId) {
                $this->switchDatabase($tenant);
                $this->currentTenantId = $tenantId;
            }

        } catch (\Exception $e) {
            $this->logger->error('❌ Error resolviendo tenant', [
                'subdomain' => $subdomain,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function extractSubdomainFromHost(string $host): ?string
    {
        // Extraer primer segmento del host
        // ej: lacolina.melisaupgrade.prod -> lacolina
        $parts = explode('.', $host);

        if (count($parts) >= 2) {
            $subdomain = $parts[0];

            // Omitir subdominios reservados
            if (!in_array($subdomain, ['www', 'api', 'admin', 'localhost'])) {
                return $subdomain;
            }
        }

        // Fallback para desarrollo local
        return $this->getFallbackSubdomain();
    }

    private function getFallbackSubdomain(): ?string
    {
        // Para desarrollo o testing
        return $_ENV['TENANT_DEFAULT_FALLBACK'] ?? null;
    }

    private function switchDatabase(array $tenant): void
    {
        $databaseName = $tenant['database_name'] ?? null;
        
        if (!$databaseName) {
            $this->logger->warning('⚠️ Tenant sin database_name configurado', ['tenant' => $tenant]);
            return;
        }

        $this->logger->info('🔄 Disparando SwitchDbEvent del bundle', [
            'database' => $databaseName,
            'tenant_id' => $tenant['id'] ?? 'unknown'
        ]);

        // Usar el identificador del tenant (id) como dbIndex
        // El bundle usa esto para consultar su TenantConfigProvider
        // Pero nosotros NO usamos ese provider, así que adaptaremos
        $dbIndex = (string)($tenant['id'] ?? $databaseName);

        // Disparar evento del bundle
        $switchEvent = new SwitchDbEvent($dbIndex);
        $this->eventDispatcher->dispatch($switchEvent);

        $this->logger->info('✅ SwitchDbEvent disparado correctamente');
    }
}
