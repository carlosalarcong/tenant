<?php

namespace App\Service\Revenue\Dte;

/**
 * AcesException
 *
 * Lanzada cuando la API de Aces retorna un error HTTP ≥ 400
 * o cuando el parsing de la respuesta JSON falla.
 */
class AcesException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $httpStatusCode = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }
}
