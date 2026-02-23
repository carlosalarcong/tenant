<?php

namespace App\Service\Revenue\Dte;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * AcesClient
 *
 * Cliente HTTP para el webservice Aces de emisión de DTE (SII Chile).
 *
 * El webservice recibe un POST con datos form-encoded. Las credenciales
 * (u, p, apikey, idempresa) se agregan automáticamente a cada llamada.
 *
 * Si la respuesta del API indica error (no HTTP, sino error semántico),
 * se realiza 1 reintento automático (comportamiento igual al legacy).
 *
 * Configuración vía services.yaml (argumentos escalares):
 *   $wsUrl, $user, $password, $apiKey, $idEmpresa, $preview
 */
class AcesClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $wsUrl,
        private readonly string $user,
        private readonly string $password,
        private readonly string $apiKey,
        private readonly string $idEmpresa,
        private readonly bool   $preview,
    ) {}

    // ── Enviar documento ──────────────────────────────────────────────────────

    /**
     * Envía un payload de negocio al webservice Aces.
     *
     * Las credenciales de autenticación se agregan internamente;
     * $data sólo debe contener campos de negocio (tipodte, folio, Detalle, etc.).
     *
     * @param array<string, mixed> $data Campos de negocio del documento DTE
     * @return object Respuesta JSON parseada como objeto stdClass
     * @throws AcesException en error HTTP o si la segunda llamada también falla
     */
    public function send(array $data): object
    {
        $payload = $this->buildPayload($data);

        $response = $this->doPost($payload);

        // 1 reintento automático si la respuesta indica error semántico
        if ($this->isErrorResponse($response)) {
            $response = $this->doPost($payload);
        }

        return $response;
    }

    // ── Helpers internos ──────────────────────────────────────────────────────

    /**
     * Combina credenciales con los datos de negocio.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function buildPayload(array $data): array
    {
        return array_merge([
            'u'         => $this->user,
            'p'         => $this->password,
            'apikey'    => $this->apiKey,
            'preview'   => $this->preview ? '1' : '0',
            'idempresa' => $this->idEmpresa,
        ], $data);
    }

    /**
     * Ejecuta el POST form-encoded contra el webservice.
     *
     * @param array<string, mixed> $payload
     * @throws AcesException
     */
    private function doPost(array $payload): object
    {
        try {
            $response = $this->httpClient->request('POST', $this->wsUrl, [
                'body'    => $payload,
                'headers' => ['Accept' => 'application/json'],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode >= 400) {
                $body = '';
                try { $body = $response->getContent(false); } catch (\Throwable) {}
                throw new AcesException(
                    sprintf('Aces API error HTTP %d: %s', $statusCode, $body),
                    $statusCode
                );
            }

            $decoded = json_decode($response->getContent(), false);
            if ($decoded === null) {
                throw new AcesException('Respuesta Aces no es JSON válido: ' . $response->getContent());
            }

            return $decoded;

        } catch (AcesException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new AcesException(
                sprintf('Error de conexión con Aces: %s', $e->getMessage()),
                0,
                $e
            );
        }
    }

    /**
     * Detecta si la respuesta Aces indica un error semántico (no HTTP).
     * Los campos de error más comunes en APIs Aces/SII chilenas:
     *   - success === false
     *   - estado/status === 'error' o === '-1'
     *   - error property truthy
     */
    private function isErrorResponse(object $response): bool
    {
        if (isset($response->success) && $response->success === false) {
            return true;
        }
        if (isset($response->error) && $response->error) {
            return true;
        }
        if (isset($response->status) && in_array($response->status, ['error', '-1'], true)) {
            return true;
        }
        if (isset($response->estado) && in_array($response->estado, ['error', '-1'], true)) {
            return true;
        }
        return false;
    }
}
