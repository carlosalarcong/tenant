<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303150637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE fee_code_mass_adjustment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE hourly_rate_surcharge_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE insurance_plan_mass_adjustment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE open_plan_mass_adjustment_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE fee_code_mass_adjustment (id INT NOT NULL, branch_payer_id INT NOT NULL, created_by_id INT NOT NULL, cancellation_user_id INT DEFAULT NULL, closure_user_id INT DEFAULT NULL, effective_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, adjustment_percentage NUMERIC(10, 2) NOT NULL, confirmed_count INT DEFAULT 0 NOT NULL, plan_count INT DEFAULT 0 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, cancellation_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, closure_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_52A6CD7426D9006A ON fee_code_mass_adjustment (branch_payer_id)');
        $this->addSql('CREATE INDEX IDX_52A6CD74B03A8386 ON fee_code_mass_adjustment (created_by_id)');
        $this->addSql('CREATE INDEX IDX_52A6CD74D28DB158 ON fee_code_mass_adjustment (cancellation_user_id)');
        $this->addSql('CREATE INDEX IDX_52A6CD7485D6EC68 ON fee_code_mass_adjustment (closure_user_id)');
        $this->addSql('CREATE TABLE hourly_rate_surcharge (id INT NOT NULL, branch_payer_id INT NOT NULL, created_by_id INT DEFAULT NULL, start_time TIME(0) WITHOUT TIME ZONE NOT NULL, end_time TIME(0) WITHOUT TIME ZONE NOT NULL, day_of_week INT NOT NULL, percentage NUMERIC(10, 2) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C439DFEE26D9006A ON hourly_rate_surcharge (branch_payer_id)');
        $this->addSql('CREATE INDEX IDX_C439DFEEB03A8386 ON hourly_rate_surcharge (created_by_id)');
        $this->addSql('CREATE TABLE insurance_plan_mass_adjustment (id INT NOT NULL, branch_payer_id INT NOT NULL, created_by_id INT NOT NULL, cancellation_user_id INT DEFAULT NULL, closure_user_id INT DEFAULT NULL, effective_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, adjustment_percentage NUMERIC(10, 2) NOT NULL, confirmed_count INT DEFAULT 0 NOT NULL, plan_count INT DEFAULT 0 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, cancellation_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, closure_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4623B02A26D9006A ON insurance_plan_mass_adjustment (branch_payer_id)');
        $this->addSql('CREATE INDEX IDX_4623B02AB03A8386 ON insurance_plan_mass_adjustment (created_by_id)');
        $this->addSql('CREATE INDEX IDX_4623B02AD28DB158 ON insurance_plan_mass_adjustment (cancellation_user_id)');
        $this->addSql('CREATE INDEX IDX_4623B02A85D6EC68 ON insurance_plan_mass_adjustment (closure_user_id)');
        $this->addSql('CREATE TABLE open_plan_mass_adjustment (id INT NOT NULL, branch_payer_id INT NOT NULL, created_by_id INT NOT NULL, cancellation_user_id INT DEFAULT NULL, closure_user_id INT DEFAULT NULL, effective_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, adjustment_percentage NUMERIC(10, 2) NOT NULL, confirmed_count INT DEFAULT 0 NOT NULL, plan_count INT DEFAULT 0 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, cancellation_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, closure_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3D4007726D9006A ON open_plan_mass_adjustment (branch_payer_id)');
        $this->addSql('CREATE INDEX IDX_3D40077B03A8386 ON open_plan_mass_adjustment (created_by_id)');
        $this->addSql('CREATE INDEX IDX_3D40077D28DB158 ON open_plan_mass_adjustment (cancellation_user_id)');
        $this->addSql('CREATE INDEX IDX_3D4007785D6EC68 ON open_plan_mass_adjustment (closure_user_id)');
        $this->addSql('ALTER TABLE fee_code_mass_adjustment ADD CONSTRAINT FK_52A6CD7426D9006A FOREIGN KEY (branch_payer_id) REFERENCES branch_payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fee_code_mass_adjustment ADD CONSTRAINT FK_52A6CD74B03A8386 FOREIGN KEY (created_by_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fee_code_mass_adjustment ADD CONSTRAINT FK_52A6CD74D28DB158 FOREIGN KEY (cancellation_user_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE fee_code_mass_adjustment ADD CONSTRAINT FK_52A6CD7485D6EC68 FOREIGN KEY (closure_user_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE hourly_rate_surcharge ADD CONSTRAINT FK_C439DFEE26D9006A FOREIGN KEY (branch_payer_id) REFERENCES branch_payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE hourly_rate_surcharge ADD CONSTRAINT FK_C439DFEEB03A8386 FOREIGN KEY (created_by_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE insurance_plan_mass_adjustment ADD CONSTRAINT FK_4623B02A26D9006A FOREIGN KEY (branch_payer_id) REFERENCES branch_payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE insurance_plan_mass_adjustment ADD CONSTRAINT FK_4623B02AB03A8386 FOREIGN KEY (created_by_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE insurance_plan_mass_adjustment ADD CONSTRAINT FK_4623B02AD28DB158 FOREIGN KEY (cancellation_user_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE insurance_plan_mass_adjustment ADD CONSTRAINT FK_4623B02A85D6EC68 FOREIGN KEY (closure_user_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE open_plan_mass_adjustment ADD CONSTRAINT FK_3D4007726D9006A FOREIGN KEY (branch_payer_id) REFERENCES branch_payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE open_plan_mass_adjustment ADD CONSTRAINT FK_3D40077B03A8386 FOREIGN KEY (created_by_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE open_plan_mass_adjustment ADD CONSTRAINT FK_3D40077D28DB158 FOREIGN KEY (cancellation_user_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE open_plan_mass_adjustment ADD CONSTRAINT FK_3D4007785D6EC68 FOREIGN KEY (closure_user_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE fee_code_mass_adjustment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE hourly_rate_surcharge_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE insurance_plan_mass_adjustment_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE open_plan_mass_adjustment_id_seq CASCADE');
        $this->addSql('ALTER TABLE fee_code_mass_adjustment DROP CONSTRAINT FK_52A6CD7426D9006A');
        $this->addSql('ALTER TABLE fee_code_mass_adjustment DROP CONSTRAINT FK_52A6CD74B03A8386');
        $this->addSql('ALTER TABLE fee_code_mass_adjustment DROP CONSTRAINT FK_52A6CD74D28DB158');
        $this->addSql('ALTER TABLE fee_code_mass_adjustment DROP CONSTRAINT FK_52A6CD7485D6EC68');
        $this->addSql('ALTER TABLE hourly_rate_surcharge DROP CONSTRAINT FK_C439DFEE26D9006A');
        $this->addSql('ALTER TABLE hourly_rate_surcharge DROP CONSTRAINT FK_C439DFEEB03A8386');
        $this->addSql('ALTER TABLE insurance_plan_mass_adjustment DROP CONSTRAINT FK_4623B02A26D9006A');
        $this->addSql('ALTER TABLE insurance_plan_mass_adjustment DROP CONSTRAINT FK_4623B02AB03A8386');
        $this->addSql('ALTER TABLE insurance_plan_mass_adjustment DROP CONSTRAINT FK_4623B02AD28DB158');
        $this->addSql('ALTER TABLE insurance_plan_mass_adjustment DROP CONSTRAINT FK_4623B02A85D6EC68');
        $this->addSql('ALTER TABLE open_plan_mass_adjustment DROP CONSTRAINT FK_3D4007726D9006A');
        $this->addSql('ALTER TABLE open_plan_mass_adjustment DROP CONSTRAINT FK_3D40077B03A8386');
        $this->addSql('ALTER TABLE open_plan_mass_adjustment DROP CONSTRAINT FK_3D40077D28DB158');
        $this->addSql('ALTER TABLE open_plan_mass_adjustment DROP CONSTRAINT FK_3D4007785D6EC68');
        $this->addSql('DROP TABLE fee_code_mass_adjustment');
        $this->addSql('DROP TABLE hourly_rate_surcharge');
        $this->addSql('DROP TABLE insurance_plan_mass_adjustment');
        $this->addSql('DROP TABLE open_plan_mass_adjustment');
    }
}
