#!/bin/bash

# Script para insertar datos de prueba en los 24 mantenedores de Hospitalaria
# Se ejecuta en los 3 tenants: melisalacolina, melisahospital, melisa_template

set -e

DB_USER="melisa"
DB_HOST="localhost"
export PGPASSWORD="melisamelisa"

TENANTS=("melisalacolina" "melisahospital" "melisa_template")

echo "🏥 Insertando datos de prueba en mantenedores Hospitalaria..."

for TENANT in "${TENANTS[@]}"; do
    echo ""
    echo "📦 Procesando tenant: $TENANT"
    
    psql -h $DB_HOST -U $DB_USER -d $TENANT <<'EOF'
-- ============================================
-- FASE 1: 12 Mantenedores Simples
-- ============================================

-- 1. Medical Device (Dispositivos Médicos RCH)
INSERT INTO medical_device (name, is_active, id_estado, created_at) VALUES
('Catéter Venoso Central', true, 1, NOW()),
('Sonda Foley', true, 1, NOW()),
('Tubo Endotraqueal', true, 1, NOW()),
('Drenaje Torácico', true, 1, NOW()),
('Monitor Cardíaco', true, 1, NOW())
ON CONFLICT DO NOTHING;

-- 2. Intoxication State (Estados de Ebriedad)
INSERT INTO intoxication_state (name, is_active, id_estado, created_at) VALUES
('Sobrio', true, 1, NOW()),
('Aliento Alcohólico', true, 1, NOW()),
('Ebriedad Leve', true, 1, NOW()),
('Ebriedad Moderada', true, 1, NOW()),
('Ebriedad Grave', true, 1, NOW())
ON CONFLICT DO NOTHING;

-- 3. Care Closure Destination (Destinos Cierre Atención)
INSERT INTO care_closure_destination (name, is_active, id_estado, created_at) VALUES
('Alta Médica', true, 1, NOW()),
('Traslado a Otro Centro', true, 1, NOW()),
('Hospitalización', true, 1, NOW()),
('Defunción', true, 1, NOW()),
('Fuga', true, 1, NOW())
ON CONFLICT DO NOTHING;

-- 4. Dosage Type (Tipos de Posología)
INSERT INTO dosage_type (name, is_active, id_estado, created_at) VALUES
('Dosis Única', true, 1, NOW()),
('Cada 8 horas', true, 1, NOW()),
('Cada 12 horas', true, 1, NOW()),
('Cada 24 horas', true, 1, NOW()),
('Según Necesidad (PRN)', true, 1, NOW())
ON CONFLICT DO NOTHING;

-- 5. Prescription Type (Tipos de Receta Fármacos)
INSERT INTO prescription_type (name, is_active, id_estado, created_at) VALUES
('Receta Simple', true, 1, NOW()),
('Receta Retenida', true, 1, NOW()),
('Receta Cheque', true, 1, NOW()),
('Receta Magistral', true, 1, NOW())
ON CONFLICT DO NOTHING;

-- 6. Care Category (Categorías de Cuidados RCH)
INSERT INTO care_category (name, is_active, id_estado, created_at) VALUES
('Higiene y Confort', true, 1, NOW()),
('Alimentación', true, 1, NOW()),
('Eliminación', true, 1, NOW()),
('Movilización', true, 1, NOW()),
('Signos Vitales', true, 1, NOW()),
('Administración Medicamentos', true, 1, NOW())
ON CONFLICT DO NOTHING;

-- 7. Nutritionist BMI Index (Índices IMC)
INSERT INTO nutritionist_bmi_index (name, is_active, id_estado, created_at) VALUES
('Bajo Peso Severo (<16)', true, 1, NOW()),
('Bajo Peso (16-18.5)', true, 1, NOW()),
('Normal (18.5-24.9)', true, 1, NOW()),
('Sobrepeso (25-29.9)', true, 1, NOW()),
('Obesidad Grado I (30-34.9)', true, 1, NOW()),
('Obesidad Grado II (35-39.9)', true, 1, NOW()),
('Obesidad Grado III (≥40)', true, 1, NOW())
ON CONFLICT DO NOTHING;

-- 8. Nutritionist TE Index (Índices Talla/Edad)
INSERT INTO nutritionist_te_index (name, is_active, id_estado, created_at) VALUES
('Talla Normal', true, 1, NOW()),
('Riesgo Talla Baja', true, 1, NOW()),
('Talla Baja', true, 1, NOW()),
('Talla Baja Severa', true, 1, NOW())
ON CONFLICT DO NOTHING;

