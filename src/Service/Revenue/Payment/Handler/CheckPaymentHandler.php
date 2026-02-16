<?php

namespace App\Service\Revenue\Payment\Handler;

use App\DTO\Revenue\Payment\PaymentRowDTO;

class CheckPaymentHandler implements PaymentMethodHandlerInterface
{
    public const METHOD_CODE = 'check';

    public function supports(string $methodCode): bool
    {
        return self::METHOD_CODE === $methodCode;
    }

    public function normalizeAndValidate(int $index, array $rowData): PaymentRowDTO
    {
        $checkNumber = trim((string) ($rowData['check_number'] ?? ''));
        $bankId = $this->toPositiveInt($rowData['bank_id'] ?? null);
        $amount = $this->toAmount($rowData['amount'] ?? null);
        $rut = strtoupper(trim((string) ($rowData['rut'] ?? '')));
        $name = trim((string) ($rowData['name'] ?? ''));
        $conditionId = $this->toPositiveInt($rowData['condition_id'] ?? null);
        $checkDate = trim((string) ($rowData['check_date'] ?? ''));

        $row = new PaymentRowDTO(self::METHOD_CODE, $index, [
            'check_number' => $checkNumber,
            'bank_id' => $bankId,
            'amount' => $amount,
            'rut' => $rut,
            'name' => $name,
            'condition_id' => $conditionId,
            'check_date' => $checkDate,
        ]);

        if ('' === $checkNumber) {
            $row->addError(sprintf('[check:%d] El número de cheque es obligatorio.', $index));
        }

        if ($bankId <= 0) {
            $row->addError(sprintf('[check:%d] Debes seleccionar el banco.', $index));
        }

        if ($amount <= 0.0) {
            $row->addError(sprintf('[check:%d] El monto debe ser mayor a 0.', $index));
        }

        if ('' === $rut || !$this->isValidRut($rut)) {
            $row->addError(sprintf('[check:%d] El RUT ingresado no es válido.', $index));
        }

        if ('' === $name) {
            $row->addError(sprintf('[check:%d] El nombre es obligatorio.', $index));
        }

        if ($conditionId <= 0) {
            $row->addError(sprintf('[check:%d] Debes seleccionar la condición de pago.', $index));
        }

        if ('' !== $checkDate && false === \DateTimeImmutable::createFromFormat('Y-m-d', $checkDate)) {
            $row->addError(sprintf('[check:%d] La fecha del cheque no tiene formato válido (Y-m-d).', $index));
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

    private function isValidRut(string $rut): bool
    {
        $normalized = strtoupper(str_replace(['.', '-'], '', $rut));
        if (!preg_match('/^\d+[0-9K]$/', $normalized)) {
            return false;
        }

        $body = substr($normalized, 0, -1);
        $verifier = substr($normalized, -1);

        $sum = 0;
        $multiplier = 2;

        for ($i = strlen($body) - 1; $i >= 0; --$i) {
            $sum += (int) $body[$i] * $multiplier;
            $multiplier = $multiplier === 7 ? 2 : $multiplier + 1;
        }

        $remainder = 11 - ($sum % 11);
        $expectedVerifier = match ($remainder) {
            11 => '0',
            10 => 'K',
            default => (string) $remainder,
        };

        return $verifier === $expectedVerifier;
    }
}
