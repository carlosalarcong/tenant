<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260130183129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        
        // Check if slug column exists
        $slugExists = $this->connection->fetchOne("SHOW COLUMNS FROM tenant_db LIKE 'slug'");
        
        if (!$slugExists) {
            // Step 1: Add slug column as nullable first
            $this->addSql('ALTER TABLE tenant_db ADD slug VARCHAR(100) DEFAULT NULL');
            
            // Step 2: Populate slug from database_name
            $this->addSql('UPDATE tenant_db SET slug = CASE WHEN database_name = \'melisalacolina\' THEN \'lacolina\' WHEN database_name = \'melisa_template\' THEN \'template\' WHEN database_name = \'melisahospital\' THEN \'hospital\' ELSE LOWER(REPLACE(database_name, \'melisa\', \'\')) END WHERE slug IS NULL OR slug = \'\'');
            
            // Step 3: Now make it NOT NULL
            $this->addSql('ALTER TABLE tenant_db CHANGE slug slug VARCHAR(100) NOT NULL');
        } else {
            // Ensure existing slugs are populated
            $this->addSql('UPDATE tenant_db SET slug = CASE WHEN database_name = \'melisalacolina\' THEN \'lacolina\' WHEN database_name = \'melisa_template\' THEN \'template\' WHEN database_name = \'melisahospital\' THEN \'hospital\' ELSE LOWER(REPLACE(database_name, \'melisa\', \'\')) END WHERE slug IS NULL OR slug = \'\'');
            
            // Make sure it's NOT NULL
            $this->addSql('ALTER TABLE tenant_db CHANGE slug slug VARCHAR(100) NOT NULL');
        }
        
        // Add unique constraint if it doesn't exist
        $indexExists = $this->connection->fetchOne("SHOW INDEX FROM tenant_db WHERE Key_name = 'UNIQ_CCFEFA52989D9B62'");
        if (!$indexExists) {
            $this->addSql('CREATE UNIQUE INDEX UNIQ_CCFEFA52989D9B62 ON tenant_db (slug)');
        }
        
        // Step 4: Drop old columns and update other fields (check if columns exist first)
        $columns = $this->connection->fetchAllAssociative("SHOW COLUMNS FROM tenant_db");
        $columnNames = array_column($columns, 'Field');
        
        $dropColumns = [];
        foreach (['subdomain', 'domain', 'rut_empresa', 'host', 'host_port', 'db_user', 'db_password', 'driver', 'version', 'language', 'tenant_path'] as $col) {
            if (in_array($col, $columnNames)) {
                $dropColumns[] = "DROP $col";
            }
        }
        
        if (!empty($dropColumns)) {
            $this->addSql('ALTER TABLE tenant_db ' . implode(', ', $dropColumns) . ', CHANGE database_status database_status VARCHAR(50) NOT NULL, CHANGE is_active is_active TINYINT(1) DEFAULT 1 NOT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL');
        } else {
            $this->addSql('ALTER TABLE tenant_db CHANGE database_status database_status VARCHAR(50) NOT NULL, CHANGE is_active is_active TINYINT(1) DEFAULT 1 NOT NULL, CHANGE name name VARCHAR(255) DEFAULT NULL');
        }
        
        // Messenger messages indices update
        $messengerIndexes = $this->connection->fetchAllAssociative("SHOW INDEX FROM messenger_messages");
        $messengerIndexNames = array_column($messengerIndexes, 'Key_name');
        
        if (in_array('IDX_75EA56E0FB7336F0', $messengerIndexNames)) {
            $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0 ON messenger_messages');
        }
        if (in_array('IDX_75EA56E0E3BD61CE', $messengerIndexNames)) {
            $this->addSql('DROP INDEX IDX_75EA56E0E3BD61CE ON messenger_messages');
        }
        if (in_array('IDX_75EA56E016BA31DB', $messengerIndexNames)) {
            $this->addSql('DROP INDEX IDX_75EA56E016BA31DB ON messenger_messages');
        }
        
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        
        $compositeIndexExists = $this->connection->fetchOne("SHOW INDEX FROM messenger_messages WHERE Key_name = 'IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750'");
        if (!$compositeIndexExists) {
            $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('DROP INDEX UNIQ_CCFEFA52989D9B62 ON tenant_db');
        $this->addSql('ALTER TABLE tenant_db ADD subdomain VARCHAR(255) NOT NULL, ADD domain VARCHAR(255) DEFAULT NULL, ADD rut_empresa VARCHAR(255) DEFAULT NULL, ADD host VARCHAR(255) DEFAULT NULL, ADD host_port INT DEFAULT NULL, ADD db_user VARCHAR(255) DEFAULT NULL, ADD db_password VARCHAR(255) DEFAULT NULL, ADD driver VARCHAR(255) DEFAULT NULL, ADD version VARCHAR(255) DEFAULT NULL, ADD language VARCHAR(255) DEFAULT NULL, ADD tenant_path VARCHAR(500) DEFAULT NULL, DROP slug, CHANGE database_status database_status VARCHAR(50) DEFAULT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE is_active is_active TINYINT(1) NOT NULL');
    }
}
