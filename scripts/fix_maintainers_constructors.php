#!/usr/bin/env php
<?php
/**
 * Script para agregar TranslatorInterface a todos los controladores de Maintainers
 * que faltan este parámetro en el constructor
 */

$projectRoot = dirname(__DIR__);
$controllersPath = $projectRoot . '/src/Controller/Maintainers';

function fixController($file) {
    $content = file_get_contents($file);
    $original = $content;
    $modified = false;
    
    // 1. Verificar si ya tiene TranslatorInterface en los use
    if (!preg_match('/use\s+Symfony\\\\Contracts\\\\Translation\\\\TranslatorInterface;/', $content)) {
        // Agregar el use statement después de Route
        $content = preg_replace(
            '/(use\s+Symfony\\\\Component\\\\Routing\\\\Attribute\\\\Route;)/',
            "$1\nuse Symfony\Contracts\Translation\TranslatorInterface;",
            $content
        );
        $modified = true;
    }
    
    // 2. Verificar si el constructor necesita actualización
    if (preg_match('/parent::__construct\(\$tenantEntityManager\);/', $content)) {
        // Agregar TranslatorInterface al constructor
        $content = preg_replace(
            '/(public function __construct\([^)]+)(TenantEntityManager \$tenantEntityManager,[\s\n]+ExportService \$exportService)\s*\)\s*\{/',
            '$1$2,\n        TranslatorInterface $translator\n    ) {',
            $content
        );
        
        // Actualizar llamada al parent
        $content = str_replace(
            'parent::__construct($tenantEntityManager);',
            'parent::__construct($tenantEntityManager, $translator);',
            $content
        );
        
        $modified = true;
    }
    
    if ($modified) {
        file_put_contents($file, $content);
        echo "✓ Actualizado: " . basename(dirname($file)) . "/" . basename($file) . "\n";
        return true;
    }
    
    return false;
}

// Procesar todos los archivos recursivamente
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

echo "\n✅ Total de archivos actualizados: {$count}\n";
echo "🔄 Ejecuta: php bin/console cache:clear\n";
