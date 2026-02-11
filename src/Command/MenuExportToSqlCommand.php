<?php

namespace App\Command;

use App\Service\Menu\MenuDefinition;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Comando para exportar el menú hardcoded a SQL
 *
 * Genera un script SQL con INSERT statements para poblar la tabla menu_items
 * desde la estructura hardcoded definida en MenuDefinition.
 */
#[AsCommand(
    name: 'app:menu:export-to-sql',
    description: 'Exporta el menú hardcoded a un script SQL para insertar en la base de datos'
)]
class MenuExportToSqlCommand extends Command
{
    private int $currentId = 1;
    private array $idMap = [];

    public function __construct(
        private MenuDefinition $menuDefinition
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'truncate',
            't',
            InputOption::VALUE_NONE,
            'Incluir TRUNCATE TABLE antes de los INSERT'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('📤 Exportando menú hardcoded a SQL');

        try {
            // Obtener estructura hardcoded
            $menuStructure = $this->menuDefinition->getMenuStructure('default');

            $io->info(sprintf('Se encontraron %d items de menú de nivel superior', count($menuStructure)));

            // Generar SQL
            $sql = $this->generateSqlHeader($input->getOption('truncate'));
            $sql .= $this->generateInserts($menuStructure);
            $sql .= $this->generateSqlFooter();

            // Mostrar SQL generado
            $output->writeln($sql);

            $io->success('✅ Script SQL generado exitosamente');
            $io->note('Puedes redirigir la salida a un archivo: php bin/console app:menu:export-to-sql > menu_migration.sql');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('❌ Error generando SQL: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function generateSqlHeader(bool $includeTruncate): string
    {
        $sql = "-- ====================================================================\n";
        $sql .= "-- Migración del menú hardcoded a base de datos\n";
        $sql .= "-- Generado el: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- ====================================================================\n\n";

        if ($includeTruncate) {
            $sql .= "-- Limpiar tabla existente\n";
            $sql .= "TRUNCATE TABLE menu_items RESTART IDENTITY CASCADE;\n\n";
        }

        $sql .= "-- Insertar items del menú\n";
        $sql .= "BEGIN;\n\n";

        return $sql;
    }

    private function generateSqlFooter(): string
    {
        $sql = "\n-- Actualizar secuencia\n";
        $sql .= "SELECT setval('menu_items_id_seq', (SELECT MAX(id) FROM menu_items));\n\n";
        $sql .= "COMMIT;\n\n";
        $sql .= "-- ====================================================================\n";
        $sql .= "-- Total de items insertados: " . ($this->currentId - 1) . "\n";
        $sql .= "-- ====================================================================\n";

        return $sql;
    }

    private function generateInserts(array $items, ?int $parentId = null, int $position = 0): string
    {
        $sql = '';

        foreach ($items as $index => $item) {
            $currentPosition = $position > 0 ? $position : $index + 1;
            $itemId = $this->currentId++;

            // Guardar ID para referencias futuras
            $this->idMap[$item['name']] = $itemId;

            // Escapar valores
            $name = $this->escape($item['name']);
            $label = $this->escape($item['label']);
            $route = isset($item['route']) ? $this->escape($item['route']) : 'NULL';
            $icon = isset($item['icon']) ? $this->escape($item['icon']) : 'NULL';
            $module = isset($item['module']) ? $this->escape($item['module']) : 'NULL';
            $parentIdStr = $parentId ? (string)$parentId : 'NULL';

            // Generar INSERT
            $sql .= sprintf(
                "INSERT INTO menu_items (id, name, label, route, icon, module, parent_id, position, enabled, visible_in_sidebar, requires_auth, required_roles, created_at) VALUES\n" .
                "    (%d, %s, %s, %s, %s, %s, %s, %d, true, true, true, '[\"ROLE_USER\"]', NOW());\n",
                $itemId,
                $name,
                $label,
                $route,
                $icon,
                $module,
                $parentIdStr,
                $currentPosition
            );

            // Procesar hijos recursivamente
            if (!empty($item['children'])) {
                $sql .= "\n-- Hijos de '{$item['label']}'\n";
                $sql .= $this->generateInserts($item['children'], $itemId, 0);
                $sql .= "\n";
            }
        }

        return $sql;
    }

    private function escape(string $value): string
    {
        // Escapar comillas simples para PostgreSQL
        $escaped = str_replace("'", "''", $value);
        return "'" . $escaped . "'";
    }
}
