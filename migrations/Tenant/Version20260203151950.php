<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203151950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE care_category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE care_closure_destination_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE care_intervention_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE clinical_action_answer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE clinical_action_category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE clinical_action_question_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE dosage_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE eating_disorder_history_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE intoxication_state_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE medical_device_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE nutritional_diagnosis_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE nutritionist_bmi_index_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE nutritionist_index_classification_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE nutritionist_te_index_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE physical_exam_base_field_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE physical_exam_field_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE physical_exam_grouping_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE prescription_dispensation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE prescription_dosage_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE prescription_format_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE prescription_frequency_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE prescription_route_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE prescription_rule_detail_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE prescription_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE care_category (id INT NOT NULL, name VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE care_closure_destination (id INT NOT NULL, name VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE care_intervention (id INT NOT NULL, care_category_id INT DEFAULT NULL, description VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5C2A5BD828041BFD ON care_intervention (care_category_id)');
        $this->addSql('CREATE TABLE clinical_action_answer (id INT NOT NULL, clinical_action_question_id INT NOT NULL, sort_order INT NOT NULL, pre_text VARCHAR(255) DEFAULT NULL, post_text VARCHAR(255) DEFAULT NULL, placeholder VARCHAR(100) DEFAULT NULL, default_value VARCHAR(100) DEFAULT NULL, entity_response VARCHAR(100) DEFAULT NULL, is_checked BOOLEAN NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_66B8378243192507 ON clinical_action_answer (clinical_action_question_id)');
        $this->addSql('CREATE TABLE clinical_action_category (id INT NOT NULL, name VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE clinical_action_question (id INT NOT NULL, clinical_action_category_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, sort_order INT NOT NULL, range_min INT NOT NULL, range_max INT DEFAULT NULL, age_min INT DEFAULT NULL, age_max INT DEFAULT NULL, help_text VARCHAR(100) DEFAULT NULL, is_multiple BOOLEAN NOT NULL, is_extended BOOLEAN NOT NULL, is_required BOOLEAN NOT NULL, field_type VARCHAR(50) DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_7905D2844F784E5A ON clinical_action_question (clinical_action_category_id)');
        $this->addSql('CREATE TABLE dosage_type (id INT NOT NULL, name VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE eating_disorder_history (id INT NOT NULL, name VARCHAR(150) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE intoxication_state (id INT NOT NULL, name VARCHAR(45) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE medical_device (id INT NOT NULL, name VARCHAR(50) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE nutritional_diagnosis (id INT NOT NULL, name VARCHAR(150) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE nutritionist_bmi_index (id INT NOT NULL, name VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE nutritionist_index_classification (id INT NOT NULL, name VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE nutritionist_te_index (id INT NOT NULL, name VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE physical_exam_base_field (id INT NOT NULL, name VARCHAR(100) NOT NULL, description VARCHAR(255) DEFAULT NULL, sort_order INT NOT NULL, field_type VARCHAR(50) DEFAULT NULL, is_required BOOLEAN NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE physical_exam_field (id INT NOT NULL, grouping1_id INT DEFAULT NULL, grouping2_id INT DEFAULT NULL, name VARCHAR(45) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, sort_order INT NOT NULL, range_min INT DEFAULT NULL, range_max INT DEFAULT NULL, age_min INT DEFAULT NULL, age_max INT DEFAULT NULL, unit VARCHAR(45) DEFAULT NULL, is_weight BOOLEAN NOT NULL, is_height BOOLEAN NOT NULL, is_bmi BOOLEAN NOT NULL, is_temperature BOOLEAN NOT NULL, is_systolic BOOLEAN NOT NULL, is_diastolic BOOLEAN NOT NULL, is_saturation BOOLEAN NOT NULL, is_respiratory_rate BOOLEAN NOT NULL, is_pce BOOLEAN NOT NULL, field_type VARCHAR(50) DEFAULT NULL, exam_type VARCHAR(50) DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9AE19B10618CAF8A ON physical_exam_field (grouping1_id)');
        $this->addSql('CREATE INDEX IDX_9AE19B1073390064 ON physical_exam_field (grouping2_id)');
        $this->addSql('CREATE TABLE physical_exam_grouping (id INT NOT NULL, name VARCHAR(100) NOT NULL, sort_order INT NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE prescription_dispensation (id INT NOT NULL, name VARCHAR(100) NOT NULL, sort_order INT NOT NULL, quantity INT NOT NULL, time_unit VARCHAR(50) DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE prescription_dosage (id INT NOT NULL, name VARCHAR(50) NOT NULL, quantity NUMERIC(10, 2) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE prescription_format (id INT NOT NULL, name VARCHAR(100) NOT NULL, sort_order INT NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE prescription_frequency (id INT NOT NULL, name VARCHAR(100) NOT NULL, quantity INT NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE prescription_route (id INT NOT NULL, name VARCHAR(100) NOT NULL, sort_order INT NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE prescription_rule_detail (id INT NOT NULL, intervals VARCHAR(255) NOT NULL, daily_quantity INT NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE prescription_type (id INT NOT NULL, name VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE care_intervention ADD CONSTRAINT FK_5C2A5BD828041BFD FOREIGN KEY (care_category_id) REFERENCES care_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE clinical_action_answer ADD CONSTRAINT FK_66B8378243192507 FOREIGN KEY (clinical_action_question_id) REFERENCES clinical_action_question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE clinical_action_question ADD CONSTRAINT FK_7905D2844F784E5A FOREIGN KEY (clinical_action_category_id) REFERENCES clinical_action_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE physical_exam_field ADD CONSTRAINT FK_9AE19B10618CAF8A FOREIGN KEY (grouping1_id) REFERENCES physical_exam_grouping (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE physical_exam_field ADD CONSTRAINT FK_9AE19B1073390064 FOREIGN KEY (grouping2_id) REFERENCES physical_exam_grouping (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE anesthesia_type ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE blood_type ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE surgery_block_reason ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE surgery_cancellation_reason ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE surgery_patient_status ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE surgery_patient_status_config ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE surgery_suspension_cause ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE surgical_block ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE surgical_stage ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE surgical_stage_item ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE surgical_team_role ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE treatment_regimen ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE wound_type ALTER id DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE care_category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE care_closure_destination_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE care_intervention_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE clinical_action_answer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE clinical_action_category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE clinical_action_question_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE dosage_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE eating_disorder_history_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE intoxication_state_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE medical_device_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE nutritional_diagnosis_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE nutritionist_bmi_index_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE nutritionist_index_classification_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE nutritionist_te_index_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE physical_exam_base_field_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE physical_exam_field_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE physical_exam_grouping_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE prescription_dispensation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE prescription_dosage_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE prescription_format_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE prescription_frequency_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE prescription_route_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE prescription_rule_detail_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE prescription_type_id_seq CASCADE');
        $this->addSql('ALTER TABLE care_intervention DROP CONSTRAINT FK_5C2A5BD828041BFD');
        $this->addSql('ALTER TABLE clinical_action_answer DROP CONSTRAINT FK_66B8378243192507');
        $this->addSql('ALTER TABLE clinical_action_question DROP CONSTRAINT FK_7905D2844F784E5A');
        $this->addSql('ALTER TABLE physical_exam_field DROP CONSTRAINT FK_9AE19B10618CAF8A');
        $this->addSql('ALTER TABLE physical_exam_field DROP CONSTRAINT FK_9AE19B1073390064');
        $this->addSql('DROP TABLE care_category');
        $this->addSql('DROP TABLE care_closure_destination');
        $this->addSql('DROP TABLE care_intervention');
        $this->addSql('DROP TABLE clinical_action_answer');
        $this->addSql('DROP TABLE clinical_action_category');
        $this->addSql('DROP TABLE clinical_action_question');
        $this->addSql('DROP TABLE dosage_type');
        $this->addSql('DROP TABLE eating_disorder_history');
        $this->addSql('DROP TABLE intoxication_state');
        $this->addSql('DROP TABLE medical_device');
        $this->addSql('DROP TABLE nutritional_diagnosis');
        $this->addSql('DROP TABLE nutritionist_bmi_index');
        $this->addSql('DROP TABLE nutritionist_index_classification');
        $this->addSql('DROP TABLE nutritionist_te_index');
        $this->addSql('DROP TABLE physical_exam_base_field');
        $this->addSql('DROP TABLE physical_exam_field');
        $this->addSql('DROP TABLE physical_exam_grouping');
        $this->addSql('DROP TABLE prescription_dispensation');
        $this->addSql('DROP TABLE prescription_dosage');
        $this->addSql('DROP TABLE prescription_format');
        $this->addSql('DROP TABLE prescription_frequency');
        $this->addSql('DROP TABLE prescription_route');
        $this->addSql('DROP TABLE prescription_rule_detail');
        $this->addSql('DROP TABLE prescription_type');
        $this->addSql('CREATE SEQUENCE surgery_patient_status_config_id_seq');
        $this->addSql('SELECT setval(\'surgery_patient_status_config_id_seq\', (SELECT MAX(id) FROM surgery_patient_status_config))');
        $this->addSql('ALTER TABLE surgery_patient_status_config ALTER id SET DEFAULT nextval(\'surgery_patient_status_config_id_seq\')');
        $this->addSql('CREATE SEQUENCE surgery_patient_status_id_seq');
        $this->addSql('SELECT setval(\'surgery_patient_status_id_seq\', (SELECT MAX(id) FROM surgery_patient_status))');
        $this->addSql('ALTER TABLE surgery_patient_status ALTER id SET DEFAULT nextval(\'surgery_patient_status_id_seq\')');
        $this->addSql('CREATE SEQUENCE wound_type_id_seq');
        $this->addSql('SELECT setval(\'wound_type_id_seq\', (SELECT MAX(id) FROM wound_type))');
        $this->addSql('ALTER TABLE wound_type ALTER id SET DEFAULT nextval(\'wound_type_id_seq\')');
        $this->addSql('CREATE SEQUENCE surgery_suspension_cause_id_seq');
        $this->addSql('SELECT setval(\'surgery_suspension_cause_id_seq\', (SELECT MAX(id) FROM surgery_suspension_cause))');
        $this->addSql('ALTER TABLE surgery_suspension_cause ALTER id SET DEFAULT nextval(\'surgery_suspension_cause_id_seq\')');
        $this->addSql('CREATE SEQUENCE surgery_cancellation_reason_id_seq');
        $this->addSql('SELECT setval(\'surgery_cancellation_reason_id_seq\', (SELECT MAX(id) FROM surgery_cancellation_reason))');
        $this->addSql('ALTER TABLE surgery_cancellation_reason ALTER id SET DEFAULT nextval(\'surgery_cancellation_reason_id_seq\')');
        $this->addSql('CREATE SEQUENCE surgery_block_reason_id_seq');
        $this->addSql('SELECT setval(\'surgery_block_reason_id_seq\', (SELECT MAX(id) FROM surgery_block_reason))');
        $this->addSql('ALTER TABLE surgery_block_reason ALTER id SET DEFAULT nextval(\'surgery_block_reason_id_seq\')');
        $this->addSql('CREATE SEQUENCE blood_type_id_seq');
        $this->addSql('SELECT setval(\'blood_type_id_seq\', (SELECT MAX(id) FROM blood_type))');
        $this->addSql('ALTER TABLE blood_type ALTER id SET DEFAULT nextval(\'blood_type_id_seq\')');
        $this->addSql('CREATE SEQUENCE anesthesia_type_id_seq');
        $this->addSql('SELECT setval(\'anesthesia_type_id_seq\', (SELECT MAX(id) FROM anesthesia_type))');
        $this->addSql('ALTER TABLE anesthesia_type ALTER id SET DEFAULT nextval(\'anesthesia_type_id_seq\')');
        $this->addSql('CREATE SEQUENCE treatment_regimen_id_seq');
        $this->addSql('SELECT setval(\'treatment_regimen_id_seq\', (SELECT MAX(id) FROM treatment_regimen))');
        $this->addSql('ALTER TABLE treatment_regimen ALTER id SET DEFAULT nextval(\'treatment_regimen_id_seq\')');
        $this->addSql('CREATE SEQUENCE surgical_team_role_id_seq');
        $this->addSql('SELECT setval(\'surgical_team_role_id_seq\', (SELECT MAX(id) FROM surgical_team_role))');
        $this->addSql('ALTER TABLE surgical_team_role ALTER id SET DEFAULT nextval(\'surgical_team_role_id_seq\')');
        $this->addSql('CREATE SEQUENCE surgical_stage_item_id_seq');
        $this->addSql('SELECT setval(\'surgical_stage_item_id_seq\', (SELECT MAX(id) FROM surgical_stage_item))');
        $this->addSql('ALTER TABLE surgical_stage_item ALTER id SET DEFAULT nextval(\'surgical_stage_item_id_seq\')');
        $this->addSql('CREATE SEQUENCE surgical_stage_id_seq');
        $this->addSql('SELECT setval(\'surgical_stage_id_seq\', (SELECT MAX(id) FROM surgical_stage))');
        $this->addSql('ALTER TABLE surgical_stage ALTER id SET DEFAULT nextval(\'surgical_stage_id_seq\')');
        $this->addSql('CREATE SEQUENCE surgical_block_id_seq');
        $this->addSql('SELECT setval(\'surgical_block_id_seq\', (SELECT MAX(id) FROM surgical_block))');
        $this->addSql('ALTER TABLE surgical_block ALTER id SET DEFAULT nextval(\'surgical_block_id_seq\')');
    }
}
