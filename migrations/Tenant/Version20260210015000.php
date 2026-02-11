<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Audit Trail Migration: Add createdAt, createdBy, updatedAt, updatedBy to all maintainer tables
 */
final class Version20260210015000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add audit trail support: createdAt, createdBy, updatedAt, updatedBy to all maintainer tables';
    }

    public function up(Schema $schema): void
    {
        // Agregar columnas de audit trail a todas las tablas de mantenedores
        // createdAt (required), createdBy, updatedAt, updatedBy
        // NOTA: *By son INT sin FK porque Member está en tenant DB
        
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
                        -- Agregar createdAt si no existe (requerido, default NOW())
                        IF NOT EXISTS (SELECT FROM information_schema.columns WHERE table_name = '{$table}' AND column_name = 'created_at') THEN
                            ALTER TABLE {$table} ADD COLUMN created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP;
                        END IF;
                        
                        -- Agregar created_by si no existe
                        IF NOT EXISTS (SELECT FROM information_schema.columns WHERE table_name = '{$table}' AND column_name = 'created_by') THEN
                            ALTER TABLE {$table} ADD COLUMN created_by INT DEFAULT NULL;
                        END IF;
                        
                        -- Agregar updatedAt si no existe
                        IF NOT EXISTS (SELECT FROM information_schema.columns WHERE table_name = '{$table}' AND column_name = 'updated_at') THEN
                            ALTER TABLE {$table} ADD COLUMN updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL;
                        END IF;
                        
                        -- Agregar updated_by si no existe
                        IF NOT EXISTS (SELECT FROM information_schema.columns WHERE table_name = '{$table}' AND column_name = 'updated_by') THEN
                            ALTER TABLE {$table} ADD COLUMN updated_by INT DEFAULT NULL;
                        END IF;
                    END IF;
                END $$;
            ");
        }
    }

    public function down(Schema $schema): void
    {
        // Revertir: eliminar columnas de audit trail
        
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
                        IF EXISTS (SELECT FROM information_schema.columns WHERE table_name = '{$table}' AND column_name = 'updated_by') THEN
                            ALTER TABLE {$table} DROP COLUMN updated_by;
                        END IF;
                        
                        IF EXISTS (SELECT FROM information_schema.columns WHERE table_name = '{$table}' AND column_name = 'updated_at') THEN
                            ALTER TABLE {$table} DROP COLUMN updated_at;
                        END IF;
                        
                        IF EXISTS (SELECT FROM information_schema.columns WHERE table_name = '{$table}' AND column_name = 'created_by') THEN
                            ALTER TABLE {$table} DROP COLUMN created_by;
                        END IF;
                        
                        IF EXISTS (SELECT FROM information_schema.columns WHERE table_name = '{$table}' AND column_name = 'created_at') THEN
                            ALTER TABLE {$table} DROP COLUMN created_at;
                        END IF;
                    END IF;
                END $$;
            ");
        }
    }
}
