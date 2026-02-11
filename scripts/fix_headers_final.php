#!/usr/bin/env php
<?php
/**
 * Script mejorado para convertir headers hardcodeados a traducciones
 */

$projectRoot = dirname(__DIR__);
$controllersPath = $projectRoot . '/src/Controller/Maintainers';

$translationMap = [
    'Nombre' => 'name',
    'Código' => 'code',
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
    'Codigo' => 'code',
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

function convertHeaders($file, $translationMap) {
    $content = file_get_contents($file);
    $modified = false;
    
    // Buscar headers: ['String', 'String', ...]
    $pattern = "/(headers:\s*\[)([^\]]+)(\])/";
    
    if (preg_match($pattern, $content)) {
        preg_match_all("/'([^']+)'/", $content, $headerMatches);
        
        if (!empty($headerMatches[1])) {
            $translatedHeaders = [];
            $missingTranslations = [];
            
            foreach ($headerMatches[1] as $header) {
                $header = trim($header);
                if (isset($translationMap[$header])) {
                    $translatedHeaders[] = "\$this->translator->trans('maintainers.columns.{$translationMap[$header]}', [], 'maintainers')";
                } else {
                    $missingTranslations[] = $header;
                }
            }
            
            if (!empty($missingTranslations)) {
                echo "  ⚠️  Missing translations in " . basename($file) . ": " . implode(', ', $missingTranslations) . "\n";
                return false;
            }
            
            if (count($translatedHeaders) > 0) {
                // Reconstruir el array de headers
                $newHeaders = "headers: [\n                " . 
                              implode(",\n                ", $translatedHeaders) . 
                              "\n            ]";
                
                $newContent = preg_replace($pattern, $newHeaders, $content, 1);
                
                if ($newContent !== $content) {
                    file_put_contents($file, $newContent);
                    $modified = true;
                }
            }
        }
    }
    
    return $modified;
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($controllersPath)
);

$count = 0;
$skipped = 0;

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $result = convertHeaders($file->getPathname(), $translationMap);
        if ($result === true) {
            echo "✓ " . str_replace($controllersPath . '/', '', $file->getPathname()) . "\n";
            $count++;
        } elseif ($result === false) {
            $skipped++;
        }
    }
}

echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Converted: $count\n";
if ($skipped > 0) {
    echo "⚠️  Skipped (missing translations): $skipped\n";
}
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
