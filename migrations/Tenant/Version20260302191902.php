<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260302191902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Budget: convert surgery package columns into foreign keys';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE budget ADD CONSTRAINT FK_73F2F77BCAE4C663 FOREIGN KEY (surgery_package_plan_id) REFERENCES surgery_package_plan (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_73F2F77BCAE4C663 ON budget (surgery_package_plan_id)');
        $this->addSql('ALTER TABLE budget_detail ADD CONSTRAINT FK_7AB3392F301296A6 FOREIGN KEY (surgery_package_item_id) REFERENCES surgery_package_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_7AB3392F301296A6 ON budget_detail (surgery_package_item_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE budget_detail DROP CONSTRAINT FK_7AB3392F301296A6');
        $this->addSql('DROP INDEX IDX_7AB3392F301296A6');
        $this->addSql('ALTER TABLE budget DROP CONSTRAINT FK_73F2F77BCAE4C663');
        $this->addSql('DROP INDEX IDX_73F2F77BCAE4C663');
    }
}
