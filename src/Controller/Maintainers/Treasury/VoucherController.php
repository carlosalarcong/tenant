<?php

namespace App\Controller\Maintainers\Treasury;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\Voucher;
use App\Form\Maintainers\Treasury\VoucherType;
use App\Repository\Tenant\VoucherRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * VoucherController
 *
 * Mantenedor CRUD de talonarios de boletas (Voucher / CorrelativoBoletas).
 *
 * Rutas:
 *   GET  /maintainers/treasury/voucher/             → listado
 *   GET/POST /maintainers/treasury/voucher/create   → nuevo talonario
 *   GET/POST /maintainers/treasury/voucher/{id}/edit → editar
 *                 ⚠ Bloqueado si currentFolio > folioFrom (folios consumidos)
 *   POST /maintainers/treasury/voucher/{id}/deactivate → desactivar
 *   GET  /maintainers/treasury/voucher/export        → exportar CSV
 *
 * Nota: la ruta de desactivación lleva el nombre _delete para que
 * _table_row.html.twig la derive automáticamente (_index → _delete).
 */
#[Route('/maintainers/treasury/voucher')]
class VoucherController extends AbstractMantenedorController
{
    public function __construct(
        private readonly VoucherRepository $repository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator,
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    #[Route('/', name: 'app_maintainers_treasury_voucher_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->handleIndex($request);
    }

    #[Route('/create', name: 'app_maintainers_treasury_voucher_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        return $this->handleCreate($request);
    }

    /**
     * Editar un talonario.
     * Rechaza la edición si ya hay folios consumidos (currentFolio > folioFrom).
     */
    #[Route('/{id}/edit', name: 'app_maintainers_treasury_voucher_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        $voucher = $this->repository->find($id);

        if ($voucher && $voucher->getCurrentFolio() > $voucher->getFolioFrom()) {
            $this->addFlash(
                'error',
                sprintf(
                    'El talonario #%d tiene folios consumidos (folio actual: %d). No se puede editar.',
                    $voucher->getId(),
                    $voucher->getCurrentFolio()
                )
            );
            return $this->redirectToRoute('app_maintainers_treasury_voucher_index');
        }

        return $this->handleEdit($request, $id);
    }

    /**
     * Desactiva un talonario (isActive = false).
     * Nombre _delete para compatibilidad con _table_row.html.twig auto-derivation.
     */
    #[Route('/{id}/deactivate', name: 'app_maintainers_treasury_voucher_delete', methods: ['POST'])]
    public function deactivate(int $id): Response
    {
        $voucher = $this->repository->find($id);

        if (!$voucher) {
            $this->addFlash('error', 'Talonario no encontrado.');
            return $this->redirectToRoute('app_maintainers_treasury_voucher_index');
        }

        $voucher->setIsActive(false);
        $voucher->setUpdatedAt(new \DateTime());
        $this->entityManager->flush();

        $this->addFlash('success', sprintf('Talonario #%d desactivado correctamente.', $voucher->getId()));

        return $this->redirectToRoute('app_maintainers_treasury_voucher_index');
    }

    #[Route('/export', name: 'app_maintainers_treasury_voucher_export', methods: ['GET'])]
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
        return Voucher::class;
    }

    protected function getFormType(): string
    {
        return VoucherType::class;
    }

    protected function getRepository()
    {
        return $this->repository;
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/treasury/voucher/index.html.twig';
    }

    protected function getRoutePrefix(): string
    {
        return 'app_maintainers_treasury_voucher';
    }

    protected function getColumns(): array
    {
        return [
            'id'                        => 'ID',
            'cashRegisterLocation.name' => 'Ubicación de caja',
            'subCompany.name'           => 'Sub-empresa',
            'folioFrom'                 => 'Desde',
            'folioTo'                   => 'Hasta',
            'currentFolio'              => 'Folio actual',
            'isActive'                  => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers'),
        ];
    }

    protected function createNewEntity(): object
    {
        return new Voucher();
    }

    protected function getIndexRoute(): string
    {
        return 'app_maintainers_treasury_voucher_index';
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->repository->createQueryBuilder('v')
            ->leftJoin('v.cashRegisterLocation', 'crl')->addSelect('crl')
            ->leftJoin('v.subCompany', 'sc')->addSelect('sc')
            ->orderBy('crl.name', 'ASC')
            ->addOrderBy('v.folioFrom', 'ASC');
    }

    protected function getExportColumns(): array
    {
        return [
            'id'                        => 'ID',
            'cashRegisterLocation.name' => 'Ubicación de caja',
            'subCompany.name'           => 'Sub-empresa',
            'folioFrom'                 => 'Folio inicial',
            'folioTo'                   => 'Folio final',
            'currentFolio'              => 'Folio actual',
            'isActive'                  => 'Activo',
            'createdAt'                 => 'Fecha creación',
        ];
    }

    protected function getExportFileName(): string
    {
        return 'talonarios_' . date('Y-m-d_His') . '.csv';
    }
}
