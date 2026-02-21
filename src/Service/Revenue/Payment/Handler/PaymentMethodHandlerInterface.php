<?php

namespace App\Service\Revenue\Payment\Handler;

use App\DTO\Revenue\Payment\PaymentRowDTO;

interface PaymentMethodHandlerInterface
{
    public function supports(string $methodCode): bool;

    /**
     * @param array<string, mixed> $rowData
     */
    public function normalizeAndValidate(int $index, array $rowData): PaymentRowDTO;
}
