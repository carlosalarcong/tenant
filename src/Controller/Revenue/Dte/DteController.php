<?php

namespace App\Controller\Revenue\Dte;

use App\Controller\AbstractTenantAwareController;
use App\Repository\Tenant\DteDocumentRepository;
use App\Service\Revenue\Dte\AcesClient;
use App\Service\Revenue\Dte\AcesException;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * DteController
 *
 * Gestión operacional de documentos DTE ya emitidos.
 *
 * Rutas:
 *   POST /revenue/dte/{id}/retry → Reintenta el envío de un DteDocument fallido
 */
#[Route('/revenue/dte', name: 'app_revenue_dte_')]
class DteController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly DteDocumentRepository $dteRepository,
        private readonly AcesClient            $acesClient,
        private readonly TenantEntityManager   $em,
    ) {}

    // ── Retry ─────────────────────────────────────────────────────────────────

    /**
     * Reintenta el envío de un DteDocument con status 'error' o 'retry_pending'.
     *
     * Usa el retryData guardado en el DteDocument original (sin credenciales)
     * y deja que AcesClient agregue las credenciales actuales.
     *
     * @return JsonResponse {success: bool, status: string, message: string}
     */
    #[Route('/{id}/retry', name: 'retry', methods: ['POST'])]
    public function retry(int $id, Request $request): JsonResponse
    {
        if (!$this->isCsrfTokenValid('dte_retry_' . $id, $request->request->get('_token'))) {
            return $this->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'Token CSRF inválido.',
            ], 403);
        }

        $dte = $this->dteRepository->find($id);

        if ($dte === null) {
            return $this->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'Documento DTE no encontrado.',
            ], 404);
        }

        if (!$dte->isRetryable()) {
            return $this->json([
                'success' => false,
                'status'  => $dte->getStatus(),
                'message' => sprintf(
                    'El documento tiene estado "%s" y no puede reintentarse.',
                    $dte->getStatus()
                ),
            ], 422);
        }

        $retryData = $dte->getRetryData();

        if (empty($retryData)) {
            return $this->json([
                'success' => false,
                'status'  => 'error',
                'message' => 'No hay datos de reintento almacenados.',
            ], 422);
        }

        try {
            $response = $this->acesClient->send($retryData);

            $responseArray = json_decode(json_encode($response), true);
            $dte->setAcesResponse($responseArray);
            $dte->setStatus('sent');
            $dte->setSentAt(new \DateTimeImmutable());

            $this->em->flush();

            return $this->json([
                'success' => true,
                'status'  => 'sent',
                'message' => 'Documento DTE reenviado correctamente.',
            ]);

        } catch (AcesException $e) {
            $dte->setStatus('retry_pending');
            $this->em->flush();

            return $this->json([
                'success' => false,
                'status'  => 'retry_pending',
                'message' => 'Error al reenviar: ' . $e->getMessage(),
            ], 502);
        }
    }
}
