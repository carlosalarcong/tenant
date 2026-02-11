<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:tenant:permission-profile',
    description: 'Gestiona el perfil de permisos del tenant y visualiza configuración actual'
)]
class TenantPermissionProfileCommand extends Command
{
    public function __construct(
        private Connection $connection
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('tenant_db', InputArgument::REQUIRED, 'Nombre de la BD del tenant (ej: lacolina)')
            ->addArgument('action', InputArgument::OPTIONAL, 'Acción: show|set', 'show')
            ->addArgument('profile', InputArgument::OPTIONAL, 'Tipo de perfil: collaborative|restrictive|custom')
            ->setHelp(<<<'HELP'
Este comando permite visualizar y cambiar el perfil de permisos del tenant.

Ejemplos:
  # Ver configuración actual del tenant
  php bin/console app:tenant:permission-profile lacolina show

  # Cambiar a perfil collaborative
  php bin/console app:tenant:permission-profile lacolina set collaborative

  # Cambiar a perfil restrictive
  php bin/console app:tenant:permission-profile lacolina set restrictive

  # Cambiar a perfil custom (usa overrides de BD)
  php bin/console app:tenant:permission-profile lacolina set custom
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tenantDb = $input->getArgument('tenant_db');
        $action = $input->getArgument('action');

        if ($action === 'show') {
            return $this->showCurrentProfile($io, $tenantDb);
        }

        if ($action === 'set') {
            $profile = $input->getArgument('profile');
            if (!$profile) {
                $io->error('Debes especificar el tipo de perfil: collaborative|restrictive|custom');
                return Command::FAILURE;
            }
            return $this->setProfile($io, $tenantDb, $profile);
        }

        $io->error("Acción no válida. Usa 'show' o 'set'");
        return Command::FAILURE;
    }

    private function showCurrentProfile(SymfonyStyle $io, string $tenantDb): int
    {
        try {
            // Consultar directamente la BD del tenant
            $profile = $this->connection->executeQuery(
                "SELECT * FROM {$tenantDb}.tenant_permission_profile ORDER BY id ASC LIMIT 1"
            )->fetchAssociative();

            if (!$profile) {
                $io->warning('No hay perfil de permisos configurado. Se usará "collaborative" por defecto.');
                return Command::SUCCESS;
            }

            $io->title("Configuración de Permisos del Tenant: {$tenantDb}");
            
            $io->section('Perfil Actual');
            $io->table(
                ['Campo', 'Valor'],
                [
                    ['Tipo de Perfil', $profile['profile_type']],
                    ['Creado', $profile['created_at']],
                    ['Actualizado', $profile['updated_at'] ?? 'N/A'],
                ]
            );

            // Mostrar descripción del perfil
            $descriptions = [
                'collaborative' => '✓ Múltiples roles pueden acceder a diferentes módulos (clínicas grandes)',
                'restrictive' => '✗ Solo administradores tienen acceso (clínicas pequeñas)',
                'custom' => '⚙ Configuración personalizada desde base de datos',
            ];
            
            $io->text($descriptions[$profile['profile_type']] ?? 'Perfil desconocido');

            // Si es custom, mostrar los overrides
            if ($profile['profile_type'] === 'custom') {
                $io->section('Overrides de Módulos (Custom)');
                $overrides = $this->connection->executeQuery(
                    "SELECT * FROM {$tenantDb}.tenant_module_permission_override WHERE is_active = 1 ORDER BY module_name"
                )->fetchAllAssociative();
                
                if (empty($overrides)) {
                    $io->warning('No hay overrides configurados. Todos los módulos requerirán ROLE_ADMIN.');
                } else {
                    $rows = [];
                    foreach ($overrides as $override) {
                        $rows[] = [
                            $override['module_name'],
                            $override['required_roles'],
                            $override['description'] ?? 'N/A',
                        ];
                    }
                    
                    $io->table(['Módulo', 'Roles Requeridos', 'Descripción'], $rows);
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error al consultar la base de datos: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function setProfile(SymfonyStyle $io, string $tenantDb, string $profileType): int
    {
        $validProfiles = ['collaborative', 'restrictive', 'custom'];
        
        if (!in_array($profileType, $validProfiles)) {
            $io->error("Perfil no válido. Opciones: " . implode(', ', $validProfiles));
            return Command::FAILURE;
        }

        try {
            // Obtener perfil actual
            $profile = $this->connection->executeQuery(
                "SELECT * FROM {$tenantDb}.tenant_permission_profile ORDER BY id ASC LIMIT 1"
            )->fetchAssociative();
            
            if (!$profile) {
                $io->error('No hay perfil configurado. Ejecuta primero la migración y crea un perfil.');
                return Command::FAILURE;
            }

            $oldType = $profile['profile_type'];
            
            // Actualizar perfil
            $this->connection->executeStatement(
                "UPDATE {$tenantDb}.tenant_permission_profile SET profile_type = ?, updated_at = NOW() WHERE id = ?",
                [$profileType, $profile['id']]
            );

            $io->success("Perfil cambiado de '{$oldType}' a '{$profileType}'");
            
            // Mostrar información del nuevo perfil
            $descriptions = [
                'collaborative' => 'Ahora múltiples roles pueden acceder según configuración predefinida',
                'restrictive' => 'Ahora solo ROLE_ADMIN tiene acceso a la mayoría de módulos',
                'custom' => 'Ahora se usarán los overrides de la tabla tenant_module_permission_override',
            ];
            
            $io->note($descriptions[$profileType]);

            if ($profileType === 'custom') {
                $io->section('Recordatorio');
                $io->text([
                    'Con el perfil "custom", debes configurar los módulos en la tabla:',
                    '  tenant_module_permission_override',
                    '',
                    'Ejemplo SQL:',
                    "  INSERT INTO {$tenantDb}.tenant_module_permission_override ",
                    "    (module_name, required_roles, is_active, created_at)",
                    "  VALUES ",
                    "    ('patients', '[\"ROLE_ADMIN\", \"ROLE_DOCTOR\"]', 1, NOW());",
                ]);
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error al actualizar la base de datos: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
