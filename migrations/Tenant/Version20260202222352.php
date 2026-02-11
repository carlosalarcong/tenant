<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260202222352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE bank_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE bank_account_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE billing_payment_method_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE cash_register_location_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE credit_card_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE credit_card_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE currency_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE difference_direction_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE difference_reason_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE difference_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE document_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE gratuity_reason_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE gratuity_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE payment_condition_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE payment_method_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE payment_method_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE transfer_indicator_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE bank (id INT NOT NULL, rut VARCHAR(50) DEFAULT NULL, name VARCHAR(255) NOT NULL, current_account BIGINT DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE bank_account_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE billing_payment_method (id INT NOT NULL, code VARCHAR(3) NOT NULL, name VARCHAR(100) NOT NULL, is_cash BOOLEAN NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE cash_register_location (id INT NOT NULL, branch_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, id_estado INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_225A76E6DCD6CC49 ON cash_register_location (branch_id)');
        $this->addSql('CREATE TABLE credit_card (id INT NOT NULL, credit_card_type_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, abbreviation VARCHAR(50) DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_11D627EEB4A31CAF ON credit_card (credit_card_type_id)');
        $this->addSql('CREATE TABLE credit_card_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE currency_type (id INT NOT NULL, name VARCHAR(100) NOT NULL, is_clp BOOLEAN NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE difference_direction (id INT NOT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE difference_reason (id INT NOT NULL, difference_direction_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AEE18C7B109781D ON difference_reason (difference_direction_id)');
        $this->addSql('CREATE TABLE difference_type (id INT NOT NULL, difference_direction_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A1779E66109781D ON difference_type (difference_direction_id)');
        $this->addSql('CREATE TABLE document_type (id INT NOT NULL, sii_code VARCHAR(3) DEFAULT NULL, name VARCHAR(70) NOT NULL, is_dte BOOLEAN DEFAULT false NOT NULL, is_logistics BOOLEAN DEFAULT false NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, id_estado INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE gratuity_reason (id INT NOT NULL, branch_id INT DEFAULT NULL, gratuity_type_id INT DEFAULT NULL, name VARCHAR(50) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_12F4FE3EDCD6CC49 ON gratuity_reason (branch_id)');
        $this->addSql('CREATE INDEX IDX_12F4FE3EDB29165 ON gratuity_reason (gratuity_type_id)');
        $this->addSql('CREATE TABLE gratuity_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE payment_condition (id INT NOT NULL, name VARCHAR(255) NOT NULL, interface_code VARCHAR(10) NOT NULL, max_term INT NOT NULL, is_up_to_date BOOLEAN NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE payment_method (id INT NOT NULL, parent_id INT DEFAULT NULL, payment_method_type_id INT DEFAULT NULL, code VARCHAR(10) DEFAULT NULL, name VARCHAR(255) NOT NULL, issues_receipt BOOLEAN DEFAULT false NOT NULL, is_guarantee BOOLEAN DEFAULT false NOT NULL, is_professional_payment BOOLEAN DEFAULT false NOT NULL, is_web_payment BOOLEAN DEFAULT false NOT NULL, document_type_code VARCHAR(10) DEFAULT NULL, accounting_code VARCHAR(50) DEFAULT NULL, visible_in_cash_register BOOLEAN DEFAULT true NOT NULL, credit_card_payment BOOLEAN DEFAULT false NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, id_estado INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7B61A1F6727ACA70 ON payment_method (parent_id)');
        $this->addSql('CREATE INDEX IDX_7B61A1F62476A5D8 ON payment_method (payment_method_type_id)');
        $this->addSql('CREATE TABLE payment_method_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, id_estado INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE transfer_indicator (id INT NOT NULL, code INT NOT NULL, name VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE cash_register_location ADD CONSTRAINT FK_225A76E6DCD6CC49 FOREIGN KEY (branch_id) REFERENCES branch (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE credit_card ADD CONSTRAINT FK_11D627EEB4A31CAF FOREIGN KEY (credit_card_type_id) REFERENCES credit_card_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE difference_reason ADD CONSTRAINT FK_AEE18C7B109781D FOREIGN KEY (difference_direction_id) REFERENCES difference_direction (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE difference_type ADD CONSTRAINT FK_A1779E66109781D FOREIGN KEY (difference_direction_id) REFERENCES difference_direction (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE gratuity_reason ADD CONSTRAINT FK_12F4FE3EDCD6CC49 FOREIGN KEY (branch_id) REFERENCES branch (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE gratuity_reason ADD CONSTRAINT FK_12F4FE3EDB29165 FOREIGN KEY (gratuity_type_id) REFERENCES gratuity_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment_method ADD CONSTRAINT FK_7B61A1F6727ACA70 FOREIGN KEY (parent_id) REFERENCES payment_method (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment_method ADD CONSTRAINT FK_7B61A1F62476A5D8 FOREIGN KEY (payment_method_type_id) REFERENCES payment_method_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE bank_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE bank_account_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE billing_payment_method_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE cash_register_location_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE credit_card_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE credit_card_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE currency_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE difference_direction_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE difference_reason_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE difference_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE document_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE gratuity_reason_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE gratuity_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE payment_condition_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE payment_method_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE payment_method_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE transfer_indicator_id_seq CASCADE');
        $this->addSql('ALTER TABLE cash_register_location DROP CONSTRAINT FK_225A76E6DCD6CC49');
        $this->addSql('ALTER TABLE credit_card DROP CONSTRAINT FK_11D627EEB4A31CAF');
        $this->addSql('ALTER TABLE difference_reason DROP CONSTRAINT FK_AEE18C7B109781D');
        $this->addSql('ALTER TABLE difference_type DROP CONSTRAINT FK_A1779E66109781D');
        $this->addSql('ALTER TABLE gratuity_reason DROP CONSTRAINT FK_12F4FE3EDCD6CC49');
        $this->addSql('ALTER TABLE gratuity_reason DROP CONSTRAINT FK_12F4FE3EDB29165');
        $this->addSql('ALTER TABLE payment_method DROP CONSTRAINT FK_7B61A1F6727ACA70');
        $this->addSql('ALTER TABLE payment_method DROP CONSTRAINT FK_7B61A1F62476A5D8');
        $this->addSql('DROP TABLE bank');
        $this->addSql('DROP TABLE bank_account_type');
        $this->addSql('DROP TABLE billing_payment_method');
        $this->addSql('DROP TABLE cash_register_location');
        $this->addSql('DROP TABLE credit_card');
        $this->addSql('DROP TABLE credit_card_type');
        $this->addSql('DROP TABLE currency_type');
        $this->addSql('DROP TABLE difference_direction');
        $this->addSql('DROP TABLE difference_reason');
        $this->addSql('DROP TABLE difference_type');
        $this->addSql('DROP TABLE document_type');
        $this->addSql('DROP TABLE gratuity_reason');
        $this->addSql('DROP TABLE gratuity_type');
        $this->addSql('DROP TABLE payment_condition');
        $this->addSql('DROP TABLE payment_method');
        $this->addSql('DROP TABLE payment_method_type');
        $this->addSql('DROP TABLE transfer_indicator');
    }
}
