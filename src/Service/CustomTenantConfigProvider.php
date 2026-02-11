<?php

namespace App\Service;

use App\Service\TenantResolver;
use Hakam\MultiTenancyBundle\Config\TenantConnectionConfigDTO;
use Hakam\MultiTenancyBundle\Enum\DatabaseStatusEnum;
use Hakam\MultiTenancyBundle\Enum\DriverTypeEnum;
use Hakam\MultiTenancyBundle\Port\TenantConfigProviderInterface;

/**
 * Implementación personalizada de TenantConfigProvider
 * 
 * El bundle requiere esta interface para obtener configuración de conexiones.
 * Nuestra implementación usa TenantResolver para leer desde tenant_central.
 */
class CustomTenantConfigProvider implements TenantConfigProviderInterface
{
    public function __construct(
        private TenantResolver $tenantResolver
    ) {}

    /**
     * Obtiene configuración de conexión para un tenant
     * 
     * @param mixed $identifier ID del tenant (subdomain o ID numérico)
     * @return TenantConnectionConfigDTO Configuración para conexión
     */
    public function getTenantConnectionConfig(mixed $identifier): TenantConnectionConfigDTO
    {
        // El identifier puede ser subdomain (string) o ID (int)
        $tenant = null;
        
        // Primero intentar por ID si es numérico
        if (is_numeric($identifier)) {
            $tenant = $this->tenantResolver->getTenantById((int)$identifier);
        }
        
        // Si no se encontró, intentar por subdomain
        if (!$tenant) {
            $tenant = $this->tenantResolver->getTenantBySlug((string)$identifier);
        }
        
        if (!$tenant) {
            throw new \RuntimeException(
                "Tenant no encontrado en tenant_central: {$identifier}"
            );
        }

        // Convertir array de tenant a DTO del bundle usando fromArgs
        // Usar PostgreSQL y credenciales desde DATABASE_URL
        return TenantConnectionConfigDTO::fromArgs(
            identifier: $tenant['id'] ?? $identifier,
            driver: DriverTypeEnum::POSTGRES,
            dbStatus: DatabaseStatusEnum::tryFrom($tenant['database_status'] ?? 'DATABASE_CREATED') ?? DatabaseStatusEnum::DATABASE_CREATED,
            host: 'localhost',
            port: 5432,
            dbname: $tenant['database_name'],
            user: $_ENV['TENANT_DB_USER'] ?? 'tenant',
            password: $_ENV['TENANT_DB_PASSWORD'] ?? ''
        );
    }

    /**
     * Lista todas las configuraciones de tenants activos
     * 
     * @return TenantConnectionConfigDTO[]
     */
    public function getAllTenantsConfig(): array
    {
        $activeTenants = $this->tenantResolver->getAllActiveTenants();
        
        $configs = [];
        foreach ($activeTenants as $tenant) {
            $subdomain = $tenant['subdomain'];
            try {
                $configs[$subdomain] = $this->getTenantConnectionConfig($subdomain);
            } catch (\Exception $e) {
                // Log error pero continuar con otros tenants
                continue;
            }
        }
        
        return $configs;
    }
}
