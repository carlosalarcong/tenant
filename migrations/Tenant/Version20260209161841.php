<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209161841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crear tabla maintainer_role_permission para gestión dinámica de permisos de mantenedores';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE maintainer_role_permission_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE maintainer_role_permission (id INT NOT NULL, role VARCHAR(100) NOT NULL, permission VARCHAR(50) NOT NULL, granted BOOLEAN NOT NULL, category VARCHAR(50) DEFAULT NULL, maintainer VARCHAR(100) DEFAULT NULL, description VARCHAR(255) DEFAULT NULL, priority INT DEFAULT 0 NOT NULL, is_active BOOLEAN DEFAULT true NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_role ON maintainer_role_permission (role)');
        $this->addSql('CREATE INDEX idx_permission ON maintainer_role_permission (permission)');
        $this->addSql('CREATE UNIQUE INDEX unique_role_permission ON maintainer_role_permission (role, permission)');
        $this->addSql('COMMENT ON COLUMN maintainer_role_permission.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN maintainer_role_permission.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE menu_items ALTER id DROP DEFAULT');
        
        // ===== SEED: Datos iniciales de permisos =====
        // Migración de la matriz hardcoded a base de datos
        
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        
        // ROLE_ADMIN: Wildcard (*) = Acceso completo a todo
        $this->addSql("
            INSERT INTO maintainer_role_permission 
                (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at) 
            VALUES 
                (nextval('maintainer_role_permission_id_seq'), 'ROLE_ADMIN', '*', true, NULL, NULL, 'Acceso completo a todos los mantenedores', 100, true, '$now')
        ");
        
        // ROLE_MAINTAINER_MANAGER: CRUD completo + Export
        $this->addSql("
            INSERT INTO maintainer_role_permission 
                (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at) 
            VALUES 
                (nextval('maintainer_role_permission_id_seq'), 'ROLE_MAINTAINER_MANAGER', 'CREATE', true, NULL, NULL, 'Puede crear nuevos registros en mantenedores', 50, true, '$now'),
                (nextval('maintainer_role_permission_id_seq'), 'ROLE_MAINTAINER_MANAGER', 'READ', true, NULL, NULL, 'Puede leer y listar mantenedores', 50, true, '$now'),
                (nextval('maintainer_role_permission_id_seq'), 'ROLE_MAINTAINER_MANAGER', 'UPDATE', true, NULL, NULL, 'Puede editar registros existentes', 50, true, '$now'),
                (nextval('maintainer_role_permission_id_seq'), 'ROLE_MAINTAINER_MANAGER', 'DELETE', true, NULL, NULL, 'Puede eliminar registros', 50, true, '$now'),
                (nextval('maintainer_role_permission_id_seq'), 'ROLE_MAINTAINER_MANAGER', 'EXPORT', true, NULL, NULL, 'Puede exportar datos a CSV', 50, true, '$now')
        ");
        
        // ROLE_MAINTAINER_USER: Solo lectura
        $this->addSql("
            INSERT INTO maintainer_role_permission 
                (id, role, permission, granted, category, maintainer, description, priority, is_active, created_at) 
            VALUES 
                (nextval('maintainer_role_permission_id_seq'), 'ROLE_MAINTAINER_USER', 'READ', true, NULL, NULL, 'Solo puede leer mantenedores (sin modificar)', 10, true, '$now')
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE maintainer_role_permission_id_seq CASCADE');
        $this->addSql('DROP TABLE maintainer_role_permission');
        $this->addSql('CREATE SEQUENCE menu_items_id_seq');
        $this->addSql('SELECT setval(\'menu_items_id_seq\', (SELECT MAX(id) FROM menu_items))');
        $this->addSql('ALTER TABLE menu_items ALTER id SET DEFAULT nextval(\'menu_items_id_seq\')');
    }
}
