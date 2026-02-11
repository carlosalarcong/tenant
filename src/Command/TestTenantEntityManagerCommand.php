<?php

namespace App\Command;

use App\Service\TenantResolver;
use App\Service\CustomTenantConfigProvider;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Hakam\MultiTenancyBundle\Event\SwitchDbEvent;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[AsCommand(
    name: 'app:test-tenant-em',
    description: 'Prueba de TenantEntityManager y SwitchDbEvent'
)]
class TestTenantEntityManagerCommand extends Command
{
    public function __construct(
        private TenantResolver $tenantResolver,
        private CustomTenantConfigProvider $configProvider,
        private TenantEntityManager $tenantEntityManager,
        private EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('🧪 Prueba de TenantEntityManager y SwitchDbEvent');
        
        // 1. Listar tenants disponibles
        $io->section('1️⃣ Listando tenants activos desde tenant_central');
        try {
            $tenants = $this->tenantResolver->getAllActiveTenants();
            $io->table(
                ['Subdomain', 'Nombre'],
                array_map(fn($t) => [$t['subdomain'], $t['name']], $tenants)
            );
        } catch (\Exception $e) {
            $io->error('Error listando tenants: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // 2. Probar resolución de tenant específico
        $testSubdomain = 'lacolina';
        $io->section("2️⃣ Resolviendo tenant: {$testSubdomain}");
        
        try {
            $tenant = $this->tenantResolver->getTenantBySlug($testSubdomain);
            
            if (!$tenant) {
                $io->error("Tenant {$testSubdomain} no encontrado");
                return Command::FAILURE;
            }
            
            $io->success("Tenant encontrado:");
            $io->listing([
                "ID: {$tenant['id']}",
                "Nombre: {$tenant['name']}",
                "Database: {$tenant['database_name']}",
                "Host: {$tenant['host']}:{$tenant['host_port']}",
            ]);
        } catch (\Exception $e) {
            $io->error('Error resolviendo tenant: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // 3. Probar CustomTenantConfigProvider
        $io->section('3️⃣ Probando CustomTenantConfigProvider');
        
        try {
            $config = $this->configProvider->getTenantConnectionConfig($testSubdomain);
            
            $io->success("Configuración obtenida:");
            $io->listing([
                "Identifier: {$config->identifier}",
                "Driver: {$config->driver->value}",
                "DB Status: {$config->dbStatus->value}",
                "Database: {$config->dbname}",
                "Host: {$config->host}:{$config->port}",
                "User: {$config->user}",
            ]);
        } catch (\Exception $e) {
            $io->error('Error obteniendo config: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // 4. Probar SwitchDbEvent
        $io->section('4️⃣ Probando cambio de DB con SwitchDbEvent');
        
        try {
            $io->text("Disparando SwitchDbEvent para: {$testSubdomain}");
            
            $switchEvent = new SwitchDbEvent($testSubdomain);
            $this->eventDispatcher->dispatch($switchEvent);
            
            $io->success('SwitchDbEvent disparado correctamente');
        } catch (\Exception $e) {
            $io->error('Error disparando evento: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // 5. Verificar conexión actual del TenantEntityManager
        $io->section('5️⃣ Verificando conexión del TenantEntityManager');
        
        try {
            $connection = $this->tenantEntityManager->getConnection();
            
            // Ejecutar una query PRIMERO para forzar conexión
            $io->text('Ejecutando query para verificar la BD activa...');
            $result = $connection->executeQuery('SELECT DATABASE() as current_db');
            $currentDb = $result->fetchOne();
            
            $io->success("Base de datos activa: {$currentDb}");
            
            // Ahora mostrar tablas
            $result = $connection->executeQuery('SHOW TABLES');
            $tables = $result->fetchFirstColumn();
            
            $io->text("Tablas encontradas: " . count($tables));
            if (count($tables) > 0) {
                $io->listing(array_slice($tables, 0, 10));
            }
            
        } catch (\Exception $e) {
            $io->error('Error verificando conexión: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // 6. Probar con otro tenant que existe (lacolina ya fue probado en paso 4-5)
        $io->section("6️⃣ Probando cambio dinámico a melisa_template");
        
        try {
            $switchEvent = new SwitchDbEvent('template');  // Usar 'template' que apunta a melisa_template
            $this->eventDispatcher->dispatch($switchEvent);
            
            $connection = $this->tenantEntityManager->getConnection();
            
            // Verificar la BD activa mediante query
            $result = $connection->executeQuery('SELECT DATABASE() as current_db');
            $currentDb = $result->fetchOne();
            
            $io->success("Cambió exitosamente a: {$currentDb}");
            
            $result = $connection->executeQuery('SHOW TABLES');
            $tables = $result->fetchFirstColumn();
            
            $io->text("Tablas en {$currentDb}: " . count($tables));
            if (count($tables) > 0) {
                $io->listing(array_slice($tables, 0, 5));
            }
            
        } catch (\Exception $e) {
            $io->error('Error cambiando tenant: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $io->success('✅ Todas las pruebas pasaron exitosamente!');
        
        return Command::SUCCESS;
    }
}
