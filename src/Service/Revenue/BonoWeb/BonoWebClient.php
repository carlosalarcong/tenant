<?php

namespace App\Service\Revenue\BonoWeb;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * BonoWebClient
 *
 * Cliente HTTP para la API de Snabb/BonoWeb (bono FONASA electrónico).
 *
 * Endpoints Snabb:
 *   POST {apiUrl}/vouchers                  → crear voucher
 *   GET  {apiUrl}/voucher/{id}              → consultar estado
 *   POST {apiUrl}/voucher/{id}/update-status → cambiar estado (Done|Canceled)
 *
 * Autenticación: Bearer token en header Authorization.
 * En error HTTP ≥ 400 lanza BonoWebException.
 *
 * Configuración en services.yaml (argumentos escalares):
 *   $apiUrl, $apiKey, $timeout
 */
class BonoWebClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $apiUrl,
        private readonly string $apiKey,
        private readonly int $timeout = 30,
    ) {}

    // ── Crear voucher ─────────────────────────────────────────────────────────

    /**
     * Crea un voucher de bono en Snabb.
     *
     * Payload esperado:
     * ```json
     * {
     *   "patientRut":  "12345678-9",
     *   "patientName": "Juan Pérez",
     *   "externalRef": "PA-{paymentAccountId}",
     *   "items": [
     *     {
     *       "serviceCode": "P0301",
     *       "serviceName": "Consulta Médica",
     *       "quantity": 1
     *     }
     *   ]
     * }
     * ```
     *
     * Respuesta exitosa:
     * ```json
     * {
     *   "id":                "uuid-...",
     *   "url":               "https://bono.fonasa.cl/...",
     *   "status":            "Created",
     *   "copagoTotal":       5000.00,
     *   "bonificacionTotal": 10000.00
     * }
     * ```
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     * @throws BonoWebException
     */
    public function createVoucher(array $data): array
    {
        return $this->post('/vouchers', $data);
    }

    // ── Consultar voucher ─────────────────────────────────────────────────────

    /**
     * Obtiene el estado actual de un voucher por su UUID en Snabb.
     *
     * @return array<string, mixed>
     * @throws BonoWebException
     */
    public function getVoucher(string $voucherId): array
    {
        return $this->get(sprintf('/voucher/%s', rawurlencode($voucherId)));
    }

    // ── Actualizar estado ─────────────────────────────────────────────────────

    /**
     * Actualiza el estado de un voucher en Snabb.
     *
     * @param string $status 'Done' o 'Canceled'
     * @return array<string, mixed>
     * @throws BonoWebException
     */
    public function updateVoucherStatus(string $voucherId, string $status): array
    {
        return $this->post(
            sprintf('/voucher/%s/update-status', rawurlencode($voucherId)),
            ['status' => $status]
        );
    }

    // ── Helpers HTTP ──────────────────────────────────────────────────────────

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    private function post(string $path, array $body): array
    {
        try {
            $response = $this->httpClient->request('POST', $this->apiUrl . $path, [
                'headers' => $this->defaultHeaders(),
                'json'    => $body,
                'timeout' => $this->timeout,
            ]);

            return $this->parseResponse($response);
        } catch (BonoWebException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new BonoWebException(
                sprintf('Error de conexión con BonoWeb (%s): %s', $path, $e->getMessage()),
                0,
                $e
            );
        }
    }

    /** @return array<string, mixed> */
    private function get(string $path): array
    {
        try {
            $response = $this->httpClient->request('GET', $this->apiUrl . $path, [
                'headers' => $this->defaultHeaders(),
                'timeout' => $this->timeout,
            ]);

            return $this->parseResponse($response);
        } catch (BonoWebException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new BonoWebException(
                sprintf('Error de conexión con BonoWeb (%s): %s', $path, $e->getMessage()),
                0,
                $e
            );
        }
    }

    /** @return array<string, string> */
    private function defaultHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ];
    }

    /**
     * @return array<string, mixed>
     * @throws BonoWebException
     */
    private function parseResponse(\Symfony\Contracts\HttpClient\ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            $body = '';
            try {
                $body = $response->getContent(false);
            } catch (\Throwable) {}

            throw new BonoWebException(
                sprintf('BonoWeb API error HTTP %d: %s', $statusCode, $body),
                $statusCode
            );
        }

        return $response->toArray();
    }
}
