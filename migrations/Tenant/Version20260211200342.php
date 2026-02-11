<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211200342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE care_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE insurance_plan_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE patient_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE care_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, route VARCHAR(255) DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE insurance_plan (id INT NOT NULL, name VARCHAR(255) NOT NULL, is_package BOOLEAN DEFAULT false NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN insurance_plan.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN insurance_plan.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE patient (id INT NOT NULL, person_id INT NOT NULL, tutor_id INT DEFAULT NULL, payer_id INT NOT NULL, agreement_id INT DEFAULT NULL, insurance_plan_id INT DEFAULT NULL, care_type_id INT NOT NULL, origin_id INT DEFAULT NULL, external_referrer_id INT DEFAULT NULL, professional_id INT DEFAULT NULL, requesting_company_id INT DEFAULT NULL, event_number INT DEFAULT NULL, care_number INT DEFAULT NULL, admission_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_external BOOLEAN DEFAULT false NOT NULL, external_professional VARCHAR(150) DEFAULT NULL, exam_order VARCHAR(100) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1ADAD7EB217BBB47 ON patient (person_id)');
        $this->addSql('CREATE INDEX IDX_1ADAD7EB208F64F1 ON patient (tutor_id)');
        $this->addSql('CREATE INDEX IDX_1ADAD7EBC17AD9A9 ON patient (payer_id)');
        $this->addSql('CREATE INDEX IDX_1ADAD7EB24890B2B ON patient (agreement_id)');
        $this->addSql('CREATE INDEX IDX_1ADAD7EB5B8273E ON patient (insurance_plan_id)');
        $this->addSql('CREATE INDEX IDX_1ADAD7EB852D12A6 ON patient (care_type_id)');
        $this->addSql('CREATE INDEX IDX_1ADAD7EB56A273CC ON patient (origin_id)');
        $this->addSql('CREATE INDEX IDX_1ADAD7EB114B6FAC ON patient (external_referrer_id)');
        $this->addSql('CREATE INDEX IDX_1ADAD7EBDB77003 ON patient (professional_id)');
        $this->addSql('CREATE INDEX IDX_1ADAD7EBE3FB3114 ON patient (requesting_company_id)');
        $this->addSql('COMMENT ON COLUMN patient.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN patient.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EB217BBB47 FOREIGN KEY (person_id) REFERENCES "person" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EB208F64F1 FOREIGN KEY (tutor_id) REFERENCES "person" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EBC17AD9A9 FOREIGN KEY (payer_id) REFERENCES payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EB24890B2B FOREIGN KEY (agreement_id) REFERENCES agreement (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EB5B8273E FOREIGN KEY (insurance_plan_id) REFERENCES insurance_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EB852D12A6 FOREIGN KEY (care_type_id) REFERENCES care_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EB56A273CC FOREIGN KEY (origin_id) REFERENCES maintainer_origin (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EB114B6FAC FOREIGN KEY (external_referrer_id) REFERENCES external_referrer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EBDB77003 FOREIGN KEY (professional_id) REFERENCES professional (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EBE3FB3114 FOREIGN KEY (requesting_company_id) REFERENCES requesting_company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE care_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE insurance_plan_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE patient_id_seq CASCADE');
        $this->addSql('ALTER TABLE patient DROP CONSTRAINT FK_1ADAD7EB217BBB47');
        $this->addSql('ALTER TABLE patient DROP CONSTRAINT FK_1ADAD7EB208F64F1');
        $this->addSql('ALTER TABLE patient DROP CONSTRAINT FK_1ADAD7EBC17AD9A9');
        $this->addSql('ALTER TABLE patient DROP CONSTRAINT FK_1ADAD7EB24890B2B');
        $this->addSql('ALTER TABLE patient DROP CONSTRAINT FK_1ADAD7EB5B8273E');
        $this->addSql('ALTER TABLE patient DROP CONSTRAINT FK_1ADAD7EB852D12A6');
        $this->addSql('ALTER TABLE patient DROP CONSTRAINT FK_1ADAD7EB56A273CC');
        $this->addSql('ALTER TABLE patient DROP CONSTRAINT FK_1ADAD7EB114B6FAC');
        $this->addSql('ALTER TABLE patient DROP CONSTRAINT FK_1ADAD7EBDB77003');
        $this->addSql('ALTER TABLE patient DROP CONSTRAINT FK_1ADAD7EBE3FB3114');
        $this->addSql('DROP TABLE care_type');
        $this->addSql('DROP TABLE insurance_plan');
        $this->addSql('DROP TABLE patient');
    }
}
