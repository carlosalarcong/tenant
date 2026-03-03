<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303030904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE surgery_package_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE surgery_package_plan_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE surgery_package_price_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE surgery_package (id INT NOT NULL, plan_id INT NOT NULL, billing_item_id INT NOT NULL, created_by_id INT NOT NULL, cancellation_user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, adjustment_percentage NUMERIC(10, 2) DEFAULT \'0\' NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, cancellation_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_68616182E899029B ON surgery_package (plan_id)');
        $this->addSql('CREATE INDEX IDX_686161827941D989 ON surgery_package (billing_item_id)');
        $this->addSql('CREATE INDEX IDX_68616182B03A8386 ON surgery_package (created_by_id)');
        $this->addSql('CREATE INDEX IDX_68616182D28DB158 ON surgery_package (cancellation_user_id)');
        $this->addSql('CREATE TABLE surgery_package_plan (id INT NOT NULL, branch_payer_id INT NOT NULL, created_by_id INT NOT NULL, cancellation_user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, cancellation_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2CF2184A26D9006A ON surgery_package_plan (branch_payer_id)');
        $this->addSql('CREATE INDEX IDX_2CF2184AB03A8386 ON surgery_package_plan (created_by_id)');
        $this->addSql('CREATE INDEX IDX_2CF2184AD28DB158 ON surgery_package_plan (cancellation_user_id)');
        $this->addSql('CREATE TABLE surgery_package_price (id INT NOT NULL, package_id INT NOT NULL, surgery_fee_item_id INT NOT NULL, payer_price NUMERIC(10, 2) NOT NULL, clinic_price NUMERIC(10, 2) NOT NULL, effective_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_in_use BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_72842F95F44CABFF ON surgery_package_price (package_id)');
        $this->addSql('CREATE INDEX IDX_72842F95CB5A3466 ON surgery_package_price (surgery_fee_item_id)');
        $this->addSql('ALTER TABLE surgery_package ADD CONSTRAINT FK_68616182E899029B FOREIGN KEY (plan_id) REFERENCES surgery_package_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package ADD CONSTRAINT FK_686161827941D989 FOREIGN KEY (billing_item_id) REFERENCES billing_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package ADD CONSTRAINT FK_68616182B03A8386 FOREIGN KEY (created_by_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package ADD CONSTRAINT FK_68616182D28DB158 FOREIGN KEY (cancellation_user_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package_plan ADD CONSTRAINT FK_2CF2184A26D9006A FOREIGN KEY (branch_payer_id) REFERENCES branch_payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package_plan ADD CONSTRAINT FK_2CF2184AB03A8386 FOREIGN KEY (created_by_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package_plan ADD CONSTRAINT FK_2CF2184AD28DB158 FOREIGN KEY (cancellation_user_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package_price ADD CONSTRAINT FK_72842F95F44CABFF FOREIGN KEY (package_id) REFERENCES surgery_package (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE surgery_package_price ADD CONSTRAINT FK_72842F95CB5A3466 FOREIGN KEY (surgery_fee_item_id) REFERENCES surgery_fee_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE surgery_package_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE surgery_package_plan_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE surgery_package_price_id_seq CASCADE');
        $this->addSql('ALTER TABLE surgery_package DROP CONSTRAINT FK_68616182E899029B');
        $this->addSql('ALTER TABLE surgery_package DROP CONSTRAINT FK_686161827941D989');
        $this->addSql('ALTER TABLE surgery_package DROP CONSTRAINT FK_68616182B03A8386');
        $this->addSql('ALTER TABLE surgery_package DROP CONSTRAINT FK_68616182D28DB158');
        $this->addSql('ALTER TABLE surgery_package_plan DROP CONSTRAINT FK_2CF2184A26D9006A');
        $this->addSql('ALTER TABLE surgery_package_plan DROP CONSTRAINT FK_2CF2184AB03A8386');
        $this->addSql('ALTER TABLE surgery_package_plan DROP CONSTRAINT FK_2CF2184AD28DB158');
        $this->addSql('ALTER TABLE surgery_package_price DROP CONSTRAINT FK_72842F95F44CABFF');
        $this->addSql('ALTER TABLE surgery_package_price DROP CONSTRAINT FK_72842F95CB5A3466');
        $this->addSql('DROP TABLE surgery_package');
        $this->addSql('DROP TABLE surgery_package_plan');
        $this->addSql('DROP TABLE surgery_package_price');
    }
}
