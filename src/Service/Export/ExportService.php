<?php

namespace App\Service\Export;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Servicio genérico de exportación de datos
 * 
 * Características:
 * - Streaming para alta performance y bajo consumo de memoria
 * - Soporta QueryBuilder para datasets grandes
 * - Configurable: columnas, headers, formato
 * - Reutilizable en cualquier parte del sistema
 * 
 * Performance:
 * - Procesa chunks de 1000 registros
 * - No carga todo en memoria (streaming directo al buffer de salida)
 * - Puede exportar millones de registros sin timeout
 * 
 * Uso:
 * ```php
 * return $this->exportService->exportToCsv(
 *     queryBuilder: $qb,
 *     columns: ['id', 'name', 'email'],
 *     headers: ['ID', 'Nombre', 'Correo'],
 *     filename: 'usuarios.csv'
 * );
 * ```
 */
class ExportService
{
    private const BATCH_SIZE = 1000;
    
    /**
     * Exporta datos a CSV usando streaming
     * 
     * @param QueryBuilder $queryBuilder Query para obtener datos
     * @param array $columns Columnas a exportar ['id', 'name', 'email']
     * @param array|null $headers Headers personalizados (opcional, usa nombres de columnas si es null)
     * @param string $filename Nombre del archivo a descargar
     * @param string $delimiter Delimitador CSV (por defecto ;)
     * @param bool $includeHeaders Si incluir fila de headers
     * @return StreamedResponse
     */
    public function exportToCsv(
        QueryBuilder $queryBuilder,
        array $columns,
        ?array $headers = null,
        string $filename = 'export.csv',
        string $delimiter = ';',
        bool $includeHeaders = true
    ): StreamedResponse {
        $headers = $headers ?? $columns;
        
        $response = new StreamedResponse(function() use ($queryBuilder, $columns, $headers, $delimiter, $includeHeaders) {
            $handle = fopen('php://output', 'w');
            
            // BOM UTF-8 para Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            if ($includeHeaders) {
                fputcsv($handle, $headers, $delimiter);
            }
            
            // Procesamiento por lotes para evitar memory exhaustion
            $offset = 0;
            $query = $queryBuilder
                ->setMaxResults(self::BATCH_SIZE)
                ->getQuery();
            
            do {
                $query->setFirstResult($offset);
                $results = $query->getResult();
                
                foreach ($results as $entity) {
                    $row = [];
                    foreach ($columns as $column) {
                        $row[] = $this->extractValue($entity, $column);
                    }
                    fputcsv($handle, $row, $delimiter);
                }
                
                // Liberar memoria
                $queryBuilder->getEntityManager()->clear();
                
                $offset += self::BATCH_SIZE;
            } while (count($results) === self::BATCH_SIZE);
            
            fclose($handle);
        });
        
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
        $response->headers->set('Cache-Control', 'max-age=0');
        
        return $response;
    }
    
    /**
     * Exporta array simple a CSV (sin streaming, para datasets pequeños)
     * 
     * @param array $data Array de datos
     * @param array $columns Columnas a exportar
     * @param array|null $headers Headers personalizados
     * @param string $filename Nombre del archivo
     * @param string $delimiter Delimitador CSV
     * @return StreamedResponse
     */
    public function exportArrayToCsv(
        array $data,
        array $columns,
        ?array $headers = null,
        string $filename = 'export.csv',
        string $delimiter = ';'
    ): StreamedResponse {
        $headers = $headers ?? $columns;
        
        $response = new StreamedResponse(function() use ($data, $columns, $headers, $delimiter) {
            $handle = fopen('php://output', 'w');
            
            // BOM UTF-8 para Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($handle, $headers, $delimiter);
            
            // Datos
            foreach ($data as $item) {
                $row = [];
                foreach ($columns as $column) {
                    $row[] = $this->extractValue($item, $column);
                }
                fputcsv($handle, $row, $delimiter);
            }
            
            fclose($handle);
        });
        
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));
        $response->headers->set('Cache-Control', 'max-age=0');
        
        return $response;
    }
    
    /**
     * Extrae valor de una propiedad de entidad u objeto
     * Soporta: propiedades públicas, getters, arrays, is*()
     */
    private function extractValue(mixed $entity, string $property): mixed
    {
        // Array access
        if (is_array($entity)) {
            return $entity[$property] ?? '';
        }
        
        // Propiedad pública directa
        if (is_object($entity) && property_exists($entity, $property)) {
            try {
                $reflection = new \ReflectionProperty($entity, $property);
                if ($reflection->isPublic()) {
                    $value = $entity->$property;
                    return $this->formatValue($value);
                }
            } catch (\ReflectionException $e) {
                // Continuar con getters
            }
        }
        
        // Getter methods
        if (is_object($entity)) {
            // Intentar varios patrones de getters
            $propertyUcFirst = ucfirst($property);
            
            // Si la propiedad empieza con 'is', crear versión sin el 'is'
            $cleanProperty = $propertyUcFirst;
            if (stripos($property, 'is') === 0 && strlen($property) > 2) {
                // isActive -> Active
                $cleanProperty = ucfirst(substr($property, 2));
            }
            
            $methods = [
                'get' . $propertyUcFirst,    // getIsActive
                'is' . $cleanProperty,        // isActive ✓
                'get' . $cleanProperty,       // getActive
                'has' . $propertyUcFirst,     // hasIsActive
                'has' . $cleanProperty,       // hasActive
                $property,                    // isActive (método directo)
            ];
            
            foreach ($methods as $method) {
                if (method_exists($entity, $method)) {
                    $value = $entity->$method();
                    return $this->formatValue($value);
                }
            }
        }
        
        return '';
    }
    
    /**
     * Formatea valor para exportación
     */
    private function formatValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        
        if (is_bool($value)) {
            return $value ? 'Sí' : 'No';
        }
        
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        
        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return (string) $value;
            }
            // Para relaciones, intentar obtener nombre o id
            if (method_exists($value, 'getName')) {
                return $value->getName();
            }
            if (method_exists($value, 'getId')) {
                return (string) $value->getId();
            }
            return '';
        }
        
        return (string) $value;
    }
}
