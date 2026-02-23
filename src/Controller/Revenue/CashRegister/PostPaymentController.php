<?php

namespace App\Controller\Revenue\CashRegister;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\Member;
use App\Repository\Tenant\ClinicalActionPatientRepository;
use App\Repository\Tenant\PaymentAccountDetailRepository;
use App\Repository\Tenant\PaymentAccountRepository;
use App\Repository\Tenant\PaymentStatusRepository;
use App\Repository\Tenant\VoucherEntryRepository;
use App\Service\Revenue\PdfService;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * PostPaymentController
 *
 * Gestiona la vista post-pago: resumen, historial, impresión de boleta y anulación.
 *
 * Todos los endpoints GET retornan Turbo Frames (sin layout completo).
 * El endpoint de impresión usa data-turbo="false" en el template para
 * que el navegador descargue/muestre el PDF directamente.
 *
 * Legacy: ResumenPagoController / ImprimirBoletaController / AnularPagoController
 */
#[Route('/revenue/cash-register/post-payment', name: 'app_revenue_cash_register_post_payment_')]
class PostPaymentController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly TenantEntityManager             $em,
        private readonly PaymentAccountRepository        $paymentAccountRepository,
        private readonly PaymentAccountDetailRepository  $detailRepository,
        private readonly ClinicalActionPatientRepository $clinicalActionRepository,
        private readonly VoucherEntryRepository          $voucherEntryRepository,
        private readonly PaymentStatusRepository         $paymentStatusRepository,
        private readonly PdfService                      $pdfService,
    ) {}

    // -------------------------------------------------------------------------
    // Resumen del pago
    // -------------------------------------------------------------------------

    /**
     * Vista de resumen post-pago: datos del paciente, prestaciones,
     * medios de pago y folio de boleta.
     *
     * Es el destino del Turbo Stream de éxito de PaymentConfirmController.
     *
     * Turbo Frame: payment-panel
     * Legacy: ResumenPagoAction
     */
    #[Route('/{id}', name: 'summary', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function summary(int $id): Response
    {
        $paymentAccount = $this->paymentAccountRepository->findWithDetailsById($id);
        if ($paymentAccount === null) {
            throw $this->createNotFoundException('Pago no encontrado.');
        }

        $details         = $this->detailRepository->findByPaymentAccount($paymentAccount);
        $clinicalActions = $this->clinicalActionRepository->findByPaymentAccount($paymentAccount);
        $voucherEntry    = $this->voucherEntryRepository->findOneByPaymentAccount($paymentAccount);

        return $this->render('revenue/cash-register/_post_payment.html.twig', [
            'paymentAccount'  => $paymentAccount,
            'details'         => $details,
            'clinicalActions' => $clinicalActions,
            'voucherEntry'    => $voucherEntry,
        ]);
    }

    // -------------------------------------------------------------------------
    // Historial de pagos del paciente
    // -------------------------------------------------------------------------

    /**
     * Tab de historial: muestra los últimos 20 pagos del mismo paciente.
     *
     * Turbo Frame: post-payment-history
     * Legacy: HistorialPagosAction
     */
    #[Route('/{id}/history', name: 'history', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function history(int $id): Response
    {
        $paymentAccount = $this->paymentAccountRepository->find($id);
        if ($paymentAccount === null) {
            throw $this->createNotFoundException('Pago no encontrado.');
        }

        $patient = $paymentAccount->getPatient();
        if ($patient === null) {
            throw $this->createNotFoundException('El pago no tiene paciente asociado.');
        }

        $history = $this->paymentAccountRepository->findHistoryByPatientId(
            (int) $patient->getId(),
            20
        );

        return $this->render('revenue/cash-register/_post_payment_history.html.twig', [
            'currentPaymentAccount' => $paymentAccount,
            'history'               => $history,
        ]);
    }

    // -------------------------------------------------------------------------
    // Impresión / descarga del PDF
    // -------------------------------------------------------------------------

    /**
     * Genera y retorna el PDF de la boleta en línea (inline).
     *
     * El template que enlaza a este endpoint DEBE usar data-turbo="false"
     * para que el navegador maneje la respuesta PDF directamente.
     *
     * Legacy: ImprimirBoletaAction
     */
    #[Route('/{id}/print', name: 'print', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function print(int $id): Response
    {
        $paymentAccount = $this->paymentAccountRepository->findWithDetailsById($id);
        if ($paymentAccount === null) {
            throw $this->createNotFoundException('Pago no encontrado.');
        }

        $pdf = $this->pdfService->generateVoucher($paymentAccount);

        return new Response(
            $pdf,
            Response::HTTP_OK,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => sprintf('inline; filename="boleta-%d.pdf"', $id),
                'Cache-Control'       => 'private, no-store',
            ]
        );
    }

    // -------------------------------------------------------------------------
    // Anulación del pago
    // -------------------------------------------------------------------------

    /**
     * Anula el PaymentAccount y su VoucherEntry asociado.
     *
     * El Stimulus controller solicita confirmación al usuario antes de enviar
     * el formulario (window.confirm). El token CSRF está incluido en el form.
     *
     * Legacy: AnularPagoAction
     */
    #[Route('/{id}/void', name: 'void', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function void(int $id, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('payment_void_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        $paymentAccount = $this->paymentAccountRepository->find($id);
        if ($paymentAccount === null) {
            throw $this->createNotFoundException('Pago no encontrado.');
        }

        if ($paymentAccount->getCancellationDate() !== null) {
            $this->addFlash('warning', 'Este pago ya fue anulado anteriormente.');
            return $this->redirectToRoute('app_revenue_cash_register_post_payment_summary', ['id' => $id]);
        }

        $member = $this->resolveCurrentMember();
        $reason = trim($request->request->getString('reason', 'Anulado por cajero'));

        // ── Void PaymentAccount ────────────────────────────────────────────
        $paymentAccount->setCancellationDate(new \DateTime());
        $paymentAccount->setCancelledByMember($member);
        $paymentAccount->setCancellationReason($reason ?: 'Anulado por cajero');

        $cancelledStatus = $this->paymentStatusRepository->findOneBy(['name' => 'Anulado']);
        if ($cancelledStatus !== null) {
            $paymentAccount->setPaymentStatus($cancelledStatus);
        }

        // ── Void VoucherEntry ──────────────────────────────────────────────
        // Busca sin filtro isCancelled=false para poder anular también el folio
        $voucherEntry = $this->voucherEntryRepository->findOneBy(['paymentAccount' => $paymentAccount]);
        if ($voucherEntry !== null) {
            $voucherEntry->setIsCancelled(true);
        }

        // ── Void PaymentAccountDetails ─────────────────────────────────────
        $details = $this->detailRepository->findByPaymentAccount($paymentAccount);
        foreach ($details as $detail) {
            $detail->setIsCancelled(true);
        }

        $this->em->flush();

        $this->addFlash('success', sprintf('Pago #%d anulado correctamente.', $id));
        return $this->redirectToRoute('app_revenue_cash_register_post_payment_summary', ['id' => $id]);
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
