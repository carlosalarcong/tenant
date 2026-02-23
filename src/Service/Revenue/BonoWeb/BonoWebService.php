<?php

namespace App\Service\Revenue\BonoWeb;

use App\Entity\Tenant\BonoWebVoucher;
use App\Entity\Tenant\BonoWebVoucherDetail;
use App\Entity\Tenant\BillingItem;
use App\Entity\Tenant\CashRegisterLocation;
use App\Entity\Tenant\Member;
use App\Entity\Tenant\PaymentAccount;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * BonoWebService
 *
 * Orquesta el ciclo de vida de los vouchers BonoWeb/Snabb:
 *   createAndPersist  → genera el voucher en Snabb y persiste en BD
 *   syncStatus        → consulta estado actual en Snabb y actualiza BD
 *   confirmDone       → marca como Done en Snabb
 *   cancel            → marca como Canceled en Snabb
 */
class BonoWebService
{
    public function __construct(
        private readonly BonoWebClient        $client,
        private readonly TenantEntityManager  $em,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {}

    // ── Crear y persistir ─────────────────────────────────────────────────────

    /**
     * Genera un voucher BonoWeb en Snabb para las prestaciones indicadas.
     *
     * Payload Snabb:
     * ```json
     * {
     *   "beneficiario":    { "run": "12345678-9" },
     *   "codigoSucursal":  "SUC01",
     *   "prestaciones":    [{ "codigo": "P0301" }],
     *   "callback_url":    "https://app/revenue/bonoweb/webhook",
     *   "redirect_url":    "",
     *   "fechaExpiracion": "2026-02-24T10:00:00+00:00",
     *   "practitioner":    { "nombre": "Juan Pérez", "run": "98765432-1" }
     * }
     * ```
     *
     * @param array<int, array{
     *   name: string,
     *   code?: string|null,
     *   quantity?: int,
     *   billing_item_id?: int|null,
     *   copago?: string,
     *   bonificacion?: string,
     * }> $prestaciones
     *
     * @throws BonoWebException si la API de Snabb retorna error
     */
    public function createAndPersist(
        PaymentAccount        $paymentAccount,
        array                 $prestaciones,
        ?CashRegisterLocation $location     = null,
        ?Member               $practitioner = null,
    ): BonoWebVoucher {
        $patient = $paymentAccount->getPatient();
        $person  = $patient?->getPerson();

        // ── Construir payload según estructura API real de Snabb ─────────────
        $payload = [
            'beneficiario'   => [
                'run' => $person?->getIdentification() ?? '',
            ],
            'codigoSucursal' => $location?->getBranch()?->getCode() ?? '',
            'prestaciones'   => array_map(
                static fn(array $p) => ['codigo' => $p['code'] ?? ''],
                $prestaciones
            ),
            'callback_url'   => $this->urlGenerator->generate(
                'app_revenue_bonoweb_webhook',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            'redirect_url'   => '',
            'fechaExpiracion' => (new \DateTimeImmutable('+24 hours'))
                ->format(\DateTimeInterface::ATOM),
            'practitioner'   => [
                'nombre' => $practitioner?->getFullName() ?? '',
                'run'    => $practitioner?->getUsername()  ?? '',
            ],
        ];

        $apiResponse = $this->client->createVoucher($payload);

        // ── Persistir BonoWebVoucher ─────────────────────────────────────────
        $voucher = new BonoWebVoucher();
        $voucher->setVoucherId($apiResponse['id'] ?? '');
        $voucher->setVoucherUrl($apiResponse['url'] ?? null);
        $voucher->setStatus($apiResponse['status'] ?? 'Created');
        $voucher->setCopagoTotal((string) ($apiResponse['copagoTotal'] ?? '0.00'));
        $voucher->setBonificacionTotal((string) ($apiResponse['bonificacionTotal'] ?? '0.00'));

        $this->em->persist($voucher);

        // ── Persistir detalles ───────────────────────────────────────────────
        foreach ($prestaciones as $p) {
            $detail = new BonoWebVoucherDetail();
            $detail->setBonoWebVoucher($voucher);
            $detail->setServiceName($p['name'] ?? 'Prestación');
            $detail->setServiceCode($p['code'] ?? null);
            $detail->setCopago((string) ($p['copago'] ?? '0.00'));
            $detail->setBonificacion((string) ($p['bonificacion'] ?? '0.00'));
            $detail->setQuantity((int) ($p['quantity'] ?? 1));

            if (!empty($p['billing_item_id'])) {
                $billingItem = $this->em->getRepository(BillingItem::class)
                    ->find((int) $p['billing_item_id']);
                if ($billingItem !== null) {
                    $detail->setBillingItem($billingItem);
                }
            }

            $this->em->persist($detail);
        }

        $this->em->flush();

        return $voucher;
    }

    // ── Sincronizar estado ────────────────────────────────────────────────────

    /**
     * Consulta el estado actual del voucher en Snabb y actualiza la BD.
     *
     * @throws BonoWebException si la API de Snabb retorna error
     */
    public function syncStatus(BonoWebVoucher $voucher): BonoWebVoucher
    {
        $apiResponse = $this->client->getVoucher($voucher->getVoucherId());

        $voucher->setStatus($apiResponse['status'] ?? $voucher->getStatus());

        if (isset($apiResponse['copagoTotal'])) {
            $voucher->setCopagoTotal((string) $apiResponse['copagoTotal']);
        }
        if (isset($apiResponse['bonificacionTotal'])) {
            $voucher->setBonificacionTotal((string) $apiResponse['bonificacionTotal']);
        }

        $voucher->setUpdatedAt(new \DateTime());
        $this->em->flush();

        return $voucher;
    }

    // ── Confirmar Done ────────────────────────────────────────────────────────

    /**
     * Marca el voucher como completado (Done) en Snabb.
     *
     * @throws BonoWebException
     */
    public function confirmDone(BonoWebVoucher $voucher): void
    {
        $this->client->updateVoucherStatus($voucher->getVoucherId(), 'Done');
        $voucher->setStatus('Done');
        $voucher->setUpdatedAt(new \DateTime());
        $this->em->flush();
    }

    // ── Cancelar ─────────────────────────────────────────────────────────────

    /**
     * Cancela el voucher en Snabb.
     *
     * @throws BonoWebException
     */
    public function cancel(BonoWebVoucher $voucher): void
    {
        $this->client->updateVoucherStatus($voucher->getVoucherId(), 'Canceled');
        $voucher->setStatus('Canceled');
        $voucher->setUpdatedAt(new \DateTime());
        $this->em->flush();
    }
}
