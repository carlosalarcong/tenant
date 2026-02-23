<?php

namespace App\Controller\Revenue\Supervisor;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\Member;
use App\Repository\Tenant\DifferenceRepository;
use App\Service\Revenue\CashRegister\DifferenceService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * DifferenceAuthorizationController
 *
 * Flujo de autorización de descuentos para supervisores de caja.
 *
 * El cajero solicita una diferencia desde el módulo Caja (DifferenceController).
 * Si el monto supera el límite, queda en estado 'solicitada'.
 * El supervisor ve el listado y puede aprobar o rechazar sin recargar la página
 * (Turbo Frame que envuelve la lista de pendientes).
 *
 * Rutas:
 *   GET  /revenue/supervisor/differences              → listado (pendientes + historial)
 *   GET  /revenue/supervisor/differences/{id}         → detalle de una solicitud
 *   POST /revenue/supervisor/differences/{id}/approve → aprueba la diferencia
 *   POST /revenue/supervisor/differences/{id}/reject  → rechaza la diferencia
 */
#[Route('/revenue/supervisor/differences', name: 'app_revenue_supervisor_differences_')]
#[IsGranted('ROLE_CASH_SUPERVISOR')]
class DifferenceAuthorizationController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly DifferenceRepository $differenceRepository,
        private readonly DifferenceService    $differenceService,
    ) {}

    // ── Listado ───────────────────────────────────────────────────────────────

    /**
     * Lista las diferencias pendientes y el historial reciente.
     * La sección de pendientes está envuelta en <turbo-frame id="pending-differences">
     * para que el redirect tras aprobar/rechazar la refresque sin recargar la página.
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $tab = $request->query->get('tab', 'pending');

        $pending = $this->differenceRepository->createQueryBuilder('d')
            ->leftJoin('d.requestedByMember', 'rbm')->addSelect('rbm')
            ->leftJoin('d.differenceType', 'dt')->addSelect('dt')
            ->leftJoin('d.differenceReason', 'dr')->addSelect('dr')
            ->leftJoin('d.patientAccount', 'pa')->addSelect('pa')
            ->where('d.status = :status')
            ->setParameter('status', 'solicitada')
            ->orderBy('d.requestedAt', 'ASC')
            ->getQuery()
            ->getResult();

        $history = $this->differenceRepository->createQueryBuilder('d')
            ->leftJoin('d.requestedByMember', 'rbm')->addSelect('rbm')
            ->leftJoin('d.authorizedByMember', 'abm')->addSelect('abm')
            ->leftJoin('d.differenceType', 'dt')->addSelect('dt')
            ->where('d.status != :status')
            ->setParameter('status', 'solicitada')
            ->orderBy('d.authorizedAt', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();

        return $this->render('revenue/supervisor/difference_authorization/index.html.twig', [
            'pending'     => $pending,
            'history'     => $history,
            'activeTab'   => $tab,
        ]);
    }

    // ── Detalle ───────────────────────────────────────────────────────────────

    /** Vista de detalle de una solicitud de diferencia. */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): Response
    {
        $difference = $this->differenceRepository->findWithDetailsById($id);

        if (!$difference) {
            $this->addFlash('error', 'Solicitud de diferencia no encontrada.');
            return $this->redirectToRoute('app_revenue_supervisor_differences_index');
        }

        return $this->render('revenue/supervisor/difference_authorization/index.html.twig', [
            'pending'    => [],
            'history'    => [],
            'activeTab'  => 'detail',
            'difference' => $difference,
        ]);
    }

    // ── Aprobar ───────────────────────────────────────────────────────────────

    /**
     * Aprueba una diferencia pendiente.
     * Registra el supervisor autorizante y la fecha.
     * Redirige al listado → el Turbo Frame pending-differences se refresca.
     */
    #[Route('/{id}/approve', name: 'approve', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function approve(int $id): Response
    {
        $difference = $this->differenceRepository->find($id);

        if (!$difference) {
            $this->addFlash('error', 'Solicitud de diferencia no encontrada.');
            return $this->redirectToRoute('app_revenue_supervisor_differences_index');
        }

        if (!$difference->isPending()) {
            $this->addFlash('error', sprintf('La diferencia #%d ya fue procesada (estado: %s).', $id, $difference->getStatus()));
            return $this->redirectToRoute('app_revenue_supervisor_differences_index');
        }

        $supervisor = $this->getUser();
        if (!$supervisor instanceof Member) {
            throw $this->createAccessDeniedException('El usuario actual no es un Member válido.');
        }

        if ($supervisor->getId() === $difference->getRequestedByMember()?->getId()) {
            $this->addFlash('error', 'No puede autorizar su propia solicitud de diferencia.');
            return $this->redirectToRoute('app_revenue_supervisor_differences_index', ['tab' => 'pending']);
        }

        $this->differenceService->approve($difference, $supervisor);

        $this->addFlash('success', sprintf(
            'Diferencia #%d aprobada. Descuento de $%s autorizado.',
            $id,
            number_format((float) $difference->getTotalDiscount(), 0, ',', '.')
        ));

        return $this->redirectToRoute('app_revenue_supervisor_differences_index', ['tab' => 'pending']);
    }

    // ── Rechazar ──────────────────────────────────────────────────────────────

    /**
     * Rechaza una diferencia pendiente.
     * Registra el supervisor y la fecha de resolución.
     */
    #[Route('/{id}/reject', name: 'reject', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function reject(int $id): Response
    {
        $difference = $this->differenceRepository->find($id);

        if (!$difference) {
            $this->addFlash('error', 'Solicitud de diferencia no encontrada.');
            return $this->redirectToRoute('app_revenue_supervisor_differences_index');
        }

        if (!$difference->isPending()) {
            $this->addFlash('error', sprintf('La diferencia #%d ya fue procesada (estado: %s).', $id, $difference->getStatus()));
            return $this->redirectToRoute('app_revenue_supervisor_differences_index');
        }

        $supervisor = $this->getUser();
        if (!$supervisor instanceof Member) {
            throw $this->createAccessDeniedException('El usuario actual no es un Member válido.');
        }

        if ($supervisor->getId() === $difference->getRequestedByMember()?->getId()) {
            $this->addFlash('error', 'No puede rechazar su propia solicitud de diferencia.');
            return $this->redirectToRoute('app_revenue_supervisor_differences_index', ['tab' => 'pending']);
        }

        $this->differenceService->reject($difference, $supervisor);

        $this->addFlash('success', sprintf('Diferencia #%d rechazada.', $id));

        return $this->redirectToRoute('app_revenue_supervisor_differences_index', ['tab' => 'pending']);
    }
}
