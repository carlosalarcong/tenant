<?php

namespace App\Service\Revenue;

use App\Entity\Tenant\Budget;
use App\Entity\Tenant\PaymentAccount;
use App\Repository\Tenant\BudgetFooterByFunderRepository;
use App\Repository\Tenant\BudgetFooterRepository;
use App\Repository\Tenant\ClinicalActionPatientRepository;
use App\Repository\Tenant\PaymentAccountDetailRepository;
use App\Repository\Tenant\VoucherEntryRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;

/**
 * PdfService
 *
 * Genera documentos PDF a partir de templates Twig usando dompdf.
 * Soporta la boleta de pago (voucher) del módulo de caja y los
 * presupuestos del módulo de Presupuesto.
 *
 * Legacy: ImprimirBoletaController / generarBoletaPdfAction
 */
class PdfService
{
    public function __construct(
        private readonly Environment                       $twig,
        private readonly PaymentAccountDetailRepository    $detailRepository,
        private readonly ClinicalActionPatientRepository   $clinicalActionRepository,
        private readonly VoucherEntryRepository            $voucherEntryRepository,
        private readonly BudgetFooterRepository            $budgetFooterRepo,
        private readonly BudgetFooterByFunderRepository    $budgetFooterByFunderRepo,
    ) {}

    /**
     * Genera el PDF de la boleta de pago para el PaymentAccount dado.
     *
     * @return string PDF como string binario listo para enviar al cliente.
     */
    public function generateVoucher(PaymentAccount $paymentAccount): string
    {
        $details         = $this->detailRepository->findByPaymentAccount($paymentAccount);
        $clinicalActions = $this->clinicalActionRepository->findByPaymentAccount($paymentAccount);
        $voucherEntry    = $this->voucherEntryRepository->findOneByPaymentAccount($paymentAccount);

        $html = $this->twig->render(
            'revenue/cash-register/closing/_voucher_pdf.html.twig',
            [
                'paymentAccount'  => $paymentAccount,
                'details'         => $details,
                'clinicalActions' => $clinicalActions,
                'voucherEntry'    => $voucherEntry,
            ]
        );

        return $this->dompdfRender($html);
    }

    /**
     * Genera el PDF de un presupuesto.
     *
     * @param bool $summary true = PDF resumido (subtotales por tipo),
     *                      false = PDF detallado (desglose completo)
     * @return string PDF como string binario listo para enviar al cliente.
     */
    public function generateBudgetPdf(Budget $budget, bool $summary = false): string
    {
        $footerText = $this->resolveBudgetFooter($budget);

        $detailsByType = [];
        foreach ($budget->getDetails() as $detail) {
            $detailsByType[$detail->getItemType()][] = $detail;
        }

        $totalsByType = [];
        $grandTotal   = 0;
        foreach ($detailsByType as $type => $details) {
            $typeTotal = array_sum(array_map(fn($d) => (float) $d->getAmount(), $details));
            $totalsByType[$type] = $typeTotal;
            $grandTotal += $typeTotal;
        }

        $template = $summary
            ? 'budget/pdf/summary.html.twig'
            : 'budget/pdf/detail.html.twig';

        $html = $this->twig->render($template, [
            'budget'        => $budget,
            'detailsByType' => $detailsByType,
            'totalsByType'  => $totalsByType,
            'grandTotal'    => $grandTotal,
            'footerText'    => $footerText,
        ]);

        return $this->dompdfRender($html);
    }

    private function resolveBudgetFooter(Budget $budget): ?string
    {
        $payer     = $budget->getPayer();
        $agreement = $budget->getAgreement();

        // Nivel 1: BudgetFooterByFunder por payer del convenio
        if ($agreement?->getPayer()) {
            $footer = $this->budgetFooterByFunderRepo->findOneBy(
                ['payer' => $agreement->getPayer(), 'isActive' => true]
            );
            if ($footer?->getDetail()) {
                return $footer->getDetail();
            }
        }

        // Nivel 2: BudgetFooterByFunder por financiador
        if ($payer) {
            $footer = $this->budgetFooterByFunderRepo->findOneBy(
                ['payer' => $payer, 'isActive' => true]
            );
            if ($footer?->getDetail()) {
                return $footer->getDetail();
            }
        }

        // Nivel 3: BudgetFooter activo del sistema
        $footer = $this->budgetFooterRepo->findOneBy(['isActive' => true]);
        return $footer?->getDetail();
    }

    private function dompdfRender(string $html): string
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return (string) $dompdf->output();
    }
}
