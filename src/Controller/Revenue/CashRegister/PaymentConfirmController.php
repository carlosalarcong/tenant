<?php

namespace App\Controller\Revenue\CashRegister;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\CashRegister;
use App\Entity\Tenant\ClinicalActionPatient;
use App\Entity\Tenant\Member;
use App\Entity\Tenant\PatientAccount;
use App\Entity\Tenant\PaymentAccount;
use App\Entity\Tenant\PaymentAccountDetail;
use App\Repository\Tenant\BillingItemRepository;
use App\Repository\Tenant\CashRegisterRepository;
use App\Repository\Tenant\PatientAccountRepository;
use App\Repository\Tenant\PatientRepository;
use App\Repository\Tenant\PaymentMethodRepository;
use App\Repository\Tenant\PaymentStatusRepository;
use App\Service\Revenue\CashRegister\VoucherService;
use App\Service\Revenue\Dte\DteService;
use App\Service\Revenue\Payment\PaymentBatchProcessor;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * PaymentConfirmController
 *
 * Núcleo transaccional del flujo de caja.
 * Procesa el formulario completo de cobro en una única transacción Doctrine:
 *
 *   1. Crear / recuperar PatientAccount
 *   2. Crear PaymentAccount
 *   3. Persistir prestaciones como ClinicalActionPatient
 *   4. Procesar medios de pago con PaymentBatchProcessor
 *   5. Persistir detalles como PaymentAccountDetail
 *   6. Consumir folio con VoucherService
 *   7. Stub DTE (log, no bloquea)
 *   8. flush() único
 *
 * En éxito: Turbo Stream reemplaza el frame payment-panel con un frame
 * apuntando al resumen del pago.
 * En error: Turbo Stream reemplaza el frame payment-panel con mensajes de error.
 *
 * Legacy: ConfirmarPagoController / procesarPagoAction
 */
