<?php

namespace App\Controller\Revenue\CashRegister;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\CashRegister;
use App\Entity\Tenant\Member;
use App\Repository\Tenant\CashRegisterDetailRepository;
use App\Repository\Tenant\CashRegisterLocationRepository;
use App\Repository\Tenant\CashRegisterRepository;
use App\Repository\Tenant\PaymentMethodRepository;
use App\Service\Revenue\CashRegister\CashRegisterService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * CashOpeningController
 *
 * Gestiona el ciclo de vida de apertura y cierre de caja, más el reporte de cierre.
 *
 * Todos los endpoints retornan Turbo Frames — sin layout completo.
 * La barra de estado (cash-register-status-bar) se actualiza en el redirect
 * posterior al POST ya que Turbo reemplaza el frame activo tras el 303.
 *
 * Legacy: GestionCaja/GestionCajaController
 */
#[Route('/revenue/cash-register', name: 'app_revenue_cash_register_')]
class CashOpeningController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly CashRegisterService $cashRegisterService,
        private readonly CashRegisterRepository $cashRegisterRepository,
        private readonly CashRegisterDetailRepository $cashRegisterDetailRepository,
        private readonly CashRegisterLocationRepository $locationRepository,
        private readonly PaymentMethodRepository $paymentMethodRepository,
    ) {}

    // -------------------------------------------------------------------------
    // Apertura
    // -------------------------------------------------------------------------

    /**
     * Formulario de apertura de caja.
     *
     * Evalúa el estado operativo del cajero y renderiza:
     *   - 'closed'     → selector de ubicación (flujo normal de apertura)
     *   - 'open'       → aviso: ya existe caja abierta hoy + link a cierre
     *   - 'unclosed'   → error: caja de días anteriores sin cerrar
     *   - 'no_voucher' → error: no hay talonario con folios disponibles
     *
     * Turbo Frame: cash-register-open
     * Legacy: gestionAbrirCajaAction (GET parte)
     */
    #[Route('/open', name: 'open', methods: ['GET'])]
    public function open(): Response
    {
        $member = $this->resolveCurrentMember();
        $status = $this->cashRegisterService->validateOperatingStatus($member);

        $locations = ($status === 'closed')
            ? $this->locationRepository->findAllActive()
            : [];

        $openRegister = in_array($status, ['open', 'no_voucher'], true)
            ? $this->findOpenRegisterForMember($member)
            : null;

        return $this->render('revenue/cash-register/opening/_open_panel.html.twig', [
            'status'       => $status,
            'locations'    => $locations,
            'openRegister' => $openRegister,
        ]);
    }

    /**
     * Procesa la apertura de caja.
     *
     * Solo ejecuta si el estado es 'closed'; en cualquier otro caso redirige
     * con mensaje de error sin crear un registro duplicado.
     *
     * Guarda el ID de la nueva caja en sesión (cash_register_id).
     * Redirige al GET /open para que el frame se actualice con el nuevo estado.
     *
     * Legacy: gestionAbrirCajaAction (POST parte)
     */
    #[Route('/open', name: 'open_submit', methods: ['POST'])]
    public function openSubmit(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('cash_register_open', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        $member = $this->resolveCurrentMember();
        $status = $this->cashRegisterService->validateOperatingStatus($member);

        if ($status !== 'closed') {
            $this->addFlash('warning', match ($status) {
                'open'       => 'Ya tienes una caja abierta para hoy.',
                'unclosed'   => 'Tienes una caja de un día anterior sin cerrar. Ciérrala antes de abrir una nueva.',
                'no_voucher' => 'No hay talonario activo con folios disponibles para esta caja.',
                default      => 'No es posible abrir una caja en el estado actual.',
            });

            return $this->redirectToRoute('app_revenue_cash_register_open');
        }

        $locationId = (int) $request->request->get('location_id', 0);
        if ($locationId <= 0) {
            $this->addFlash('danger', 'Debe seleccionar una ubicación de caja.');
            return $this->redirectToRoute('app_revenue_cash_register_open');
        }

        $location = $this->locationRepository->find($locationId);
        if ($location === null) {
            throw $this->createNotFoundException('Ubicación de caja no encontrada.');
        }

        $cashRegister = $this->cashRegisterService->openForUser($member, $location);

        $request->getSession()->set('cash_register_id', $cashRegister->getId());

        $this->addFlash('success', sprintf(
            'Caja "%s" abierta correctamente.',
            $location->getName()
        ));

        return $this->redirectToRoute('app_revenue_cash_register_open');
    }

    // -------------------------------------------------------------------------
    // Cierre
    // -------------------------------------------------------------------------

    /**
     * Formulario de cierre de caja.
     *
     * Muestra el formulario con una fila de entrada por cada forma de pago activa
     * y los totales esperados del sistema (actualmente siempre $0 hasta Fase C).
     *
     * Turbo Frame: cash-register-close
     * Legacy: gestionCerrarCajaAction
     */
    #[Route('/close/{id}', name: 'close', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function close(int $id): Response
    {
        $cashRegister = $this->cashRegisterRepository->findWithDetailsById($id);
        if ($cashRegister === null) {
            throw $this->createNotFoundException('Registro de caja no encontrado.');
        }

        $this->denyAccessIfNotOwner($cashRegister);

        $paymentMethods = $this->paymentMethodRepository->findAllActive();

        return $this->render('revenue/cash-register/closing/_close_form.html.twig', [
            'cashRegister'  => $cashRegister,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    /**
     * Procesa el cierre de caja.
     *
     * Persiste CashRegisterDetail por forma de pago, calcula surplus/deficit,
     * marca la caja como cerrada y limpia la sesión.
     *
     * Redirige al reporte de cierre.
     *
     * Legacy: gestionCerrarCajaCerradoAction
     */
    #[Route('/close/{id}', name: 'close_submit', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function closeSubmit(int $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('cash_register_close_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        $cashRegister = $this->cashRegisterRepository->find($id);
        if ($cashRegister === null) {
            throw $this->createNotFoundException('Registro de caja no encontrado.');
        }

        $this->denyAccessIfNotOwner($cashRegister);

        if ($cashRegister->getStatus() !== 'abierta') {
            $this->addFlash('warning', 'Esta caja ya fue cerrada anteriormente.');
            return $this->redirectToRoute('app_revenue_cash_register_report', ['id' => $id]);
        }

        // Construir detailData desde el formulario: amounts[{payment_method_id}] = monto
        $amounts  = $request->request->all('amounts');
        $bankIds  = $request->request->all('bank_ids');
        $deposits = $request->request->all('deposit_numbers');

        $detailData = [];
        foreach ($amounts as $methodId => $amount) {
            $amountClean = str_replace(['.', ','], ['', '.'], (string) $amount);
            if (!is_numeric($amountClean) || (float) $amountClean < 0) {
                continue;
            }
            $detailData[] = [
                'payment_method_id' => (int) $methodId,
                'amount'            => number_format((float) $amountClean, 2, '.', ''),
                'bank_id'           => isset($bankIds[$methodId]) ? (int) $bankIds[$methodId] : null,
                'deposit_number'    => $deposits[$methodId] ?? null,
            ];
        }

        $this->cashRegisterService->closeRegister($cashRegister, $detailData);

        $request->getSession()->remove('cash_register_id');

        $this->addFlash('success', 'Caja cerrada correctamente.');

        return $this->redirectToRoute('app_revenue_cash_register_report', ['id' => $id]);
    }

    // -------------------------------------------------------------------------
    // Reporte
    // -------------------------------------------------------------------------

    /**
     * Resumen HTML del cierre de caja.
     * (Sin PDF por ahora — Fase G.)
     *
     * Turbo Frame: cash-register-report
     * Legacy: gestionInformeCajaAction
     */
    #[Route('/report/{id}', name: 'report', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function report(int $id): Response
    {
        $cashRegister = $this->cashRegisterRepository->findWithDetailsById($id);
        if ($cashRegister === null) {
            throw $this->createNotFoundException('Registro de caja no encontrado.');
        }

        // Cargar los detalles del cierre por forma de pago
        $details = $this->loadCloseDetails($id);

        return $this->render('revenue/cash-register/closing/_close_report.html.twig', [
            'cashRegister' => $cashRegister,
            'details'      => $details,
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Retorna el Member autenticado.
     * $this->getUser() retorna Member directamente porque MemberProvider
     * carga App\Entity\Tenant\Member como UserInterface.
     */
    private function resolveCurrentMember(): Member
    {
        $user = $this->getUser();
        if (!$user instanceof Member) {
            throw $this->createAccessDeniedException('Usuario no autenticado como cajero.');
        }
        return $user;
    }

    /**
     * Busca la caja actualmente abierta del cajero para mostrarla en el panel de estado.
     * Duplica la lógica del servicio pero solo consulta (no lanza excepción si no hay).
     */
    private function findOpenRegisterForMember(Member $member): ?CashRegister
    {
        return $this->cashRegisterRepository
            ->createQueryBuilder('cr')
            ->leftJoin('cr.cashRegisterLocation', 'loc')
            ->addSelect('loc')
            ->where('cr.member = :member')
            ->andWhere('cr.status = :status')
            ->setParameter('member', $member)
            ->setParameter('status', 'abierta')
            ->orderBy('cr.openedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Lanza acceso denegado si la caja no pertenece al usuario autenticado.
     * Los supervisores pueden ver cajas ajenas (rol ROLE_ADMIN).
     */
    private function denyAccessIfNotOwner(CashRegister $cashRegister): void
    {
        $member = $this->resolveCurrentMember();
        $isOwner = $cashRegister->getMember()?->getId() === $member->getId();
        $isSupervisor = $this->isGranted('ROLE_ADMIN');

        if (!$isOwner && !$isSupervisor) {
            throw $this->createAccessDeniedException('No tienes permiso para acceder a esta caja.');
        }
    }

    /**
     * Carga los detalles de cierre (CashRegisterDetail) para el reporte.
     *
     * @return \App\Entity\Tenant\CashRegisterDetail[]
     */
    private function loadCloseDetails(int $cashRegisterId): array
    {
        return $this->cashRegisterDetailRepository->findByCashRegisterId($cashRegisterId);
    }
}
