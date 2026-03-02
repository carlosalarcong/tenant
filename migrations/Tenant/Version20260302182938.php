<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260302182938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Budget: add footer detail fields and payer relation; remove duplicate budget_funder_footer table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE budget_footer ADD detail TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE budget_footer_by_funder ADD payer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE budget_footer_by_funder ADD detail TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE budget_footer_by_funder ADD CONSTRAINT FK_D7997836C17AD9A9 FOREIGN KEY (payer_id) REFERENCES payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D7997836C17AD9A9 ON budget_footer_by_funder (payer_id)');
        $this->addSql('DROP TABLE IF EXISTS budget_funder_footer CASCADE');
        $this->addSql('DROP SEQUENCE IF EXISTS budget_funder_footer_id_seq CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE budget_footer DROP COLUMN detail');
        $this->addSql('ALTER TABLE budget_footer_by_funder DROP CONSTRAINT FK_D7997836C17AD9A9');
        $this->addSql('DROP INDEX IDX_D7997836C17AD9A9');
        $this->addSql('ALTER TABLE budget_footer_by_funder DROP payer_id');
        $this->addSql('ALTER TABLE budget_footer_by_funder DROP detail');
        $this->addSql('CREATE SEQUENCE budget_funder_footer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE budget_funder_footer (id INT NOT NULL, budget_footer_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, id_estado INT DEFAULT 1 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_1b56104df57e09e4 ON budget_funder_footer (budget_footer_id)');
        $this->addSql('ALTER TABLE budget_funder_footer ADD CONSTRAINT fk_1b56104df57e09e4 FOREIGN KEY (budget_footer_id) REFERENCES budget_footer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('SELECT setval(\'budget_funder_footer_id_seq\', COALESCE((SELECT MAX(id) FROM budget_funder_footer), 1), true)');
    }
}
