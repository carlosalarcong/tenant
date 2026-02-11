<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Agrega autoincremento al campo id de menu_items
 */
final class Version20260204203000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Agrega secuencia de autoincremento al campo id de menu_items';
    }

    public function up(Schema $schema): void
    {
        // Crear secuencia si no existe
        $this->addSql('CREATE SEQUENCE IF NOT EXISTS menu_items_id_seq');
        
        // Obtener el valor máximo actual del id
        $this->addSql("SELECT setval('menu_items_id_seq', (SELECT COALESCE(MAX(id), 0) + 1 FROM menu_items), false)");
        
        // Asignar la secuencia como default del campo id
        $this->addSql("ALTER TABLE menu_items ALTER COLUMN id SET DEFAULT nextval('menu_items_id_seq')");
        
        // Asignar la secuencia como propiedad del campo (para que se elimine con la tabla)
        $this->addSql("ALTER SEQUENCE menu_items_id_seq OWNED BY menu_items.id");
    }

    public function down(Schema $schema): void
    {
        // Remover el default
        $this->addSql('ALTER TABLE menu_items ALTER COLUMN id DROP DEFAULT');
        
        // Eliminar la secuencia
        $this->addSql('DROP SEQUENCE IF EXISTS menu_items_id_seq');
    }
}