-- 9. Nutritionist Index Classification (Clasificaciones Índices)
INSERT INTO nutritionist_index_classification (name, is_active, id_estado, created_at) VALUES
('Eutrofia', true, 1, NOW()),
('Riesgo Desnutrición', true, 1, NOW()),
('Desnutrición Leve', true, 1, NOW()),
('Desnutrición Moderada', true, 1, NOW()),
('Desnutrición Severa', true, 1, NOW()),
('Sobrepeso', true, 1, NOW()),
('Obesidad', true, 1, NOW())
ON CONFLICT DO NOTHING;

-- 10. Nutritional Diagnosis (Diagnósticos Nutricionales)
INSERT INTO nutritional_diagnosis (name, is_active, id_estado, created_at) VALUES
('Déficit Calórico', true, 1, NOW()),
('Déficit Proteico', true, 1, NOW()),
('Exceso Calórico', true, 1, NOW()),
('Déficit Vitamina D', true, 1, NOW()),
('Déficit Hierro', true, 1, NOW()),
('Estado Nutricional Normal', true, 1, NOW())
ON CONFLICT DO NOTHING;

-- 11. Eating Disorder History (Antecedentes TCA)
INSERT INTO eating_disorder_history (name, is_active, id_estado, created_at) VALUES
('Sin Antecedentes', true, 1, NOW()),
('Anorexia Nerviosa', true, 1, NOW()),
('Bulimia Nerviosa', true, 1, NOW()),
('Trastorno por Atracón', true, 1, NOW()),
('EDNOS', true, 1, NOW())
ON CONFLICT DO NOTHING;

-- 12. Clinical Action Category (Categorías Acción Clínica)
INSERT INTO clinical_action_category (name, is_active, id_estado, created_at) VALUES
('Evaluación Inicial', true, 1, NOW()),
('Anamnesis', true, 1, NOW()),
('Examen Físico', true, 1, NOW()),
('Diagnóstico', true, 1, NOW()),
('Tratamiento', true, 1, NOW()),
('Seguimiento', true, 1, NOW())
ON CONFLICT DO NOTHING;

-- ============================================
-- FASE 2: 6 Mantenedores Simple+
-- ============================================

-- 13. Prescription Frequency (Frecuencias de Receta)
INSERT INTO prescription_frequency (name, quantity, is_active, id_estado, created_at) VALUES
('Cada 4 horas', 6, true, 1, NOW()),
('Cada 6 horas', 4, true, 1, NOW()),
('Cada 8 horas', 3, true, 1, NOW()),
('Cada 12 horas', 2, true, 1, NOW()),
('Cada 24 horas', 1, true, 1, NOW()),
('Según necesidad', 0, true, 1, NOW())
ON CONFLICT DO NOTHING;

-- 14. Prescription Route (Vías de Administración)
INSERT INTO prescription_route (name, sort_order, is_active, id_estado, created_at) VALUES
('Oral', 1, true, 1, NOW()),
('Intravenosa', 2, true, 1, NOW()),
('Intramuscular', 3, true, 1, NOW()),
('Subcutánea', 4, true, 1, NOW()),
('Tópica', 5, true, 1, NOW()),
('Rectal', 6, true, 1, NOW()),
('Inhalatoria', 7, true, 1, NOW())
ON CONFLICT DO NOTHING;

-- 15. Prescription Dosage (Dosis de Receta)
INSERT INTO prescription_dosage (name, quantity, is_active, id_estado, created_at) VALUES
('500 mg', 500.00, true, 1, NOW()),
('1 g', 1000.00, true, 1, NOW()),
('5 ml', 5.00, true, 1, NOW()),
('10 ml', 10.00, true, 1, NOW()),
('250 mg', 250.00, true, 1, NOW())
ON CONFLICT DO NOTHING;

-- 16. Prescription Format (Formatos de Receta)
INSERT INTO prescription_format (name, sort_order, is_active, id_estado, created_at) VALUES
('Comprimido', 1, true, 1, NOW()),
('Cápsula', 2, true, 1, NOW()),
('Jarabe', 3, true, 1, NOW()),
('Suspensión', 4, true, 1, NOW()),
('Inyectable', 5, true, 1, NOW()),
('Pomada', 6, true, 1, NOW())
ON CONFLICT DO NOTHING;

