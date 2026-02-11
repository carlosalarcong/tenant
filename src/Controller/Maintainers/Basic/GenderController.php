<?php

namespace App\Controller\Maintainers\Basic;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\Gender;
use App\Form\Maintainers\Personal\GenderType;
use App\Repository\Tenant\GenderRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Gender Controller
 * 
 * Gestiona el mantenedor de Géneros/Sexos
 * Los géneros son datos compartidos por todos los tenants
 */
#[Route('/maintainers/basic/gender')]
class GenderController extends AbstractMantenedorController
{
    public function __construct(
        private GenderRepository $genderRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('', name: 'app_maintainers_gender_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_gender_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_gender_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    #[Route('/{id}/delete', name: 'app_maintainers_gender_delete', methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        return $this->handleDelete($request, $id);
    }
    
    #[Route('/export', name: 'app_maintainers_gender_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        return $this->handleExport(
            request: $request,
            columns: ['name', 'code', 'isActive'],
            headers: $this->translateColumns(['name', 'code', 'is_active']),
            filename: 'generos_' . date('Y-m-d') . '.csv'
        );
    }

    // ========================================================================
    // Implementación de métodos abstractos
    // ========================================================================

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->genderRepository->createQueryBuilder('g')
            ->orderBy('g.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
        'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
        'code' => $this->translator->trans('maintainers.columns.code', [], 'maintainers'),
        'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
    ];
    
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/basic/gender/index.html.twig';
    }

    protected function getFormType(): string
    {
        return GenderType::class;
    }

    protected function createNewEntity(): object
    {
        return new Gender();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_gender_index';
    }

    // ========================================================================
    // Hooks personalizados (opcional)
    // ========================================================================

    /**
     * Normalizar el código a mayúsculas antes de guardar
     */
    protected function beforeSave(object $entity, Request $request): void
    {
        if ($entity instanceof Gender && $entity->getCode()) {
            $entity->setCode(strtoupper($entity->getCode()));
        }
        
        $entity->setUpdatedAt(new \DateTime());
    }

    /**
     * Verificar que no haya registros asociados antes de eliminar
     */
    protected function canDelete(object $entity): bool
    {
        // TODO: Verificar si hay pacientes con este género
        // $count = $this->patientRepository->countByGender($entity);
        // return $count === 0;
        
        return true;
    }

    /**
     * Mensajes personalizados en español
     */

}
