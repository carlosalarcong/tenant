<?php

namespace App\Command;

use App\Service\TenantResolver;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:tenant:migrate-all',
    description: 'Wrapper simplificado para migrar todos los tenants usando el bundle'
)]
class TenantMigrateAllCommand extends Command
{
    public function __construct(
        private TenantResolver $tenantResolver
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('tenant', InputArgument::OPTIONAL, 'Subdomain del tenant (ej: lacolina). Si se omite, migra todos.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Mostrar qué se ejecutaría sin aplicar cambios')
            ->setHelp('
Wrapper simplificado para tenant:migrations:migrate del bundle.

<info>Ejemplos:</info>

  <comment># Migrar todos los tenants activos</comment>
  php bin/console app:tenant:migrate-all

  <comment># Migrar solo un tenant específico</comment>
  php bin/console app:tenant:migrate-all lacolina

  <comment># Ver qué migraciones se aplicarían (dry-run)</comment>
  php bin/console app:tenant:migrate-all lacolina --dry-run

<info>Comandos del bundle disponibles:</info>
  tenant:migrations:migrate    - Migrar tenants
  tenant:migrations:diff       - Generar migraciones  
  tenant:database:create       - Crear BD de tenant
  tenant:fixtures:load         - Cargar fixtures

<info>Comandos legacy (deprecated):</info>
  app:migrate-tenant-legacy    - Comando antiguo con cleanup
  app:migrations-tenant-legacy - Comando antiguo para diff
            ');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tenantSubdomain = $input->getArgument('tenant');
        $dryRun = $input->getOption('dry-run');

        $io->title('🚀 Migración de Tenants usando Bundle');

        try {
            // Si se especificó un tenant, migrar solo ese
            if ($tenantSubdomain) {
                return $this->migrateSingleTenant($io, $tenantSubdomain, $dryRun);
            }

            // Si no, migrar todos los tenants activos
            return $this->migrateAllTenants($io, $dryRun);

        } catch (\Exception $e) {
            $io->error('Error durante migración: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function migrateSingleTenant(SymfonyStyle $io, string $subdomain, bool $dryRun): int
    {
        $io->section("Migrando tenant: {$subdomain}");

        // Verificar que el tenant existe
        $tenant = $this->tenantResolver->getTenantBySlug($subdomain);
        
        if (!$tenant) {
            $io->error("Tenant '{$subdomain}' no encontrado en tenant_central");
            return Command::FAILURE;
        }

        $io->info("Base de datos: {$tenant['database_name']}");

        if ($dryRun) {
            $io->warning('[DRY RUN] No se aplicarán cambios reales');
            $io->note("Ejecutar sin --dry-run para aplicar migraciones");
            return Command::SUCCESS;
        }

        // Ejecutar comando doctrine:migrations:migrate directamente con la BD del tenant
        // Usando variables de entorno para cambiar la conexión
        $command = $this->getApplication()->find('doctrine:migrations:migrate');
        
        $arguments = [
            '--no-interaction' => true,
            '--em' => 'tenant', // Usar el entity manager del tenant
        ];

        $greetInput = new ArrayInput($arguments);
        
        // Antes de ejecutar, disparar evento para cambiar BD
        $io->text("🔄 Cambiando conexión a BD del tenant...");
        
        // Disparar SwitchDbEvent para cambiar a la BD del tenant
        $eventDispatcher = $this->getApplication()->getKernel()->getContainer()->get('event_dispatcher');
        $switchEvent = new \Hakam\MultiTenancyBundle\Event\SwitchDbEvent((string)$tenant['id']);
        $eventDispatcher->dispatch($switchEvent);
        
        $io->text("✅ Conexión cambiada a: {$tenant['database_name']}");
        $io->text("🚀 Ejecutando migraciones...");
        
        $returnCode = $command->run($greetInput, $io);

        if ($returnCode === Command::SUCCESS) {
            $io->success("✅ Tenant '{$subdomain}' migrado exitosamente");
        } else {
            $io->error("❌ Error migrando tenant '{$subdomain}'");
        }

        return $returnCode;
    }

    private function migrateAllTenants(SymfonyStyle $io, bool $dryRun): int
    {
        $io->section('Migrando todos los tenants activos');

        // Obtener lista de tenants
        $tenants = $this->tenantResolver->getAllActiveTenants();

        if (empty($tenants)) {
            $io->warning('No se encontraron tenants activos');
            return Command::SUCCESS;
        }

        $io->info('Tenants encontrados: ' . count($tenants));
        $io->table(
            ['Subdomain', 'Nombre'],
            array_map(fn($t) => [$t['subdomain'], $t['name']], $tenants)
        );

        if ($dryRun) {
            $io->warning('[DRY RUN] No se aplicarán cambios reales');
            $io->note('Usar sin --dry-run para aplicar migraciones');
            return Command::SUCCESS;
        }

        if (!$io->confirm('¿Migrar todos estos tenants?', false)) {
            $io->info('Operación cancelada');
            return Command::SUCCESS;
        }

        $io->progressStart(count($tenants));

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($tenants as $tenant) {
            $subdomain = $tenant['subdomain'];
            
            try {
                $tenantData = $this->tenantResolver->getTenantBySlug($subdomain);
                
                if (!$tenantData) {
                    $results['failed']++;
                    $results['errors'][] = "{$subdomain}: No se pudo obtener datos completos";
                    $io->progressAdvance();
                    continue;
                }

                // Ejecutar migración para este tenant
                $command = $this->getApplication()->find('tenant:migrations:migrate');
                $arguments = [
                    '--dbid' => (string)$tenantData['id'],
                    '--no-interaction' => true,
                ];

                $greetInput = new ArrayInput($arguments);
                
                // Usar modo silencioso para no saturar consola
                $greetInput->setInteractive(false);
                
                $returnCode = $command->run($greetInput, $io);

                if ($returnCode === Command::SUCCESS) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "{$subdomain}: Comando retornó código {$returnCode}";
                }

            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "{$subdomain}: {$e->getMessage()}";
            }

            $io->progressAdvance();
        }

        $io->progressFinish();

        // Mostrar resumen
        $io->section('📊 Resumen de Migración');
        $io->table(
            ['Métrica', 'Cantidad'],
            [
                ['Tenants procesados', count($tenants)],
                ['✅ Exitosos', $results['success']],
                ['❌ Fallidos', $results['failed']],
            ]
        );

        if (!empty($results['errors'])) {
            $io->section('❌ Errores encontrados');
            foreach ($results['errors'] as $error) {
                $io->writeln("  • {$error}");
            }
        }

        if ($results['failed'] > 0) {
            $io->warning("Se completó con {$results['failed']} errores");
            return Command::FAILURE;
        }

        $io->success('✅ Todos los tenants migrados exitosamente');
        return Command::SUCCESS;
    }
}
