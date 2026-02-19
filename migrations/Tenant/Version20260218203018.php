<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218203018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE account_status_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE patient_account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE payment_account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE payment_status_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE account_status (id INT NOT NULL, name VARCHAR(60) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE patient_account (id INT NOT NULL, patient_id INT NOT NULL, account_status_id INT DEFAULT NULL, modified_by_member_id INT DEFAULT NULL, total_account NUMERIC(12, 2) DEFAULT NULL, affected_account NUMERIC(12, 2) DEFAULT NULL, question_one INT DEFAULT NULL, question_two INT DEFAULT NULL, total_pre_account NUMERIC(12, 2) DEFAULT NULL, pre_account_number INT DEFAULT NULL, total_discount NUMERIC(12, 2) DEFAULT NULL, balance_correction_rut INT DEFAULT NULL, account_balance NUMERIC(10, 2) DEFAULT NULL, total_packaged_account NUMERIC(12, 2) DEFAULT NULL, modified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2055034F6B899279 ON patient_account (patient_id)');
        $this->addSql('CREATE INDEX IDX_2055034F498DD8E6 ON patient_account (account_status_id)');
        $this->addSql('CREATE INDEX IDX_2055034F24151A9B ON patient_account (modified_by_member_id)');
        $this->addSql('CREATE TABLE payment_account (id INT NOT NULL, patient_account_id INT NOT NULL, patient_id INT DEFAULT NULL, payment_status_id INT DEFAULT NULL, created_by_member_id INT DEFAULT NULL, cancelled_by_member_id INT DEFAULT NULL, cash_register_location_id INT DEFAULT NULL, sub_company_id INT DEFAULT NULL, difference_reason_id INT DEFAULT NULL, payment_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, document_number BIGINT DEFAULT NULL, tax INT DEFAULT NULL, amount NUMERIC(12, 2) DEFAULT NULL, document_status_id INT DEFAULT NULL, installment VARCHAR(11) DEFAULT NULL, scheduled_payment_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, cancellation_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, cancellation_reason TEXT DEFAULT NULL, regularization_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, regularization_notes TEXT DEFAULT NULL, difference_amount NUMERIC(10, 2) DEFAULT NULL, price_variance NUMERIC(10, 2) DEFAULT NULL, is_collection BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_647F584E2DABF955 ON payment_account (patient_account_id)');
        $this->addSql('CREATE INDEX IDX_647F584E6B899279 ON payment_account (patient_id)');
        $this->addSql('CREATE INDEX IDX_647F584E28DE2F95 ON payment_account (payment_status_id)');
        $this->addSql('CREATE INDEX IDX_647F584E2F92D693 ON payment_account (created_by_member_id)');
        $this->addSql('CREATE INDEX IDX_647F584EFA4FB431 ON payment_account (cancelled_by_member_id)');
        $this->addSql('CREATE INDEX IDX_647F584E39F50F9A ON payment_account (cash_register_location_id)');
        $this->addSql('CREATE INDEX IDX_647F584EBB50891B ON payment_account (sub_company_id)');
        $this->addSql('CREATE INDEX IDX_647F584E7EDE4786 ON payment_account (difference_reason_id)');
        $this->addSql('CREATE TABLE payment_status (id INT NOT NULL, name VARCHAR(45) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE patient_account ADD CONSTRAINT FK_2055034F6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE patient_account ADD CONSTRAINT FK_2055034F498DD8E6 FOREIGN KEY (account_status_id) REFERENCES account_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE patient_account ADD CONSTRAINT FK_2055034F24151A9B FOREIGN KEY (modified_by_member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment_account ADD CONSTRAINT FK_647F584E2DABF955 FOREIGN KEY (patient_account_id) REFERENCES patient_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment_account ADD CONSTRAINT FK_647F584E6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment_account ADD CONSTRAINT FK_647F584E28DE2F95 FOREIGN KEY (payment_status_id) REFERENCES payment_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment_account ADD CONSTRAINT FK_647F584E2F92D693 FOREIGN KEY (created_by_member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment_account ADD CONSTRAINT FK_647F584EFA4FB431 FOREIGN KEY (cancelled_by_member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment_account ADD CONSTRAINT FK_647F584E39F50F9A FOREIGN KEY (cash_register_location_id) REFERENCES cash_register_location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment_account ADD CONSTRAINT FK_647F584EBB50891B FOREIGN KEY (sub_company_id) REFERENCES sub_company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment_account ADD CONSTRAINT FK_647F584E7EDE4786 FOREIGN KEY (difference_reason_id) REFERENCES difference_reason (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP INDEX idx_a1a74bdb6b899279');
        $this->addSql('ALTER TABLE admission_record ADD patient_account_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD CONSTRAINT FK_A1A74BDB2DABF955 FOREIGN KEY (patient_account_id) REFERENCES patient_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A1A74BDB6B899279 ON admission_record (patient_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A1A74BDB2DABF955 ON admission_record (patient_account_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE admission_record DROP CONSTRAINT FK_A1A74BDB2DABF955');
        $this->addSql('DROP SEQUENCE account_status_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE patient_account_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE payment_account_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE payment_status_id_seq CASCADE');
        $this->addSql('ALTER TABLE patient_account DROP CONSTRAINT FK_2055034F6B899279');
        $this->addSql('ALTER TABLE patient_account DROP CONSTRAINT FK_2055034F498DD8E6');
        $this->addSql('ALTER TABLE patient_account DROP CONSTRAINT FK_2055034F24151A9B');
        $this->addSql('ALTER TABLE payment_account DROP CONSTRAINT FK_647F584E2DABF955');
        $this->addSql('ALTER TABLE payment_account DROP CONSTRAINT FK_647F584E6B899279');
        $this->addSql('ALTER TABLE payment_account DROP CONSTRAINT FK_647F584E28DE2F95');
        $this->addSql('ALTER TABLE payment_account DROP CONSTRAINT FK_647F584E2F92D693');
        $this->addSql('ALTER TABLE payment_account DROP CONSTRAINT FK_647F584EFA4FB431');
        $this->addSql('ALTER TABLE payment_account DROP CONSTRAINT FK_647F584E39F50F9A');
        $this->addSql('ALTER TABLE payment_account DROP CONSTRAINT FK_647F584EBB50891B');
        $this->addSql('ALTER TABLE payment_account DROP CONSTRAINT FK_647F584E7EDE4786');
        $this->addSql('DROP TABLE account_status');
        $this->addSql('DROP TABLE patient_account');
        $this->addSql('DROP TABLE payment_account');
        $this->addSql('DROP TABLE payment_status');
        $this->addSql('DROP INDEX UNIQ_A1A74BDB6B899279');
        $this->addSql('DROP INDEX UNIQ_A1A74BDB2DABF955');
        $this->addSql('ALTER TABLE admission_record DROP patient_account_id');
        $this->addSql('CREATE INDEX idx_a1a74bdb6b899279 ON admission_record (patient_id)');
    }
}
