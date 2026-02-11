#!/usr/bin/env php
<?php
/**
 * Script para reemplazar textos hardcodeados en controladores de Maintainers
 * con llamadas al translator usando el dominio 'maintainers'
 */

$projectRoot = dirname(__DIR__);
$controllersPath = $projectRoot . '/src/Controller/Maintainers';

// Mapeo de textos español -> clave de traducción
$columnTranslations = [
    'Nombre' => "maintainers.columns.name",
    'Código' => "maintainers.columns.code",
    'Code' => "maintainers.columns.code",
    'Activo' => "maintainers.columns.active",
    'Estado' => "maintainers.columns.is_active",
    'Descripción' => "maintainers.columns.description",
    'RUT' => "RUT",
    'Cuenta Corriente' => "Cuenta Corriente",
    'Cantidad' => "Cantidad",
    'Orden' => "Orden",
    'Unidad' => "Unidad",
    'Agrupacion 1' => "Agrupacion 1",
    'Peso' => "Peso",
    'Temperatura' => "Temperatura",
    'Tipo de Origen' => "maintainers.columns.origin_type",
    'Tipo Campo' => "Tipo Campo",
    'Obligatorio' => "Obligatorio",
    'Codigo Interfaz' => "Codigo Interfaz",
    'Plazo Maximo' => "Plazo Maximo",
    'Es Al Dia' => "Es Al Dia",
    'Tipo Gratuidad' => "Tipo Gratuidad",
    'Sucursal' => "Sucursal",
    'Ciudad' => "Ciudad",
    'Región' => "Región",
    'Teléfono' => "Teléfono",
    'Email' => "Email",
    'Intervalos' => "Intervalos",
    'Cantidad Diaria' => "Cantidad Diaria",
    'Nivel de Instrucción' => "maintainers.columns.education_level",
    'Nivel Instrucción' => "maintainers.columns.education_level",
    'Unidad Tiempo' => "Unidad Tiempo",
    'Artículo' => "Artículo",
    'Proveedor' => "Proveedor",
    'Precio' => "Precio",
    'Número' => "maintainers.columns.number",
    'Código HL7' => "maintainers.columns.religion_code_hl7",
    'Es Efectivo' => "Es Efectivo",
    'Nombre Abreviado' => "Nombre Abreviado",
    'Nombre Genérico' => "Nombre Genérico",
    'Tipo' => "Tipo",
    'Sub Empresa' => "Sub Empresa",
    'Sentido' => "Sentido",
    'Cargo' => "Cargo",
    'Es CLP' => "Es CLP",
    'Abreviacion' => "Abreviacion",
    'Bodega' => "Bodega",
    'Stock Mínimo' => "Stock Mínimo",
    'Stock Crítico' => "Stock Crítico",
    'Stock Óptimo' => "Stock Óptimo",
    'Es Crítico' => "Es Crítico",
    'Especialidad' => "Especialidad",
    'Es Fármaco' => "Es Fármaco",
];

function processFile($file) {
    global $columnTranslations;
    
    $content = file_get_contents($file);
    $original = $content;
    $modified = false;
    
    // 1. Reemplazar headers hardcodeados en export()
    if (preg_match('/headers:\s*\[(.*?)\]/s', $content, $match)) {
        $headersStr = $match[1];
        $newHeaders = $headersStr;
        
        foreach ($columnTranslations as $spanish => $key) {
            if (strpos($key, 'maintainers.') === 0) {
                // Es una traducción
                $newHeaders = str_replace(
                    "'{$spanish}'",
                    "\$this->translator->trans('{$key}', [], 'maintainers')",
                    $newHeaders
                );
                $newHeaders = str_replace(
                    "\"{$spanish}\"",
                    "\$this->translator->trans('{$key}', [], 'maintainers')",
                    $newHeaders
                );
            }
        }
        
        if ($newHeaders !== $headersStr) {
            $content = str_replace(
                "headers: [{$headersStr}]",
                "headers: [{$newHeaders}]",
                $content
            );
            $modified = true;
        }
    }
    
    // 2. Reemplazar getColumns() array asociativo con traducciones
    if (preg_match('/protected function getColumns\(\):\s*array\s*\{[\s\S]*?return\s*\[([\s\S]*?)\];[\s\S]*?\}/m', $content, $match)) {
        $columnsContent = $match[1];
        $newColumnsContent = $columnsContent;
        
        foreach ($columnTranslations as $spanish => $key) {
            if (strpos($key, 'maintainers.') === 0) {
                $newColumnsContent = preg_replace(
                    "/=>\s*['\"]" . preg_quote($spanish, '/') . "['\"]/",
                    "=> \$this->translator->trans('{$key}', [], 'maintainers')",
                    $newColumnsContent
                );
            }
        }
        
        if ($newColumnsContent !== $columnsContent) {
            $content = str_replace($columnsContent, $newColumnsContent, $content);
            $modified = true;
        }
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
        if (processFile($file->getPathname())) {
            $count++;
        }
    }
}

echo "\n✅ Total de archivos actualizados: {$count}\n";
echo "🔄 Ejecuta: php bin/console cache:clear\n";