-- 17. Prescription Dispensation (Dispensaciones de Receta)
INSERT INTO prescription_dispensation (name, sort_order, quantity, time_unit, is_active, id_estado, created_at) VALUES
('Dosis diaria', 1, 1, 'días', true, 1, NOW()),
('Dosis semanal', 2, 7, 'días', true, 1, NOW()),
('Dosis mensual', 3, 30, 'días', true, 1, NOW()),
('Dosis según indicación', 4, 0, NULL, true, 1, NOW())
ON CONFLICT DO NOTHING;

-- 18. Physical Exam Grouping (Agrupaciones Examen Físico)
INSERT INTO physical_exam_grouping (name, sort_order, is_active, id_estado, created_at) VALUES
('Cabeza y Cuello', 1, true, 1, NOW()),
('Tórax', 2, true, 1, NOW()),
('Abdomen', 3, true, 1, NOW()),
('Extremidades', 4, true, 1, NOW()),
('Signos Vitales', 5, true, 1, NOW()),
('Neurológico', 6, true, 1, NOW())
ON CONFLICT DO NOTHING;

-- ============================================
-- FASE 3: 3 Mantenedores Moderados (con FK)
-- ============================================

-- 19. Care Intervention (Cuidados Clínicos)
DO $$
DECLARE
    categoria_id INT;
BEGIN
    SELECT id INTO categoria_id FROM care_category WHERE name = 'Higiene y Confort' LIMIT 1;
    IF categoria_id IS NOT NULL THEN
        INSERT INTO care_intervention (description, care_category_id, is_active, id_estado, created_at) VALUES
        ('Baño de esponja', categoria_id, true, 1, NOW()),
        ('Cambio de ropa de cama', categoria_id, true, 1, NOW()),
        ('Aseo bucal', categoria_id, true, 1, NOW())
        ON CONFLICT DO NOTHING;
    END IF;

    SELECT id INTO categoria_id FROM care_category WHERE name = 'Signos Vitales' LIMIT 1;
    IF categoria_id IS NOT NULL THEN
        INSERT INTO care_intervention (description, care_category_id, is_active, id_estado, created_at) VALUES
        ('Control de presión arterial', categoria_id, true, 1, NOW()),
        ('Monitoreo de temperatura', categoria_id, true, 1, NOW())
        ON CONFLICT DO NOTHING;
    END IF;
END $$;

-- 20. Clinical Action Question (Preguntas Acción Clínica)
DO $$
DECLARE
    categoria_id INT;
BEGIN
    SELECT id INTO categoria_id FROM clinical_action_category WHERE name = 'Anamnesis' LIMIT 1;
    IF categoria_id IS NOT NULL THEN
        INSERT INTO clinical_action_question (name, sort_order, range_min, is_required, clinical_action_category_id, is_active, id_estado, created_at) VALUES
        ('¿Motivo de consulta?', 1, 0, true, categoria_id, true, 1, NOW()),
        ('¿Antecedentes mórbidos?', 2, 0, true, categoria_id, true, 1, NOW()),
        ('¿Alergias medicamentosas?', 3, 0, true, categoria_id, true, 1, NOW())
        ON CONFLICT DO NOTHING;
    END IF;

    SELECT id INTO categoria_id FROM clinical_action_category WHERE name = 'Examen Físico' LIMIT 1;
    IF categoria_id IS NOT NULL THEN
        INSERT INTO clinical_action_question (name, sort_order, range_min, is_required, clinical_action_category_id, is_active, id_estado, created_at) VALUES
        ('Estado general', 1, 0, true, categoria_id, true, 1, NOW())
        ON CONFLICT DO NOTHING;
    END IF;
END $$;

-- 21. Clinical Action Answer (Respuestas Acción Clínica)
DO $$
DECLARE
    pregunta_id INT;
