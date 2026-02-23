<?php

namespace App\Service\Revenue;

use App\Entity\Tenant\PaymentAccount;
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
 * Actualmente soporta la boleta de pago (voucher) del módulo de caja.
 *
 * Legacy: ImprimirBoletaController / generarBoletaPdfAction
 */
class PdfService
{
    public function __construct(
        private readonly Environment                     $twig,
        private readonly PaymentAccountDetailRepository  $detailRepository,
        private readonly ClinicalActionPatientRepository $clinicalActionRepository,
        private readonly VoucherEntryRepository          $voucherEntryRepository,
    ) {}

    /**
     * Genera el PDF de la boleta de pago para el PaymentAccount dado.
     *
     * Carga automáticamente las prestaciones, detalles y folio asociados.
     * Renderiza `revenue/cash-register/closing/_voucher_pdf.html.twig` y
     * lo convierte a PDF con dompdf (A4 portrait, fuente Helvetica).
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
