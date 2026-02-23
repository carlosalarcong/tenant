<?php

namespace App\Service\Revenue\Payment\Handler;

use App\DTO\Revenue\Payment\PaymentRowDTO;

/**
 * Handler para Transferencia Bancaria.
 * Legacy: FormaDePago tipo 7
 */
class BankTransferPaymentHandler implements PaymentMethodHandlerInterface
{
    public const METHOD_CODE = 'bank_transfer';

    public function supports(string $methodCode): bool
    {
        return self::METHOD_CODE === $methodCode;
    }

    public function normalizeAndValidate(int $index, array $rowData): PaymentRowDTO
    {
        $bankId         = $this->toPositiveInt($rowData['bank_id'] ?? null);
        $transferNumber = trim((string) ($rowData['transfer_number'] ?? ''));
        $accountHolder  = trim((string) ($rowData['account_holder'] ?? ''));
        $amount         = $this->toAmount($rowData['amount'] ?? null);

        $row = new PaymentRowDTO(self::METHOD_CODE, $index, [
            'bank_id'         => $bankId,
            'transfer_number' => $transferNumber,
            'account_holder'  => $accountHolder,
            'amount'          => $amount,
        ]);

        if ($bankId <= 0) {
            $row->addError(sprintf('[bank_transfer:%d] Debes seleccionar el banco.', $index));
        }

        if ('' === $transferNumber) {
            $row->addError(sprintf('[bank_transfer:%d] El número de operación es obligatorio.', $index));
        }

        if (mb_strlen($transferNumber) > 30) {
            $row->addError(sprintf('[bank_transfer:%d] El número de operación excede 30 caracteres.', $index));
        }

        if ($amount <= 0.0) {
            $row->addError(sprintf('[bank_transfer:%d] El monto debe ser mayor a 0.', $index));
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
