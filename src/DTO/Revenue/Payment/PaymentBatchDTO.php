<?php

namespace App\DTO\Revenue\Payment;

class PaymentBatchDTO
{
    /**
     * @var array<string, array<int, PaymentRowDTO>>
     */
    private array $rowsByMethod = [];

    /**
     * @var array<string>
     */
    private array $errors = [];

    public function addRow(PaymentRowDTO $row): void
    {
        $methodCode = $row->getMethodCode();
        if (!isset($this->rowsByMethod[$methodCode])) {
            $this->rowsByMethod[$methodCode] = [];
        }

        $this->rowsByMethod[$methodCode][$row->getIndex()] = $row;
    }

    /**
     * @return array<string, array<int, PaymentRowDTO>>
     */
    public function getRowsByMethod(): array
    {
        return $this->rowsByMethod;
    }

    /**
     * @return array<PaymentRowDTO>
     */
    public function getRowsFlat(): array
    {
        $rows = [];
        foreach ($this->rowsByMethod as $methodRows) {
            foreach ($methodRows as $row) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function isValid(): bool
    {
        if ([] !== $this->errors) {
            return false;
        }

        foreach ($this->getRowsFlat() as $row) {
            if (!$row->isValid()) {
                return false;
            }
        }

        return true;
    }
}
