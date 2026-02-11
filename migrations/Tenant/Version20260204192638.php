<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260204192638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE budget_footer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE budget_footer_by_funder_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE budget_funder_footer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE budget_footer (id INT NOT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, id_estado INT DEFAULT 1 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE budget_footer_by_funder (id INT NOT NULL, budget_footer_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, id_estado INT DEFAULT 1 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D7997836F57E09E4 ON budget_footer_by_funder (budget_footer_id)');
        $this->addSql('CREATE TABLE budget_funder_footer (id INT NOT NULL, budget_footer_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, id_estado INT DEFAULT 1 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1B56104DF57E09E4 ON budget_funder_footer (budget_footer_id)');
        $this->addSql('ALTER TABLE budget_footer_by_funder ADD CONSTRAINT FK_D7997836F57E09E4 FOREIGN KEY (budget_footer_id) REFERENCES budget_footer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE budget_funder_footer ADD CONSTRAINT FK_1B56104DF57E09E4 FOREIGN KEY (budget_footer_id) REFERENCES budget_footer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE budget_footer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE budget_footer_by_funder_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE budget_funder_footer_id_seq CASCADE');
        $this->addSql('ALTER TABLE budget_footer_by_funder DROP CONSTRAINT FK_D7997836F57E09E4');
        $this->addSql('ALTER TABLE budget_funder_footer DROP CONSTRAINT FK_1B56104DF57E09E4');
        $this->addSql('DROP TABLE budget_footer');
        $this->addSql('DROP TABLE budget_footer_by_funder');
        $this->addSql('DROP TABLE budget_funder_footer');
    }
}
