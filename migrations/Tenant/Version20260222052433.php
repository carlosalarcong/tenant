<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260222052433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Phase A: create Revenue/CashRegister core tables — cashier_assignment, cash_register, cash_register_detail, cash_register_check_detail, voucher, voucher_entry, difference, clinical_action_patient, payment_account_detail';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE cash_register_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE cash_register_check_detail_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE cash_register_detail_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE cashier_assignment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE clinical_action_patient_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE difference_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE payment_account_detail_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE voucher_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE voucher_entry_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE cash_register (id INT NOT NULL, member_id INT NOT NULL, cash_register_location_id INT NOT NULL, branch_id INT DEFAULT NULL, reopened_by_member_id INT DEFAULT NULL, opened_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, closed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, initial_amount NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, real_amount NUMERIC(12, 2) DEFAULT NULL, surplus NUMERIC(12, 2) DEFAULT NULL, deficit NUMERIC(12, 2) DEFAULT NULL, status VARCHAR(20) DEFAULT \'abierta\' NOT NULL, reopened_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3D7AB1D97597D3FE ON cash_register (member_id)');
        $this->addSql('CREATE INDEX IDX_3D7AB1D939F50F9A ON cash_register (cash_register_location_id)');
        $this->addSql('CREATE INDEX IDX_3D7AB1D9DCD6CC49 ON cash_register (branch_id)');
        $this->addSql('CREATE INDEX IDX_3D7AB1D9A6C55B4F ON cash_register (reopened_by_member_id)');
        $this->addSql('CREATE TABLE cash_register_check_detail (id INT NOT NULL, cash_register_id INT NOT NULL, bank_id INT DEFAULT NULL, check_number VARCHAR(30) DEFAULT NULL, amount NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, check_date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4113EA6FA917CC69 ON cash_register_check_detail (cash_register_id)');
        $this->addSql('CREATE INDEX IDX_4113EA6F11C8FB41 ON cash_register_check_detail (bank_id)');
        $this->addSql('CREATE TABLE cash_register_detail (id INT NOT NULL, cash_register_id INT NOT NULL, payment_method_id INT NOT NULL, bank_id INT DEFAULT NULL, amount NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, deposit_number VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_89DBC644A917CC69 ON cash_register_detail (cash_register_id)');
        $this->addSql('CREATE INDEX IDX_89DBC6445AA1164F ON cash_register_detail (payment_method_id)');
        $this->addSql('CREATE INDEX IDX_89DBC64411C8FB41 ON cash_register_detail (bank_id)');
        $this->addSql('CREATE TABLE cashier_assignment (id INT NOT NULL, member_id INT NOT NULL, cash_register_location_id INT NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2423CBDC7597D3FE ON cashier_assignment (member_id)');
        $this->addSql('CREATE INDEX IDX_2423CBDC39F50F9A ON cashier_assignment (cash_register_location_id)');
        $this->addSql('CREATE TABLE clinical_action_patient (id INT NOT NULL, payment_account_id INT NOT NULL, billing_item_id INT DEFAULT NULL, difference_id INT DEFAULT NULL, professional_id INT DEFAULT NULL, quantity INT DEFAULT 1 NOT NULL, unit_price NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, total_amount NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, discount_amount NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, is_cancelled BOOLEAN DEFAULT false NOT NULL, notes TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_52D484DDAE9DDE6F ON clinical_action_patient (payment_account_id)');
        $this->addSql('CREATE INDEX IDX_52D484DD7941D989 ON clinical_action_patient (billing_item_id)');
        $this->addSql('CREATE INDEX IDX_52D484DD48C6C17F ON clinical_action_patient (difference_id)');
        $this->addSql('CREATE INDEX IDX_52D484DDDB77003 ON clinical_action_patient (professional_id)');
        $this->addSql('CREATE TABLE difference (id INT NOT NULL, requested_by_member_id INT NOT NULL, difference_reason_id INT DEFAULT NULL, difference_type_id INT DEFAULT NULL, difference_direction_id INT DEFAULT NULL, patient_account_id INT DEFAULT NULL, authorized_by_member_id INT DEFAULT NULL, cancelled_by_member_id INT DEFAULT NULL, requested_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, total_account NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, total_discount NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, total_after_discount NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, status VARCHAR(20) DEFAULT \'solicitada\' NOT NULL, authorized_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, cancelled_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D3364A8E497E6F ON difference (requested_by_member_id)');
        $this->addSql('CREATE INDEX IDX_D3364A8E7EDE4786 ON difference (difference_reason_id)');
        $this->addSql('CREATE INDEX IDX_D3364A8EBB83108 ON difference (difference_type_id)');
        $this->addSql('CREATE INDEX IDX_D3364A8E109781D ON difference (difference_direction_id)');
        $this->addSql('CREATE INDEX IDX_D3364A8E2DABF955 ON difference (patient_account_id)');
        $this->addSql('CREATE INDEX IDX_D3364A8E4B243BD9 ON difference (authorized_by_member_id)');
        $this->addSql('CREATE INDEX IDX_D3364A8EFA4FB431 ON difference (cancelled_by_member_id)');
        $this->addSql('CREATE TABLE payment_account_detail (id INT NOT NULL, payment_account_id INT NOT NULL, payment_method_id INT NOT NULL, voucher_entry_id INT DEFAULT NULL, amount NUMERIC(12, 2) DEFAULT \'0.00\' NOT NULL, reference_number VARCHAR(100) DEFAULT NULL, card_last_digits VARCHAR(4) DEFAULT NULL, installments INT DEFAULT NULL, is_cancelled BOOLEAN DEFAULT false NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EC1A0BE4AE9DDE6F ON payment_account_detail (payment_account_id)');
        $this->addSql('CREATE INDEX IDX_EC1A0BE45AA1164F ON payment_account_detail (payment_method_id)');
        $this->addSql('CREATE INDEX IDX_EC1A0BE4249111CB ON payment_account_detail (voucher_entry_id)');
        $this->addSql('CREATE TABLE voucher (id INT NOT NULL, cash_register_location_id INT NOT NULL, sub_company_id INT DEFAULT NULL, folio_from INT NOT NULL, folio_to INT NOT NULL, current_folio INT NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1392A5D839F50F9A ON voucher (cash_register_location_id)');
        $this->addSql('CREATE INDEX IDX_1392A5D8BB50891B ON voucher (sub_company_id)');
        $this->addSql('CREATE TABLE voucher_entry (id INT NOT NULL, voucher_id INT NOT NULL, payment_account_id INT NOT NULL, member_id INT DEFAULT NULL, folio_number INT NOT NULL, issued_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_cancelled BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EE9ACB4E28AA1B6F ON voucher_entry (voucher_id)');
        $this->addSql('CREATE INDEX IDX_EE9ACB4EAE9DDE6F ON voucher_entry (payment_account_id)');
        $this->addSql('CREATE INDEX IDX_EE9ACB4E7597D3FE ON voucher_entry (member_id)');
        $this->addSql('ALTER TABLE cash_register ADD CONSTRAINT FK_3D7AB1D97597D3FE FOREIGN KEY (member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cash_register ADD CONSTRAINT FK_3D7AB1D939F50F9A FOREIGN KEY (cash_register_location_id) REFERENCES cash_register_location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cash_register ADD CONSTRAINT FK_3D7AB1D9DCD6CC49 FOREIGN KEY (branch_id) REFERENCES branch (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cash_register ADD CONSTRAINT FK_3D7AB1D9A6C55B4F FOREIGN KEY (reopened_by_member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cash_register_check_detail ADD CONSTRAINT FK_4113EA6FA917CC69 FOREIGN KEY (cash_register_id) REFERENCES cash_register (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cash_register_check_detail ADD CONSTRAINT FK_4113EA6F11C8FB41 FOREIGN KEY (bank_id) REFERENCES bank (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cash_register_detail ADD CONSTRAINT FK_89DBC644A917CC69 FOREIGN KEY (cash_register_id) REFERENCES cash_register (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cash_register_detail ADD CONSTRAINT FK_89DBC6445AA1164F FOREIGN KEY (payment_method_id) REFERENCES payment_method (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cash_register_detail ADD CONSTRAINT FK_89DBC64411C8FB41 FOREIGN KEY (bank_id) REFERENCES bank (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cashier_assignment ADD CONSTRAINT FK_2423CBDC7597D3FE FOREIGN KEY (member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cashier_assignment ADD CONSTRAINT FK_2423CBDC39F50F9A FOREIGN KEY (cash_register_location_id) REFERENCES cash_register_location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE clinical_action_patient ADD CONSTRAINT FK_52D484DDAE9DDE6F FOREIGN KEY (payment_account_id) REFERENCES payment_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE clinical_action_patient ADD CONSTRAINT FK_52D484DD7941D989 FOREIGN KEY (billing_item_id) REFERENCES billing_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE clinical_action_patient ADD CONSTRAINT FK_52D484DD48C6C17F FOREIGN KEY (difference_id) REFERENCES difference (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE clinical_action_patient ADD CONSTRAINT FK_52D484DDDB77003 FOREIGN KEY (professional_id) REFERENCES professional (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE difference ADD CONSTRAINT FK_D3364A8E497E6F FOREIGN KEY (requested_by_member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE difference ADD CONSTRAINT FK_D3364A8E7EDE4786 FOREIGN KEY (difference_reason_id) REFERENCES difference_reason (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE difference ADD CONSTRAINT FK_D3364A8EBB83108 FOREIGN KEY (difference_type_id) REFERENCES difference_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE difference ADD CONSTRAINT FK_D3364A8E109781D FOREIGN KEY (difference_direction_id) REFERENCES difference_direction (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE difference ADD CONSTRAINT FK_D3364A8E2DABF955 FOREIGN KEY (patient_account_id) REFERENCES patient_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE difference ADD CONSTRAINT FK_D3364A8E4B243BD9 FOREIGN KEY (authorized_by_member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE difference ADD CONSTRAINT FK_D3364A8EFA4FB431 FOREIGN KEY (cancelled_by_member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment_account_detail ADD CONSTRAINT FK_EC1A0BE4AE9DDE6F FOREIGN KEY (payment_account_id) REFERENCES payment_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment_account_detail ADD CONSTRAINT FK_EC1A0BE45AA1164F FOREIGN KEY (payment_method_id) REFERENCES payment_method (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE payment_account_detail ADD CONSTRAINT FK_EC1A0BE4249111CB FOREIGN KEY (voucher_entry_id) REFERENCES voucher_entry (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE voucher ADD CONSTRAINT FK_1392A5D839F50F9A FOREIGN KEY (cash_register_location_id) REFERENCES cash_register_location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE voucher ADD CONSTRAINT FK_1392A5D8BB50891B FOREIGN KEY (sub_company_id) REFERENCES sub_company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE voucher_entry ADD CONSTRAINT FK_EE9ACB4E28AA1B6F FOREIGN KEY (voucher_id) REFERENCES voucher (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE voucher_entry ADD CONSTRAINT FK_EE9ACB4EAE9DDE6F FOREIGN KEY (payment_account_id) REFERENCES payment_account (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE voucher_entry ADD CONSTRAINT FK_EE9ACB4E7597D3FE FOREIGN KEY (member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE cash_register_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE cash_register_check_detail_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE cash_register_detail_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE cashier_assignment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE clinical_action_patient_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE difference_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE payment_account_detail_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE voucher_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE voucher_entry_id_seq CASCADE');
        $this->addSql('ALTER TABLE cash_register DROP CONSTRAINT FK_3D7AB1D97597D3FE');
        $this->addSql('ALTER TABLE cash_register DROP CONSTRAINT FK_3D7AB1D939F50F9A');
        $this->addSql('ALTER TABLE cash_register DROP CONSTRAINT FK_3D7AB1D9DCD6CC49');
        $this->addSql('ALTER TABLE cash_register DROP CONSTRAINT FK_3D7AB1D9A6C55B4F');
        $this->addSql('ALTER TABLE cash_register_check_detail DROP CONSTRAINT FK_4113EA6FA917CC69');
        $this->addSql('ALTER TABLE cash_register_check_detail DROP CONSTRAINT FK_4113EA6F11C8FB41');
        $this->addSql('ALTER TABLE cash_register_detail DROP CONSTRAINT FK_89DBC644A917CC69');
        $this->addSql('ALTER TABLE cash_register_detail DROP CONSTRAINT FK_89DBC6445AA1164F');
        $this->addSql('ALTER TABLE cash_register_detail DROP CONSTRAINT FK_89DBC64411C8FB41');
        $this->addSql('ALTER TABLE cashier_assignment DROP CONSTRAINT FK_2423CBDC7597D3FE');
        $this->addSql('ALTER TABLE cashier_assignment DROP CONSTRAINT FK_2423CBDC39F50F9A');
        $this->addSql('ALTER TABLE clinical_action_patient DROP CONSTRAINT FK_52D484DDAE9DDE6F');
        $this->addSql('ALTER TABLE clinical_action_patient DROP CONSTRAINT FK_52D484DD7941D989');
        $this->addSql('ALTER TABLE clinical_action_patient DROP CONSTRAINT FK_52D484DD48C6C17F');
        $this->addSql('ALTER TABLE clinical_action_patient DROP CONSTRAINT FK_52D484DDDB77003');
        $this->addSql('ALTER TABLE difference DROP CONSTRAINT FK_D3364A8E497E6F');
        $this->addSql('ALTER TABLE difference DROP CONSTRAINT FK_D3364A8E7EDE4786');
        $this->addSql('ALTER TABLE difference DROP CONSTRAINT FK_D3364A8EBB83108');
        $this->addSql('ALTER TABLE difference DROP CONSTRAINT FK_D3364A8E109781D');
        $this->addSql('ALTER TABLE difference DROP CONSTRAINT FK_D3364A8E2DABF955');
        $this->addSql('ALTER TABLE difference DROP CONSTRAINT FK_D3364A8E4B243BD9');
        $this->addSql('ALTER TABLE difference DROP CONSTRAINT FK_D3364A8EFA4FB431');
        $this->addSql('ALTER TABLE payment_account_detail DROP CONSTRAINT FK_EC1A0BE4AE9DDE6F');
        $this->addSql('ALTER TABLE payment_account_detail DROP CONSTRAINT FK_EC1A0BE45AA1164F');
        $this->addSql('ALTER TABLE payment_account_detail DROP CONSTRAINT FK_EC1A0BE4249111CB');
        $this->addSql('ALTER TABLE voucher DROP CONSTRAINT FK_1392A5D839F50F9A');
        $this->addSql('ALTER TABLE voucher DROP CONSTRAINT FK_1392A5D8BB50891B');
        $this->addSql('ALTER TABLE voucher_entry DROP CONSTRAINT FK_EE9ACB4E28AA1B6F');
        $this->addSql('ALTER TABLE voucher_entry DROP CONSTRAINT FK_EE9ACB4EAE9DDE6F');
        $this->addSql('ALTER TABLE voucher_entry DROP CONSTRAINT FK_EE9ACB4E7597D3FE');
        $this->addSql('DROP TABLE cash_register');
        $this->addSql('DROP TABLE cash_register_check_detail');
        $this->addSql('DROP TABLE cash_register_detail');
        $this->addSql('DROP TABLE cashier_assignment');
        $this->addSql('DROP TABLE clinical_action_patient');
        $this->addSql('DROP TABLE difference');
        $this->addSql('DROP TABLE payment_account_detail');
        $this->addSql('DROP TABLE voucher');
        $this->addSql('DROP TABLE voucher_entry');
    }
}
