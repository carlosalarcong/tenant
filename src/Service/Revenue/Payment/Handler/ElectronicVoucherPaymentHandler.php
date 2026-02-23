<?php

namespace App\Service\Revenue\Payment\Handler;

use App\DTO\Revenue\Payment\PaymentRowDTO;

/**
 * Handler para Bono Electrónico (FONASA/ISAPRE).
 * Legacy: FormaDePago_BonoElectronico (tipo 3)
 */
class ElectronicVoucherPaymentHandler implements PaymentMethodHandlerInterface
{
    public const METHOD_CODE = 'electronic_voucher';

    public function supports(string $methodCode): bool
    {
        return self::METHOD_CODE === $methodCode;
    }

    public function normalizeAndValidate(int $index, array $rowData): PaymentRowDTO
    {
        $folio             = trim((string) ($rowData['folio'] ?? ''));
        $authorizationCode = trim((string) ($rowData['authorization_code'] ?? ''));
        $amount            = $this->toAmount($rowData['amount'] ?? null);

        $row = new PaymentRowDTO(self::METHOD_CODE, $index, [
            'folio'              => $folio,
            'authorization_code' => $authorizationCode,
            'amount'             => $amount,
        ]);

        if ('' === $folio) {
            $row->addError(sprintf('[electronic_voucher:%d] El N° de folio del bono es obligatorio.', $index));
        }

        if (mb_strlen($folio) > 20) {
            $row->addError(sprintf('[electronic_voucher:%d] El folio excede 20 caracteres.', $index));
        }

        if ('' === $authorizationCode) {
            $row->addError(sprintf('[electronic_voucher:%d] El código de autorización es obligatorio.', $index));
        }

        if (mb_strlen($authorizationCode) > 20) {
            $row->addError(sprintf('[electronic_voucher:%d] El código de autorización excede 20 caracteres.', $index));
        }

        if ($amount <= 0.0) {
            $row->addError(sprintf('[electronic_voucher:%d] El monto debe ser mayor a 0.', $index));
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
