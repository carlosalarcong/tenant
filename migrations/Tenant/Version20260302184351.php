<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260302184351 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Budget pricing: create surgery package, insurance plan and open plan distribution tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE insurance_plan_price_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE open_plan_distribution_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE surgery_fee_price_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE surgery_package_item_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE surgery_package_plan_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE surgery_package_price_id_seq INCREMENT BY 1 MINVALUE 1 START 1');

        $this->addSql('CREATE TABLE insurance_plan_price (id INT NOT NULL, insurance_plan_id INT NOT NULL, medical_service_id INT NOT NULL, branch_payer_id INT DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, effective_date DATE NOT NULL, expiration_date DATE DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E24738FA5B8273E ON insurance_plan_price (insurance_plan_id)');
        $this->addSql('CREATE INDEX IDX_E24738FAC61D802A ON insurance_plan_price (medical_service_id)');
        $this->addSql('CREATE INDEX IDX_E24738FA26D9006A ON insurance_plan_price (branch_payer_id)');

        $this->addSql('CREATE TABLE open_plan_distribution (id INT NOT NULL, medical_service_id INT NOT NULL, insurance_plan_id INT NOT NULL, branch_payer_id INT DEFAULT NULL, honorarium_amount NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, clinical_amount NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, valuable_amount NUMERIC(10, 2) DEFAULT \'0.00\' NOT NULL, effective_date DATE NOT NULL, expiration_date DATE DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_30B281EDC61D802A ON open_plan_distribution (medical_service_id)');
        $this->addSql('CREATE INDEX IDX_30B281ED5B8273E ON open_plan_distribution (insurance_plan_id)');
        $this->addSql('CREATE INDEX IDX_30B281ED26D9006A ON open_plan_distribution (branch_payer_id)');

        $this->addSql('CREATE TABLE surgery_fee_price (id INT NOT NULL, medical_service_id INT NOT NULL, branch_payer_id INT DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, fee_type VARCHAR(30) DEFAULT NULL, effective_date DATE NOT NULL, expiration_date DATE DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9527D951C61D802A ON surgery_fee_price (medical_service_id)');
        $this->addSql('CREATE INDEX IDX_9527D95126D9006A ON surgery_fee_price (branch_payer_id)');

        $this->addSql('CREATE TABLE surgery_package_plan (id INT NOT NULL, cancelled_by_id INT DEFAULT NULL, parent_plan_id INT DEFAULT NULL, branch_payer_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, is_disabled BOOLEAN DEFAULT false NOT NULL, is_telemedicine BOOLEAN DEFAULT NULL, cancelled_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2CF2184A187B2D12 ON surgery_package_plan (cancelled_by_id)');
        $this->addSql('CREATE INDEX IDX_2CF2184A9AD176DD ON surgery_package_plan (parent_plan_id)');
        $this->addSql('CREATE INDEX IDX_2CF2184A26D9006A ON surgery_package_plan (branch_payer_id)');
        $this->addSql('COMMENT ON COLUMN surgery_package_plan.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN surgery_package_plan.updated_at IS \'(DC2Type:datetime_immutable)\'');

        $this->addSql('CREATE TABLE surgery_package_item (id INT NOT NULL, medical_service_id INT DEFAULT NULL, surgery_package_plan_id INT DEFAULT NULL, name VARCHAR(200) NOT NULL, code VARCHAR(50) DEFAULT NULL, item_type VARCHAR(30) NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EEB36629C61D802A ON surgery_package_item (medical_service_id)');
        $this->addSql('CREATE INDEX IDX_EEB36629CAE4C663 ON surgery_package_item (surgery_package_plan_id)');

        $this->addSql('CREATE TABLE surgery_package_price (id INT NOT NULL, surgery_package_item_id INT NOT NULL, surgery_package_plan_id INT NOT NULL, branch_payer_id INT DEFAULT NULL, price_isapre NUMERIC(10, 2) NOT NULL, price_fonasa NUMERIC(10, 2) DEFAULT NULL, effective_date DATE NOT NULL, expiration_date DATE DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_72842F95301296A6 ON surgery_package_price (surgery_package_item_id)');
        $this->addSql('CREATE INDEX IDX_72842F95CAE4C663 ON surgery_package_price (surgery_package_plan_id)');
        $this->addSql('CREATE INDEX IDX_72842F9526D9006A ON surgery_package_price (branch_payer_id)');

        $this->addSql('ALTER TABLE insurance_plan_price ADD CONSTRAINT FK_E24738FA5B8273E FOREIGN KEY (insurance_plan_id) REFERENCES insurance_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE insurance_plan_price ADD CONSTRAINT FK_E24738FAC61D802A FOREIGN KEY (medical_service_id) REFERENCES medical_service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE insurance_plan_price ADD CONSTRAINT FK_E24738FA26D9006A FOREIGN KEY (branch_payer_id) REFERENCES branch_payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE open_plan_distribution ADD CONSTRAINT FK_30B281EDC61D802A FOREIGN KEY (medical_service_id) REFERENCES medical_service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE open_plan_distribution ADD CONSTRAINT FK_30B281ED5B8273E FOREIGN KEY (insurance_plan_id) REFERENCES insurance_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE open_plan_distribution ADD CONSTRAINT FK_30B281ED26D9006A FOREIGN KEY (branch_payer_id) REFERENCES branch_payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE surgery_fee_price ADD CONSTRAINT FK_9527D951C61D802A FOREIGN KEY (medical_service_id) REFERENCES medical_service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_fee_price ADD CONSTRAINT FK_9527D95126D9006A FOREIGN KEY (branch_payer_id) REFERENCES branch_payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE surgery_package_plan ADD CONSTRAINT FK_2CF2184A187B2D12 FOREIGN KEY (cancelled_by_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package_plan ADD CONSTRAINT FK_2CF2184A9AD176DD FOREIGN KEY (parent_plan_id) REFERENCES surgery_package_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package_plan ADD CONSTRAINT FK_2CF2184A26D9006A FOREIGN KEY (branch_payer_id) REFERENCES branch_payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE surgery_package_item ADD CONSTRAINT FK_EEB36629C61D802A FOREIGN KEY (medical_service_id) REFERENCES medical_service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package_item ADD CONSTRAINT FK_EEB36629CAE4C663 FOREIGN KEY (surgery_package_plan_id) REFERENCES surgery_package_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE surgery_package_price ADD CONSTRAINT FK_72842F95301296A6 FOREIGN KEY (surgery_package_item_id) REFERENCES surgery_package_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package_price ADD CONSTRAINT FK_72842F95CAE4C663 FOREIGN KEY (surgery_package_plan_id) REFERENCES surgery_package_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package_price ADD CONSTRAINT FK_72842F9526D9006A FOREIGN KEY (branch_payer_id) REFERENCES branch_payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE surgery_package_price DROP CONSTRAINT FK_72842F95301296A6');
        $this->addSql('ALTER TABLE surgery_package_price DROP CONSTRAINT FK_72842F95CAE4C663');
        $this->addSql('ALTER TABLE surgery_package_price DROP CONSTRAINT FK_72842F9526D9006A');
        $this->addSql('ALTER TABLE surgery_package_item DROP CONSTRAINT FK_EEB36629C61D802A');
        $this->addSql('ALTER TABLE surgery_package_item DROP CONSTRAINT FK_EEB36629CAE4C663');
        $this->addSql('ALTER TABLE surgery_package_plan DROP CONSTRAINT FK_2CF2184A187B2D12');
        $this->addSql('ALTER TABLE surgery_package_plan DROP CONSTRAINT FK_2CF2184A9AD176DD');
        $this->addSql('ALTER TABLE surgery_package_plan DROP CONSTRAINT FK_2CF2184A26D9006A');
        $this->addSql('ALTER TABLE surgery_fee_price DROP CONSTRAINT FK_9527D951C61D802A');
        $this->addSql('ALTER TABLE surgery_fee_price DROP CONSTRAINT FK_9527D95126D9006A');
        $this->addSql('ALTER TABLE open_plan_distribution DROP CONSTRAINT FK_30B281EDC61D802A');
        $this->addSql('ALTER TABLE open_plan_distribution DROP CONSTRAINT FK_30B281ED5B8273E');
        $this->addSql('ALTER TABLE open_plan_distribution DROP CONSTRAINT FK_30B281ED26D9006A');
        $this->addSql('ALTER TABLE insurance_plan_price DROP CONSTRAINT FK_E24738FA5B8273E');
        $this->addSql('ALTER TABLE insurance_plan_price DROP CONSTRAINT FK_E24738FAC61D802A');
        $this->addSql('ALTER TABLE insurance_plan_price DROP CONSTRAINT FK_E24738FA26D9006A');

        $this->addSql('DROP TABLE surgery_package_price');
        $this->addSql('DROP TABLE surgery_package_item');
        $this->addSql('DROP TABLE surgery_package_plan');
        $this->addSql('DROP TABLE surgery_fee_price');
        $this->addSql('DROP TABLE open_plan_distribution');
        $this->addSql('DROP TABLE insurance_plan_price');

        $this->addSql('DROP SEQUENCE surgery_package_price_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE surgery_package_plan_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE surgery_package_item_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE surgery_fee_price_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE open_plan_distribution_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE insurance_plan_price_id_seq CASCADE');
    }
}
