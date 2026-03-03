<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303005120 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE open_plan_distribution_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE open_plan_price_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE open_plan_distribution (id INT NOT NULL, open_plan_price_id INT NOT NULL, surgery_fee_item_id INT NOT NULL, amount NUMERIC(10, 2) NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_30B281ED58D4A975 ON open_plan_distribution (open_plan_price_id)');
        $this->addSql('CREATE INDEX IDX_30B281EDCB5A3466 ON open_plan_distribution (surgery_fee_item_id)');
        $this->addSql('CREATE TABLE open_plan_price (id INT NOT NULL, plan_id INT NOT NULL, billing_item_id INT NOT NULL, cancellation_user_id INT DEFAULT NULL, unit_price NUMERIC(10, 2) NOT NULL, copay_amount NUMERIC(10, 2) NOT NULL, theatre_amount NUMERIC(10, 2) NOT NULL, effective_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, cancellation_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_active BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6817C636E899029B ON open_plan_price (plan_id)');
        $this->addSql('CREATE INDEX IDX_6817C6367941D989 ON open_plan_price (billing_item_id)');
        $this->addSql('CREATE INDEX IDX_6817C636D28DB158 ON open_plan_price (cancellation_user_id)');
        $this->addSql('ALTER TABLE open_plan_distribution ADD CONSTRAINT FK_30B281ED58D4A975 FOREIGN KEY (open_plan_price_id) REFERENCES open_plan_price (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE open_plan_distribution ADD CONSTRAINT FK_30B281EDCB5A3466 FOREIGN KEY (surgery_fee_item_id) REFERENCES surgery_fee_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE open_plan_price ADD CONSTRAINT FK_6817C636E899029B FOREIGN KEY (plan_id) REFERENCES insurance_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE open_plan_price ADD CONSTRAINT FK_6817C6367941D989 FOREIGN KEY (billing_item_id) REFERENCES billing_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE open_plan_price ADD CONSTRAINT FK_6817C636D28DB158 FOREIGN KEY (cancellation_user_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE open_plan_distribution_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE open_plan_price_id_seq CASCADE');
        $this->addSql('ALTER TABLE open_plan_distribution DROP CONSTRAINT FK_30B281ED58D4A975');
        $this->addSql('ALTER TABLE open_plan_distribution DROP CONSTRAINT FK_30B281EDCB5A3466');
        $this->addSql('ALTER TABLE open_plan_price DROP CONSTRAINT FK_6817C636E899029B');
        $this->addSql('ALTER TABLE open_plan_price DROP CONSTRAINT FK_6817C6367941D989');
        $this->addSql('ALTER TABLE open_plan_price DROP CONSTRAINT FK_6817C636D28DB158');
        $this->addSql('DROP TABLE open_plan_distribution');
        $this->addSql('DROP TABLE open_plan_price');
    }
}
