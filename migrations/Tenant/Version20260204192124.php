<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260204192124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE bank_account_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE company_user_association_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE daily_uf_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE professional_participation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE settlement_base_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE bank_account (id INT NOT NULL, bank_id INT DEFAULT NULL, bank_account_type_id INT DEFAULT NULL, account_number VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_53A23E0A11C8FB41 ON bank_account (bank_id)');
        $this->addSql('CREATE INDEX IDX_53A23E0AF3FFAAD7 ON bank_account (bank_account_type_id)');
        $this->addSql('CREATE TABLE company_user_association (id INT NOT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE daily_uf (id INT NOT NULL, date DATE NOT NULL, value NUMERIC(10, 2) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE professional_participation (id INT NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE settlement_base (id INT NOT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE bank_account ADD CONSTRAINT FK_53A23E0A11C8FB41 FOREIGN KEY (bank_id) REFERENCES bank (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bank_account ADD CONSTRAINT FK_53A23E0AF3FFAAD7 FOREIGN KEY (bank_account_type_id) REFERENCES bank_account_type (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE bank_account_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE company_user_association_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE daily_uf_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE professional_participation_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE settlement_base_id_seq CASCADE');
        $this->addSql('ALTER TABLE bank_account DROP CONSTRAINT FK_53A23E0A11C8FB41');
        $this->addSql('ALTER TABLE bank_account DROP CONSTRAINT FK_53A23E0AF3FFAAD7');
        $this->addSql('DROP TABLE bank_account');
        $this->addSql('DROP TABLE company_user_association');
        $this->addSql('DROP TABLE daily_uf');
        $this->addSql('DROP TABLE professional_participation');
        $this->addSql('DROP TABLE settlement_base');
    }
}
