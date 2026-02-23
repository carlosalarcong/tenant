<?php

namespace App\Service\Revenue\Payment\Handler;

use App\DTO\Revenue\Payment\PaymentRowDTO;

/**
 * Handler para Bono Manual (bono en papel).
 * Legacy: FormaDePago_BonoManual (tipo 5)
 */
class ManualVoucherPaymentHandler implements PaymentMethodHandlerInterface
{
    public const METHOD_CODE = 'manual_voucher';

    public function supports(string $methodCode): bool
    {
        return self::METHOD_CODE === $methodCode;
    }

    public function normalizeAndValidate(int $index, array $rowData): PaymentRowDTO
    {
        $folio     = trim((string) ($rowData['folio'] ?? ''));
        $payerName = trim((string) ($rowData['payer_name'] ?? ''));
        $amount    = $this->toAmount($rowData['amount'] ?? null);

        $row = new PaymentRowDTO(self::METHOD_CODE, $index, [
            'folio'      => $folio,
            'payer_name' => $payerName,
            'amount'     => $amount,
        ]);

        if ('' === $folio) {
            $row->addError(sprintf('[manual_voucher:%d] El N° de folio del bono es obligatorio.', $index));
        }

        if (mb_strlen($folio) > 20) {
            $row->addError(sprintf('[manual_voucher:%d] El folio excede 20 caracteres.', $index));
        }

        if ($amount <= 0.0) {
            $row->addError(sprintf('[manual_voucher:%d] El monto debe ser mayor a 0.', $index));
        }

        return $row;
    }

    private function toAmount(mixed $value): float
    {
        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        return 0.0;
    }
}
