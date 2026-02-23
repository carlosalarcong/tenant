<?php

namespace App\Service\Revenue\Payment\Handler;

use App\DTO\Revenue\Payment\PaymentRowDTO;

/**
 * BonoWebPaymentHandler
 *
 * Normaliza y valida una fila de pago con medio 'bonoweb'.
 *
 * Campos esperados en $rowData:
 *   - amount     (float) → copago que paga el paciente
 *   - voucher_id (string UUID) → UUID del voucher Snabb ya confirmado
 */
class BonoWebPaymentHandler implements PaymentMethodHandlerInterface
{
    public const METHOD_CODE = 'bonoweb';

    public function supports(string $methodCode): bool
    {
        return self::METHOD_CODE === $methodCode;
    }

    public function normalizeAndValidate(int $index, array $rowData): PaymentRowDTO
    {
        $amount    = $this->toAmount($rowData['amount'] ?? null);
        $voucherId = trim((string) ($rowData['voucher_id'] ?? ''));

        $row = new PaymentRowDTO(self::METHOD_CODE, $index, [
            'amount'     => $amount,
            'voucher_id' => $voucherId,
        ]);

        if ($amount <= 0.0) {
            $row->addError(sprintf('[bonoweb:%d] El copago debe ser mayor a 0.', $index));
        }

        if ($voucherId === '') {
            $row->addError(sprintf('[bonoweb:%d] El ID del voucher BonoWeb es requerido.', $index));
        }

        return $row;
    }

    private function toAmount(mixed $value): float
    {
        return is_numeric($value) ? round((float) $value, 2) : 0.0;
    }
}
