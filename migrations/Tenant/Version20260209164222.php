<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209164222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crea tabla role para gestión dinámica de roles del sistema y seed de roles iniciales';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE role_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE role (id INT NOT NULL, code VARCHAR(50) NOT NULL, name VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, position INT NOT NULL, is_active BOOLEAN NOT NULL, is_system BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_57698A6A77153098 ON role (code)');
        
        // Seed de roles iniciales del sistema
        $now = date('Y-m-d H:i:s');
        
        $this->addSql("INSERT INTO role (id, code, name, description, position, is_active, is_system, created_at) VALUES 
            (nextval('role_id_seq'), 'ROLE_ADMIN', 'Administrador', 'Acceso completo al sistema, gestión de usuarios y configuraciones', 1, true, true, '$now'),
            (nextval('role_id_seq'), 'ROLE_MAINTAINER_MANAGER', 'Gestor de Mantenedores', 'CRUD completo sobre todos los mantenedores del sistema', 2, true, true, '$now'),
            (nextval('role_id_seq'), 'ROLE_MAINTAINER_USER', 'Usuario de Mantenedores', 'Solo lectura de mantenedores', 3, true, true, '$now'),
            (nextval('role_id_seq'), 'ROLE_DOCTOR', 'Doctor/Médico', 'Profesional médico con acceso a información clínica', 4, true, true, '$now'),
            (nextval('role_id_seq'), 'ROLE_ENFERMERA', 'Enfermera', 'Personal de enfermería con acceso a información clínica', 5, true, true, '$now'),
            (nextval('role_id_seq'), 'ROLE_RECEPCION', 'Recepción', 'Personal de recepción con acceso limitado', 6, true, true, '$now'),
            (nextval('role_id_seq'), 'ROLE_CLINICAL_MANAGER', 'Gestor Clínico', 'Gestión de módulos clínicos', 7, true, true, '$now'),
            (nextval('role_id_seq'), 'ROLE_FINANCE', 'Finanzas', 'Gestión financiera y contable', 8, true, true, '$now'),
            (nextval('role_id_seq'), 'ROLE_HR', 'Recursos Humanos', 'Gestión de personal y recursos humanos', 9, true, true, '$now')
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE role_id_seq CASCADE');
        $this->addSql('DROP TABLE role');
    }
}
