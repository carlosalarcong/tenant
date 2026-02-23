<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Hakam\MultiTenancyBundle\Attribute\TenantFixture;

/**
 * Seed the account_status catalog for local development.
 *
 * Source: estado_cuenta table from legacy melisawiclinic DB (12 records).
 * In production these records already exist; run migrations to add the code column.
 *
 * Load with: php8.3 bin/console tenant:fixtures:load <tenant_id>
 */
#[TenantFixture]
class AccountStatusFixture extends Fixture
{
    /**
     * Each entry: [id, name, code]
     * IDs are fixed to match legacy estado_cuenta.ID and production data.
     */
    private const RECORDS = [
        [1,  'CERRADA / PAGADA',                        'cerrada_pagada'],
        [2,  'ANULADA',                                 'anulada'],
        [3,  'ABIERTA - EN GARANTIA',                   'abierta_en_garantia'],
        [4,  'CERRADA - EN REVISION INTERNA',           'cerrada_revision_interna'],
        [5,  'CERRADA - EN REVISION EN FINANCIADOR',    'cerrada_revision_financiador'],
        [6,  'CERRADA - PENDIENTE DE PAGO',             'cerrada_pendiente_pago'],
        [7,  'CERRADA - EN COBRANZA INTERNA',           'cerrada_cobranza_interna'],
        [8,  'CERRADA - EN COBRANZA JUDICIAL',          'cerrada_cobranza_judicial'],
        [9,  'CERRADA - PAGADA CON SALDO PENDIENTE',    'cerrada_pagada_con_saldo_pendiente'],
        [10, 'CERRADA - PAGADA TOTAL',                  'cerrada_pagada_total'],
        [11, 'ABIERTA - PENDIENTE DE PAGO',             'abierta_pendiente_pago'],
        [12, 'ABIERTA - PAGADA TOTAL',                  'abierta_pagada_total'],
    ];

    /**
     * The ObjectManager received here is the tenant EntityManager,
     * injected by TenantFixtureLoader via ORMExecutor.
     */
    public function load(ObjectManager $manager): void
    {
        /** @var EntityManagerInterface $manager */
        $connection = $manager->getConnection();

        foreach (self::RECORDS as [$id, $name, $code]) {
            // UPSERT: safe to re-run; preserves production IDs.
            $connection->executeStatement(
                'INSERT INTO account_status (id, name, code)
                 VALUES (:id, :name, :code)
                 ON CONFLICT (id) DO UPDATE SET name = EXCLUDED.name, code = EXCLUDED.code',
                ['id' => $id, 'name' => $name, 'code' => $code]
            );
        }

        // Keep the sequence in sync so future inserts don't collide.
        $connection->executeStatement(
            "SELECT setval(
                pg_get_serial_sequence('account_status', 'id'),
                (SELECT MAX(id) FROM account_status)
            )"
        );
    }
}
