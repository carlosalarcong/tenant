#!/usr/bin/env php
<?php
/**
 * Script para convertir getColumns() con traducciones inline
 */

$projectRoot = dirname(__DIR__);
$controllersPath = $projectRoot . '/src/Controller/Maintainers';

$translationMap = [
    'Id' => 'id',
    'Nombre' => 'name',
    'Código' => 'code',
    'Codigo' => 'code',
    'Descripción' => 'description',
    'Descripcion' => 'description',
    'Estado' => 'is_active',
    'Activo' => 'is_active',
    'Tipo' => 'type',
    'Precio' => 'price',
    'Controlado' => 'controlled',
    'Crítico' => 'critical',
    'Venta' => 'sale',
];

function convertGetColumns($file, $translationMap) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Buscar getColumns(): array con su contenido
    $pattern = '/(protected function getColumns\(\): array\s*\{)(.*?)(\n\s*\})/s';
    
    if (!preg_match($pattern, $content, $matches)) {
        return false;
    }
    
    $columnsContent = $matches[2];
    
    // Extraer pares 'key' => 'Valor Español'
    preg_match_all("/'(\w+(?:\.\w+)?)'\\s*=>\\s*'([^']+)'/", $columnsContent, $pairs);
    
    if (empty($pairs[1])) {
        return false;
    }
    
    $hasChanges = false;
    $newPairs = [];
    
    foreach ($pairs[1] as $idx => $key) {
        $spanishValue = $pairs[2][$idx];
        
        // Buscar traducción
        if (isset($translationMap[$spanishValue])) {
            $translationKey = $translationMap[$spanishValue];
            $newPairs[] = "        '$key' => \$this->translator->trans('maintainers.columns.$translationKey', [], 'maintainers')";
            $hasChanges = true;
        } else {
            // Mantener original si no hay traducción
            $newPairs[] = "        '$key' => '$spanishValue'";
        }
    }
    
    if (!$hasChanges) {
        return false;
    }
    
    // Reconstruir getColumns
    $newColumnsBody = "\n        return [\n" . implode(",\n", $newPairs) . "\n    ];\n    ";
    
    $newContent = preg_replace(
        $pattern,
        "$1$newColumnsBody$3",
        $content,
        1
    );
    
    if ($newContent !== $originalContent) {
        file_put_contents($file, $newContent);
        return true;
    }
    
    return false;
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($controllersPath, RecursiveDirectoryIterator::SKIP_DOTS)
);

$converted = 0;

echo "Converting getColumns() to use translations...\n\n";

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $relativePath = str_replace($controllersPath . '/', '', $file->getPathname());
        
        if (convertGetColumns($file->getPathname(), $translationMap)) {
            echo "✓ $relativePath\n";
            $converted++;
        }
    }
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Converted: $converted files\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
