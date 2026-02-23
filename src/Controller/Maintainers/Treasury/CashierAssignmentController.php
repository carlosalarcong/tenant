<?php

namespace App\Controller\Maintainers\Treasury;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\CashierAssignment;
use App\Form\Maintainers\Treasury\CashierAssignmentType;
use App\Repository\Tenant\CashierAssignmentRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CashierAssignmentController
 *
 * CRUD de asignaciones de cajeros a ubicaciones de caja.
 *
 * Rutas:
 *   GET  /maintainers/treasury/cashier-assignment/          → listado
 *   GET/POST /maintainers/treasury/cashier-assignment/create → crear
 *   GET/POST /maintainers/treasury/cashier-assignment/{id}/edit → editar
 *   POST /maintainers/treasury/cashier-assignment/{id}/deactivate → desactivar
 *   GET  /maintainers/treasury/cashier-assignment/export     → exportar CSV
 *
 * Nota: la ruta de desactivación usa el nombre _delete para que
 * maintainers/_table_row.html.twig la derive automáticamente al sustituir
 * _index → _delete. Físicamente la acción desactiva (isActive = false)
 * en lugar de eliminar, ya que la entidad no tiene campo deletedAt.
 */
#[Route('/maintainers/treasury/cashier-assignment')]
class CashierAssignmentController extends AbstractMantenedorController
{
    public function __construct(
        private readonly CashierAssignmentRepository $repository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator,
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('/', name: 'app_maintainers_treasury_cashier_assignment_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_treasury_cashier_assignment_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    #[Route('/{id}/edit', name: 'app_maintainers_treasury_cashier_assignment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleEdit($request, $id);
    }

    /**
     * Desactiva una asignación de cajero (isActive = false).
     *
     * El nombre de ruta termina en _delete para que _table_row.html.twig
     * derive el botón correctamente. La URL /deactivate documenta la acción real.
     */
    #[Route('/{id}/deactivate', name: 'app_maintainers_treasury_cashier_assignment_delete', methods: ['POST'])]
    public function deactivate(int $id): Response
    {
        $assignment = $this->repository->find($id);

        if (!$assignment) {
            $this->addFlash('error', 'Asignación no encontrada.');
            return $this->redirectToRoute('app_maintainers_treasury_cashier_assignment_index');
        }

        $assignment->setIsActive(false);
        $assignment->setUpdatedAt(new \DateTime());
        $this->entityManager->flush();

        $this->addFlash('success', 'Asignación desactivada correctamente.');

        return $this->redirectToRoute('app_maintainers_treasury_cashier_assignment_index');
    }

    #[Route('/export', name: 'app_maintainers_treasury_cashier_assignment_export', methods: ['GET'])]
    public function export(Request $request): Response
    {
        $exportColumns = $this->getExportColumns();
        return $this->handleExport(
            request: $request,
            columns: array_keys($exportColumns),
            headers: array_values($exportColumns),
            filename: $this->getExportFileName(),
        );
    }

    // ── Template Method overrides ─────────────────────────────────────────────

    protected function getEntityClass(): string
    {
        return CashierAssignment::class;
    }

    protected function getFormType(): string
    {
        return CashierAssignmentType::class;
    }

    protected function getRepository()
    {
        return $this->repository;
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/treasury/cashier_assignment/index.html.twig';
    }

    protected function getRoutePrefix(): string
    {
        return 'app_maintainers_treasury_cashier_assignment';
    }

    protected function getColumns(): array
    {
        return [
            'id'                        => 'ID',
            'member.username'           => 'Cajero',
            'cashRegisterLocation.name' => 'Ubicación de caja',
            'isActive'                  => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers'),
            'createdAt'                 => 'Fecha asignación',
        ];
    }

    protected function createNewEntity(): object
    {
        return new CashierAssignment();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_treasury_cashier_assignment_index';
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->repository->createQueryBuilder('ca')
            ->leftJoin('ca.member', 'm')->addSelect('m')
            ->leftJoin('ca.cashRegisterLocation', 'crl')->addSelect('crl')
            ->orderBy('m.username', 'ASC');
    }

    protected function getExportColumns(): array
    {
        return [
            'id'                        => 'ID',
            'member.username'           => 'Cajero',
            'cashRegisterLocation.name' => 'Ubicación de caja',
            'isActive'                  => 'Activo',
            'createdAt'                 => 'Fecha asignación',
        ];
    }

    protected function getExportFileName(): string
    {
        return 'asignaciones_cajeros_' . date('Y-m-d_His') . '.csv';
    }
}