BEGIN
    SELECT id INTO pregunta_id FROM clinical_action_question WHERE name = '¿Motivo de consulta?' LIMIT 1;
    IF pregunta_id IS NOT NULL THEN
        INSERT INTO clinical_action_answer (sort_order, pre_text, clinical_action_question_id, is_active, id_estado, created_at) VALUES
        (1, 'Dolor', pregunta_id, true, 1, NOW()),
        (2, 'Fiebre', pregunta_id, true, 1, NOW()),
        (3, 'Malestar general', pregunta_id, true, 1, NOW())
        ON CONFLICT DO NOTHING;
    END IF;

    SELECT id INTO pregunta_id FROM clinical_action_question WHERE name = 'Estado general' LIMIT 1;
    IF pregunta_id IS NOT NULL THEN
        INSERT INTO clinical_action_answer (sort_order, pre_text, clinical_action_question_id, is_active, id_estado, created_at) VALUES
        (1, 'Bueno', pregunta_id, true, 1, NOW()),
        (2, 'Regular', pregunta_id, true, 1, NOW()),
        (3, 'Malo', pregunta_id, true, 1, NOW())
        ON CONFLICT DO NOTHING;
    END IF;
END $$;

-- ============================================
-- FASE 4: 3 Mantenedores Complejos
-- ============================================

-- 22. Physical Exam Base Field (Campos Base Examen Físico)
INSERT INTO physical_exam_base_field (name, description, sort_order, field_type, is_required, is_active, id_estado, created_at) VALUES
('Peso', 'Peso corporal en kilogramos', 1, 'number', true, true, 1, NOW()),
('Talla', 'Estatura en centímetros', 2, 'number', true, true, 1, NOW()),
('IMC', 'Índice de masa corporal', 3, 'number', false, true, 1, NOW()),
('Frecuencia Cardíaca', 'Latidos por minuto', 4, 'number', true, true, 1, NOW()),
('Presión Arterial', 'Sistólica/Diastólica', 5, 'text', true, true, 1, NOW())
ON CONFLICT DO NOTHING;

-- 23. Physical Exam Field (Campos Examen Físico)
DO $$
DECLARE
    group1_id INT;
    group2_id INT;
BEGIN
    SELECT id INTO group1_id FROM physical_exam_grouping WHERE name = 'Signos Vitales' LIMIT 1;
    
    IF group1_id IS NOT NULL THEN
        INSERT INTO physical_exam_field (name, sort_order, unit, is_weight, is_temperature, grouping1_id, is_active, id_estado, created_at) VALUES
        ('Peso', 1, 'kg', true, false, group1_id, true, 1, NOW()),
        ('Temperatura', 2, '°C', false, true, group1_id, true, 1, NOW()),
        ('FC', 3, 'lpm', false, false, group1_id, true, 1, NOW())
        ON CONFLICT DO NOTHING;
    END IF;

    SELECT id INTO group1_id FROM physical_exam_grouping WHERE name = 'Tórax' LIMIT 1;
    IF group1_id IS NOT NULL THEN
        INSERT INTO physical_exam_field (name, description, sort_order, grouping1_id, is_active, id_estado, created_at) VALUES
        ('Auscultación', 'Ruidos pulmonares', 1, group1_id, true, 1, NOW())
        ON CONFLICT DO NOTHING;
    END IF;
END $$;

-- 24. Prescription Rule Detail (Reglas de Prescripción)
INSERT INTO prescription_rule_detail (intervals, daily_quantity, is_active, id_estado, created_at) VALUES
('08:00,14:00,20:00', 3, true, 1, NOW()),
('08:00,20:00', 2, true, 1, NOW()),
('08:00,12:00,16:00,20:00', 4, true, 1, NOW()),
('09:00', 1, true, 1, NOW()),
('08:00,12:00,18:00,22:00', 4, true, 1, NOW())
ON CONFLICT DO NOTHING;

EOF

    if [ $? -eq 0 ]; then
        echo "  ✅ Datos de prueba insertados exitosamente en $TENANT"
    else
        echo "  ❌ Error insertando datos en $TENANT"
    fi
done

echo ""
echo "✨ Proceso completado. Datos de prueba insertados en 24 mantenedores en 3 tenants."
echo ""
echo "📊 Resumen de registros por mantenedor:"
echo "  - Fase 1 (12 mantenedores simples): ~5-7 registros cada uno"
echo "  - Fase 2 (6 mantenedores simple+): ~4-7 registros cada uno"
echo "  - Fase 3 (3 mantenedores moderados): ~3-5 registros cada uno (con FK)"
echo "  - Fase 4 (3 mantenedores complejos): ~3-5 registros cada uno"
echo ""
echo "Total aproximado: ~120 registros de prueba por tenant"
