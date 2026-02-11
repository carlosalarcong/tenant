<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260210185459 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crear tabla admission_record con relaciones hacia person/payer/agreement/service/bed';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE admission_record_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE admission_record (
            id INT NOT NULL,
            person_id INT NOT NULL,
            payer_id INT DEFAULT NULL,
            agreement_id INT DEFAULT NULL,
            service_id INT DEFAULT NULL,
            bed_id INT DEFAULT NULL,
            admission_type VARCHAR(30) NOT NULL,
            status VARCHAR(30) NOT NULL,
            triage VARCHAR(10) DEFAULT NULL,
            consultation_reason TEXT DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_A1A74BDB217BBB47 ON admission_record (person_id)');
        $this->addSql('CREATE INDEX IDX_A1A74BDBC17AD9A9 ON admission_record (payer_id)');
        $this->addSql('CREATE INDEX IDX_A1A74BDB24890B2B ON admission_record (agreement_id)');
        $this->addSql('CREATE INDEX IDX_A1A74BDBED5CA9E6 ON admission_record (service_id)');
        $this->addSql('CREATE INDEX IDX_A1A74BDB88688BB9 ON admission_record (bed_id)');
        $this->addSql("COMMENT ON COLUMN admission_record.created_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN admission_record.updated_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql('ALTER TABLE admission_record ADD CONSTRAINT FK_A1A74BDB217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE admission_record ADD CONSTRAINT FK_A1A74BDBC17AD9A9 FOREIGN KEY (payer_id) REFERENCES payer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE admission_record ADD CONSTRAINT FK_A1A74BDB24890B2B FOREIGN KEY (agreement_id) REFERENCES agreement (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE admission_record ADD CONSTRAINT FK_A1A74BDBED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE admission_record ADD CONSTRAINT FK_A1A74BDB88688BB9 FOREIGN KEY (bed_id) REFERENCES bed (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE admission_record DROP CONSTRAINT FK_A1A74BDB88688BB9');
        $this->addSql('ALTER TABLE admission_record DROP CONSTRAINT FK_A1A74BDBED5CA9E6');
        $this->addSql('ALTER TABLE admission_record DROP CONSTRAINT FK_A1A74BDB24890B2B');
        $this->addSql('ALTER TABLE admission_record DROP CONSTRAINT FK_A1A74BDBC17AD9A9');
        $this->addSql('ALTER TABLE admission_record DROP CONSTRAINT FK_A1A74BDB217BBB47');
        $this->addSql('DROP TABLE admission_record');
        $this->addSql('DROP SEQUENCE admission_record_id_seq CASCADE');
    }
}
