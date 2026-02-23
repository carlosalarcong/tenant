<?php

namespace App\Controller\Revenue\CashRegister;

use App\Controller\AbstractTenantAwareController;
use App\Repository\Tenant\BillingItemRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * CashRegisterServicesController
 *
 * Gestiona la selección de prestaciones (BillingItems) en el flujo de caja.
 * El estado de la lista se mantiene en sesión (cash_register_services).
 *
 * Endpoints:
 *   GET  /services           → Turbo Frame services-panel (tabla + total + payload)
 *   GET  /services/search    → JSON autocomplete por nombre
 *   POST /services/add       → Turbo Stream: agrega fila a la tabla
 *   DELETE /services/remove  → Turbo Stream: elimina fila de la tabla
 *
 * Legacy: SeleccionPrestacionesController
 */
#[Route('/revenue/cash-register/services', name: 'app_revenue_cash_register_services_')]
class CashRegisterServicesController extends AbstractTenantAwareController
{
    private const SESSION_KEY = 'cash_register_services';

    public function __construct(
        private readonly BillingItemRepository $billingItemRepository,
    ) {}

    // -------------------------------------------------------------------------
    // Panel principal
    // -------------------------------------------------------------------------

    /**
     * Renderiza el panel de prestaciones (tabla + buscador + total).
     *
     * Carga las filas actuales desde sesión para que el estado persista
     * al navegar entre pasos del flujo de caja.
     *
     * Turbo Frame: services-panel
     */
    #[Route('', name: 'panel', methods: ['GET'])]
    public function panel(Request $request): Response
    {
        $rows    = array_values($request->getSession()->get(self::SESSION_KEY, []));
        $total   = $this->buildTotal($rows);
        $payload = $this->buildPayload($rows);

        return $this->render('revenue/cash-register/_services_panel.html.twig', [
            'rows'    => $rows,
            'total'   => $total,
            'payload' => $payload,
        ]);
    }

    // -------------------------------------------------------------------------
    // Autocomplete JSON
    // -------------------------------------------------------------------------

    /**
     * Devuelve hasta 15 ítems de facturación que coincidan con ?q=
     *
     * Respuesta: [{id, code, name, unitAmount, requiresProfessional}]
     *
     * Nota: BillingItem no tiene los campos code, unitAmount ni requiresProfessional;
     * se retornan como null / false hasta que la entidad sea extendida.
     */
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = trim($request->query->getString('q', ''));

        if (mb_strlen($query) < 2) {
            return $this->json([]);
        }

        $items = $this->billingItemRepository->searchByQuery($query, 15);

        $results = array_map(static fn($item) => [
            'id'                  => $item->getId(),
            'code'                => null,  // campo no disponible en la entidad actual
            'name'                => $item->getName(),
            'unitAmount'          => null,  // campo no disponible; el cajero lo ingresa manualmente
            'requiresProfessional' => false, // campo no disponible; siempre false por ahora
        ], $items);

        return $this->json($results);
    }

    // -------------------------------------------------------------------------
    // Agregar prestación
    // -------------------------------------------------------------------------

    /**
     * Agrega una fila de prestación a la sesión y retorna un Turbo Stream
     * que hace append de la nueva fila en la tabla del panel.
     *
     * POST body: billingItemId, quantity, unitAmount, discount, _csrf_token
     *
     * Turbo Stream actions:
     *   - append  → services-table-body  (nueva fila)
     */
    #[Route('/add', name: 'add', methods: ['POST'])]
    public function add(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('services_add', $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        $billingItemId = (int) $request->request->get('billingItemId', 0);
        $quantity      = max(1, (int) $request->request->get('quantity', 1));
        $unitAmount    = $this->toDecimal($request->request->get('unitAmount', '0'));
        $discount      = $this->toDecimal($request->request->get('discount', '0'));

        $billingItem = $this->billingItemRepository->find($billingItemId);
        if ($billingItem === null) {
            throw $this->createNotFoundException('Prestación no encontrada.');
        }

        // lineTotal = max(0, quantity × unitAmount − discount)
        $lineSubtotal = bcmul((string) $quantity, $unitAmount, 2);
        $lineTotal    = bcsub($lineSubtotal, $discount, 2);
        if (bccomp($lineTotal, '0.00', 2) < 0) {
            $lineTotal = '0.00';
        }

        $rowId = uniqid('sr', true);
        $row   = [
            'rowId'         => $rowId,
            'billingItemId' => $billingItemId,
            'name'          => $billingItem->getName(),
            'quantity'      => $quantity,
            'unitAmount'    => $unitAmount,
            'discount'      => $discount,
            'totalAmount'   => $lineTotal,
        ];

        $rows          = $request->getSession()->get(self::SESSION_KEY, []);
        $rows[$rowId]  = $row;
        $request->getSession()->set(self::SESSION_KEY, $rows);

        return $this->render('revenue/cash-register/_services_stream.html.twig', [
            'operation' => 'add',
            'row'       => $row,
        ], new Response('', 200, ['Content-Type' => 'text/vnd.turbo-stream.html']));
    }

    // -------------------------------------------------------------------------
    // Eliminar prestación
    // -------------------------------------------------------------------------

    /**
     * Elimina una fila de la sesión y retorna un Turbo Stream que remueve
     * el elemento <tr> correspondiente del DOM.
     *
     * DELETE body (JSON): { rowId, _csrf_token }
     *
     * Turbo Stream actions:
     *   - remove → service-row-{rowId}
     */
    #[Route('/remove', name: 'remove', methods: ['DELETE'])]
    public function remove(Request $request): Response
    {
        $data  = json_decode($request->getContent(), true) ?? [];
        $rowId = (string) ($data['rowId'] ?? '');

        if (!$this->isCsrfTokenValid('services_remove', $data['_csrf_token'] ?? '')) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }

        $rows = $request->getSession()->get(self::SESSION_KEY, []);
        unset($rows[$rowId]);
        $request->getSession()->set(self::SESSION_KEY, $rows);

        return $this->render('revenue/cash-register/_services_stream.html.twig', [
            'operation' => 'remove',
            'rowId'     => $rowId,
        ], new Response('', 200, ['Content-Type' => 'text/vnd.turbo-stream.html']));
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Suma el totalAmount de todas las filas con bcmath.
     */
    private function buildTotal(array $rows): string
    {
        $total = '0.00';
        foreach ($rows as $row) {
            $total = bcadd($total, $row['totalAmount'] ?? '0.00', 2);
        }
        return $total;
    }

    /**
     * Serializa las filas a JSON para el campo hidden services_payload.
     */
    private function buildPayload(array $rows): string
    {
        return json_encode(array_values($rows), JSON_UNESCAPED_UNICODE) ?: '[]';
    }

    /**
     * Convierte un valor de entrada a string decimal con 2 decimales.
     * Rechaza negativos; retorna '0.00' ante cualquier valor inválido.
     */
    private function toDecimal(mixed $value): string
    {
        $num = filter_var($value, FILTER_VALIDATE_FLOAT);
        if ($num === false || $num < 0.0) {
            return '0.00';
        }
        return number_format($num, 2, '.', '');
    }
}
