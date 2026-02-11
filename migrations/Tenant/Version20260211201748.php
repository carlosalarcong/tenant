<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260211201748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE admission_record DROP CONSTRAINT fk_a1a74bdb217bbb47');
        $this->addSql('DROP INDEX idx_a1a74bdb217bbb47');
        $this->addSql('ALTER TABLE admission_record RENAME COLUMN person_id TO patient_id');
        $this->addSql('ALTER TABLE admission_record ADD CONSTRAINT FK_A1A74BDB6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A1A74BDB6B899279 ON admission_record (patient_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE admission_record DROP CONSTRAINT FK_A1A74BDB6B899279');
        $this->addSql('DROP INDEX IDX_A1A74BDB6B899279');
        $this->addSql('ALTER TABLE admission_record RENAME COLUMN patient_id TO person_id');
        $this->addSql('ALTER TABLE admission_record ADD CONSTRAINT fk_a1a74bdb217bbb47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_a1a74bdb217bbb47 ON admission_record (person_id)');
    }
}
