<?php

namespace App\Controller;

use App\Service\LocalizationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controlador de gestión de idiomas del tenant
 */
#[Route('/locale')]
class LocaleController extends AbstractController
{
    private LocalizationService $localizationService;

    public function __construct(LocalizationService $localizationService)
    {
        $this->localizationService = $localizationService;
    }

    /**
     * Cambiar idioma del usuario
     */
    #[Route('/change/{locale}', name: 'app_locale_change', methods: ['POST', 'GET'])]
    public function changeLocale(string $locale, Request $request): Response
    {
        // Verificar que el idioma esté soportado
        if (!$this->localizationService->isLocaleSupported($locale)) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Idioma no soportado'
                ], 400);
            }
            
            $this->addFlash('error', 'Idioma no soportado');
            return $this->redirectToRoute('app_dashboard');
        }

        // Establecer el nuevo idioma
        $success = $this->localizationService->setUserLocale($locale);
        
        // Establecer también en el request actual
        $request->setLocale($locale);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => $success,
                'locale' => $locale,
                'message' => $success ? 'Idioma cambiado correctamente' : 'Error al cambiar idioma',
                'current_locale' => $this->localizationService->getCurrentLocale(),
                'session_locale' => $request->getSession()->get('_locale')
            ]);
        }

        if ($success) {
            $this->addFlash('success', 'Idioma cambiado correctamente');
        } else {
            $this->addFlash('error', 'Error al cambiar idioma');
        }

        // Redirigir a la página anterior o al dashboard
        $referer = $request->headers->get('referer');
        if ($referer && strpos($referer, $request->getSchemeAndHttpHost()) === 0) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_dashboard', ['controller' => 'dashboard']);
    }

    /**
     * API para obtener información de idiomas disponibles
     */
    #[Route('/available', name: 'app_locale_available', methods: ['GET'])]
    public function getAvailableLocales(): JsonResponse
    {
        return new JsonResponse([
            'current_locale' => $this->localizationService->getCurrentLocale(),
            'current_locale_name' => $this->localizationService->getCurrentLocaleName(),
            'supported_locales' => $this->localizationService->getSupportedLocalesInfo(),
            'tenant_translations' => $this->localizationService->getTenantSpecificTranslations()
        ]);
    }

    /**
     * API para obtener traducciones específicas
     */
    #[Route('/translations/{domain}', name: 'app_locale_translations', methods: ['GET'])]
    public function getTranslations(string $domain = 'messages'): JsonResponse
    {
        $locale = $this->localizationService->getCurrentLocale();
        
        // Aquí podrías cargar traducciones específicas si necesitas
        // Por ahora retornamos información básica
        return new JsonResponse([
            'locale' => $locale,
            'domain' => $domain,
            'tenant_specific' => $this->localizationService->getTenantSpecificTranslations()
        ]);
    }
}