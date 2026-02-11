<?php

namespace App\Twig;

use App\Service\Menu\MenuBuilder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Extensión de Twig que proporciona funciones para cargar el menú de forma lazy
 *
 * CAMBIO: Ya NO usa GlobalsInterface porque cargaba el menú ANTES de que el tenant
 * estuviera establecido. Ahora usa una función Twig que se ejecuta bajo demanda.
 */
class MenuExtension extends AbstractExtension
{
    public function __construct(
        private MenuBuilder $menuBuilder
    ) {}

    /**
     * Registra funciones Twig personalizadas
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('get_menu', [$this->menuBuilder, 'buildMenuSafe']),
        ];
    }
}
