<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203121750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE article_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE article_supplier_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE article_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE article_warehouse_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE dispatch_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE signature_footer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE warehouse_specialty_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE article (id INT NOT NULL, article_type_id INT DEFAULT NULL, billing_sub_company_id INT DEFAULT NULL, sub_company_id INT DEFAULT NULL, budget_item_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(2000) DEFAULT NULL, account_group_code VARCHAR(255) DEFAULT NULL, is_consignment BOOLEAN NOT NULL, is_controlled BOOLEAN NOT NULL, has_expiration_date BOOLEAN NOT NULL, photo_name VARCHAR(255) DEFAULT NULL, min_stock NUMERIC(10, 2) DEFAULT NULL, critical_stock NUMERIC(10, 2) DEFAULT NULL, optimal_stock NUMERIC(10, 2) DEFAULT NULL, max_stock NUMERIC(10, 2) DEFAULT NULL, is_critical BOOLEAN NOT NULL, is_generic BOOLEAN NOT NULL, is_resterilizable BOOLEAN NOT NULL, is_for_sale BOOLEAN NOT NULL, is_billable BOOLEAN NOT NULL, is_first_aid_deduction BOOLEAN NOT NULL, generic_name VARCHAR(100) DEFAULT NULL, short_name VARCHAR(100) DEFAULT NULL, margin NUMERIC(10, 2) DEFAULT NULL, icon_code VARCHAR(255) DEFAULT NULL, cenabast_code VARCHAR(255) DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_23A0E66289EC824 ON article (article_type_id)');
        $this->addSql('CREATE INDEX IDX_23A0E663395B1AC ON article (billing_sub_company_id)');
        $this->addSql('CREATE INDEX IDX_23A0E66BB50891B ON article (sub_company_id)');
        $this->addSql('CREATE INDEX IDX_23A0E66D0EC18BF ON article (budget_item_id)');
        $this->addSql('CREATE TABLE article_supplier (id INT NOT NULL, article_id INT NOT NULL, supplier_name VARCHAR(255) DEFAULT NULL, price NUMERIC(10, 2) DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CEC298157294869C ON article_supplier (article_id)');
        $this->addSql('CREATE TABLE article_type (id INT NOT NULL, warehouse_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, is_pharmaceutical BOOLEAN NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3C9CD0285080ECDE ON article_type (warehouse_id)');
        $this->addSql('CREATE TABLE article_warehouse (id INT NOT NULL, article_id INT NOT NULL, warehouse_id INT NOT NULL, min_stock NUMERIC(10, 2) DEFAULT NULL, critical_stock NUMERIC(10, 2) DEFAULT NULL, optimal_stock NUMERIC(10, 2) DEFAULT NULL, is_critical BOOLEAN NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3686DBD87294869C ON article_warehouse (article_id)');
        $this->addSql('CREATE INDEX IDX_3686DBD85080ECDE ON article_warehouse (warehouse_id)');
        $this->addSql('CREATE TABLE dispatch_type (id INT NOT NULL, name VARCHAR(100) NOT NULL, code VARCHAR(50) DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE signature_footer (id INT NOT NULL, branch_id INT DEFAULT NULL, code INT NOT NULL, name VARCHAR(255) NOT NULL, position VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1E00DADDDCD6CC49 ON signature_footer (branch_id)');
        $this->addSql('CREATE TABLE warehouse_specialty (id INT NOT NULL, warehouse_id INT NOT NULL, specialty_id INT NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DFF3E8535080ECDE ON warehouse_specialty (warehouse_id)');
        $this->addSql('CREATE INDEX IDX_DFF3E8539A353316 ON warehouse_specialty (specialty_id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66289EC824 FOREIGN KEY (article_type_id) REFERENCES article_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E663395B1AC FOREIGN KEY (billing_sub_company_id) REFERENCES sub_company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66BB50891B FOREIGN KEY (sub_company_id) REFERENCES sub_company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66D0EC18BF FOREIGN KEY (budget_item_id) REFERENCES budget_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article_supplier ADD CONSTRAINT FK_CEC298157294869C FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article_type ADD CONSTRAINT FK_3C9CD0285080ECDE FOREIGN KEY (warehouse_id) REFERENCES warehouse (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article_warehouse ADD CONSTRAINT FK_3686DBD87294869C FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article_warehouse ADD CONSTRAINT FK_3686DBD85080ECDE FOREIGN KEY (warehouse_id) REFERENCES warehouse (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE signature_footer ADD CONSTRAINT FK_1E00DADDDCD6CC49 FOREIGN KEY (branch_id) REFERENCES branch (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE warehouse_specialty ADD CONSTRAINT FK_DFF3E8535080ECDE FOREIGN KEY (warehouse_id) REFERENCES warehouse (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE warehouse_specialty ADD CONSTRAINT FK_DFF3E8539A353316 FOREIGN KEY (specialty_id) REFERENCES specialty (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE article_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE article_supplier_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE article_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE article_warehouse_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE dispatch_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE signature_footer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE warehouse_specialty_id_seq CASCADE');
        $this->addSql('ALTER TABLE article DROP CONSTRAINT FK_23A0E66289EC824');
        $this->addSql('ALTER TABLE article DROP CONSTRAINT FK_23A0E663395B1AC');
        $this->addSql('ALTER TABLE article DROP CONSTRAINT FK_23A0E66BB50891B');
        $this->addSql('ALTER TABLE article DROP CONSTRAINT FK_23A0E66D0EC18BF');
        $this->addSql('ALTER TABLE article_supplier DROP CONSTRAINT FK_CEC298157294869C');
        $this->addSql('ALTER TABLE article_type DROP CONSTRAINT FK_3C9CD0285080ECDE');
        $this->addSql('ALTER TABLE article_warehouse DROP CONSTRAINT FK_3686DBD87294869C');
        $this->addSql('ALTER TABLE article_warehouse DROP CONSTRAINT FK_3686DBD85080ECDE');
        $this->addSql('ALTER TABLE signature_footer DROP CONSTRAINT FK_1E00DADDDCD6CC49');
        $this->addSql('ALTER TABLE warehouse_specialty DROP CONSTRAINT FK_DFF3E8535080ECDE');
        $this->addSql('ALTER TABLE warehouse_specialty DROP CONSTRAINT FK_DFF3E8539A353316');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE article_supplier');
        $this->addSql('DROP TABLE article_type');
        $this->addSql('DROP TABLE article_warehouse');
        $this->addSql('DROP TABLE dispatch_type');
        $this->addSql('DROP TABLE signature_footer');
        $this->addSql('DROP TABLE warehouse_specialty');
    }
}
