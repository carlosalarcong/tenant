<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260218185255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE account_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE admission_status_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE bed_assignment_status_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE bed_patient_assignment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE emergency_admission_complement_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE emergency_admission_complement_detail_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE triage_category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE warehouse_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE account_type (id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE admission_status (id INT NOT NULL, name VARCHAR(60) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE bed_assignment_status (id INT NOT NULL, name VARCHAR(45) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE bed_patient_assignment (id INT NOT NULL, patient_id INT NOT NULL, bed_id INT NOT NULL, bed_assignment_status_id INT DEFAULT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B78051D86B899279 ON bed_patient_assignment (patient_id)');
        $this->addSql('CREATE INDEX IDX_B78051D888688BB9 ON bed_patient_assignment (bed_id)');
        $this->addSql('CREATE INDEX IDX_B78051D87AA131EF ON bed_patient_assignment (bed_assignment_status_id)');
        $this->addSql('CREATE TABLE emergency_admission_complement (id INT NOT NULL, admission_record_id INT NOT NULL, emergency_consultation_type_id INT DEFAULT NULL, company_agreement_id INT DEFAULT NULL, triage_category_id INT DEFAULT NULL, notice_police BOOLEAN DEFAULT false NOT NULL, accompanied_by VARCHAR(255) DEFAULT NULL, arrived_by VARCHAR(255) DEFAULT NULL, accident_location VARCHAR(255) DEFAULT NULL, accident_type VARCHAR(255) DEFAULT NULL, other_consultation_type VARCHAR(255) DEFAULT NULL, triage_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, code_blue BOOLEAN DEFAULT false NOT NULL, discharge_notes TEXT DEFAULT NULL, discharge_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, discharge_user_role VARCHAR(150) DEFAULT NULL, discharge_update_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, discharge_update_user_role VARCHAR(150) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_61F222FBBCA2BFC5 ON emergency_admission_complement (admission_record_id)');
        $this->addSql('CREATE INDEX IDX_61F222FBFB506460 ON emergency_admission_complement (emergency_consultation_type_id)');
        $this->addSql('CREATE INDEX IDX_61F222FBDA44C1E5 ON emergency_admission_complement (company_agreement_id)');
        $this->addSql('CREATE INDEX IDX_61F222FBC74E6A32 ON emergency_admission_complement (triage_category_id)');
        $this->addSql('CREATE TABLE emergency_admission_complement_detail (id INT NOT NULL, emergency_admission_complement_id INT NOT NULL, article_id INT DEFAULT NULL, article_package_id INT DEFAULT NULL, service_package_id INT DEFAULT NULL, status INT DEFAULT NULL, entry_date DATE DEFAULT NULL, is_package BOOLEAN DEFAULT false NOT NULL, article_quantity INT DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, comment_date DATE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4A46C1552BD18C70 ON emergency_admission_complement_detail (emergency_admission_complement_id)');
        $this->addSql('CREATE INDEX IDX_4A46C1557294869C ON emergency_admission_complement_detail (article_id)');
        $this->addSql('CREATE INDEX IDX_4A46C155C8957A7E ON emergency_admission_complement_detail (article_package_id)');
        $this->addSql('CREATE INDEX IDX_4A46C155621D924B ON emergency_admission_complement_detail (service_package_id)');
        $this->addSql('CREATE TABLE triage_category (id INT NOT NULL, name VARCHAR(45) NOT NULL, color VARCHAR(45) NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE warehouse_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE bed_patient_assignment ADD CONSTRAINT FK_B78051D86B899279 FOREIGN KEY (patient_id) REFERENCES patient (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bed_patient_assignment ADD CONSTRAINT FK_B78051D888688BB9 FOREIGN KEY (bed_id) REFERENCES bed (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bed_patient_assignment ADD CONSTRAINT FK_B78051D87AA131EF FOREIGN KEY (bed_assignment_status_id) REFERENCES bed_assignment_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE emergency_admission_complement ADD CONSTRAINT FK_61F222FBBCA2BFC5 FOREIGN KEY (admission_record_id) REFERENCES admission_record (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE emergency_admission_complement ADD CONSTRAINT FK_61F222FBFB506460 FOREIGN KEY (emergency_consultation_type_id) REFERENCES emergency_consultation_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE emergency_admission_complement ADD CONSTRAINT FK_61F222FBDA44C1E5 FOREIGN KEY (company_agreement_id) REFERENCES company_agreement (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE emergency_admission_complement ADD CONSTRAINT FK_61F222FBC74E6A32 FOREIGN KEY (triage_category_id) REFERENCES triage_category (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE emergency_admission_complement_detail ADD CONSTRAINT FK_4A46C1552BD18C70 FOREIGN KEY (emergency_admission_complement_id) REFERENCES emergency_admission_complement (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE emergency_admission_complement_detail ADD CONSTRAINT FK_4A46C1557294869C FOREIGN KEY (article_id) REFERENCES article (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE emergency_admission_complement_detail ADD CONSTRAINT FK_4A46C155C8957A7E FOREIGN KEY (article_package_id) REFERENCES article_package (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE emergency_admission_complement_detail ADD CONSTRAINT FK_4A46C155621D924B FOREIGN KEY (service_package_id) REFERENCES service_package (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE admission_record ADD admission_status_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD branch_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD professional_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD cancellation_reason_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD specialty_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD insurance_plan_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD origin_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD account_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD bed_patient_assignment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD admission_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD pre_admission_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD cancellation_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD number INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD is_surgical_admission BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE admission_record ADD has_medical_order BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE admission_record ADD notes VARCHAR(240) DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD emergency_notice VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD emergency_phone VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD medical_order_file VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD other_origin VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD cancellation_notes VARCHAR(2000) DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD with_fees BOOLEAN DEFAULT true NOT NULL');
        $this->addSql('ALTER TABLE admission_record ADD dau INT DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record ADD referring_doctor VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE admission_record DROP status');
        $this->addSql('ALTER TABLE admission_record ADD CONSTRAINT FK_A1A74BDB9AA8CA74 FOREIGN KEY (admission_status_id) REFERENCES admission_status (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE admission_record ADD CONSTRAINT FK_A1A74BDBDCD6CC49 FOREIGN KEY (branch_id) REFERENCES branch (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE admission_record ADD CONSTRAINT FK_A1A74BDBDB77003 FOREIGN KEY (professional_id) REFERENCES professional (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE admission_record ADD CONSTRAINT FK_A1A74BDB8453C906 FOREIGN KEY (cancellation_reason_id) REFERENCES cancellation_reason (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE admission_record ADD CONSTRAINT FK_A1A74BDB9A353316 FOREIGN KEY (specialty_id) REFERENCES specialty (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE admission_record ADD CONSTRAINT FK_A1A74BDB5B8273E FOREIGN KEY (insurance_plan_id) REFERENCES insurance_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE admission_record ADD CONSTRAINT FK_A1A74BDB56A273CC FOREIGN KEY (origin_id) REFERENCES maintainer_origin (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE admission_record ADD CONSTRAINT FK_A1A74BDBC6798DB FOREIGN KEY (account_type_id) REFERENCES account_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE admission_record ADD CONSTRAINT FK_A1A74BDBABC34E3A FOREIGN KEY (bed_patient_assignment_id) REFERENCES bed_patient_assignment (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A1A74BDB9AA8CA74 ON admission_record (admission_status_id)');
        $this->addSql('CREATE INDEX IDX_A1A74BDBDCD6CC49 ON admission_record (branch_id)');
        $this->addSql('CREATE INDEX IDX_A1A74BDBDB77003 ON admission_record (professional_id)');
        $this->addSql('CREATE INDEX IDX_A1A74BDB8453C906 ON admission_record (cancellation_reason_id)');
        $this->addSql('CREATE INDEX IDX_A1A74BDB9A353316 ON admission_record (specialty_id)');
        $this->addSql('CREATE INDEX IDX_A1A74BDB5B8273E ON admission_record (insurance_plan_id)');
        $this->addSql('CREATE INDEX IDX_A1A74BDB56A273CC ON admission_record (origin_id)');
        $this->addSql('CREATE INDEX IDX_A1A74BDBC6798DB ON admission_record (account_type_id)');
        $this->addSql('CREATE INDEX IDX_A1A74BDBABC34E3A ON admission_record (bed_patient_assignment_id)');
        $this->addSql('ALTER TABLE company_agreement ADD code INT DEFAULT NULL');
        $this->addSql('ALTER TABLE diagnosis ADD parent_diagnosis_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE diagnosis ADD code VARCHAR(6) DEFAULT NULL');
        $this->addSql('ALTER TABLE diagnosis ADD CONSTRAINT FK_7ED10F3D6CD0102E FOREIGN KEY (parent_diagnosis_id) REFERENCES diagnosis (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7ED10F3D6CD0102E ON diagnosis (parent_diagnosis_id)');
        $this->addSql('ALTER TABLE insurance_plan ADD cancellation_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE insurance_plan ADD parent_plan_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE insurance_plan ADD branch_payer_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE insurance_plan ADD is_disabled BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('ALTER TABLE insurance_plan ADD is_telemedicine BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE insurance_plan ADD cancellation_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE insurance_plan ADD CONSTRAINT FK_37B14D4D28DB158 FOREIGN KEY (cancellation_user_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE insurance_plan ADD CONSTRAINT FK_37B14D49AD176DD FOREIGN KEY (parent_plan_id) REFERENCES insurance_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE insurance_plan ADD CONSTRAINT FK_37B14D426D9006A FOREIGN KEY (branch_payer_id) REFERENCES branch_payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_37B14D4D28DB158 ON insurance_plan (cancellation_user_id)');
        $this->addSql('CREATE INDEX IDX_37B14D49AD176DD ON insurance_plan (parent_plan_id)');
        $this->addSql('CREATE INDEX IDX_37B14D426D9006A ON insurance_plan (branch_payer_id)');
        $this->addSql('ALTER TABLE maintainer_origin ADD branch_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE maintainer_origin ADD CONSTRAINT FK_23B5077CDCD6CC49 FOREIGN KEY (branch_id) REFERENCES branch (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_23B5077CDCD6CC49 ON maintainer_origin (branch_id)');
        $this->addSql('ALTER TABLE warehouse ADD warehouse_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE warehouse ADD CONSTRAINT FK_ECB38BFCE2AB99E6 FOREIGN KEY (warehouse_type_id) REFERENCES warehouse_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_ECB38BFCE2AB99E6 ON warehouse (warehouse_type_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE admission_record DROP CONSTRAINT FK_A1A74BDBC6798DB');
        $this->addSql('ALTER TABLE admission_record DROP CONSTRAINT FK_A1A74BDB9AA8CA74');
        $this->addSql('ALTER TABLE admission_record DROP CONSTRAINT FK_A1A74BDBABC34E3A');
        $this->addSql('ALTER TABLE warehouse DROP CONSTRAINT FK_ECB38BFCE2AB99E6');
        $this->addSql('DROP SEQUENCE account_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE admission_status_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE bed_assignment_status_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE bed_patient_assignment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE emergency_admission_complement_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE emergency_admission_complement_detail_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE triage_category_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE warehouse_type_id_seq CASCADE');
        $this->addSql('ALTER TABLE bed_patient_assignment DROP CONSTRAINT FK_B78051D86B899279');
        $this->addSql('ALTER TABLE bed_patient_assignment DROP CONSTRAINT FK_B78051D888688BB9');
        $this->addSql('ALTER TABLE bed_patient_assignment DROP CONSTRAINT FK_B78051D87AA131EF');
        $this->addSql('ALTER TABLE emergency_admission_complement DROP CONSTRAINT FK_61F222FBBCA2BFC5');
        $this->addSql('ALTER TABLE emergency_admission_complement DROP CONSTRAINT FK_61F222FBFB506460');
        $this->addSql('ALTER TABLE emergency_admission_complement DROP CONSTRAINT FK_61F222FBDA44C1E5');
        $this->addSql('ALTER TABLE emergency_admission_complement DROP CONSTRAINT FK_61F222FBC74E6A32');
        $this->addSql('ALTER TABLE emergency_admission_complement_detail DROP CONSTRAINT FK_4A46C1552BD18C70');
        $this->addSql('ALTER TABLE emergency_admission_complement_detail DROP CONSTRAINT FK_4A46C1557294869C');
        $this->addSql('ALTER TABLE emergency_admission_complement_detail DROP CONSTRAINT FK_4A46C155C8957A7E');
        $this->addSql('ALTER TABLE emergency_admission_complement_detail DROP CONSTRAINT FK_4A46C155621D924B');
        $this->addSql('DROP TABLE account_type');
        $this->addSql('DROP TABLE admission_status');
        $this->addSql('DROP TABLE bed_assignment_status');
        $this->addSql('DROP TABLE bed_patient_assignment');
        $this->addSql('DROP TABLE emergency_admission_complement');
        $this->addSql('DROP TABLE emergency_admission_complement_detail');
        $this->addSql('DROP TABLE triage_category');
        $this->addSql('DROP TABLE warehouse_type');
        $this->addSql('ALTER TABLE diagnosis DROP CONSTRAINT FK_7ED10F3D6CD0102E');
        $this->addSql('DROP INDEX IDX_7ED10F3D6CD0102E');
        $this->addSql('ALTER TABLE diagnosis DROP parent_diagnosis_id');
        $this->addSql('ALTER TABLE diagnosis DROP code');
        $this->addSql('ALTER TABLE insurance_plan DROP CONSTRAINT FK_37B14D4D28DB158');
        $this->addSql('ALTER TABLE insurance_plan DROP CONSTRAINT FK_37B14D49AD176DD');
        $this->addSql('ALTER TABLE insurance_plan DROP CONSTRAINT FK_37B14D426D9006A');
        $this->addSql('DROP INDEX IDX_37B14D4D28DB158');
        $this->addSql('DROP INDEX IDX_37B14D49AD176DD');
        $this->addSql('DROP INDEX IDX_37B14D426D9006A');
        $this->addSql('ALTER TABLE insurance_plan DROP cancellation_user_id');
        $this->addSql('ALTER TABLE insurance_plan DROP parent_plan_id');
        $this->addSql('ALTER TABLE insurance_plan DROP branch_payer_id');
        $this->addSql('ALTER TABLE insurance_plan DROP is_disabled');
        $this->addSql('ALTER TABLE insurance_plan DROP is_telemedicine');
        $this->addSql('ALTER TABLE insurance_plan DROP cancellation_date');
        $this->addSql('ALTER TABLE maintainer_origin DROP CONSTRAINT FK_23B5077CDCD6CC49');
        $this->addSql('DROP INDEX IDX_23B5077CDCD6CC49');
        $this->addSql('ALTER TABLE maintainer_origin DROP branch_id');
        $this->addSql('ALTER TABLE company_agreement DROP code');
        $this->addSql('ALTER TABLE admission_record DROP CONSTRAINT FK_A1A74BDBDCD6CC49');
        $this->addSql('ALTER TABLE admission_record DROP CONSTRAINT FK_A1A74BDBDB77003');
        $this->addSql('ALTER TABLE admission_record DROP CONSTRAINT FK_A1A74BDB8453C906');
        $this->addSql('ALTER TABLE admission_record DROP CONSTRAINT FK_A1A74BDB9A353316');
        $this->addSql('ALTER TABLE admission_record DROP CONSTRAINT FK_A1A74BDB5B8273E');
        $this->addSql('ALTER TABLE admission_record DROP CONSTRAINT FK_A1A74BDB56A273CC');
        $this->addSql('DROP INDEX IDX_A1A74BDB9AA8CA74');
        $this->addSql('DROP INDEX IDX_A1A74BDBDCD6CC49');
        $this->addSql('DROP INDEX IDX_A1A74BDBDB77003');
        $this->addSql('DROP INDEX IDX_A1A74BDB8453C906');
        $this->addSql('DROP INDEX IDX_A1A74BDB9A353316');
        $this->addSql('DROP INDEX IDX_A1A74BDB5B8273E');
        $this->addSql('DROP INDEX IDX_A1A74BDB56A273CC');
        $this->addSql('DROP INDEX IDX_A1A74BDBC6798DB');
        $this->addSql('DROP INDEX IDX_A1A74BDBABC34E3A');
        $this->addSql('ALTER TABLE admission_record ADD status VARCHAR(30) NOT NULL');
        $this->addSql('ALTER TABLE admission_record DROP admission_status_id');
        $this->addSql('ALTER TABLE admission_record DROP branch_id');
        $this->addSql('ALTER TABLE admission_record DROP professional_id');
        $this->addSql('ALTER TABLE admission_record DROP cancellation_reason_id');
        $this->addSql('ALTER TABLE admission_record DROP specialty_id');
        $this->addSql('ALTER TABLE admission_record DROP insurance_plan_id');
        $this->addSql('ALTER TABLE admission_record DROP origin_id');
        $this->addSql('ALTER TABLE admission_record DROP account_type_id');
        $this->addSql('ALTER TABLE admission_record DROP bed_patient_assignment_id');
        $this->addSql('ALTER TABLE admission_record DROP admission_date');
        $this->addSql('ALTER TABLE admission_record DROP pre_admission_date');
        $this->addSql('ALTER TABLE admission_record DROP cancellation_date');
        $this->addSql('ALTER TABLE admission_record DROP number');
        $this->addSql('ALTER TABLE admission_record DROP is_surgical_admission');
        $this->addSql('ALTER TABLE admission_record DROP has_medical_order');
        $this->addSql('ALTER TABLE admission_record DROP notes');
        $this->addSql('ALTER TABLE admission_record DROP emergency_notice');
        $this->addSql('ALTER TABLE admission_record DROP emergency_phone');
        $this->addSql('ALTER TABLE admission_record DROP medical_order_file');
        $this->addSql('ALTER TABLE admission_record DROP other_origin');
        $this->addSql('ALTER TABLE admission_record DROP cancellation_notes');
        $this->addSql('ALTER TABLE admission_record DROP with_fees');
        $this->addSql('ALTER TABLE admission_record DROP dau');
        $this->addSql('ALTER TABLE admission_record DROP referring_doctor');
        $this->addSql('DROP INDEX IDX_ECB38BFCE2AB99E6');
        $this->addSql('ALTER TABLE warehouse DROP warehouse_type_id');
    }
}
