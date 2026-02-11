#!/usr/bin/env php
<?php
/**
 * Script profesional para convertir headers hardcodeados a traducciones
 * 
 * Usa el método translateColumns() del AbstractMantenedorController
 * para mantener código limpio y profesional.
 */

$projectRoot = dirname(__DIR__);
$controllersPath = $projectRoot . '/src/Controller/Maintainers';

// Mapeo completo español -> clave de traducción
$translationMap = [
    'Nombre' => 'name',
    'Código' => 'code',
    'Codigo' => 'code',
    'Activo' => 'is_active',
    'Estado' => 'is_active',
    'Descripción' => 'description',
    'Descripcion' => 'description',
    'Orden' => 'order',
    'RUT' => 'rut',
    'Email' => 'email',
    'Teléfono' => 'phone',
    'Ciudad' => 'city',
    'Región' => 'region',
    'Artículo' => 'article',
    'Proveedor' => 'supplier',
    'Precio' => 'price',
    'Bodega' => 'warehouse',
    'Especialidad' => 'specialty',
    'Stock Mínimo' => 'min_stock',
    'Stock Crítico' => 'critical_stock',
    'Stock Óptimo' => 'optimal_stock',
    'Es Crítico' => 'is_critical',
    'Es Fármaco' => 'is_drug',
    'Nombre Abreviado' => 'short_name',
    'Nombre Genérico' => 'generic_name',
    'Tipo' => 'type',
    'Sub Empresa' => 'sub_company',
    'Sucursal' => 'branch',
    'Cantidad' => 'quantity',
    'Unidad' => 'unit',
    'Peso' => 'weight',
    'Temperatura' => 'temperature',
    'Agrupacion 1' => 'grouping_1',
    'Tipo Campo' => 'field_type',
    'Obligatorio' => 'required',
    'Intervalos' => 'intervals',
    'Cantidad Diaria' => 'daily_quantity',
    'Unidad Tiempo' => 'time_unit',
    'Código HL7' => 'hl7_code',
    'Número' => 'number',
    'Nivel de Instrucción' => 'education_level',
    'Tipo de Origen' => 'origin_type',
    'Cuenta Corriente' => 'checking_account',
    'Cargo' => 'position',
    'Codigo Interfaz' => 'interface_code',
    'Plazo Maximo' => 'max_term',
    'Es Al Dia' => 'is_up_to_date',
    'Abreviacion' => 'abbreviation',
    'Sentido' => 'direction',
    'Es Efectivo' => 'is_cash',
    'Es CLP' => 'is_clp',
    'Tipo Gratuidad' => 'gratuity_type',
    'Consignación' => 'consignment',
    'Controlado' => 'controlled',
    'Fecha Venc.' => 'expiration_date',
    'Crítico' => 'critical',
    'Genérico' => 'generic',
    'Reesterilizable' => 'resterilizable',
    'Venta' => 'sale',
    'Facturable' => 'billable',
    'Rebaja Botiquín' => 'first_aid_deduction',
    'Stock Min' => 'min_stock',
    'Stock Crít' => 'critical_stock',
    'Stock Ópt' => 'optimal_stock',
    'Stock Max' => 'max_stock',
    'Cód Cenabast' => 'cenabast_code',
];

function convertFile(string $filePath, array $translationMap): bool
{
    $content = file_get_contents($filePath);
    $originalContent = $content;
    
    // Buscar patrón: headers: ['String', 'String', ...]
    // Captura todo el array incluyendo multilinea
    $pattern = '/(\s+headers:\s*\[)([^\]]+)(\])/s';
    
    if (!preg_match($pattern, $content, $mainMatch)) {
        return false; // No tiene headers
    }
    
    $headersContent = $mainMatch[2];
    
    // Extraer todos los strings entre comillas simples
    preg_match_all("/'([^']+)'/", $headersContent, $matches);
    
    if (empty($matches[1])) {
        return false;
    }
    
    $translationKeys = [];
    $missingTranslations = [];
    
    foreach ($matches[1] as $headerText) {
        $headerText = trim($headerText);
        
        if (isset($translationMap[$headerText])) {
            $translationKeys[] = $translationMap[$headerText];
        } else {
            $missingTranslations[] = $headerText;
        }
    }
    
    // Si hay traducciones faltantes, reportar y no modificar
    if (!empty($missingTranslations)) {
        $fileName = basename($filePath);
        echo "  ⚠️  $fileName: Missing translations for: " . implode(', ', $missingTranslations) . "\n";
        return false;
    }
    
    // Construir el nuevo código profesional
    $keysFormatted = "'" . implode("', '", $translationKeys) . "'";
    $newHeaders = "headers: \$this->translateColumns([$keysFormatted])";
    
    // Reemplazar
    $newContent = preg_replace($pattern, "\n            $newHeaders", $content, 1);
    
    if ($newContent !== $originalContent) {
        file_put_contents($filePath, $newContent);
        return true;
    }
    
    return false;
}

// Procesar todos los archivos
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($controllersPath, RecursiveDirectoryIterator::SKIP_DOTS)
);

$converted = 0;
$skipped = 0;

echo "Converting headers to professional translateColumns() pattern...\n\n";

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $relativePath = str_replace($controllersPath . '/', '', $file->getPathname());
        
        if (convertFile($file->getPathname(), $translationMap)) {
            echo "✓ $relativePath\n";
            $converted++;
        } else {
            $skipped++;
        }
    }
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Converted: $converted files\n";
if ($skipped > 0) {
    echo "⚠️  Skipped: $skipped files (no headers or missing translations)\n";
}
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
