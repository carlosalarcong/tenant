<?php

namespace App\Controller\Maintainers\Basic;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\Religion;
use App\Form\Maintainers\Personal\ReligionType;
use App\Repository\Tenant\ReligionRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Religion Controller
 * 
 * Gestiona el mantenedor de Religiones
 * Las religiones son datos compartidos por todos los tenants
 */
#[Route('/maintainers/basic/religion')]
class ReligionController extends AbstractMantenedorController
{
    public function __construct(
        private ReligionRepository $religionRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_religion_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_religion_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_religion_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_religion_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_religion_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'religionCodeHl7', 'isActive'],
            headers: $this->translateColumns(['name', 'hl7_code', 'is_active']),
            filename: 'religiones_' . date('Y-m-d') . '.csv'
        );
    }

    // ========================================================================
    // Implementación de métodos abstractos
    // ========================================================================

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->religionRepository->createQueryBuilder('r')
            ->orderBy('r.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'religionCodeHl7' => 'Código HL7',
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/basic/religion/index.html.twig';
    }

    protected function getFormType(): string
    {
        return ReligionType::class;
    }

    protected function createNewEntity(): object
    {
        return new Religion();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_religion_index';
    }

    // ========================================================================
    // Hooks personalizados (opcional)
    // ========================================================================

    /**
     * Normalizar el código a mayúsculas antes de guardar
     */
    protected function beforeSave(object $entity, Request $request): void
    {
        if ($entity instanceof Religion && $entity->getReligionCodeHl7()) {
            $entity->setReligionCodeHl7(strtoupper($entity->getReligionCodeHl7()));
        }
    }

    /**
     * Mensajes personalizados en español
     */

}
