<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209210804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add soft delete support: deletedAt and deletedBy fields to all maintainer tables (references member table)';
    }

    public function up(Schema $schema): void
    {
        // Agregar deletedAt y deleted_by_id a todas las tablas de mantenedores
        // Estas son las tablas que tienen isActive y representan mantenedores
        // NOTA: deleted_by_id es INT sin FK porque User está en Main DB (cross-database)
        
        $tables = [
            'physical_exam_field', 'article_supplier', 'warehouse_medical_service', 'bank_account_type',
            'billing_item', 'credit_card_type', 'clinical_action_category', 'origin_type', 'agreement',
            'cancellation_reason', 'treatment_regimen', 'branch_payer', 'surgery_patient_status_config',
            'surgery_block_reason', 'sub_company', 'cost_center', 'warehouse', 'doctor_type',
            'article_family', 'article_subfamily', 'article_category', 'article_subcategory',
            'article_unit', 'article_vat', 'article_presentation', 'supplier', 'manufacturer',
            'supplier_contact', 'admission_mode', 'bed_type', 'branch', 'payer',
            'specialty', 'disease', 'discharge_condition', 'hospital_discharge_type',
            'operating_room', 'pre_anesthetic_consultation', 'post_surgical_control',
            'service_type', 'medical_service', 'assistance_level', 'budget_type',
            'budget_status', 'settlement_type', 'settlement_status', 'payment_method',
            'payment_condition', 'currency_type', 'nationality', 'civil_status',
            'education_level', 'occupation', 'prevision', 'identification_type', 'gender',
            'religion', 'commune', 'region', 'country', 'ethnicity', 'disability_type',
            'job_position', 'contract_type', 'professional_category', 'work_shift'
        ];
        
        foreach ($tables as $table) {
            // Verificar si la tabla existe antes de agregar columnas
            $this->addSql("
                DO $$
                BEGIN
                    IF EXISTS (SELECT FROM information_schema.tables WHERE table_name = '{$table}') THEN
                        -- Agregar deletedAt si no existe
                        IF NOT EXISTS (SELECT FROM information_schema.columns WHERE table_name = '{$table}' AND column_name = 'deleted_at') THEN
                            ALTER TABLE {$table} ADD COLUMN deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL;
                        END IF;
                        
                        -- Agregar deleted_by_id si no existe (sin FK, solo el ID del usuario)
                        IF NOT EXISTS (SELECT FROM information_schema.columns WHERE table_name = '{$table}' AND column_name = 'deleted_by_id') THEN
                            ALTER TABLE {$table} ADD COLUMN deleted_by_id INT DEFAULT NULL;
                        END IF;
                    END IF;
                END $$;
            ");
        }
    }

    public function down(Schema $schema): void
    {
        // Revertir: eliminar deletedAt y deleted_by_id de todas las tablas
        
        $tables = [
            'physical_exam_field', 'article_supplier', 'warehouse_medical_service', 'bank_account_type',
            'billing_item', 'credit_card_type', 'clinical_action_category', 'origin_type', 'agreement',
            'cancellation_reason', 'treatment_regimen', 'branch_payer', 'surgery_patient_status_config',
            'surgery_block_reason', 'sub_company', 'cost_center', 'warehouse', 'doctor_type',
            'article_family', 'article_subfamily', 'article_category', 'article_subcategory',
            'article_unit', 'article_vat', 'article_presentation', 'supplier', 'manufacturer',
            'supplier_contact', 'admission_mode', 'bed_type', 'branch', 'payer',
            'specialty', 'disease', 'discharge_condition', 'hospital_discharge_type',
            'operating_room', 'pre_anesthetic_consultation', 'post_surgical_control',
            'service_type', 'medical_service', 'assistance_level', 'budget_type',
            'budget_status', 'settlement_type', 'settlement_status', 'payment_method',
            'payment_condition', 'currency_type', 'nationality', 'civil_status',
            'education_level', 'occupation', 'prevision', 'identification_type', 'gender',
            'religion', 'commune', 'region', 'country', 'ethnicity', 'disability_type',
            'job_position', 'contract_type', 'professional_category', 'work_shift'
        ];
        
        foreach ($tables as $table) {
            $this->addSql("
                DO $$
                BEGIN
                    IF EXISTS (SELECT FROM information_schema.tables WHERE table_name = '{$table}') THEN
                        -- Eliminar columnas
                        IF EXISTS (SELECT FROM information_schema.columns WHERE table_name = '{$table}' AND column_name = 'deleted_by_id') THEN
                            ALTER TABLE {$table} DROP COLUMN deleted_by_id;
                        END IF;
                        
                        IF EXISTS (SELECT FROM information_schema.columns WHERE table_name = '{$table}' AND column_name = 'deleted_at') THEN
                            ALTER TABLE {$table} DROP COLUMN deleted_at;
                        END IF;
                    END IF;
                END $$;
            ");
        }
    }
}
