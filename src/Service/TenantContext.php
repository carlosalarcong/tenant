<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TenantContext
{
    private ?array $currentTenant = null;
    private ?string $currentSubdomain = null;
    private RequestStack $requestStack;
    private array $centralDbConfig;
    
    /**
     * Constructor con inyección de DATABASE_URL
     * 
     * @param RequestStack $requestStack Stack de requests de Symfony
     * @param string $centralDbUrl URL de conexión desde .env
     */
    public function __construct(
        RequestStack $requestStack,
        private readonly string $centralDbUrl
    ) {
        $this->requestStack = $requestStack;
        $this->centralDbConfig = $this->parseDatabaseUrl($centralDbUrl);
    }
    
    /**
     * Parsea la URL de base de datos en array de configuración
     * 
     * @param string $url URL en formato mysql://user:pass@host:port/dbname
     * @return array Configuración con host, port, user, password
     */
    private function parseDatabaseUrl(string $url): array
    {
        $parsed = parse_url($url);
        
        return [
            'host' => $parsed['host'] ?? 'localhost',
            'port' => $parsed['port'] ?? 3306,
            'user' => $parsed['user'] ?? '',
            'password' => $parsed['pass'] ?? '',
        ];
    }
    
    public function setCurrentTenant(?array $tenant): void
    {
        $this->currentTenant = $tenant;
        $this->currentSubdomain = $tenant['subdomain'] ?? null;
    }
    
    public function getCurrentTenant(): ?array
    {
        if ($this->currentTenant) {
            return $this->currentTenant;
        }
        
        // Intentar obtener de la sesión
        $request = $this->requestStack->getCurrentRequest();
        if ($request && $request->hasSession()) {
            $session = $request->getSession();
            
            // Primero intentar obtener el array completo del tenant
            $tenantData = $session->get('tenant');
            
            if ($tenantData && is_array($tenantData)) {
                $this->setCurrentTenant($tenantData);
                return $this->currentTenant;
            }
            
            // Si no existe como array, intentar reconstruir desde claves individuales
            if ($session->has('tenant_id') || $session->has('tenant_name') || $session->has('tenant_slug')) {
                $reconstructedTenant = [
                    'id' => $session->get('tenant_id'),
                    'name' => $session->get('tenant_name', 'Clinic'),
                    'subdomain' => $session->get('tenant_slug', 'default'),
                    'database_name' => $session->get('database_name', ''),
                    'rut_empresa' => null, // Datos adicionales pueden ser null
                    'host' => $this->centralDbConfig['host'],
                    'host_port' => $this->centralDbConfig['port'],
                    'db_user' => $this->centralDbConfig['user'],
                    'db_password' => $this->centralDbConfig['password']
                ];
                
                $this->setCurrentTenant($reconstructedTenant);
                return $this->currentTenant;
            }
        }
        
        return null;
    }
    
    public function getCurrentSubdomain(): ?string
    {
        if ($this->currentSubdomain) {
            return $this->currentSubdomain;
        }
        
        $tenantData = $this->getCurrentTenant();
        return $tenantData['subdomain'] ?? null;
    }
    
    public function hasCurrentTenant(): bool
    {
        return $this->getCurrentTenant() !== null;
    }
    
    public function getCurrentTenantName(): ?string
    {
        $tenantData = $this->getCurrentTenant();
        return $tenantData['name'] ?? null;
    }
    
    public function getCurrentDatabaseName(): ?string
    {
        $tenantData = $this->getCurrentTenant();
        return $tenantData['database_name'] ?? null;
    }
}
