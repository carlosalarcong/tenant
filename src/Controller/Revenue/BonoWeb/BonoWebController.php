<?php

namespace App\Controller\Revenue\BonoWeb;

use App\Controller\AbstractTenantAwareController;
use App\Entity\Tenant\Member;
use App\Repository\Tenant\BonoWebVoucherRepository;
use App\Repository\Tenant\CashRegisterLocationRepository;
use App\Repository\Tenant\PaymentAccountRepository;
use App\Service\Revenue\BonoWeb\BonoWebException;
use App\Service\Revenue\BonoWeb\BonoWebService;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * BonoWebController
 *
 * Integración BonoWeb/Snabb para bono FONASA electrónico.
 *
 * Flujo normal:
 *   1. GET  /panel?paymentAccountId={id}  → carga formulario con prestaciones
 *   2. POST /generate                     → llama Snabb, guarda voucher, inicia polling
 *   3. GET  /{voucherId}/status           → polling (Turbo Frame, no-cache)
 *   4. POST /{voucherId}/confirm          → cajero confirma pago → Done en Snabb
 *
 * Webhook:
 *   POST /webhook  → callback de Snabb → sincroniza estado en BD
 *
 * Todos los frames responden dentro de <turbo-frame id="bonoweb-panel">
 * o <turbo-frame id="bonoweb-status">.
 */
