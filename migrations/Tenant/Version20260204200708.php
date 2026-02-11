<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260204200708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE exam_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE exam_service_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE exam_service_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE physical_exam_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE physical_exam_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE exam_group (id INT NOT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, id_estado INT DEFAULT 1 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE exam_service (id INT NOT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, id_estado INT DEFAULT 1 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE exam_service_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, id_estado INT DEFAULT 1 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE physical_exam_group (id INT NOT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, id_estado INT DEFAULT 1 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE physical_exam_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, id_estado INT DEFAULT 1 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE physical_exam_field DROP CONSTRAINT fk_9ae19b10618caf8a');
        $this->addSql('ALTER TABLE physical_exam_field DROP CONSTRAINT fk_9ae19b1073390064');
        $this->addSql('DROP INDEX idx_9ae19b10618caf8a');
        $this->addSql('DROP INDEX idx_9ae19b1073390064');
        $this->addSql('ALTER TABLE physical_exam_field DROP grouping1_id');
        $this->addSql('ALTER TABLE physical_exam_field DROP grouping2_id');
        $this->addSql('ALTER TABLE physical_exam_field DROP sort_order');
        $this->addSql('ALTER TABLE physical_exam_field DROP range_min');
        $this->addSql('ALTER TABLE physical_exam_field DROP range_max');
        $this->addSql('ALTER TABLE physical_exam_field DROP age_min');
        $this->addSql('ALTER TABLE physical_exam_field DROP age_max');
        $this->addSql('ALTER TABLE physical_exam_field DROP unit');
        $this->addSql('ALTER TABLE physical_exam_field DROP is_weight');
        $this->addSql('ALTER TABLE physical_exam_field DROP is_height');
        $this->addSql('ALTER TABLE physical_exam_field DROP is_bmi');
        $this->addSql('ALTER TABLE physical_exam_field DROP is_temperature');
        $this->addSql('ALTER TABLE physical_exam_field DROP is_systolic');
        $this->addSql('ALTER TABLE physical_exam_field DROP is_diastolic');
        $this->addSql('ALTER TABLE physical_exam_field DROP is_saturation');
        $this->addSql('ALTER TABLE physical_exam_field DROP is_respiratory_rate');
        $this->addSql('ALTER TABLE physical_exam_field DROP is_pce');
        $this->addSql('ALTER TABLE physical_exam_field DROP field_type');
        $this->addSql('ALTER TABLE physical_exam_field DROP exam_type');
        $this->addSql('ALTER TABLE physical_exam_field ALTER name SET NOT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ALTER name TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE physical_exam_field ALTER description TYPE TEXT');
        $this->addSql('ALTER TABLE physical_exam_field ALTER is_active SET DEFAULT true');
        $this->addSql('ALTER TABLE physical_exam_field ALTER id_estado SET DEFAULT 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE exam_group_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE exam_service_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE exam_service_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE physical_exam_group_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE physical_exam_type_id_seq CASCADE');
        $this->addSql('DROP TABLE exam_group');
        $this->addSql('DROP TABLE exam_service');
        $this->addSql('DROP TABLE exam_service_type');
        $this->addSql('DROP TABLE physical_exam_group');
        $this->addSql('DROP TABLE physical_exam_type');
        $this->addSql('ALTER TABLE physical_exam_field ADD grouping1_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD grouping2_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD sort_order INT NOT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD range_min INT DEFAULT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD range_max INT DEFAULT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD age_min INT DEFAULT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD age_max INT DEFAULT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD unit VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD is_weight BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD is_height BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD is_bmi BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD is_temperature BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD is_systolic BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD is_diastolic BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD is_saturation BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD is_respiratory_rate BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD is_pce BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD field_type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ADD exam_type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ALTER name DROP NOT NULL');
        $this->addSql('ALTER TABLE physical_exam_field ALTER name TYPE VARCHAR(45)');
        $this->addSql('ALTER TABLE physical_exam_field ALTER description TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE physical_exam_field ALTER is_active DROP DEFAULT');
        $this->addSql('ALTER TABLE physical_exam_field ALTER id_estado DROP DEFAULT');
        $this->addSql('ALTER TABLE physical_exam_field ADD CONSTRAINT fk_9ae19b10618caf8a FOREIGN KEY (grouping1_id) REFERENCES physical_exam_grouping (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE physical_exam_field ADD CONSTRAINT fk_9ae19b1073390064 FOREIGN KEY (grouping2_id) REFERENCES physical_exam_grouping (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_9ae19b10618caf8a ON physical_exam_field (grouping1_id)');
        $this->addSql('CREATE INDEX idx_9ae19b1073390064 ON physical_exam_field (grouping2_id)');
    }
}
