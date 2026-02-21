<?php

namespace App\Service\Revenue\Payment\Handler;

use App\DTO\Revenue\Payment\PaymentRowDTO;

class CreditCardPaymentHandler implements PaymentMethodHandlerInterface
{
    public const METHOD_CODE = 'credit_card';

    public function supports(string $methodCode): bool
    {
        return self::METHOD_CODE === $methodCode;
    }

    public function normalizeAndValidate(int $index, array $rowData): PaymentRowDTO
    {
        $cardId = $this->toPositiveInt($rowData['card_id'] ?? null);
        $voucher = trim((string) ($rowData['voucher'] ?? ''));
        $amount = $this->toAmount($rowData['amount'] ?? null);

        $row = new PaymentRowDTO(self::METHOD_CODE, $index, [
            'card_id' => $cardId,
            'voucher' => $voucher,
            'amount' => $amount,
        ]);

        if ($cardId <= 0) {
            $row->addError(sprintf('[credit_card:%d] Debes seleccionar la tarjeta.', $index));
        }

        if ('' === $voucher) {
            $row->addError(sprintf('[credit_card:%d] El voucher es obligatorio.', $index));
        }

        if (mb_strlen($voucher) > 12) {
            $row->addError(sprintf('[credit_card:%d] El voucher excede 12 caracteres.', $index));
        }

        if ($amount <= 0.0) {
            $row->addError(sprintf('[credit_card:%d] El monto debe ser mayor a 0.', $index));
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
