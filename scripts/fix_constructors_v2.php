#!/usr/bin/env php
<?php
/**
 * Script para agregar TranslatorInterface a todos los controladores correctamente
 */

$projectRoot = dirname(__DIR__);
$controllersPath = $projectRoot . '/src/Controller/Maintainers';

function fixController($file) {
    $content = file_get_contents($file);
    $original = $content;
    $modified = false;
    
    // 1. Agregar use TranslatorInterface si no existe
    if (!preg_match('/use\s+Symfony\\\\Contracts\\\\Translation\\\\TranslatorInterface;/', $content)) {
        $content = preg_replace(
            '/(use\s+Symfony\\\\Component\\\\Routing\\\\Attribute\\\\Route;)/',
            "$1\nuse Symfony\\Contracts\\Translation\\TranslatorInterface;",
            $content
        );
        $modified = true;
    }
    
    // 2. Actualizar constructor solo si tiene el patrón viejo
    if (preg_match('/parent::__construct\(\$tenantEntityManager\);/', $content)) {
        // Encontrar el constructor y reemplazarlo
        $pattern = '/(public function __construct\(\s*\n\s*private [^,]+,\s*\n\s*TenantEntityManager \$tenantEntityManager,\s*\n\s*ExportService \$exportService\s*\n\s*\)\s*\{)/s';
        
        $replacement = function($matches) {
            // Extraer la primera línea con el parámetro privado
            preg_match('/(private [^,]+,)/', $matches[0], $firstParam);
            
            return "public function __construct(\n        " . trim($firstParam[1]) . "\n" .
                   "        TenantEntityManager \$tenantEntityManager,\n" .
                   "        ExportService \$exportService,\n" .
                   "        TranslatorInterface \$translator\n" .
                   "    ) {";
        };
        
        $content = preg_replace_callback($pattern, $replacement, $content);
        
        // Actualizar llamada al parent
        $content = str_replace(
            'parent::__construct($tenantEntityManager);',
            'parent::__construct($tenantEntityManager, $translator);',
            $content
        );
        
        $modified = true;
    }
    
    if ($modified && $content !== $original) {
        file_put_contents($file, $content);
        echo "✓ " . basename(dirname($file)) . "/" . basename($file) . "\n";
        return true;
    }
    
    return false;
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($controllersPath)
);

$count = 0;
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        if (fixController($file->getPathname())) {
            $count++;
        }
    }
}

echo "\n✅ Archivos actualizados: {$count}\n";
