<?php

namespace App\Security\Voter;

/**
 * Value Object que encapsula el contexto de un mantenedor para verificación de permisos
 * 
 * Este objeto permite pasar metadata adicional al MaintainerVoter, específicamente
 * la categoría del mantenedor, para habilitar granularidad en Phase 2.
 * 
 * USO:
 * ```php
 * // En AbstractMantenedorController:
 * $context = new MaintainerContext($this->getEntityClass(), $this->getMaintainerCategory());
 * $this->denyAccessUnlessGranted(MaintainerVoter::CREATE, $context);
 * ```
 * 
 * BENEFICIOS:
 * - Permite especificar categoría explícitamente (override de namespace)
 * - Mantiene retrocompatibilidad (voter sigue aceptando string o entidad directamente)
 * - Extensible para future metadata (tags, attributes, etc.)
 * 
 * @author Melisa Development Team
 * @since Sprint 3 - Phase 2 Category Granularity (Feb 2026)
 */
readonly class MaintainerContext
{
    public function __construct(
        /**
         * La entidad o nombre de clase del mantenedor
         * Puede ser string ('App\Entity\Tenant\Gender') o objeto Gender
         */
        public string|object $subject,
        
        /**
         * Categoría explícita del mantenedor
         * Si es null, el voter intentará extraerla del namespace
         * 
         * Valores válidos: 'basic', 'clinical', 'commercial', 'hospital', 'human',
         * 'workshop', 'settlements', 'insurance', 'budget', etc.
         */
        public ?string $category = null,
        
        /**
         * Nombre específico del mantenedor (para Phase 3)
         * Si es null, se extrae del classname
         * Ejemplo: 'Gender', 'Disease', 'TreatmentType'
         */
        public ?string $maintainer = null
    ) {
    }
    
    /**
     * Obtiene la clase del subject
     */
    public function getClassName(): string
    {
        return is_object($this->subject) ? get_class($this->subject) : $this->subject;
    }
    
    /**
     * Verifica si tiene categoría explícita
     */
    public function hasExplicitCategory(): bool
    {
        return $this->category !== null;
    }
    
    /**
     * Obtiene la categoría, usando la explícita si está disponible
     * o extrayéndola del namespace
     */
    public function resolveCategory(): ?string
    {
        if ($this->category !== null) {
            return $this->category;
        }
        
        // Extraer del namespace
        $className = $this->getClassName();
        if (preg_match('/App\\\\Entity\\\\Tenant\\\\(\w+)\\\\/', $className, $matches)) {
            return strtolower($matches[1]);
        }
        
        return null;
    }
    
    /**
     * Obtiene el nombre del mantenedor
     */
    public function resolveMaintainer(): ?string
    {
        if ($this->maintainer !== null) {
            return $this->maintainer;
        }
        
        // Extraer del classname
        $className = $this->getClassName();
        $parts = explode('\\', $className);
        return end($parts) ?: null;
    }
}
