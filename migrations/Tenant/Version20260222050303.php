<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Data migration: populate account_status.code for all 12 existing records.
 * Codes are machine-readable slugs used by business logic (e.g. hasPendingPayments).
 *
 * Source: estado_cuenta table in legacy melisawiclinic DB.
 * Must run AFTER Version20260222050302 (which adds the code column).
 */
final class Version20260222050303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Populate account_status.code with machine-readable slugs for all 12 existing records';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE account_status SET code = 'cerrada_pagada'                    WHERE id = 1");
        $this->addSql("UPDATE account_status SET code = 'anulada'                           WHERE id = 2");
        $this->addSql("UPDATE account_status SET code = 'abierta_en_garantia'               WHERE id = 3");
        $this->addSql("UPDATE account_status SET code = 'cerrada_revision_interna'          WHERE id = 4");
        $this->addSql("UPDATE account_status SET code = 'cerrada_revision_financiador'      WHERE id = 5");
        $this->addSql("UPDATE account_status SET code = 'cerrada_pendiente_pago'            WHERE id = 6");
        $this->addSql("UPDATE account_status SET code = 'cerrada_cobranza_interna'          WHERE id = 7");
        $this->addSql("UPDATE account_status SET code = 'cerrada_cobranza_judicial'         WHERE id = 8");
        $this->addSql("UPDATE account_status SET code = 'cerrada_pagada_con_saldo_pendiente' WHERE id = 9");
        $this->addSql("UPDATE account_status SET code = 'cerrada_pagada_total'              WHERE id = 10");
        $this->addSql("UPDATE account_status SET code = 'abierta_pendiente_pago'            WHERE id = 11");
        $this->addSql("UPDATE account_status SET code = 'abierta_pagada_total'              WHERE id = 12");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE account_status SET code = '' WHERE id IN (1,2,3,4,5,6,7,8,9,10,11,12)");
    }
}
