<?php

namespace App\Service\Menu;

/**
 * Trait MenuIconsTrait
 * 
 * Define los iconos de Boxicons para todos los items del menú.
 * Usado por MenuDefinition para mantener consistencia de iconos en todo el sistema.
 */
trait MenuIconsTrait
{
    /**
     * Retorna el mapeo de nombres de items a sus iconos de Boxicons
     * 
     * @return array<string, string>
     */
    protected function getMenuIcons(): array
    {
        return [
            // Items principales
            'dashboard' => 'bx bx-home-circle',
            'pacientes' => 'bx bx-group',
            'patients' => 'bx bx-group',
            'citas' => 'bx bx-calendar-check',
            'appointments' => 'bx bx-calendar-check',
            'mantenedores' => 'bx bx-data',
            'reportes' => 'bx bx-bar-chart-alt-2',
            'configuracion' => 'bx bx-cog',
            
            // Carpetas de mantenedores
            'maintenance_basic' => 'bx bx-folder',
            'maintenance_clinical' => 'bx bx-folder',
            'maintenance_geographic' => 'bx bx-folder',
            
            // Mantenedores básicos
            'gender' => 'bx bx-male-female',
            'maintenance_gender' => 'bx bx-male-female',
            
            'marital_status' => 'bx bx-heart',
            'maintenance_marital_status' => 'bx bx-heart',
            
            'occupation' => 'bx bx-briefcase',
            'maintenance_occupation' => 'bx bx-briefcase',
            
            'religion' => 'bx bx-church',
            'maintenance_religion' => 'bx bx-church',
            
            'location' => 'bx bx-map',
            'maintenance_location' => 'bx bx-map',
            
            'doctor_type' => 'bx bx-user-plus',
            'maintenance_doctor_type' => 'bx bx-user-plus',
            
            'education_level' => 'bx bx-book',
            'maintenance_education_level' => 'bx bx-book',
            
            'ethnic_group' => 'bx bx-group',
            'maintenance_ethnic_group' => 'bx bx-group',
            
            'job_position' => 'bx bx-id-card',
            'maintenance_job_position' => 'bx bx-id-card',
            
            'medical_box' => 'bx bx-clinic',
            'maintenance_medical_box' => 'bx bx-clinic',
            
            'origin' => 'bx bx-map-pin',
            'maintenance_origin' => 'bx bx-map-pin',
            
            'origin_type' => 'bx bx-compass',
            'maintenance_origin_type' => 'bx bx-compass',
            
            // Mantenedores de Estructura
            'maintenance_structure' => 'bx bx-buildings',
            'cost_center' => 'bx bx-calculator',
            'service_type' => 'bx bx-category',
            'medical_service' => 'bx bx-plus-medical',
            
            // Mantenedores clínicos
            'maintenance_box' => 'bx bx-clinic',
            'maintenance_medical_specialty' => 'bx bx-plus-medical',
            
            // Mantenedores geográficos
            'maintenance_country' => 'bx bx-world',
            'maintenance_region' => 'bx bx-map-alt',
            'maintenance_province' => 'bx bx-map',
            'maintenance_commune' => 'bx bx-current-location',
        ];
    }
    
    /**
     * Obtiene el icono para un item específico
     * 
     * @param string $itemName Nombre del item
     * @param string $defaultIcon Icono por defecto si no se encuentra
     * @return string
     */
    protected function getIconForItem(string $itemName, string $defaultIcon = 'bx bx-circle'): string
    {
        $icons = $this->getMenuIcons();
        return $icons[$itemName] ?? $defaultIcon;
    }
}
