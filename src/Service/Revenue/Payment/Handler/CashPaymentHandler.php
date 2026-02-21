<?php

namespace App\Service\Revenue\Payment\Handler;

use App\DTO\Revenue\Payment\PaymentRowDTO;

class CashPaymentHandler implements PaymentMethodHandlerInterface
{
    public const METHOD_CODE = 'cash';

    public function supports(string $methodCode): bool
    {
        return self::METHOD_CODE === $methodCode;
    }

    public function normalizeAndValidate(int $index, array $rowData): PaymentRowDTO
    {
        $amount = $this->toAmount($rowData['amount'] ?? null);

        $row = new PaymentRowDTO(self::METHOD_CODE, $index, [
            'amount' => $amount,
        ]);

        if ($amount <= 0.0) {
            $row->addError(sprintf('[cash:%d] El monto debe ser mayor a 0.', $index));
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
