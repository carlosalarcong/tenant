<?php

namespace App\Service\Revenue\BonoWeb;

/**
 * Excepción lanzada cuando la API de Snabb/BonoWeb retorna un error HTTP
 * o cuando la respuesta no tiene el formato esperado.
 */
class BonoWebException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly int $httpStatusCode = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $httpStatusCode, $previous);
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }
}
