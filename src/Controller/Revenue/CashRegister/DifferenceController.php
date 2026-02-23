<?php

namespace App\Controller\Revenue\CashRegister;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\Member;
use App\Repository\Tenant\DifferenceDirectionRepository;
use App\Repository\Tenant\DifferenceReasonRepository;
use App\Repository\Tenant\DifferenceRepository;
use App\Repository\Tenant\DifferenceTypeRepository;
use App\Repository\Tenant\PatientAccountRepository;
use App\Service\Revenue\CashRegister\DifferenceService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * DifferenceController
 *
 * Gestiona el flujo de diferencias (descuentos con autorización de supervisor):
 *
 *  - Formulario de solicitud  (GET  /form)
 *  - Creación de solicitud    (POST /request)  → auto-aprueba si el tipo lo permite
 *  - Polling de estado        (GET  /{id}/status) → Turbo Frame sin cache
 *  - Cancelación              (POST /{id}/cancel)
 *
 * Legacy: DiferenciaController / AutorizarDiferenciaAction
 */
#[Route('/revenue/cash-register/difference', name: 'app_revenue_cash_register_difference_')]
class DifferenceController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly DifferenceService             $differenceService,
        private readonly DifferenceRepository          $differenceRepository,
        private readonly DifferenceTypeRepository      $differenceTypeRepository,
        private readonly DifferenceReasonRepository    $differenceReasonRepository,
        private readonly DifferenceDirectionRepository $differenceDirectionRepository,
        private readonly PatientAccountRepository      $patientAccountRepository,
    ) {}

    // -------------------------------------------------------------------------
    // Formulario de solicitud
    // -------------------------------------------------------------------------

    /**
     * Muestra el formulario de solicitud de diferencia dentro de
     * turbo-frame#difference-panel.
     *
     * Query params opcionales:
     *   - total_account      — monto de la cuenta (pre-rellena el campo oculto)
     *   - patient_account_id — ID de PatientAccount para asociar la diferencia
     */
    #[Route('/form', name: 'form', methods: ['GET'])]
    public function form(Request $request): Response
    {
        return $this->render('revenue/cash-register/difference/_form.html.twig', [
            'types'            => $this->differenceTypeRepository->findAllActive(),
            'reasons'          => $this->differenceReasonRepository->findAllActive(),
            'directions'       => $this->differenceDirectionRepository->findAllActive(),
            'totalAccount'     => $request->query->get('total_account', '0'),
            'patientAccountId' => $request->query->get('patient_account_id', ''),
        ]);
    }

    // -------------------------------------------------------------------------
    // Creación de solicitud
    // -------------------------------------------------------------------------

    /**
     * Crea la solicitud de diferencia y, si el tipo lo permite, la auto-aprueba.
     *
     * Si la diferencia queda pendiente, la respuesta incluye el Stimulus
     * controller + turbo-frame de polling para que el cajero espere la
     * autorización del supervisor.
     *
     * Responde con un turbo-frame#difference-panel listo para ser insertado en
     * la página (Turbo maneja automáticamente la sustitución de frame al recibir
     * un POST desde un <form> dentro de turbo-frame#difference-panel).
     */
    #[Route('/request', name: 'request', methods: ['POST'])]
    public function request(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('difference_request', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        $member = $this->resolveCurrentMember();

        // Resolver PatientAccount si viene informado
        $patientAccount = null;
        $patientAccountId = $request->request->getInt('patient_account_id');
        if ($patientAccountId > 0) {
            $patientAccount = $this->patientAccountRepository->find($patientAccountId);
        }

        $data = [
            'difference_type_id'      => $request->request->getInt('difference_type_id'),
            'difference_reason_id'    => $request->request->getInt('difference_reason_id'),
            'difference_direction_id' => $request->request->getInt('difference_direction_id'),
            'total_account'           => $request->request->get('total_account', '0'),
            'total_discount'          => $request->request->get('total_discount', '0'),
            'total_after_discount'    => $request->request->get('total_after_discount', '0'),
        ];

        $difference = $this->differenceService->requestDiscount($member, $data, $patientAccount);
        $this->differenceService->autoApproveIfEligible($difference);

        $statusUrl = $this->generateUrl(
            'app_revenue_cash_register_difference_status',
            ['id' => $difference->getId()]
        );
        $cancelUrl = $this->generateUrl(
            'app_revenue_cash_register_difference_cancel',
            ['id' => $difference->getId()]
        );

        return $this->render('revenue/cash-register/difference/_form.html.twig', [
            'difference'  => $difference,
            'types'       => [],
            'reasons'     => [],
            'directions'  => [],
            'statusUrl'   => $statusUrl,
            'cancelUrl'   => $cancelUrl,
        ]);
    }

    // -------------------------------------------------------------------------
    // Polling de estado
    // -------------------------------------------------------------------------

    /**
     * Retorna el estado actual de la diferencia como turbo-frame#difference-status.
     *
     * Este endpoint es consultado periódicamente (cada 3 s) por el Stimulus
     * controller `revenue--difference`. Cuando el estado es final, el controller
     * detiene el polling y emite el evento `difference:approved` o muestra el
     * rechazo al usuario.
     *
     * La respuesta lleva Cache-Control: no-cache para evitar que el navegador
     * o proxies cacheen resultados intermedios.
     */
    #[Route('/{id}/status', name: 'status', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function status(int $id): Response
    {
        $difference = $this->differenceRepository->findWithDetailsById($id);
        if ($difference === null) {
            throw $this->createNotFoundException('Diferencia no encontrada.');
        }

        $response = $this->render('revenue/cash-register/difference/_status.html.twig', [
            'difference' => $difference,
        ]);

        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    // -------------------------------------------------------------------------
    // Cancelación
    // -------------------------------------------------------------------------

    /**
     * Anula la solicitud de diferencia (solo si está pendiente).
     *
     * Después de la anulación re-renderiza el frame con el estado actualizado.
     */
    #[Route('/{id}/cancel', name: 'cancel', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function cancel(int $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('difference_cancel_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        $difference = $this->differenceRepository->find($id);
        if ($difference === null) {
            throw $this->createNotFoundException('Diferencia no encontrada.');
        }

        if ($difference->isPending()) {
            $this->differenceService->cancel($difference);
        }

        $statusUrl = $this->generateUrl(
            'app_revenue_cash_register_difference_status',
            ['id' => $id]
        );
        $cancelUrl = $this->generateUrl(
            'app_revenue_cash_register_difference_cancel',
            ['id' => $id]
        );

        return $this->render('revenue/cash-register/difference/_form.html.twig', [
            'difference'  => $difference,
            'types'       => [],
            'reasons'     => [],
            'directions'  => [],
            'statusUrl'   => $statusUrl,
            'cancelUrl'   => $cancelUrl,
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function resolveCurrentMember(): Member
    {
        $user = $this->getUser();
        if (!$user instanceof Member) {
            throw $this->createAccessDeniedException('Usuario no autenticado como cajero.');
        }
        return $user;
    }
}
