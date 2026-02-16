<?php

namespace App\Service\Revenue\Payment;

use App\DTO\Revenue\Payment\PaymentBatchDTO;
use App\Service\Revenue\Payment\Handler\PaymentMethodHandlerInterface;

class PaymentBatchProcessor
{
    /**
     * @param iterable<PaymentMethodHandlerInterface> $handlers
     */
    public function __construct(
        private readonly PaymentMethodConfigRegistry $configRegistry,
        private readonly iterable $handlers
    ) {}

    /**
     * @param array<string, mixed> $paymentBatchRequest
     */
    public function process(array $paymentBatchRequest): PaymentBatchDTO
    {
        $batch = new PaymentBatchDTO();

        /** @var array<string, array<int|string, array<string, mixed>>> $rowsByMethod */
        $rowsByMethod = is_array($paymentBatchRequest['rows'] ?? null)
            ? $paymentBatchRequest['rows']
            : [];

        foreach ($rowsByMethod as $methodCode => $rows) {
            if (!$this->configRegistry->has($methodCode)) {
                $batch->addError(sprintf('Método de pago no soportado en batch: %s', $methodCode));
                continue;
            }

            $handler = $this->resolveHandler($methodCode);
            if (!$handler instanceof PaymentMethodHandlerInterface) {
                $batch->addError(sprintf('No hay handler configurado para el método: %s', $methodCode));
                continue;
            }

            foreach ($rows as $index => $rowData) {
                if (!is_array($rowData)) {
                    continue;
                }

                $row = $handler->normalizeAndValidate((int) $index, $rowData);
                $batch->addRow($row);
            }
        }

        return $batch;
    }

    private function resolveHandler(string $methodCode): ?PaymentMethodHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($methodCode)) {
                return $handler;
            }
        }

        return null;
    }
}
