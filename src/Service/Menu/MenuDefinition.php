<?php

namespace App\Service\Menu;

use App\Repository\Tenant\MenuItemRepository;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * MenuDefinition: Fuente única de verdad para la estructura del menú
 * 
 * Obtiene la estructura del menú desde BD (configurable) con caché.
 * Fallback a estructura hardcoded si BD falla o está vacía.
 * Usado por MenuBuilder (sidebar) y PermissionAwareMenuBuilder (filtrado por permisos).
 */
class MenuDefinition
{
    use MenuIconsTrait;

    public function __construct(
        private CacheInterface $cache,
        private ?MenuItemRepository $menuRepository = null,
        private ?LoggerInterface $logger = null
    ) {}

    /**
     * Obtiene la estructura del menú con estrategia:
     * 1. Buscar en caché (TTL: 1 hora)
     * 2. Si no hay caché, consultar BD
     * 3. Si BD falla/vacía, usar estructura por defecto hardcoded
     * 
     * @param string $tenantId ID del tenant (para caché multi-tenant)
     * @return array Estructura del menú
     */
    public function getMenuStructure(string $tenantId = 'default'): array
    {
        $cacheKey = 'menu_structure_tenant_' . $tenantId;

        try {
            return $this->cache->get($cacheKey, function (ItemInterface $item) {
                // Caché por 1 hora (3600 segundos)
                $item->expiresAfter(3600);

                // Intentar obtener desde BD
                if ($this->menuRepository) {
                    try {
                        $menuItems = $this->menuRepository->getMenuWithChildren();
                        
                        if (!empty($menuItems)) {
                            $this->logger?->info('Menu cargado desde BD', ['items_count' => count($menuItems)]);
                            return $this->convertEntitiesToArray($menuItems);
                        }
                        
                        $this->logger?->warning('BD de menú vacía, usando estructura por defecto');
                    } catch (\Exception $e) {
                        $this->logger?->error('Error al cargar menú desde BD', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }

                // Fallback a estructura hardcoded
                $this->logger?->info('Usando estructura de menú por defecto (hardcoded)');
                return $this->getDefaultMenuStructure();
            });
        } catch (\Exception $e) {
            // Si incluso el caché falla, usar directamente el hardcoded
            $this->logger?->error('Error en caché de menú, usando hardcoded', [
                'error' => $e->getMessage()
            ]);
            return $this->getDefaultMenuStructure();
        }
    }

    /**
     * Invalida el caché del menú (usar después de modificar en BD).
     */
    public function invalidateCache(string $tenantId = 'default'): void
    {
        $cacheKey = 'menu_structure_tenant_' . $tenantId;
        $this->cache->delete($cacheKey);
        $this->logger?->info('Caché de menú invalidado', ['tenant' => $tenantId]);
    }

    /**
     * Convierte entidades MenuItem a arrays para compatibilidad.
     * 
     * @param \App\Entity\Tenant\MenuItem[] $menuItems
     * @return array
     */
    private function convertEntitiesToArray(array $menuItems): array
    {
        return array_map(fn($item) => $item->toArray(), $menuItems);
    }

    /**
     * Estructura por defecto del menú (fallback hardcoded).
     * Se usa cuando BD está vacía o falla.
     */
    private function getDefaultMenuStructure(): array
    {
        return [
            [
                'name' => 'dashboard',
                'label' => 'Dashboard',
                'icon' => $this->getIconForItem('dashboard'),
                'route' => 'app_dashboard_default',
                'module' => null,
                'children' => []
            ],
            [
                'name' => 'pacientes',
                'label' => 'Pacientes',
                'icon' => $this->getIconForItem('pacientes'),
                'route' => null,
                'module' => null,
                'children' => []
            ],
            [
                'name' => 'admision',
                'label' => 'Admisión',
                'icon' => 'bx bx-clinic',
                'module' => null,
                'children' => [
                    [
                        'name' => 'admission_general',
                        'label' => 'Admisión Hospitalaria',
                        'icon' => 'bx bx-user-check',
                        'route' => 'app_admission_hospitalization_index',
                        'module' => 'admission',
                        'children' => []
                    ],
                    [
                        'name' => 'admission_emergency',
                        'label' => 'Admisión Urgencia',
                        'icon' => 'bx bx-first-aid',
                        'route' => 'app_admission_emergency_index',
                        'module' => 'admission',
                        'children' => []
                    ],
                    [
                        'name' => 'admission_pre',
                        'label' => 'Pre-Admisión',
                        'icon' => 'bx bx-time',
                        'route' => 'app_admission_pre_index',
                        'module' => 'admission',
                        'children' => []
                    ],
                    [
                        'name' => 'admission_maintainers',
                        'label' => 'Mantenedores Admisión',
                        'icon' => 'bx bx-cog',
                        'module' => null,
                        'children' => [
                            [
                                'name' => 'emergency_consultation_type',
                                'label' => 'Tipos Consulta Urgencia',
                                'icon' => 'bx bx-list-ul',
                                'route' => 'app_maintainers_admission_emergency_consultation_type_index',
                                'module' => 'maintenance_admission',
                                'children' => []
                            ],
                            [
                                'name' => 'company_agreement',
                                'label' => 'Convenios Empresa',
                                'icon' => 'bx bx-buildings',
                                'route' => 'app_maintainers_admission_company_agreement_index',
                                'module' => 'maintenance_admission',
                                'children' => []
                            ],
                            [
                                'name' => 'cancellation_reason',
                                'label' => 'Motivos de Anulación',
                                'icon' => 'bx bx-x-circle',
                                'route' => 'app_maintainers_admission_cancellation_reason_index',
                                'module' => 'maintenance_admission',
                                'children' => []
                            ],
                        ]
                    ],
                ]
            ],
            [
                'name' => 'citas',
                'label' => 'Citas',
                'icon' => $this->getIconForItem('citas'),
                'route' => null,
                'module' => null,
                'children' => []
            ],
            [
                'name' => 'mantenedores',
                'label' => 'Mantenedores',
                'icon' => $this->getIconForItem('mantenedores'),
                'module' => null,
                'children' => [
                    [
                        'name' => 'maintenance_basic',
                        'label' => 'Mantenimiento Básico',
                        'icon' => $this->getIconForItem('maintenance_basic'),
                        'module' => null,
                        'children' => [
                            [
                                'name' => 'gender',
                                'label' => 'Sexo',
                                'icon' => $this->getIconForItem('gender'),
                                'route' => 'app_maintainers_gender_index',
                                'module' => 'maintenance_basic',
                                'children' => []
                            ],
                            [
                                'name' => 'marital_status',
                                'label' => 'Estado Civil',
                                'icon' => $this->getIconForItem('marital_status'),
                                'route' => 'app_maintainers_marital_status_index',
                                'module' => 'maintenance_basic',
                                'children' => []
                            ],
                            [
                                'name' => 'occupation',
                                'label' => 'Ocupación',
                                'icon' => $this->getIconForItem('occupation'),
                                'route' => 'app_maintainers_occupation_index',
                                'module' => 'maintenance_basic',
                                'children' => []
                            ],
                            [
                                'name' => 'religion',
                                'label' => 'Religión',
                                'icon' => $this->getIconForItem('religion'),
                                'route' => 'app_maintainers_religion_index',
                                'module' => 'maintenance_basic',
                                'children' => []
                            ],
                            [
                                'name' => 'location',
                                'label' => 'Localización',
                                'icon' => $this->getIconForItem('location'),
                                'route' => 'app_maintainers_location_index',
                                'module' => 'maintenance_basic',
                                'children' => []
                            ],
                            [
                                'name' => 'doctor_type',
                                'label' => 'Tipo de Doctor',
                                'icon' => $this->getIconForItem('doctor_type'),
                                'route' => 'app_maintainers_doctor_type_index',
                                'module' => 'maintenance_basic',
                                'children' => []
                            ],
                            [
                                'name' => 'education_level',
                                'label' => 'Nivel de Educación',
                                'icon' => $this->getIconForItem('education_level'),
                                'route' => 'app_maintainers_education_level_index',
                                'module' => 'maintenance_basic',
                                'children' => []
                            ],
                            [
                                'name' => 'ethnic_group',
                                'label' => 'Grupo Étnico',
                                'icon' => $this->getIconForItem('ethnic_group'),
                                'route' => 'app_maintainers_ethnic_group_index',
                                'module' => 'maintenance_basic',
                                'children' => []
                            ],
                            [
                                'name' => 'job_position',
                                'label' => 'Cargo',
                                'icon' => $this->getIconForItem('job_position'),
                                'route' => 'app_maintainers_job_position_index',
                                'module' => 'maintenance_basic',
                                'children' => []
                            ],
                            [
                                'name' => 'medical_box',
                                'label' => 'Caja Médica',
                                'icon' => $this->getIconForItem('medical_box'),
                                'route' => 'app_maintainers_medical_box_index',
                                'module' => 'maintenance_basic',
                                'children' => []
                            ],
                            [
                                'name' => 'origin',
                                'label' => 'Origen',
                                'icon' => $this->getIconForItem('origin'),
                                'route' => 'app_maintainers_origin_index',
                                'module' => 'maintenance_basic',
                                'children' => []
                            ],
                            [
                                'name' => 'origin_type',
                                'label' => 'Tipo de Origen',
                                'icon' => $this->getIconForItem('origin_type'),
                                'route' => 'app_maintainers_origin_type_index',
                                'module' => 'maintenance_basic',
                                'children' => []
                            ]
                        ]
                    ],
                    [
                        'name' => 'maintenance_structure',
                        'label' => 'Estructura',
                        'icon' => $this->getIconForItem('maintenance_structure'),
                        'module' => null,
                        'children' => [
                            [
                                'name' => 'cost_center',
                                'label' => 'Centros de Costo',
                                'icon' => $this->getIconForItem('cost_center'),
                                'route' => 'app_maintainers_cost_center_index',
                                'module' => 'maintenance_structure',
                                'children' => []
                            ],
                            [
                                'name' => 'service_type',
                                'label' => 'Tipos de Servicio',
                                'icon' => $this->getIconForItem('service_type'),
                                'route' => 'app_maintainers_service_type_index',
                                'module' => 'maintenance_structure',
                                'children' => []
                            ],
                            [
                                'name' => 'medical_service',
                                'label' => 'Servicios Médicos',
                                'icon' => $this->getIconForItem('medical_service'),
                                'route' => 'app_maintainers_medical_service_index',
                                'module' => 'maintenance_structure',
                                'children' => []
                            ]
                        ]
                    ],
                    [
                        'name' => 'maintenance_clinical',
                        'label' => 'Clínico',
                        'icon' => $this->getIconForItem('maintenance_clinical'),
                        'module' => null,
                        'children' => []
                    ],
                    [
                        'name' => 'maintenance_geographic',
                        'label' => 'Geográficos',
                        'icon' => $this->getIconForItem('maintenance_geographic'),
                        'module' => null,
                        'children' => []
                    ]
                ]
            ],
            [
                'name' => 'reportes',
                'label' => 'Reportes',
                'icon' => $this->getIconForItem('reportes'),
                'route' => null,
                'module' => null,
                'children' => []
            ],
            [
                'name' => 'configuracion',
                'label' => 'Configuración',
                'icon' => $this->getIconForItem('configuracion'),
                'module' => null,
                'children' => [
                    [
                        'name' => 'menu_config',
                        'label' => 'Configuración de Menú',
                        'icon' => 'bx bx-menu',
                        'route' => 'admin_menu_config_index',
                        'module' => 'admin',
                        'children' => []
                    ]
                ]
            ]
        ];
    }
}
