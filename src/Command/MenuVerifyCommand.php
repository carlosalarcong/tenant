<?php

namespace App\Command;

use App\Service\Menu\MenuDefinition;
use App\Repository\Tenant\MenuItemRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Comando para verificar que el menú se carga correctamente desde la BD
 */
#[AsCommand(
    name: 'app:menu:verify',
    description: 'Verifica que el menú se cargue correctamente desde la base de datos'
)]
class MenuVerifyCommand extends Command
{
    public function __construct(
        private MenuDefinition $menuDefinition,
        private ?MenuItemRepository $menuRepository = null
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('🔍 Verificando sistema de menú');

        // Test 1: Verificar repository
        $io->section('Test 1: Verificar MenuItemRepository');
        if (!$this->menuRepository) {
            $io->error('❌ MenuItemRepository no está disponible');
            return Command::FAILURE;
        }
        $io->success('✅ MenuItemRepository disponible');

        // Test 2: Consultar BD directamente
        $io->section('Test 2: Consultar items desde BD');
        try {
            $menuItems = $this->menuRepository->getMenuWithChildren();
            $io->success(sprintf('✅ Se encontraron %d items de nivel superior en BD', count($menuItems)));

            if (!empty($menuItems)) {
                $io->writeln('Items de nivel superior:');
                foreach ($menuItems as $item) {
                    $childCount = $item->getChildren()->count();
                    $io->writeln(sprintf('  - %s (%d hijos)', $item->getLabel(), $childCount));
                }
            }
        } catch (\Exception $e) {
            $io->error('❌ Error consultando BD: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Test 3: Verificar MenuDefinition
        $io->section('Test 3: Verificar MenuDefinition::getMenuStructure()');
        try {
            $menuStructure = $this->menuDefinition->getMenuStructure('default');
            $io->success(sprintf('✅ MenuDefinition retornó %d items de nivel superior', count($menuStructure)));

            // Verificar origen del menú
            $firstItem = $menuStructure[0] ?? null;
            if ($firstItem) {
                $io->info('Primer item del menú:');
                $io->writeln(sprintf('  Name: %s', $firstItem['name']));
                $io->writeln(sprintf('  Label: %s', $firstItem['label']));
                $io->writeln(sprintf('  Route: %s', $firstItem['route'] ?? 'NULL'));
                $io->writeln(sprintf('  Icon: %s', $firstItem['icon'] ?? 'NULL'));
            }

            // Detectar origen (BD vs hardcoded)
            if (count($menuStructure) === count($menuItems)) {
                $io->success('✅ El menú parece estar cargándose desde BD (mismo número de items)');
            } else {
                $io->warning('⚠️  El menú podría estar usando fallback hardcoded (diferente número de items)');
            }

        } catch (\Exception $e) {
            $io->error('❌ Error en MenuDefinition: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Test 4: Verificar cache
        $io->section('Test 4: Estado del cache');
        $io->info('Para invalidar cache: php bin/console cache:pool:clear cache.app');

        $io->success('✅ Verificación completada');

        return Command::SUCCESS;
    }
}
