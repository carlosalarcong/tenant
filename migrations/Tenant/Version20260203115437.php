<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260203115437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE article_outflow_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE inventory_adjustment_reason_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE product_condition_type_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE article_outflow_type (id INT NOT NULL, name VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE inventory_adjustment_reason (id INT NOT NULL, name VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE product_condition_type (id INT NOT NULL, name VARCHAR(100) NOT NULL, is_active BOOLEAN NOT NULL, id_estado INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE bank ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE bank_account_type ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE billing_payment_method ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE branch ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE cash_register_location ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE credit_card ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE credit_card_type ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE currency_type ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE difference_direction ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE difference_reason ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE difference_type ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE document_type ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE gratuity_reason ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE gratuity_type ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE payment_condition ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE payment_method ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE payment_method_type ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE transfer_indicator ALTER id DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE article_outflow_type_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE inventory_adjustment_reason_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE product_condition_type_id_seq CASCADE');
        $this->addSql('DROP TABLE article_outflow_type');
        $this->addSql('DROP TABLE inventory_adjustment_reason');
        $this->addSql('DROP TABLE product_condition_type');
        $this->addSql('CREATE SEQUENCE bank_id_seq');
        $this->addSql('SELECT setval(\'bank_id_seq\', (SELECT MAX(id) FROM bank))');
        $this->addSql('ALTER TABLE bank ALTER id SET DEFAULT nextval(\'bank_id_seq\')');
        $this->addSql('CREATE SEQUENCE cash_register_location_id_seq');
        $this->addSql('SELECT setval(\'cash_register_location_id_seq\', (SELECT MAX(id) FROM cash_register_location))');
        $this->addSql('ALTER TABLE cash_register_location ALTER id SET DEFAULT nextval(\'cash_register_location_id_seq\')');
        $this->addSql('CREATE SEQUENCE credit_card_type_id_seq');
        $this->addSql('SELECT setval(\'credit_card_type_id_seq\', (SELECT MAX(id) FROM credit_card_type))');
        $this->addSql('ALTER TABLE credit_card_type ALTER id SET DEFAULT nextval(\'credit_card_type_id_seq\')');
        $this->addSql('CREATE SEQUENCE document_type_id_seq');
        $this->addSql('SELECT setval(\'document_type_id_seq\', (SELECT MAX(id) FROM document_type))');
        $this->addSql('ALTER TABLE document_type ALTER id SET DEFAULT nextval(\'document_type_id_seq\')');
        $this->addSql('CREATE SEQUENCE bank_account_type_id_seq');
        $this->addSql('SELECT setval(\'bank_account_type_id_seq\', (SELECT MAX(id) FROM bank_account_type))');
        $this->addSql('ALTER TABLE bank_account_type ALTER id SET DEFAULT nextval(\'bank_account_type_id_seq\')');
        $this->addSql('CREATE SEQUENCE credit_card_id_seq');
        $this->addSql('SELECT setval(\'credit_card_id_seq\', (SELECT MAX(id) FROM credit_card))');
        $this->addSql('ALTER TABLE credit_card ALTER id SET DEFAULT nextval(\'credit_card_id_seq\')');
        $this->addSql('CREATE SEQUENCE payment_condition_id_seq');
        $this->addSql('SELECT setval(\'payment_condition_id_seq\', (SELECT MAX(id) FROM payment_condition))');
        $this->addSql('ALTER TABLE payment_condition ALTER id SET DEFAULT nextval(\'payment_condition_id_seq\')');
        $this->addSql('CREATE SEQUENCE payment_method_type_id_seq');
        $this->addSql('SELECT setval(\'payment_method_type_id_seq\', (SELECT MAX(id) FROM payment_method_type))');
        $this->addSql('ALTER TABLE payment_method_type ALTER id SET DEFAULT nextval(\'payment_method_type_id_seq\')');
        $this->addSql('CREATE SEQUENCE billing_payment_method_id_seq');
        $this->addSql('SELECT setval(\'billing_payment_method_id_seq\', (SELECT MAX(id) FROM billing_payment_method))');
        $this->addSql('ALTER TABLE billing_payment_method ALTER id SET DEFAULT nextval(\'billing_payment_method_id_seq\')');
        $this->addSql('CREATE SEQUENCE transfer_indicator_id_seq');
        $this->addSql('SELECT setval(\'transfer_indicator_id_seq\', (SELECT MAX(id) FROM transfer_indicator))');
        $this->addSql('ALTER TABLE transfer_indicator ALTER id SET DEFAULT nextval(\'transfer_indicator_id_seq\')');
        $this->addSql('CREATE SEQUENCE difference_direction_id_seq');
        $this->addSql('SELECT setval(\'difference_direction_id_seq\', (SELECT MAX(id) FROM difference_direction))');
        $this->addSql('ALTER TABLE difference_direction ALTER id SET DEFAULT nextval(\'difference_direction_id_seq\')');
        $this->addSql('CREATE SEQUENCE payment_method_id_seq');
        $this->addSql('SELECT setval(\'payment_method_id_seq\', (SELECT MAX(id) FROM payment_method))');
        $this->addSql('ALTER TABLE payment_method ALTER id SET DEFAULT nextval(\'payment_method_id_seq\')');
        $this->addSql('CREATE SEQUENCE gratuity_type_id_seq');
        $this->addSql('SELECT setval(\'gratuity_type_id_seq\', (SELECT MAX(id) FROM gratuity_type))');
        $this->addSql('ALTER TABLE gratuity_type ALTER id SET DEFAULT nextval(\'gratuity_type_id_seq\')');
        $this->addSql('CREATE SEQUENCE gratuity_reason_id_seq');
        $this->addSql('SELECT setval(\'gratuity_reason_id_seq\', (SELECT MAX(id) FROM gratuity_reason))');
        $this->addSql('ALTER TABLE gratuity_reason ALTER id SET DEFAULT nextval(\'gratuity_reason_id_seq\')');
        $this->addSql('CREATE SEQUENCE difference_type_id_seq');
        $this->addSql('SELECT setval(\'difference_type_id_seq\', (SELECT MAX(id) FROM difference_type))');
        $this->addSql('ALTER TABLE difference_type ALTER id SET DEFAULT nextval(\'difference_type_id_seq\')');
        $this->addSql('CREATE SEQUENCE branch_id_seq');
        $this->addSql('SELECT setval(\'branch_id_seq\', (SELECT MAX(id) FROM branch))');
        $this->addSql('ALTER TABLE branch ALTER id SET DEFAULT nextval(\'branch_id_seq\')');
        $this->addSql('CREATE SEQUENCE currency_type_id_seq');
        $this->addSql('SELECT setval(\'currency_type_id_seq\', (SELECT MAX(id) FROM currency_type))');
        $this->addSql('ALTER TABLE currency_type ALTER id SET DEFAULT nextval(\'currency_type_id_seq\')');
        $this->addSql('CREATE SEQUENCE difference_reason_id_seq');
        $this->addSql('SELECT setval(\'difference_reason_id_seq\', (SELECT MAX(id) FROM difference_reason))');
        $this->addSql('ALTER TABLE difference_reason ALTER id SET DEFAULT nextval(\'difference_reason_id_seq\')');
    }
}
