<?php

namespace App\Service\Revenue\Payment\Handler;

use App\DTO\Revenue\Payment\PaymentRowDTO;

/**
 * Handler para Gratuidad.
 * El monto puede ser 0 (exención total); lo que es obligatorio es el tipo y el motivo.
 * Legacy: FormaDePago_Gratuidad
 */
class GratuityPaymentHandler implements PaymentMethodHandlerInterface
{
    public const METHOD_CODE = 'gratuity';

    public function supports(string $methodCode): bool
    {
        return self::METHOD_CODE === $methodCode;
    }

    public function normalizeAndValidate(int $index, array $rowData): PaymentRowDTO
    {
        $gratuityTypeId   = $this->toPositiveInt($rowData['gratuity_type_id'] ?? null);
        $gratuityReasonId = $this->toPositiveInt($rowData['gratuity_reason_id'] ?? null);
        $amount           = $this->toAmount($rowData['amount'] ?? null);
        $notes            = trim((string) ($rowData['notes'] ?? ''));

        $row = new PaymentRowDTO(self::METHOD_CODE, $index, [
            'gratuity_type_id'   => $gratuityTypeId,
            'gratuity_reason_id' => $gratuityReasonId,
            'amount'             => $amount,
            'notes'              => $notes,
        ]);

        if ($gratuityTypeId <= 0) {
            $row->addError(sprintf('[gratuity:%d] Debes seleccionar el tipo de gratuidad.', $index));
        }

        if ($gratuityReasonId <= 0) {
            $row->addError(sprintf('[gratuity:%d] Debes seleccionar el motivo de gratuidad.', $index));
        }

        if ($amount < 0.0) {
            $row->addError(sprintf('[gratuity:%d] El monto no puede ser negativo.', $index));
        }

        return $row;
    }

    private function toPositiveInt(mixed $value): int
    {
        if (is_numeric($value)) {
            $number = (int) $value;
            return $number > 0 ? $number : 0;
        }

        return 0;
    }

    private function toAmount(mixed $value): float
    {
        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        return 0.0;
    }
}
