<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223023720 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'BonoWeb FONASA + DTE Aces: create bono_web_voucher, bono_web_voucher_detail, dte_document; add billing_item.tax_affectation_type_id FK; add payment_account.cash_register_id FK';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE bono_web_voucher_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE bono_web_voucher_detail_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE dte_document_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE bono_web_voucher (id INT NOT NULL, payment_account_detail_id INT DEFAULT NULL, voucher_id VARCHAR(100) NOT NULL, voucher_url VARCHAR(500) DEFAULT NULL, status VARCHAR(30) DEFAULT \'Created\' NOT NULL, copago_total NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, bonificacion_total NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F47CCFDA28AA1B6F ON bono_web_voucher (voucher_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F47CCFDAD63C9D11 ON bono_web_voucher (payment_account_detail_id)');
        $this->addSql('COMMENT ON COLUMN bono_web_voucher.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE bono_web_voucher_detail (id INT NOT NULL, bono_web_voucher_id INT NOT NULL, billing_item_id INT DEFAULT NULL, service_name VARCHAR(200) NOT NULL, service_code VARCHAR(20) DEFAULT NULL, copago NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, bonificacion NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, quantity INT DEFAULT 1 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D1EE444F38E83B4 ON bono_web_voucher_detail (bono_web_voucher_id)');
        $this->addSql('CREATE INDEX IDX_D1EE4447941D989 ON bono_web_voucher_detail (billing_item_id)');
        $this->addSql('CREATE TABLE dte_document (id INT NOT NULL, payment_account_id INT NOT NULL, tipodte VARCHAR(5) NOT NULL, folio_number INT NOT NULL, status VARCHAR(30) DEFAULT \'sent\' NOT NULL, aces_response JSON DEFAULT NULL, retry_data JSON DEFAULT NULL, sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3914A4E5AE9DDE6F ON dte_document (payment_account_id)');
        $this->addSql('COMMENT ON COLUMN dte_document.sent_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN dte_document.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE bono_web_voucher ADD CONSTRAINT FK_F47CCFDAD63C9D11 FOREIGN KEY (payment_account_detail_id) REFERENCES payment_account_detail (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bono_web_voucher_detail ADD CONSTRAINT FK_D1EE444F38E83B4 FOREIGN KEY (bono_web_voucher_id) REFERENCES bono_web_voucher (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bono_web_voucher_detail ADD CONSTRAINT FK_D1EE4447941D989 FOREIGN KEY (billing_item_id) REFERENCES billing_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE dte_document ADD CONSTRAINT FK_3914A4E5AE9DDE6F FOREIGN KEY (payment_account_id) REFERENCES payment_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE billing_item ADD tax_affectation_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE billing_item ADD CONSTRAINT FK_60691BD9588DA015 FOREIGN KEY (tax_affectation_type_id) REFERENCES "tax_affectation_type" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_60691BD9588DA015 ON billing_item (tax_affectation_type_id)');
        $this->addSql('ALTER TABLE payment_account ADD cash_register_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE payment_account ADD CONSTRAINT FK_PA_CASH_REGISTER FOREIGN KEY (cash_register_id) REFERENCES cash_register (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_PA_CASH_REGISTER ON payment_account (cash_register_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE bono_web_voucher_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE bono_web_voucher_detail_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE dte_document_id_seq CASCADE');
        $this->addSql('ALTER TABLE bono_web_voucher DROP CONSTRAINT FK_F47CCFDAD63C9D11');
        $this->addSql('ALTER TABLE bono_web_voucher_detail DROP CONSTRAINT FK_D1EE444F38E83B4');
        $this->addSql('ALTER TABLE bono_web_voucher_detail DROP CONSTRAINT FK_D1EE4447941D989');
        $this->addSql('ALTER TABLE dte_document DROP CONSTRAINT FK_3914A4E5AE9DDE6F');
        $this->addSql('DROP TABLE bono_web_voucher');
        $this->addSql('DROP TABLE bono_web_voucher_detail');
        $this->addSql('DROP TABLE dte_document');
        $this->addSql('ALTER TABLE billing_item DROP CONSTRAINT FK_60691BD9588DA015');
        $this->addSql('DROP INDEX IDX_60691BD9588DA015');
        $this->addSql('ALTER TABLE billing_item DROP tax_affectation_type_id');
        $this->addSql('ALTER TABLE payment_account DROP CONSTRAINT FK_PA_CASH_REGISTER');
        $this->addSql('DROP INDEX IDX_PA_CASH_REGISTER');
        $this->addSql('ALTER TABLE payment_account DROP cash_register_id');
    }
}