#[Route('/revenue/bonoweb', name: 'app_revenue_bonoweb_')]
class BonoWebController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly BonoWebService                 $bonoWebService,
        private readonly BonoWebVoucherRepository       $voucherRepository,
        private readonly PaymentAccountRepository       $paymentAccountRepository,
        private readonly CashRegisterLocationRepository $locationRepository,
        private readonly TenantEntityManager            $em,
    ) {}

    // ── Panel ─────────────────────────────────────────────────────────────────

    /**
     * Carga el panel BonoWeb dentro de turbo-frame#bonoweb-panel.
     *
     * Query params:
     *   paymentAccountId       (int)
     *   cashRegisterLocationId (int, opcional)
     */
    #[Route('/panel', name: 'panel', methods: ['GET'])]
    public function panel(Request $request): Response
    {
        $paymentAccountId = $request->query->getInt('paymentAccountId');
        $paymentAccount   = $paymentAccountId > 0
            ? $this->paymentAccountRepository->find($paymentAccountId)
            : null;

        return $this->render('revenue/bonoweb/_panel.html.twig', [
            'paymentAccount' => $paymentAccount,
            'generateUrl'    => $this->generateUrl('app_revenue_bonoweb_generate'),
        ]);
    }

    // ── Generar voucher ───────────────────────────────────────────────────────

    /**
     * Crea el voucher en Snabb vía BonoWebService::createAndPersist().
     *
     * El formulario envía:
     *   - paymentAccountId         (int)
     *   - cashRegisterLocationId   (int, opcional)
     *   - prestaciones[]           (array: name, code, quantity, billing_item_id)
     *
     * El payload enviado a Snabb sigue la estructura real de la API:
     *   beneficiario.run, codigoSucursal, prestaciones[].codigo,
     *   callback_url, redirect_url, fechaExpiracion, practitioner
     */
    #[Route('/generate', name: 'generate', methods: ['POST'])]
    public function generate(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('bonoweb_generate', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        $paymentAccountId = $request->request->getInt('paymentAccountId');
        $paymentAccount   = $this->paymentAccountRepository->find($paymentAccountId);

        if ($paymentAccount === null) {
            return $this->render('revenue/bonoweb/_panel.html.twig', [
                'paymentAccount' => null,
                'generateUrl'    => $this->generateUrl('app_revenue_bonoweb_generate'),
                'error'          => 'Cuenta de pago no encontrada.',
            ]);
        }

        // Resolución opcional de CashRegisterLocation (para codigoSucursal)
        $locationId = $request->request->getInt('cashRegisterLocationId');
        $location   = $locationId > 0
            ? $this->locationRepository->find($locationId)
            : null;

        // Practitioner = usuario autenticado en el sistema
        $practitioner = $this->getUser();
        $practitioner = $practitioner instanceof Member ? $practitioner : null;

        $prestaciones = $this->extractPrestaciones($request);

        try {
            $voucher = $this->bonoWebService->createAndPersist(
                $paymentAccount,
                $prestaciones,
                $location,
                $practitioner,
            );
        } catch (BonoWebException $e) {
            return $this->render('revenue/bonoweb/_panel.html.twig', [
                'paymentAccount' => $paymentAccount,
                'generateUrl'    => $this->generateUrl('app_revenue_bonoweb_generate'),
                'error'          => 'Error al generar voucher BonoWeb: ' . $e->getMessage(),
            ]);
        }

        $statusUrl = $this->generateUrl('app_revenue_bonoweb_status', [
            'voucherId' => $voucher->getVoucherId(),
        ]);

        return $this->render('revenue/bonoweb/_panel.html.twig', [
            'paymentAccount' => $paymentAccount,
            'voucher'        => $voucher,
            'statusUrl'      => $statusUrl,
        ]);
    }

    // ── Polling de estado ─────────────────────────────────────────────────────

    /**
     * Retorna el estado actual del voucher como turbo-frame#bonoweb-status.
     * Cache-Control: no-cache para evitar respuestas cacheadas durante el polling.
     */
    #[Route('/{voucherId}/status', name: 'status', methods: ['GET'])]
    public function status(string $voucherId): Response
    {
        $voucher = $this->voucherRepository->findByVoucherId($voucherId);

        if ($voucher === null) {
            throw $this->createNotFoundException('Voucher BonoWeb no encontrado.');
        }

        try {
            $voucher = $this->bonoWebService->syncStatus($voucher);
        } catch (BonoWebException) {
            // Si Snabb falla el polling, mostramos el estado cacheado en BD
        }

        $response = $this->render('revenue/bonoweb/_status.html.twig', [
            'voucher'    => $voucher,
            'confirmUrl' => $this->generateUrl('app_revenue_bonoweb_confirm', ['voucherId' => $voucherId]),
        ]);

        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    // ── Confirmar pago ────────────────────────────────────────────────────────

    /**
     * El cajero confirma que el paciente pagó el copago.
     * Llama confirmDone() → marca Done en Snabb.
     */
    #[Route('/{voucherId}/confirm', name: 'confirm', methods: ['POST'])]
    public function confirm(string $voucherId, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('bonoweb_confirm_' . $voucherId, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        $voucher = $this->voucherRepository->findByVoucherId($voucherId);

        if ($voucher === null) {
            throw $this->createNotFoundException('Voucher BonoWeb no encontrado.');
        }

        try {
            $this->bonoWebService->confirmDone($voucher);
        } catch (BonoWebException $e) {
            return $this->render('revenue/bonoweb/_status.html.twig', [
                'voucher'    => $voucher,
                'error'      => 'Error al confirmar en Snabb: ' . $e->getMessage(),
                'confirmUrl' => $this->generateUrl('app_revenue_bonoweb_confirm', ['voucherId' => $voucherId]),
            ]);
        }

        return $this->render('revenue/bonoweb/_confirmed.html.twig', [
            'voucher' => $voucher,
        ]);
    }

    // ── Webhook Snabb ─────────────────────────────────────────────────────────

    /**
     * Callback recibido desde Snabb cuando el estado del voucher cambia.
     * Body JSON: { "id": "uuid-...", "status": "Paid" }
     *
     * No requiere sesión ni CSRF (es llamado por Snabb, no por el navegador).
     */
    #[Route('/webhook', name: 'webhook', methods: ['POST'])]
    public function webhook(Request $request): JsonResponse
    {
        $data      = json_decode($request->getContent(), true) ?? [];
        $voucherId = (string) ($data['id'] ?? '');

        if ($voucherId !== '') {
            $voucher = $this->voucherRepository->findByVoucherId($voucherId);
            if ($voucher !== null) {
                try {
                    $this->bonoWebService->syncStatus($voucher);
                } catch (BonoWebException) {
                    // No propagar: el webhook debe responder 200 siempre
                }
            }
        }

        return $this->json(['ok' => true]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Extrae el array de prestaciones desde el request POST.
     *
     * @return array<int, array<string, mixed>>
     */
    private function extractPrestaciones(Request $request): array
    {
        $raw = $request->request->all('prestaciones');

        if (empty($raw)) {
            return [];
        }

        return array_values(array_filter(
            $raw,
            static fn(mixed $p) => is_array($p) && !empty($p['name'])
        ));
    }
}
