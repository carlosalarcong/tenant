<?php

namespace App\Controller\Revenue\CashRegister;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\Member;
use App\Repository\Tenant\CashRegisterLocationRepository;
use App\Repository\Tenant\CashRegisterRepository;
use App\Service\Revenue\CashRegister\CashRegisterService;
use App\Service\Revenue\Payment\PaymentMethodConfigRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * CashRegisterController
 *
 * Shell principal del módulo de caja.
 *
 * Entrypoints:
 *   - GET /revenue/cash-register                             → dashboard con tabs (entrada principal)
 *   - GET /revenue/cash-register/from-admission/{id}        → cobro directo vinculado a admisión
 *   - GET /revenue/cash-register/from-appointment/{id}      → cobro directo vinculado a cita
 *   - GET /revenue/cash-register/status-bar                 → Turbo Frame de barra de estado
 *
 * El index renderiza el dashboard con tabs (Gestión Caja / Pago Paciente / Informes).
 * El tab activo por defecto depende del estado operativo de la caja del cajero.
 * Los accesos desde admisión/cita renderizan el shell de cobro directamente, sin tabs.
 *
 * Legacy: CajaController / IndexCajaAction + DefaultController::indexAction
 */
#[Route('/revenue/cash-register', name: 'app_revenue_cash_register_')]
class CashRegisterController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly CashRegisterService           $cashRegisterService,
        private readonly CashRegisterRepository        $cashRegisterRepository,
        private readonly CashRegisterLocationRepository $locationRepository,
        private readonly PaymentMethodConfigRegistry   $configRegistry,
        private readonly FormFactoryInterface          $formFactory,
    ) {}

    // -------------------------------------------------------------------------
    // Index — dashboard de entrada con tabs
    // -------------------------------------------------------------------------

    /**
     * Pantalla de entrada del módulo de Caja.
     *
     * Renderiza el dashboard con 3 tabs:
     *   - Gestión Caja  → activo por defecto si la caja NO está operativa
     *   - Pago Paciente → activo por defecto si la caja está operativa (status 'open')
     *   - Informes      → placeholder (Fase 2)
     *
     * Legacy: DefaultController::indexAction + validacionComplementariaCaja
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $member     = $this->resolveCurrentMember();
        $cashStatus = $this->cashRegisterService->validateOperatingStatus($member);

        $openRegister = ($cashStatus !== 'closed')
            ? $this->cashRegisterRepository->findOpenByMember($member)
            : null;

        $locations = ($cashStatus === 'closed')
            ? $this->locationRepository->findAllActive()
            : [];

        $lastClosedRegister = ($cashStatus === 'closed')
            ? $this->cashRegisterRepository->findLastClosedByMember($member)
            : null;

        return $this->render('revenue/dashboard/index.html.twig', [
            'cashStatus'          => $cashStatus,
            'openRegister'        => $openRegister,
            'lastClosedRegister'  => $lastClosedRegister,
            'locations'           => $locations,
            'context'           => 'direct',
            'admissionRecordId' => 0,
            'appointmentId'     => 0,
            'payment_methods'   => $this->configRegistry->all(),
            'initial_rows'      => $this->createInitialPaymentRows(),
            'statusBarUrl'      => $this->generateUrl('app_revenue_cash_register_status_bar'),
            'patientSearchUrl'  => $this->generateUrl('app_revenue_cash_register_patient_search'),
            'servicesUrl'       => $this->generateUrl('app_revenue_cash_register_services_panel'),
            'confirmUrl'        => $this->generateUrl('app_revenue_cash_register_payment_confirm'),
            'differenceFormUrl' => $this->generateUrl('app_revenue_cash_register_difference_form'),
        ]);
    }

    // -------------------------------------------------------------------------
    // Accesos contextuales — renderizan el shell de cobro directamente
    // -------------------------------------------------------------------------

    /** Cobro originado desde una admisión (pasa admissionRecordId al confirm). */
    #[Route('/from-admission/{admissionRecordId}', name: 'from_admission', methods: ['GET'], requirements: ['admissionRecordId' => '\d+'])]
    public function fromAdmission(int $admissionRecordId): Response
    {
        return $this->renderShell('from_admission', admissionRecordId: $admissionRecordId);
    }

    /** Cobro originado desde una cita médica (pasa appointmentId al confirm). */
    #[Route('/from-appointment/{appointmentId}', name: 'from_appointment', methods: ['GET'], requirements: ['appointmentId' => '\d+'])]
    public function fromAppointment(int $appointmentId): Response
    {
        return $this->renderShell('from_appointment', appointmentId: $appointmentId);
    }

    // -------------------------------------------------------------------------
    // Status bar (Turbo Frame — sin layout)
    // -------------------------------------------------------------------------

    /**
     * Retorna el Turbo Frame cash-register-status-bar con el estado operativo
     * de la caja del cajero actual.
     *
     * Estado posible: open | no_voucher | unclosed | closed
     * (ver CashRegisterService::validateOperatingStatus)
     */
    #[Route('/status-bar', name: 'status_bar', methods: ['GET'])]
    public function statusBar(): Response
    {
        $member = $this->resolveCurrentMember();
        $status = $this->cashRegisterService->validateOperatingStatus($member);

        $openRegister = null;
        if (in_array($status, ['open', 'no_voucher', 'unclosed'], true)) {
            $openRegister = $this->cashRegisterRepository->findOpenByMember($member);
        }

        return $this->render('revenue/cash-register/_status_bar.html.twig', [
            'status'       => $status,
            'openRegister' => $openRegister,
        ]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Renderiza el shell de cobro directo (sin tabs de dashboard).
     * Usado por fromAdmission y fromAppointment.
     */
    private function renderShell(
        string $context,
        int $admissionRecordId = 0,
        int $appointmentId = 0,
    ): Response {
        return $this->render('revenue/cash-register/index.html.twig', [
            'context'            => $context,
            'admissionRecordId'  => $admissionRecordId,
            'appointmentId'      => $appointmentId,
            'payment_methods'    => $this->configRegistry->all(),
            'initial_rows'       => $this->createInitialPaymentRows(),
            'statusBarUrl'       => $this->generateUrl('app_revenue_cash_register_status_bar'),
            'patientSearchUrl'   => $this->generateUrl('app_revenue_cash_register_patient_search'),
            'servicesUrl'        => $this->generateUrl('app_revenue_cash_register_services_panel'),
            'confirmUrl'         => $this->generateUrl('app_revenue_cash_register_payment_confirm'),
            'differenceFormUrl'  => $this->generateUrl('app_revenue_cash_register_difference_form'),
        ]);
    }

    /**
     * Crea una fila inicial de formulario por cada método de pago registrado.
     *
     * @return array<string, \Symfony\Component\Form\FormView>
     */
    private function createInitialPaymentRows(): array
    {
        $rows = [];
        foreach ($this->configRegistry->all() as $methodCode => $config) {
            $form = $this->formFactory->createNamed(
                sprintf('payment_batch_rows_%s_%d', $methodCode, 0),
                $config['form_type'],
                null,
                ['csrf_protection' => false]
            );
            $rows[$methodCode] = $form->createView();
        }
        return $rows;
    }

    private function resolveCurrentMember(): Member
    {
        $user = $this->getUser();
        if (!$user instanceof Member) {
            throw $this->createAccessDeniedException('Usuario no autenticado como cajero.');
        }
        return $user;
    }
}
