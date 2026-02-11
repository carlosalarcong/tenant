<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203132251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE anesthesia_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE blood_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE surgery_block_reason_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE surgery_cancellation_reason_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE surgery_patient_status_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE surgery_patient_status_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE surgery_suspension_cause_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE surgical_block_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE surgical_stage_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE surgical_stage_item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE surgical_team_role_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE treatment_regimen_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE wound_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE anesthesia_type (id INT NOT NULL, name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE blood_type (id INT NOT NULL, name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE surgery_block_reason (id INT NOT NULL, name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE surgery_cancellation_reason (id INT NOT NULL, name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE surgery_patient_status (id INT NOT NULL, name VARCHAR(150) NOT NULL, color VARCHAR(20) DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE surgery_patient_status_config (id INT NOT NULL, surgery_patient_status_id INT DEFAULT NULL, color VARCHAR(20) DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_214A0EE527FE5028 ON surgery_patient_status_config (surgery_patient_status_id)');
        $this->addSql('CREATE TABLE surgery_suspension_cause (id INT NOT NULL, name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE surgical_block (id INT NOT NULL, medical_service_id INT DEFAULT NULL, name VARCHAR(150) NOT NULL, is_active BOOLEAN NOT NULL, id_estado VARCHAR(20) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F15A726CC61D802A ON surgical_block (medical_service_id)');
        $this->addSql('CREATE TABLE surgical_stage (id INT NOT NULL, branch_id INT DEFAULT NULL, sort_order INT NOT NULL, abbreviation VARCHAR(255) DEFAULT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, is_mandatory BOOLEAN NOT NULL, requires_login BOOLEAN NOT NULL, is_sequential BOOLEAN NOT NULL, template_stage_id INT DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_by INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B03D7627DCD6CC49 ON surgical_stage (branch_id)');
        $this->addSql('CREATE TABLE surgical_stage_item (id INT NOT NULL, parent_id INT DEFAULT NULL, surgical_stage_id INT NOT NULL, sort_order INT NOT NULL, name VARCHAR(255) NOT NULL, alternatives TEXT DEFAULT NULL, has_response BOOLEAN NOT NULL, is_mandatory BOOLEAN NOT NULL, item_type_id INT DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_by INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B7FD812A727ACA70 ON surgical_stage_item (parent_id)');
        $this->addSql('CREATE INDEX IDX_B7FD812A7A4E0784 ON surgical_stage_item (surgical_stage_id)');
        $this->addSql('CREATE TABLE surgical_team_role (id INT NOT NULL, surgery_item_id INT DEFAULT NULL, sort_order INT NOT NULL, name VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, id_estado VARCHAR(20) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5032D00A4665449 ON surgical_team_role (surgery_item_id)');
        $this->addSql('CREATE TABLE treatment_regimen (id INT NOT NULL, branch_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, id_estado VARCHAR(20) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_59D7DA43DCD6CC49 ON treatment_regimen (branch_id)');
        $this->addSql('CREATE TABLE wound_type (id INT NOT NULL, name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE surgery_patient_status_config ADD CONSTRAINT FK_214A0EE527FE5028 FOREIGN KEY (surgery_patient_status_id) REFERENCES surgery_patient_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgical_block ADD CONSTRAINT FK_F15A726CC61D802A FOREIGN KEY (medical_service_id) REFERENCES medical_service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgical_stage ADD CONSTRAINT FK_B03D7627DCD6CC49 FOREIGN KEY (branch_id) REFERENCES branch (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgical_stage_item ADD CONSTRAINT FK_B7FD812A727ACA70 FOREIGN KEY (parent_id) REFERENCES surgical_stage_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgical_stage_item ADD CONSTRAINT FK_B7FD812A7A4E0784 FOREIGN KEY (surgical_stage_id) REFERENCES surgical_stage (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgical_team_role ADD CONSTRAINT FK_5032D00A4665449 FOREIGN KEY (surgery_item_id) REFERENCES surgery_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE treatment_regimen ADD CONSTRAINT FK_59D7DA43DCD6CC49 FOREIGN KEY (branch_id) REFERENCES branch (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE anesthesia_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE blood_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE surgery_block_reason_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE surgery_cancellation_reason_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE surgery_patient_status_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE surgery_patient_status_config_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE surgery_suspension_cause_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE surgical_block_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE surgical_stage_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE surgical_stage_item_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE surgical_team_role_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE treatment_regimen_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE wound_type_id_seq CASCADE');
        $this->addSql('ALTER TABLE surgery_patient_status_config DROP CONSTRAINT FK_214A0EE527FE5028');
        $this->addSql('ALTER TABLE surgical_block DROP CONSTRAINT FK_F15A726CC61D802A');
        $this->addSql('ALTER TABLE surgical_stage DROP CONSTRAINT FK_B03D7627DCD6CC49');
        $this->addSql('ALTER TABLE surgical_stage_item DROP CONSTRAINT FK_B7FD812A727ACA70');
        $this->addSql('ALTER TABLE surgical_stage_item DROP CONSTRAINT FK_B7FD812A7A4E0784');
        $this->addSql('ALTER TABLE surgical_team_role DROP CONSTRAINT FK_5032D00A4665449');
        $this->addSql('ALTER TABLE treatment_regimen DROP CONSTRAINT FK_59D7DA43DCD6CC49');
        $this->addSql('DROP TABLE anesthesia_type');
        $this->addSql('DROP TABLE blood_type');
        $this->addSql('DROP TABLE surgery_block_reason');
        $this->addSql('DROP TABLE surgery_cancellation_reason');
        $this->addSql('DROP TABLE surgery_patient_status');
        $this->addSql('DROP TABLE surgery_patient_status_config');
        $this->addSql('DROP TABLE surgery_suspension_cause');
        $this->addSql('DROP TABLE surgical_block');
        $this->addSql('DROP TABLE surgical_stage');
        $this->addSql('DROP TABLE surgical_stage_item');
        $this->addSql('DROP TABLE surgical_team_role');
        $this->addSql('DROP TABLE treatment_regimen');
        $this->addSql('DROP TABLE wound_type');
    }
}
