<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260204112730 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE cancellation_reason_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE company_agreement_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE emergency_consultation_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE cancellation_reason (id INT NOT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE company_agreement (id INT NOT NULL, name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE emergency_consultation_type (id INT NOT NULL, name VARCHAR(255) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE cancellation_reason_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE company_agreement_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE emergency_consultation_type_id_seq CASCADE');
        $this->addSql('DROP TABLE cancellation_reason');
        $this->addSql('DROP TABLE company_agreement');
        $this->addSql('DROP TABLE emergency_consultation_type');
    }
}
