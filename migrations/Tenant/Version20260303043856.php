<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303043856 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE budget_funder_footer_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE budget_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE budget_detail_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE budget_observation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE surgery_package_item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE budget (id INT NOT NULL, person_id INT NOT NULL, member_id INT NOT NULL, branch_id INT NOT NULL, professional_id INT DEFAULT NULL, payer_id INT DEFAULT NULL, agreement_id INT DEFAULT NULL, insurance_plan_id INT DEFAULT NULL, surgery_package_plan_id INT DEFAULT NULL, care_type_id INT DEFAULT NULL, account_type_id INT DEFAULT NULL, origin_id INT DEFAULT NULL, number INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, footer_text TEXT DEFAULT NULL, observation TEXT DEFAULT NULL, is_ambulatory BOOLEAN DEFAULT false NOT NULL, includes_honorariums BOOLEAN DEFAULT true NOT NULL, status VARCHAR(20) DEFAULT \'active\' NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_73F2F77B217BBB47 ON budget (person_id)');
        $this->addSql('CREATE INDEX IDX_73F2F77B7597D3FE ON budget (member_id)');
        $this->addSql('CREATE INDEX IDX_73F2F77BDCD6CC49 ON budget (branch_id)');
        $this->addSql('CREATE INDEX IDX_73F2F77BDB77003 ON budget (professional_id)');
        $this->addSql('CREATE INDEX IDX_73F2F77BC17AD9A9 ON budget (payer_id)');
        $this->addSql('CREATE INDEX IDX_73F2F77B24890B2B ON budget (agreement_id)');
        $this->addSql('CREATE INDEX IDX_73F2F77B5B8273E ON budget (insurance_plan_id)');
        $this->addSql('CREATE INDEX IDX_73F2F77BCAE4C663 ON budget (surgery_package_plan_id)');
        $this->addSql('CREATE INDEX IDX_73F2F77B852D12A6 ON budget (care_type_id)');
        $this->addSql('CREATE INDEX IDX_73F2F77BC6798DB ON budget (account_type_id)');
        $this->addSql('CREATE INDEX IDX_73F2F77B56A273CC ON budget (origin_id)');
        $this->addSql('CREATE TABLE budget_detail (id INT NOT NULL, budget_id INT NOT NULL, medical_service_id INT DEFAULT NULL, surgery_package_item_id INT DEFAULT NULL, quantity INT DEFAULT 1 NOT NULL, amount NUMERIC(10, 2) NOT NULL, is_estimated BOOLEAN DEFAULT false NOT NULL, item_type VARCHAR(30) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7AB3392F36ABA6B8 ON budget_detail (budget_id)');
        $this->addSql('CREATE INDEX IDX_7AB3392FC61D802A ON budget_detail (medical_service_id)');
        $this->addSql('CREATE INDEX IDX_7AB3392F301296A6 ON budget_detail (surgery_package_item_id)');
        $this->addSql('CREATE TABLE budget_observation (id INT NOT NULL, budget_id INT NOT NULL, member_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_email_sent BOOLEAN DEFAULT false NOT NULL, observation VARCHAR(2000) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B37C9DBF36ABA6B8 ON budget_observation (budget_id)');
        $this->addSql('CREATE INDEX IDX_B37C9DBF7597D3FE ON budget_observation (member_id)');
        $this->addSql('CREATE TABLE surgery_package_item (id INT NOT NULL, medical_service_id INT DEFAULT NULL, surgery_package_plan_id INT DEFAULT NULL, code VARCHAR(50) DEFAULT NULL, name VARCHAR(200) NOT NULL, item_type VARCHAR(30) NOT NULL, unit_cost NUMERIC(10, 2) DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EEB36629C61D802A ON surgery_package_item (medical_service_id)');
        $this->addSql('CREATE INDEX IDX_EEB36629CAE4C663 ON surgery_package_item (surgery_package_plan_id)');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77B217BBB47 FOREIGN KEY (person_id) REFERENCES "person" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77B7597D3FE FOREIGN KEY (member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77BDCD6CC49 FOREIGN KEY (branch_id) REFERENCES branch (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77BDB77003 FOREIGN KEY (professional_id) REFERENCES professional (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77BC17AD9A9 FOREIGN KEY (payer_id) REFERENCES payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77B24890B2B FOREIGN KEY (agreement_id) REFERENCES agreement (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77B5B8273E FOREIGN KEY (insurance_plan_id) REFERENCES insurance_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77BCAE4C663 FOREIGN KEY (surgery_package_plan_id) REFERENCES surgery_package_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77B852D12A6 FOREIGN KEY (care_type_id) REFERENCES care_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77BC6798DB FOREIGN KEY (account_type_id) REFERENCES account_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77B56A273CC FOREIGN KEY (origin_id) REFERENCES maintainer_origin (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_detail ADD CONSTRAINT FK_7AB3392F36ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_detail ADD CONSTRAINT FK_7AB3392FC61D802A FOREIGN KEY (medical_service_id) REFERENCES medical_service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_detail ADD CONSTRAINT FK_7AB3392F301296A6 FOREIGN KEY (surgery_package_item_id) REFERENCES surgery_package_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_observation ADD CONSTRAINT FK_B37C9DBF36ABA6B8 FOREIGN KEY (budget_id) REFERENCES budget (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_observation ADD CONSTRAINT FK_B37C9DBF7597D3FE FOREIGN KEY (member_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package_item ADD CONSTRAINT FK_EEB36629C61D802A FOREIGN KEY (medical_service_id) REFERENCES medical_service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package_item ADD CONSTRAINT FK_EEB36629CAE4C663 FOREIGN KEY (surgery_package_plan_id) REFERENCES surgery_package_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_funder_footer DROP CONSTRAINT fk_1b56104df57e09e4');
        $this->addSql('DROP TABLE budget_funder_footer');
        $this->addSql('ALTER TABLE budget_footer ADD detail TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE budget_footer_by_funder ADD payer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE budget_footer_by_funder ADD detail TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE budget_footer_by_funder ADD CONSTRAINT FK_D7997836C17AD9A9 FOREIGN KEY (payer_id) REFERENCES payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D7997836C17AD9A9 ON budget_footer_by_funder (payer_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE budget_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE budget_detail_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE budget_observation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE surgery_package_item_id_seq CASCADE');
        $this->addSql('CREATE SEQUENCE budget_funder_footer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE budget_funder_footer (id INT NOT NULL, budget_footer_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, id_estado INT DEFAULT 1 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_1b56104df57e09e4 ON budget_funder_footer (budget_footer_id)');
        $this->addSql('ALTER TABLE budget_funder_footer ADD CONSTRAINT fk_1b56104df57e09e4 FOREIGN KEY (budget_footer_id) REFERENCES budget_footer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget DROP CONSTRAINT FK_73F2F77B217BBB47');
        $this->addSql('ALTER TABLE budget DROP CONSTRAINT FK_73F2F77B7597D3FE');
        $this->addSql('ALTER TABLE budget DROP CONSTRAINT FK_73F2F77BDCD6CC49');
        $this->addSql('ALTER TABLE budget DROP CONSTRAINT FK_73F2F77BDB77003');
        $this->addSql('ALTER TABLE budget DROP CONSTRAINT FK_73F2F77BC17AD9A9');
        $this->addSql('ALTER TABLE budget DROP CONSTRAINT FK_73F2F77B24890B2B');
        $this->addSql('ALTER TABLE budget DROP CONSTRAINT FK_73F2F77B5B8273E');
        $this->addSql('ALTER TABLE budget DROP CONSTRAINT FK_73F2F77BCAE4C663');
        $this->addSql('ALTER TABLE budget DROP CONSTRAINT FK_73F2F77B852D12A6');
        $this->addSql('ALTER TABLE budget DROP CONSTRAINT FK_73F2F77BC6798DB');
        $this->addSql('ALTER TABLE budget DROP CONSTRAINT FK_73F2F77B56A273CC');
        $this->addSql('ALTER TABLE budget_detail DROP CONSTRAINT FK_7AB3392F36ABA6B8');
        $this->addSql('ALTER TABLE budget_detail DROP CONSTRAINT FK_7AB3392FC61D802A');
        $this->addSql('ALTER TABLE budget_detail DROP CONSTRAINT FK_7AB3392F301296A6');
        $this->addSql('ALTER TABLE budget_observation DROP CONSTRAINT FK_B37C9DBF36ABA6B8');
        $this->addSql('ALTER TABLE budget_observation DROP CONSTRAINT FK_B37C9DBF7597D3FE');
        $this->addSql('ALTER TABLE surgery_package_item DROP CONSTRAINT FK_EEB36629C61D802A');
        $this->addSql('ALTER TABLE surgery_package_item DROP CONSTRAINT FK_EEB36629CAE4C663');
        $this->addSql('DROP TABLE budget');
        $this->addSql('DROP TABLE budget_detail');
        $this->addSql('DROP TABLE budget_observation');
        $this->addSql('DROP TABLE surgery_package_item');
        $this->addSql('ALTER TABLE budget_footer DROP detail');
        $this->addSql('ALTER TABLE budget_footer_by_funder DROP CONSTRAINT FK_D7997836C17AD9A9');
        $this->addSql('DROP INDEX IDX_D7997836C17AD9A9');
        $this->addSql('ALTER TABLE budget_footer_by_funder DROP payer_id');
        $this->addSql('ALTER TABLE budget_footer_by_funder DROP detail');
    }
}
