<?php

namespace App\Controller\Revenue\CashRegister;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\Member;
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
 *   - GET /revenue/cash-register                             → cobro directo
 *   - GET /revenue/cash-register/from-admission/{id}        → cobro vinculado a admisión
 *   - GET /revenue/cash-register/from-appointment/{id}      → cobro vinculado a cita
 *   - GET /revenue/cash-register/status-bar                 → Turbo Frame de barra de estado
 *
 * El index renderiza el layout completo con los Turbo Frames y el orquestador
 * Stimulus (revenue--cash-register). Los frames internos se cargan de forma lazy
 * desde sus respectivos controllers (PatientSearch, Services, PostPayment, etc.).
 *
 * Legacy: CajaController / IndexCajaAction
 */
#[Route('/revenue/cash-register', name: 'app_revenue_cash_register_')]
class CashRegisterController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly CashRegisterService        $cashRegisterService,
        private readonly CashRegisterRepository     $cashRegisterRepository,
        private readonly PaymentMethodConfigRegistry $configRegistry,
        private readonly FormFactoryInterface       $formFactory,
    ) {}

    // -------------------------------------------------------------------------
    // Index — shell principal
    // -------------------------------------------------------------------------

    /** Cobro directo (sin origen específico). */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->renderShell('direct');
    }

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
     * Crea una fila inicial de formulario por cada método de pago registrado
     * (igual que AdmissionWizardController::createInitialPaymentRows).
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
