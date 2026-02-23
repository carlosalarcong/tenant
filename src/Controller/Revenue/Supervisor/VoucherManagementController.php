<?php

namespace App\Controller\Revenue\Supervisor;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\Member;
use App\Repository\Tenant\VoucherEntryRepository;
use App\Repository\Tenant\VoucherRepository;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * VoucherManagementController  (MantenedorFolios)
 *
 * Gestión operacional de folios/VoucherEntry desde la vista del supervisor.
 * Permite consultar el estado de cada folio emitido y anular los que
 * corresponda (isCancelled = true).
 *
 * Rutas:
 *   GET  /revenue/supervisor/vouchers          → listado con filtros (folio, fecha, estado)
 *   POST /revenue/supervisor/vouchers/{id}/void → anula un VoucherEntry
 *
 * Nota: "isVoid=true, voidedAt, voidedBy" del spec se mapean a
 * VoucherEntry::isCancelled (la entidad no tiene voidedAt ni voidedBy).
 * Si se necesitan esos campos se agrega la migración de esquema y los setters.
 *
 * TODO fase 2:
 *   - Habilitar un folio previamente anulado (handleRestore)
 *   - Auditoría detallada de cambios de estado por folio
 *   - Vista de detalle individual de un folio
 */
#[Route('/revenue/supervisor/vouchers', name: 'app_revenue_supervisor_vouchers_')]
#[IsGranted('ROLE_CASH_SUPERVISOR')]
class VoucherManagementController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly VoucherEntryRepository $voucherEntryRepository,
        private readonly VoucherRepository      $voucherRepository,
        private readonly TenantEntityManager    $em,
    ) {}

    // ── Listado ───────────────────────────────────────────────────────────────

    /**
     * Lista VoucherEntry con filtros opcionales por folio, fecha y estado.
     * Paginado a 50 registros por página.
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $folioFilter  = $request->query->get('folio', '');
        $statusFilter = $request->query->get('status', 'all');    // all | active | cancelled
        $dateFrom     = $request->query->get('date_from', '');
        $dateTo       = $request->query->get('date_to', '');

        $qb = $this->voucherEntryRepository->createQueryBuilder('ve')
            ->leftJoin('ve.voucher', 'v')->addSelect('v')
            ->leftJoin('v.cashRegisterLocation', 'crl')->addSelect('crl')
            ->leftJoin('ve.member', 'm')->addSelect('m')
            ->orderBy('ve.issuedAt', 'DESC')
            ->setMaxResults(50);

        if ($folioFilter !== '') {
            $qb->andWhere('ve.folioNumber = :folio')
               ->setParameter('folio', (int) $folioFilter);
        }

        if ($statusFilter === 'active') {
            $qb->andWhere('ve.isCancelled = false');
        } elseif ($statusFilter === 'cancelled') {
            $qb->andWhere('ve.isCancelled = true');
        }

        if ($dateFrom !== '') {
            $qb->andWhere('ve.issuedAt >= :dateFrom')
               ->setParameter('dateFrom', new \DateTime($dateFrom . ' 00:00:00'));
        }

        if ($dateTo !== '') {
            $qb->andWhere('ve.issuedAt <= :dateTo')
               ->setParameter('dateTo', new \DateTime($dateTo . ' 23:59:59'));
        }

        $entries = $qb->getQuery()->getResult();

        return $this->render('revenue/supervisor/voucher_management/index.html.twig', [
            'entries'      => $entries,
            'folioFilter'  => $folioFilter,
            'statusFilter' => $statusFilter,
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
        ]);
    }

    // ── Anulación ─────────────────────────────────────────────────────────────

    /**
     * Anula un VoucherEntry (folio emitido).
     * Setea isCancelled = true.
     *
     * Nota: la entidad no tiene voidedAt ni voidedBy. Si se requiere trazabilidad
     * completa, agregar esos campos en una migración posterior.
     */
    #[Route('/{id}/void', name: 'void', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function void(int $id): Response
    {
        $entry = $this->voucherEntryRepository->find($id);

        if (!$entry) {
            $this->addFlash('error', 'Folio no encontrado.');
            return $this->redirectToRoute('app_revenue_supervisor_vouchers_index');
        }

        if ($entry->isCancelled()) {
            $this->addFlash('error', sprintf('El folio #%d ya fue anulado.', $entry->getFolioNumber()));
            return $this->redirectToRoute('app_revenue_supervisor_vouchers_index');
        }

        $entry->setIsCancelled(true);
        $this->em->flush();

        $this->addFlash('success', sprintf('Folio #%d anulado correctamente.', $entry->getFolioNumber()));

        return $this->redirectToRoute('app_revenue_supervisor_vouchers_index');
    }
}