#[Route('/revenue/cash-register/payment', name: 'app_revenue_cash_register_payment_')]
class PaymentConfirmController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly TenantEntityManager      $em,
        private readonly PatientRepository        $patientRepository,
        private readonly PatientAccountRepository $patientAccountRepository,
        private readonly BillingItemRepository    $billingItemRepository,
        private readonly PaymentMethodRepository  $paymentMethodRepository,
        private readonly PaymentStatusRepository  $paymentStatusRepository,
        private readonly CashRegisterRepository   $cashRegisterRepository,
        private readonly PaymentBatchProcessor    $paymentBatchProcessor,
        private readonly VoucherService           $voucherService,
        private readonly DteService               $dteService,
        private readonly LoggerInterface          $logger,
    ) {}

    // -------------------------------------------------------------------------
    // Confirmación de pago
    // -------------------------------------------------------------------------

    /**
     * Procesa el formulario completo de cobro dentro de una transacción Doctrine.
     *
     * POST body (form-encoded):
     *   _csrf_token         string
     *   patient_id          int
     *   admission_record_id int|null
     *   services_payload    JSON  (ver CashRegisterServicesController)
     *   payment_batch[rows][{methodCode}][{index}][...] array
     *
     * @see PaymentBatchProcessor::process()
     */
    #[Route('/confirm', name: 'confirm', methods: ['POST'])]
    public function confirm(Request $request): Response
    {
        // ── Validación CSRF ────────────────────────────────────────────────
        if (!$this->isCsrfTokenValid('payment_confirm', $request->request->get('_csrf_token'))) {
            return $this->renderErrorStream(['Token de seguridad inválido. Recarga la página e intenta de nuevo.']);
        }

        // ── Datos del formulario ───────────────────────────────────────────
        $patientId       = (int) $request->request->get('patient_id', 0);
        $servicesPayload = $request->request->get('services_payload', '[]');
        $paymentBatch    = $request->request->all('payment_batch');

        if ($patientId <= 0) {
            return $this->renderErrorStream(['Debes seleccionar un paciente antes de confirmar el pago.']);
        }

        $servicesRows = json_decode($servicesPayload, true);
        if (!is_array($servicesRows)) {
            return $this->renderErrorStream(['El payload de prestaciones es inválido.']);
        }

        // ── Validar batch de medios de pago ────────────────────────────────
        $batch = $this->paymentBatchProcessor->process($paymentBatch);
        if (!$batch->isValid()) {
            $errors = [];
            foreach ($batch->getErrors() as $e) {
                $errors[] = $e;
            }
            foreach ($batch->getRowsFlat() as $row) {
                foreach ($row->getErrors() as $e) {
                    $errors[] = $e;
                }
            }
            return $this->renderErrorStream($errors);
        }

        // ── Cargar entidades base ──────────────────────────────────────────
        $patient = $this->patientRepository->find($patientId);
        if ($patient === null) {
            return $this->renderErrorStream(['Paciente no encontrado.']);
        }

        $member = $this->resolveCurrentMember();

        // Obtener la caja abierta del cajero para la ubicación
        $cashRegister = $this->resolveCurrentCashRegister($request, $member);
        if ($cashRegister === null) {
            return $this->renderErrorStream(['No tienes una caja abierta. Abre una caja antes de procesar pagos.']);
        }
        $cashRegisterLocation = $cashRegister->getCashRegisterLocation();

        // ── Transacción Doctrine ───────────────────────────────────────────
        $this->em->beginTransaction();
        try {
            // ── Paso 1: PatientAccount (crear o recuperar) ─────────────────
            $patientAccount = $this->patientAccountRepository->findByPatientId($patientId);
            $isNewAccount   = false;
            if ($patientAccount === null) {
                $patientAccount = new PatientAccount();
                $patientAccount->setPatient($patient);
                $isNewAccount = true;
            }
            if ($isNewAccount) {
                $this->em->persist($patientAccount);
            }

            // ── Paso 2: PaymentAccount ─────────────────────────────────────
            $paymentAccount = new PaymentAccount();
            $paymentAccount
                ->setPatientAccount($patientAccount)
                ->setPatient($patient)
                ->setCreatedByMember($member)
                ->setCashRegisterLocation($cashRegisterLocation)
                ->setCashRegister($cashRegister)
                ->setPaymentDate(new \DateTime())
                ->setAmount($this->sumServicesTotal($servicesRows));

            // Intentar asignar el estado "pendiente" del catálogo (nullable, no bloquea si no existe)
            $pendingStatus = $this->paymentStatusRepository->findOneBy(['name' => 'Pendiente']);
            if ($pendingStatus !== null) {
                $paymentAccount->setPaymentStatus($pendingStatus);
            }

            $this->em->persist($paymentAccount);

            // ── Paso 3: ClinicalActionPatient (una fila por prestación) ────
            foreach ($servicesRows as $serviceRow) {
                $billingItemId = (int) ($serviceRow['billingItemId'] ?? 0);
                if ($billingItemId <= 0) {
                    continue;
                }

                $billingItem = $this->billingItemRepository->find($billingItemId);
                if ($billingItem === null) {
                    continue; // ítem eliminado entre pasos; se omite silenciosamente
                }

                $unitPrice      = $this->toDecimal($serviceRow['unitAmount']  ?? '0');
                $discountAmount = $this->toDecimal($serviceRow['discount']     ?? '0');
                $quantity       = max(1, (int) ($serviceRow['quantity'] ?? 1));
                $totalAmount    = $this->toDecimal($serviceRow['totalAmount']  ?? '0');

                $clinicalAction = new ClinicalActionPatient();
                $clinicalAction
                    ->setPaymentAccount($paymentAccount)
                    ->setBillingItem($billingItem)
                    ->setQuantity($quantity)
                    ->setUnitPrice($unitPrice)
                    ->setDiscountAmount($discountAmount)
                    ->setTotalAmount($totalAmount);

                $this->em->persist($clinicalAction);
            }

            // ── Paso 4 + 5: Medios de pago → PaymentAccountDetail ─────────
            foreach ($batch->getRowsFlat() as $paymentRow) {
                $methodCode    = $paymentRow->getMethodCode();
                $rowPayload    = $paymentRow->getPayload();
                $paymentMethod = $this->paymentMethodRepository->findByCode($methodCode);

                // Si no hay PaymentMethod en catálogo para este código, omitir (log).
                // Los datos ya están validados por el handler en PaymentBatchProcessor.
                if ($paymentMethod === null) {
                    $this->logger->warning('PaymentMethod no encontrado para código: {code}', ['code' => $methodCode]);
                    continue;
                }

                $detail = new PaymentAccountDetail();
                $detail
                    ->setPaymentAccount($paymentAccount)
                    ->setPaymentMethod($paymentMethod)
                    ->setAmount($this->toDecimal($rowPayload['amount'] ?? '0'))
                    ->setReferenceNumber($this->extractReference($methodCode, $rowPayload))
                    ->setCardLastDigits($this->extractCardLastDigits($methodCode, $rowPayload))
                    ->setInstallments($this->extractInstallments($methodCode, $rowPayload));

                $this->em->persist($detail);
            }

            // ── Paso 6: VoucherEntry (consume folio con PESSIMISTIC_WRITE) ─
            $voucherEntry = $this->voucherService->consumeNextFolio(
                $cashRegisterLocation,
                $paymentAccount,
                $member,
            );

            // ── Paso 7: flush único ───────────────────────────────────────
            $this->em->flush();
            $this->em->commit();

        } catch (\Throwable $e) {
            $this->em->rollback();
            $this->logger->error('Error al confirmar pago: {message}', ['message' => $e->getMessage(), 'exception' => $e]);
            return $this->renderErrorStream([
                'Ocurrió un error al procesar el pago. Por favor inténtalo de nuevo.',
            ]);
        }

        // ── Paso 8: Emitir DTE (fuera de transacción — no revierte el pago) ─
        // El pago ya fue commitado; un fallo en DTE queda registrado en
        // DteDocument.status='error' con retryData para reintento posterior.
        try {
            $this->dteService->emitirBoletas($paymentAccount);
        } catch (\Throwable $e) {
            $this->logger->error('DTE: error inesperado al emitir boletas: {message}', [
                'message'    => $e->getMessage(),
                'paymentId'  => $paymentAccount->getId(),
            ]);
        }

        // ── Limpiar sesión de servicios ────────────────────────────────────
        $request->getSession()->remove('cash_register_services');

        // ── Turbo Stream: redirige el frame al resumen ─────────────────────
        $summaryUrl = $this->generateUrl(
            'app_revenue_cash_register_post_payment_summary',
            ['id' => $paymentAccount->getId()]
        );

        return $this->render('revenue/cash-register/_payment_confirm_stream.html.twig', [
            'success'    => true,
            'summaryUrl' => $summaryUrl,
        ], new Response('', 200, ['Content-Type' => 'text/vnd.turbo-stream.html']));
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

    /**
     * Recupera el CashRegister abierto del cajero.
     * Usa el ID guardado en sesión por CashOpeningController.
     */
    private function resolveCurrentCashRegister(Request $request, Member $member): ?CashRegister
    {
        $cashRegisterId = $request->getSession()->getInt('cash_register_id', 0);
        if ($cashRegisterId <= 0) {
            return null;
        }

        $cashRegister = $this->cashRegisterRepository->find($cashRegisterId);
        if ($cashRegister === null || $cashRegister->getMember()?->getId() !== $member->getId()) {
            return null;
        }

        return $cashRegister;
    }

    /**
     * Suma el totalAmount de las prestaciones usando bcmath.
     *
     * @param array<int, array<string, mixed>> $servicesRows
     */
    private function sumServicesTotal(array $servicesRows): string
    {
        $total = '0.00';
        foreach ($servicesRows as $row) {
            $total = bcadd($total, $this->toDecimal($row['totalAmount'] ?? '0'), 2);
        }
        return $total;
    }

    private function toDecimal(mixed $value): string
    {
        $num = filter_var($value, FILTER_VALIDATE_FLOAT);
        if ($num === false || $num < 0.0) {
            return '0.00';
        }
        return number_format($num, 2, '.', '');
    }

    /**
     * Extrae el número de referencia del payload según el método de pago.
     * Campos usados: transfer_number (bank_transfer), folio (electronic_voucher,
     * manual_voucher), voucher (debit_card), check_number (check).
     *
     * @param array<string, mixed> $payload
     */
    private function extractReference(string $methodCode, array $payload): ?string
    {
        $value = match ($methodCode) {
            'bank_transfer'      => $payload['transfer_number']    ?? null,
            'electronic_voucher' => $payload['folio']              ?? null,
            'manual_voucher'     => $payload['folio']              ?? null,
            'debit_card'         => $payload['voucher']            ?? null,
            'check'              => $payload['check_number']       ?? null,
            'credit_card'        => $payload['authorization_code'] ?? null,
            default              => null,
        };
        return $value !== null && $value !== '' ? (string) $value : null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractCardLastDigits(string $methodCode, array $payload): ?string
    {
        if (in_array($methodCode, ['credit_card', 'debit_card'], true)) {
            $digits = $payload['card_last_digits'] ?? null;
            return $digits !== null && $digits !== '' ? (string) $digits : null;
        }
        return null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractInstallments(string $methodCode, array $payload): ?int
    {
        if ($methodCode === 'credit_card') {
            $installments = $payload['installments'] ?? null;
            return is_numeric($installments) && (int) $installments > 0 ? (int) $installments : null;
        }
        return null;
    }

    /**
     * Genera un Turbo Stream de error que reemplaza el frame payment-panel.
     *
     * @param string[] $errors
     */
    private function renderErrorStream(array $errors): Response
    {
        return $this->render('revenue/cash-register/_payment_confirm_stream.html.twig', [
            'success' => false,
            'errors'  => $errors,
        ], new Response('', 422, ['Content-Type' => 'text/vnd.turbo-stream.html']));
    }
}
