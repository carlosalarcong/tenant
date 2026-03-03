<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303045246 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE budget_insurance_plan_price_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE budget_surgery_fee_price_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE surgery_package_item_price_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE budget_insurance_plan_price (id INT NOT NULL, medical_service_id INT NOT NULL, insurance_plan_id INT NOT NULL, branch_payer_id INT DEFAULT NULL, unit_price NUMERIC(10, 2) NOT NULL, copay_amount NUMERIC(10, 2) DEFAULT NULL, effective_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D955CCA5C61D802A ON budget_insurance_plan_price (medical_service_id)');
        $this->addSql('CREATE INDEX IDX_D955CCA55B8273E ON budget_insurance_plan_price (insurance_plan_id)');
        $this->addSql('CREATE INDEX IDX_D955CCA526D9006A ON budget_insurance_plan_price (branch_payer_id)');
        $this->addSql('CREATE TABLE budget_surgery_fee_price (id INT NOT NULL, medical_service_id INT NOT NULL, branch_payer_id INT DEFAULT NULL, theatre_amount NUMERIC(10, 2) NOT NULL, effective_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_808D3A1CC61D802A ON budget_surgery_fee_price (medical_service_id)');
        $this->addSql('CREATE INDEX IDX_808D3A1C26D9006A ON budget_surgery_fee_price (branch_payer_id)');
        $this->addSql('CREATE TABLE surgery_package_item_price (id INT NOT NULL, surgery_package_item_id INT NOT NULL, surgery_package_plan_id INT NOT NULL, branch_payer_id INT DEFAULT NULL, price_isapre NUMERIC(10, 2) NOT NULL, price_fonasa NUMERIC(10, 2) DEFAULT NULL, effective_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DA9A0488301296A6 ON surgery_package_item_price (surgery_package_item_id)');
        $this->addSql('CREATE INDEX IDX_DA9A0488CAE4C663 ON surgery_package_item_price (surgery_package_plan_id)');
        $this->addSql('CREATE INDEX IDX_DA9A048826D9006A ON surgery_package_item_price (branch_payer_id)');
        $this->addSql('ALTER TABLE budget_insurance_plan_price ADD CONSTRAINT FK_D955CCA5C61D802A FOREIGN KEY (medical_service_id) REFERENCES medical_service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_insurance_plan_price ADD CONSTRAINT FK_D955CCA55B8273E FOREIGN KEY (insurance_plan_id) REFERENCES insurance_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_insurance_plan_price ADD CONSTRAINT FK_D955CCA526D9006A FOREIGN KEY (branch_payer_id) REFERENCES branch_payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_surgery_fee_price ADD CONSTRAINT FK_808D3A1CC61D802A FOREIGN KEY (medical_service_id) REFERENCES medical_service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_surgery_fee_price ADD CONSTRAINT FK_808D3A1C26D9006A FOREIGN KEY (branch_payer_id) REFERENCES branch_payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package_item_price ADD CONSTRAINT FK_DA9A0488301296A6 FOREIGN KEY (surgery_package_item_id) REFERENCES surgery_package_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package_item_price ADD CONSTRAINT FK_DA9A0488CAE4C663 FOREIGN KEY (surgery_package_plan_id) REFERENCES surgery_package_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package_item_price ADD CONSTRAINT FK_DA9A048826D9006A FOREIGN KEY (branch_payer_id) REFERENCES branch_payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE budget_insurance_plan_price_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE budget_surgery_fee_price_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE surgery_package_item_price_id_seq CASCADE');
        $this->addSql('ALTER TABLE budget_insurance_plan_price DROP CONSTRAINT FK_D955CCA5C61D802A');
        $this->addSql('ALTER TABLE budget_insurance_plan_price DROP CONSTRAINT FK_D955CCA55B8273E');
        $this->addSql('ALTER TABLE budget_insurance_plan_price DROP CONSTRAINT FK_D955CCA526D9006A');
        $this->addSql('ALTER TABLE budget_surgery_fee_price DROP CONSTRAINT FK_808D3A1CC61D802A');
        $this->addSql('ALTER TABLE budget_surgery_fee_price DROP CONSTRAINT FK_808D3A1C26D9006A');
        $this->addSql('ALTER TABLE surgery_package_item_price DROP CONSTRAINT FK_DA9A0488301296A6');
        $this->addSql('ALTER TABLE surgery_package_item_price DROP CONSTRAINT FK_DA9A0488CAE4C663');
        $this->addSql('ALTER TABLE surgery_package_item_price DROP CONSTRAINT FK_DA9A048826D9006A');
        $this->addSql('DROP TABLE budget_insurance_plan_price');
        $this->addSql('DROP TABLE budget_surgery_fee_price');
        $this->addSql('DROP TABLE surgery_package_item_price');
    }
}
