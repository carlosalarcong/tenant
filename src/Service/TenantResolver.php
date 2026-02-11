<?php

namespace App\Service;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\DefaultSchemaManagerFactory;
use Symfony\Component\HttpFoundation\Request;

class TenantResolver
{
    private array $centralDbConfig;
    private Configuration $dbalConfig;

    /**
     * Constructor con inyección de parámetros desde .env
     * 
     * @param string $centralDbUrl URL de conexión de la BD central (desde DATABASE_URL)
     */
    public function __construct(
        private readonly string $centralDbUrl
    ) {
        // Configuración DBAL con Schema Manager Factory
        $this->dbalConfig = new Configuration();
        $this->dbalConfig->setSchemaManagerFactory(new DefaultSchemaManagerFactory());

        // Parsear DATABASE_URL para obtener componentes
        // Formato: mysql://user:password@host:port/database
        $this->centralDbConfig = $this->parseDatabaseUrl($centralDbUrl);
    }

    /**
     * Parsea la URL de base de datos en array de configuración
     * 
     * @param string $url URL en formato mysql://user:pass@host:port/dbname
     * @return array Configuración para Doctrine DBAL
     */
    private function parseDatabaseUrl(string $url): array
    {
        $parsed = parse_url($url);
        
        // Extraer parámetros de query (serverVersion, charset, etc.)
        $queryParams = [];
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $queryParams);
        }

        return [
            'host' => $parsed['host'] ?? 'localhost',
            'port' => $parsed['port'] ?? 5432, // PostgreSQL default port
            'dbname' => ltrim($parsed['path'] ?? '', '/'),
            'user' => $parsed['user'] ?? '',
            'password' => $parsed['pass'] ?? '',
            'driver' => 'pdo_pgsql', // PostgreSQL driver
            'charset' => $queryParams['charset'] ?? 'utf8',
        ];
    }

    /**
     * Resuelve el tenant desde el subdomain de la URL
     */
    public function resolveTenantFromRequest(Request $request): ?array
    {
        $host = $request->getHost();
        
        // Extraer subdomain de la URL
        // Ejemplo: lacolina.melisaupgrade.prod → lacolina
        $parts = explode('.', $host);
        
        if (count($parts) < 2) {
            return null; // No hay subdomain
        }
        
        $slug = $parts[0];
        
        return $this->getTenantBySlug($slug);
    }

    /**
     * Obtiene los datos del tenant desde la BD central por slug
     */
    public function getTenantBySlug(string $slug): ?array
    {
        try {
            $connection = DriverManager::getConnection($this->centralDbConfig, $this->dbalConfig);

            // Query compatible con PostgreSQL (is_active = true)
            $query = '
                SELECT id, name, slug, subdomain, database_name, database_status, is_active
                FROM tenant_db
                WHERE slug = ? AND is_active = true
            ';
            
            $result = $connection->executeQuery($query, [$slug]);
            return $result->fetchAssociative() ?: null;
            
        } catch (\Exception $e) {
            throw new \Exception('Error resolviendo tenant: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene los datos del tenant desde la BD central por ID
     */
    public function getTenantById(int $id): ?array
    {
        try {
            $connection = DriverManager::getConnection($this->centralDbConfig, $this->dbalConfig);
            
            // Query compatible con PostgreSQL (is_active = true)
            $query = '
                SELECT id, name, slug, subdomain, database_name, database_status, is_active
                FROM tenant_db
                WHERE id = ? AND is_active = true
            ';
            
            $result = $connection->executeQuery($query, [$id]);
            return $result->fetchAssociative() ?: null;
            
        } catch (\Exception $e) {
            throw new \Exception('Error resolviendo tenant por ID: ' . $e->getMessage());
        }
    }

    /**
     * Crea conexión a la base de datos del tenant
     */
    public function createTenantConnection(array $tenant): \Doctrine\DBAL\Connection
    {
        $tenantDbConfig = [
            'host' => $tenant['host'],
            'port' => $tenant['host_port'],
            'dbname' => $tenant['database_name'],
            'user' => $tenant['db_user'],
            'password' => $tenant['db_password'],
            'driver' => 'pdo_pgsql', // PostgreSQL driver
        ];

        return DriverManager::getConnection($tenantDbConfig, $this->dbalConfig);
    }

    /**
     * Lista todos los tenants activos (para el selector)
     */
    public function getAllActiveTenants(): array
    {
        try {
            $connection = DriverManager::getConnection($this->centralDbConfig, $this->dbalConfig);
            
            // Query compatible con PostgreSQL (is_active = true)
            $query = 'SELECT subdomain, name FROM tenant_db WHERE is_active = true ORDER BY name';
            $result = $connection->executeQuery($query);
            
            return $result->fetchAllAssociative();
            
        } catch (\Exception $e) {
            throw new \Exception('Error obteniendo tenants: ' . $e->getMessage());
        }
    }
}