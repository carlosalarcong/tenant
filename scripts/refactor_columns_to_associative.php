#!/usr/bin/env php
<?php

/**
 * Script para refactorizar getColumns() a formato asociativo
 * Convierte: return ['name', 'code', 'isActive'];
 * A: return ['name' => 'Nombre', 'code' => 'Código', 'isActive' => 'Estado'];
 */

$columnLabels = [
    'name' => 'Nombre',
    'code' => 'Código',
    'isActive' => 'Estado',
    'description' => 'Descripción',
    'number' => 'Número',
    'educationLevel' => 'Nivel Instrucción',
    'originType' => 'Tipo Origen',
    'religionCodeHl7' => 'Código HL7',
    'maritalStatusCodeHl7' => 'Código HL7',
    'parent.name' => 'Padre',
    'branch.name' => 'Sucursal',
    'differenceDirection.name' => 'Sentido',
    'gratuityType.name' => 'Tipo Gratuidad',
    'creditCardType.name' => 'Tipo Tarjeta',
    'paymentMethodType.name' => 'Tipo',
    'siiCode' => 'Código SII',
    'isDte' => 'DTE',
    'isLogistics' => 'Logística',
    'isCash' => 'Efectivo',
    'isClp' => 'CLP',
    'interfaceCode' => 'Cód. Interfaz',
    'maxTerm' => 'Plazo Máx.',
    'isUpToDate' => 'Al Día',
    'abbreviation' => 'Abreviatura',
    'rut' => 'RUT',
    'currentAccount' => 'Cuenta Corriente',
];

$controllersDir = __DIR__ . '/../src/Controller/Maintainers';
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($controllersDir)
);

$updated = 0;
$skipped = 0;

foreach ($files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        
        // Buscar patrón: protected function getColumns(): array\n    {\n        return ['...'];
        $pattern = '/(protected function getColumns\(\): array\s*\{\s*return\s*\[)([^\]]+)(\];)/s';
        
        if (preg_match($pattern, $content, $matches)) {
            $oldArray = $matches[2];
            
            // Extraer columnas individuales (formato actual: 'name', 'code', etc.)
            preg_match_all("/'([^']+)'/", $oldArray, $columns);
            
            if (!empty($columns[1])) {
                // Construir nuevo array asociativo
                $newPairs = [];
                foreach ($columns[1] as $column) {
                    $label = $columnLabels[$column] ?? ucfirst(str_replace('_', ' ', $column));
                    $newPairs[] = "'$column' => '$label'";
                }
                
                $newArray = "\n        " . implode(",\n        ", $newPairs) . "\n    ";
                $newContent = str_replace($matches[0], $matches[1] . $newArray . $matches[3], $content);
                
                file_put_contents($file->getPathname(), $newContent);
                echo "✅ Actualizado: " . $file->getFilename() . "\n";
                $updated++;
            } else {
                echo "⏭️  Sin cambios: " . $file->getFilename() . "\n";
                $skipped++;
            }
        } else {
            echo "⚠️  No se encontró getColumns(): " . $file->getFilename() . "\n";
            $skipped++;
        }
    }
}

echo "\n📊 Resumen:\n";
echo "   Actualizados: $updated\n";
echo "   Omitidos: $skipped\n";
echo "   Total: " . ($updated + $skipped) . "\n";
