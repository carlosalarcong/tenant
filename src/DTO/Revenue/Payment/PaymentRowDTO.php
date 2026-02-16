<?php

namespace App\DTO\Revenue\Payment;

class PaymentRowDTO
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private readonly string $methodCode,
        private readonly int $index,
        private readonly array $payload,
        private array $errors = []
    ) {}

    public function getMethodCode(): string
    {
        return $this->methodCode;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function addError(string $error): void
    {
        $this->errors[] = $error;
    }

    public function isValid(): bool
    {
        return [] === $this->errors;
    }
}
