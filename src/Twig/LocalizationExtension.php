<?php

namespace App\Twig;

use App\Service\LocalizationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

/**
 * Extensión Twig para funcionalidades de localización del tenant
 * 
 * IMPORTANTE: Esta extensión SOBRESCRIBE el filtro |trans de Symfony
 * para hacerlo tenant-aware automáticamente.
 * 
 * Ahora |trans detecta el tenant y usa sus traducciones específicas:
 * - {{ 'auth.login'|trans }} funcionará automáticamente
 * - Ya NO es necesario usar |ttrans (aunque sigue disponible como alias)
 * 
 * Proporciona filtros y funciones para traducciones específicas por tenant
 */
class LocalizationExtension extends AbstractExtension
{
    /*
    private LocalizationService $localizationService;

    public function __construct(LocalizationService $localizationService)
    {
        $this->localizationService = $localizationService;
    }

    public function getFilters(): array
    {
        return [
            // SOBRESCRIBIR el filtro trans estándar de Symfony para que sea tenant-aware
            new TwigFilter('trans', [$this, 'translateTenant']),
            // Mantener ttrans como alias
            new TwigFilter('ttrans', [$this, 'translateTenant']),
            // Alias para mantener compatibilidad
            new TwigFilter('tenant_trans', [$this, 'translateTenant']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('current_locale', [$this, 'getCurrentLocale']),
            new TwigFunction('supported_locales', [$this, 'getSupportedLocales']),
            new TwigFunction('tenant_trans', [$this, 'getTenantTranslations']),
            new TwigFunction('locale_name', [$this, 'getCurrentLocaleName']),
            new TwigFunction('locale_flag', [$this, 'getLocaleFlag']),
            // Función de traducción también
            new TwigFunction('ttrans', [$this, 'translateTenant']),
        ];
    }

    /**
     * Traduce usando el dominio del tenant automáticamente
     * 
     * Este método SOBRESCRIBE el filtro |trans estándar de Symfony
     * para que detecte automáticamente el tenant y use sus traducciones.
     * 
     * Uso en Twig: {{ 'auth.login'|trans }}
     *              {{ 'auth.login'|ttrans }}  (alias)
     *              {{ 'auth.user_not_found'|trans({'%tenant%': 'Hospital'}) }}
     * 
     * Ya NO es necesario usar |ttrans, el filtro |trans funciona automáticamente
     */
    /**
    public function translateTenant(string $id, array $parameters = []): string
    {
        return $this->localizationService->trans($id, $parameters);
    }

    /**
     * Obtiene el idioma actual
     */
    /**
    public function getCurrentLocale(): string
    {
        return $this->localizationService->getCurrentLocale();
    }

    /**
     * Obtiene todos los idiomas soportados con su información
     */
    /**
    public function getSupportedLocales(): array
    {
        return $this->localizationService->getSupportedLocalesInfo();
    }

    /**
     * Obtiene traducciones específicas del tenant
     */
    /**
    public function getTenantTranslations(): array
    {
        return $this->localizationService->getTenantSpecificTranslations();
    }

    /**
     * Obtiene el nombre del idioma actual
     */
    /**
    public function getCurrentLocaleName(): string
    {
        return $this->localizationService->getCurrentLocaleName();
    }

    /**
     * Obtiene la bandera del idioma especificado
     */
    /**
    public function getLocaleFlag(string $locale): string
    {
        $locales = $this->localizationService->getSupportedLocalesInfo();
        return $locales[$locale]['flag'] ?? '🌐';
    }
     */
}