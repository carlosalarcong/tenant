#!/usr/bin/env php
<?php
/**
 * Script para convertir headers hardcodeados a traducciones
 */

$projectRoot = dirname(__DIR__);
$controllersPath = $projectRoot . '/src/Controller/Maintainers';

// Mapeo de términos comunes a claves de traducción
$translationMap = [
    'Nombre' => "maintainers.columns.name",
    'Código' => "maintainers.columns.code",
    'Activo' => "maintainers.columns.is_active",
    'Descripción' => "maintainers.columns.description",
    'Orden' => "maintainers.columns.order",
    'RUT' => "maintainers.columns.rut",
    'Email' => "maintainers.columns.email",
    'Teléfono' => "maintainers.columns.phone",
    'Ciudad' => "maintainers.columns.city",
    'Región' => "maintainers.columns.region",
    'Estado' => "maintainers.columns.is_active",
    'Artículo' => "maintainers.columns.article",
    'Proveedor' => "maintainers.columns.supplier",
    'Precio' => "maintainers.columns.price",
    'Bodega' => "maintainers.columns.warehouse",
    'Especialidad' => "maintainers.columns.specialty",
    'Stock Mínimo' => "maintainers.columns.min_stock",
    'Stock Crítico' => "maintainers.columns.critical_stock",
    'Stock Óptimo' => "maintainers.columns.optimal_stock",
    'Es Crítico' => "maintainers.columns.is_critical",
    'Es Fármaco' => "maintainers.columns.is_drug",
    'Nombre Abreviado' => "maintainers.columns.short_name",
    'Nombre Genérico' => "maintainers.columns.generic_name",
    'Tipo' => "maintainers.columns.type",
    'Sub Empresa' => "maintainers.columns.sub_company",
    'Sucursal' => "maintainers.columns.branch",
    'Cantidad' => "maintainers.columns.quantity",
    'Unidad' => "maintainers.columns.unit",
    'Peso' => "maintainers.columns.weight",
    'Temperatura' => "maintainers.columns.temperature",
    'Agrupacion 1' => "maintainers.columns.grouping_1",
    'Tipo Campo' => "maintainers.columns.field_type",
    'Obligatorio' => "maintainers.columns.required",
    'Intervalos' => "maintainers.columns.intervals",
    'Cantidad Diaria' => "maintainers.columns.daily_quantity",
    'Unidad Tiempo' => "maintainers.columns.time_unit",
    'Código HL7' => "maintainers.columns.hl7_code",
    'Número' => "maintainers.columns.number",
    'Nivel de Instrucción' => "maintainers.columns.education_level",
    'Tipo de Origen' => "maintainers.columns.origin_type",
    'Cuenta Corriente' => "maintainers.columns.checking_account",
    'Cargo' => "maintainers.columns.position",
    'Codigo Interfaz' => "maintainers.columns.interface_code",
    'Plazo Maximo' => "maintainers.columns.max_term",
    'Es Al Dia' => "maintainers.columns.is_up_to_date",
    'Abreviacion' => "maintainers.columns.abbreviation",
    'Sentido' => "maintainers.columns.direction",
    'Descripcion' => "maintainers.columns.description",
    'Es Efectivo' => "maintainers.columns.is_cash",
    'Es CLP' => "maintainers.columns.is_clp",
    'Tipo Gratuidad' => "maintainers.columns.gratuity_type",
    'Consignación' => "maintainers.columns.consignment",
    'Controlado' => "maintainers.columns.controlled",
    'Fecha Venc.' => "maintainers.columns.expiration_date",
    'Crítico' => "maintainers.columns.critical",
    'Genérico' => "maintainers.columns.generic",
    'Reesterilizable' => "maintainers.columns.resterilizable",
    'Venta' => "maintainers.columns.sale",
    'Facturable' => "maintainers.columns.billable",
    'Rebaja Botiquín' => "maintainers.columns.first_aid_deduction",
    'Stock Min' => "maintainers.columns.min_stock",
    'Stock Crít' => "maintainers.columns.critical_stock",
    'Stock Ópt' => "maintainers.columns.optimal_stock",
    'Stock Max' => "maintainers.columns.max_stock",
    'Cód Cenabast' => "maintainers.columns.cenabast_code",
    'Codigo' => "maintainers.columns.code",
];

function convertHeaders($file, $translationMap) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Buscar patrones de headers: ['String', 'String', ...]
    $pattern = "/(headers:\s*\[)([^\]]+)(\])/";
    
    if (preg_match($pattern, $content, $matches)) {
        $headersString = $matches[2];
        
        // Extraer cada header individual
        preg_match_all("/'([^']+)'/", $headersString, $headerMatches);
        
        if (!empty($headerMatches[1])) {
            $translatedHeaders = [];
            $allTranslated = true;
            
            foreach ($headerMatches[1] as $header) {
                $header = trim($header);
                if (isset($translationMap[$header])) {
                    $translatedHeaders[] = "\$this->translator->trans('{$translationMap[$header]}', [], 'maintainers')";
                } else {
                    // Si no hay traducción, mantener el original pero marcarlo
                    echo "  ⚠️  No translation found for: '$header'\n";
                    $translatedHeaders[] = "'" . $header . "' /* TODO: translate */";
                    $allTranslated = false;
                }
            }
            
            // Reconstruir la línea de headers
            $newHeadersString = "[\n                " . implode(",\n                ", $translatedHeaders) . "\n            ]";
            
            $newContent = preg_replace(
                $pattern,
                "headers: " . $newHeadersString,
                $content,
                1
            );
            
            if ($newContent !== $content) {
                file_put_contents($file, $newContent);
                return $allTranslated ? 'full' : 'partial';
            }
        }
    }
    
    return false;
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($controllersPath)
);

$fullCount = 0;
$partialCount = 0;
$skippedCount = 0;

echo "Converting hardcoded headers to translations...\n\n";

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $relativePath = str_replace($projectRoot . '/', '', $file->getPathname());
        
        $result = convertHeaders($file->getPathname(), $translationMap);
        
        if ($result === 'full') {
            echo "✓ $relativePath\n";
            $fullCount++;
        } elseif ($result === 'partial') {
            echo "⚠ $relativePath (partial)\n";
            $partialCount++;
        }
    }
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "✅ Fully converted:     $fullCount\n";
if ($partialCount > 0) {
    echo "⚠️  Partially converted: $partialCount\n";
}
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
