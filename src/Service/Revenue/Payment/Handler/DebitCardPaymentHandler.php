<?php

namespace App\Service\Revenue\Payment\Handler;

use App\DTO\Revenue\Payment\PaymentRowDTO;

class DebitCardPaymentHandler implements PaymentMethodHandlerInterface
{
    public const METHOD_CODE = 'debit_card';

    public function supports(string $methodCode): bool
    {
        return self::METHOD_CODE === $methodCode;
    }

    public function normalizeAndValidate(int $index, array $rowData): PaymentRowDTO
    {
        $voucher        = trim((string) ($rowData['voucher'] ?? ''));
        $cardLastDigits = trim((string) ($rowData['card_last_digits'] ?? ''));
        $amount         = $this->toAmount($rowData['amount'] ?? null);

        $row = new PaymentRowDTO(self::METHOD_CODE, $index, [
            'voucher'          => $voucher,
            'card_last_digits' => $cardLastDigits,
            'amount'           => $amount,
        ]);

        if ('' === $voucher) {
            $row->addError(sprintf('[debit_card:%d] El número de voucher es obligatorio.', $index));
        }

        if (mb_strlen($voucher) > 12) {
            $row->addError(sprintf('[debit_card:%d] El voucher excede 12 caracteres.', $index));
        }

        if ('' !== $cardLastDigits && !preg_match('/^\d{4}$/', $cardLastDigits)) {
            $row->addError(sprintf('[debit_card:%d] Los últimos dígitos deben ser exactamente 4 números.', $index));
        }

        if ($amount <= 0.0) {
            $row->addError(sprintf('[debit_card:%d] El monto debe ser mayor a 0.', $index));
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
