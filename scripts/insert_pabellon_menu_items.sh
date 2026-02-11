#!/bin/bash

# Insertar items de menú de Pabellón en las bases de datos de tenant
for DB in melisalacolina melisa_template melisahospital; do
    echo "========================================="
    echo "Procesando base de datos: $DB"
    echo "========================================="
    
    PGPASSWORD=melisamelisa psql -h localhost -U melisa -d "$DB" <<'EOF'
    DO $$
    DECLARE
        pabellon_id INT;
        max_id INT;
        max_pos INT;
    BEGIN
        -- 1. Crear categoría Pabellón si no existe
        IF NOT EXISTS (SELECT 1 FROM menu_items WHERE name = 'pabellon' AND parent_id = 4) THEN
            SELECT COALESCE(MAX(id), 0) INTO max_id FROM menu_items;
            SELECT COALESCE(MAX(position), -1) INTO max_pos FROM menu_items WHERE parent_id = 4;
            INSERT INTO menu_items (id, parent_id, name, label, route, icon, position, enabled, visible_in_sidebar, requires_auth, created_at)
            VALUES (max_id + 1, 4, 'pabellon', 'Pabellón', NULL, 'bx bx-pulse', max_pos + 1, true, true, true, NOW());
        END IF;
        
        -- 2. Obtener ID de Pabellón
        SELECT id INTO pabellon_id FROM menu_items WHERE name = 'pabellon' AND parent_id = 4;
        
        -- 3. Función helper para insertar items
        -- Item 1: Tipos de Anestesia
        IF NOT EXISTS (SELECT 1 FROM menu_items WHERE route = 'app_maintainers_surgery_anesthesia_type_index') THEN
            SELECT COALESCE(MAX(id), 0) INTO max_id FROM menu_items;
            SELECT COALESCE(MAX(position), -1) INTO max_pos FROM menu_items WHERE parent_id = pabellon_id;
            INSERT INTO menu_items (id, parent_id, name, label, route, icon, position, enabled, visible_in_sidebar, requires_auth, created_at)
            VALUES (max_id + 1, pabellon_id, 'anesthesia_type', 'Tipos de Anestesia', 'app_maintainers_surgery_anesthesia_type_index', 'bx bx-injection', max_pos + 1, true, true, true, NOW());
        END IF;
        
        -- Item 2: Grupos Sanguíneos
        IF NOT EXISTS (SELECT 1 FROM menu_items WHERE route = 'app_maintainers_surgery_blood_type_index') THEN
            SELECT COALESCE(MAX(id), 0) INTO max_id FROM menu_items;
            SELECT COALESCE(MAX(position), -1) INTO max_pos FROM menu_items WHERE parent_id = pabellon_id;
            INSERT INTO menu_items (id, parent_id, name, label, route, icon, position, enabled, visible_in_sidebar, requires_auth, created_at)
            VALUES (max_id + 1, pabellon_id, 'blood_type', 'Grupos Sanguíneos', 'app_maintainers_surgery_blood_type_index', 'bx bx-donate-blood', max_pos + 1, true, true, true, NOW());
        END IF;
        
        -- Item 3: Causas de Suspensión
        IF NOT EXISTS (SELECT 1 FROM menu_items WHERE route = 'app_maintainers_surgery_surgery_suspension_cause_index') THEN
            SELECT COALESCE(MAX(id), 0) INTO max_id FROM menu_items;
            SELECT COALESCE(MAX(position), -1) INTO max_pos FROM menu_items WHERE parent_id = pabellon_id;
            INSERT INTO menu_items (id, parent_id, name, label, route, icon, position, enabled, visible_in_sidebar, requires_auth, created_at)
            VALUES (max_id + 1, pabellon_id, 'surgery_suspension_cause', 'Causas de Suspensión', 'app_maintainers_surgery_surgery_suspension_cause_index', 'bx bx-calendar-x', max_pos + 1, true, true, true, NOW());
        END IF;
        
        -- Item 4: Motivos de Bloqueo
        IF NOT EXISTS (SELECT 1 FROM menu_items WHERE route = 'app_maintainers_surgery_surgery_block_reason_index') THEN
            SELECT COALESCE(MAX(id), 0) INTO max_id FROM menu_items;
            SELECT COALESCE(MAX(position), -1) INTO max_pos FROM menu_items WHERE parent_id = pabellon_id;
            INSERT INTO menu_items (id, parent_id, name, label, route, icon, position, enabled, visible_in_sidebar, requires_auth, created_at)
            VALUES (max_id + 1, pabellon_id, 'surgery_block_reason', 'Motivos de Bloqueo', 'app_maintainers_surgery_surgery_block_reason_index', 'bx bx-block', max_pos + 1, true, true, true, NOW());
        END IF;
        
        -- Item 5: Motivos de Anulación
        IF NOT EXISTS (SELECT 1 FROM menu_items WHERE route = 'app_maintainers_surgery_surgery_cancellation_reason_index') THEN
            SELECT COALESCE(MAX(id), 0) INTO max_id FROM menu_items;
            SELECT COALESCE(MAX(position), -1) INTO max_pos FROM menu_items WHERE parent_id = pabellon_id;
            INSERT INTO menu_items (id, parent_id, name, label, route, icon, position, enabled, visible_in_sidebar, requires_auth, created_at)
            VALUES (max_id + 1, pabellon_id, 'surgery_cancellation_reason', 'Motivos de Anulación', 'app_maintainers_surgery_surgery_cancellation_reason_index', 'bx bx-x-circle', max_pos + 1, true, true, true, NOW());
        END IF;
        
        -- Item 6: Tipos de Herida
        IF NOT EXISTS (SELECT 1 FROM menu_items WHERE route = 'app_maintainers_surgery_wound_type_index') THEN
            SELECT COALESCE(MAX(id), 0) INTO max_id FROM menu_items;
            SELECT COALESCE(MAX(position), -1) INTO max_pos FROM menu_items WHERE parent_id = pabellon_id;
            INSERT INTO menu_items (id, parent_id, name, label, route, icon, position, enabled, visible_in_sidebar, requires_auth, created_at)
            VALUES (max_id + 1, pabellon_id, 'wound_type', 'Tipos de Herida', 'app_maintainers_surgery_wound_type_index', 'bx bx-band-aid', max_pos + 1, true, true, true, NOW());
        END IF;
        
        -- Item 7: Estados de Paciente
        IF NOT EXISTS (SELECT 1 FROM menu_items WHERE route = 'app_maintainers_surgery_surgery_patient_status_index') THEN
            SELECT COALESCE(MAX(id), 0) INTO max_id FROM menu_items;
            SELECT COALESCE(MAX(position), -1) INTO max_pos FROM menu_items WHERE parent_id = pabellon_id;
            INSERT INTO menu_items (id, parent_id, name, label, route, icon, position, enabled, visible_in_sidebar, requires_auth, created_at)
            VALUES (max_id + 1, pabellon_id, 'surgery_patient_status', 'Estados de Paciente', 'app_maintainers_surgery_surgery_patient_status_index', 'bx bx-user-check', max_pos + 1, true, true, true, NOW());
        END IF;
        
        -- Item 8: Config. Estados Paciente
        IF NOT EXISTS (SELECT 1 FROM menu_items WHERE route = 'app_maintainers_surgery_surgery_patient_status_config_index') THEN
            SELECT COALESCE(MAX(id), 0) INTO max_id FROM menu_items;
            SELECT COALESCE(MAX(position), -1) INTO max_pos FROM menu_items WHERE parent_id = pabellon_id;
            INSERT INTO menu_items (id, parent_id, name, label, route, icon, position, enabled, visible_in_sidebar, requires_auth, created_at)
            VALUES (max_id + 1, pabellon_id, 'surgery_patient_status_config', 'Config. Estados Paciente', 'app_maintainers_surgery_surgery_patient_status_config_index', 'bx bx-cog', max_pos + 1, true, true, true, NOW());
        END IF;
        
        -- Item 9: Regímenes de Tratamiento
        IF NOT EXISTS (SELECT 1 FROM menu_items WHERE route = 'app_maintainers_surgery_treatment_regimen_index') THEN
            SELECT COALESCE(MAX(id), 0) INTO max_id FROM menu_items;
            SELECT COALESCE(MAX(position), -1) INTO max_pos FROM menu_items WHERE parent_id = pabellon_id;
            INSERT INTO menu_items (id, parent_id, name, label, route, icon, position, enabled, visible_in_sidebar, requires_auth, created_at)
            VALUES (max_id + 1, pabellon_id, 'treatment_regimen', 'Regímenes de Tratamiento', 'app_maintainers_surgery_treatment_regimen_index', 'bx bx-list-check', max_pos + 1, true, true, true, NOW());
        END IF;
        
        -- Item 10: Roles Equipo Quirúrgico
        IF NOT EXISTS (SELECT 1 FROM menu_items WHERE route = 'app_maintainers_surgery_surgical_team_role_index') THEN
            SELECT COALESCE(MAX(id), 0) INTO max_id FROM menu_items;
            SELECT COALESCE(MAX(position), -1) INTO max_pos FROM menu_items WHERE parent_id = pabellon_id;
            INSERT INTO menu_items (id, parent_id, name, label, route, icon, position, enabled, visible_in_sidebar, requires_auth, created_at)
            VALUES (max_id + 1, pabellon_id, 'surgical_team_role', 'Roles Equipo Quirúrgico', 'app_maintainers_surgery_surgical_team_role_index', 'bx bx-group', max_pos + 1, true, true, true, NOW());
        END IF;
        
        -- Item 11: Pabellones
        IF NOT EXISTS (SELECT 1 FROM menu_items WHERE route = 'app_maintainers_surgery_surgical_block_index') THEN
            SELECT COALESCE(MAX(id), 0) INTO max_id FROM menu_items;
            SELECT COALESCE(MAX(position), -1) INTO max_pos FROM menu_items WHERE parent_id = pabellon_id;
            INSERT INTO menu_items (id, parent_id, name, label, route, icon, position, enabled, visible_in_sidebar, requires_auth, created_at)
            VALUES (max_id + 1, pabellon_id, 'surgical_block', 'Pabellones', 'app_maintainers_surgery_surgical_block_index', 'bx bx-door-open', max_pos + 1, true, true, true, NOW());
        END IF;
        
        -- Item 12: Etapas Quirúrgicas
        IF NOT EXISTS (SELECT 1 FROM menu_items WHERE route = 'app_maintainers_surgery_surgical_stage_index') THEN
            SELECT COALESCE(MAX(id), 0) INTO max_id FROM menu_items;
            SELECT COALESCE(MAX(position), -1) INTO max_pos FROM menu_items WHERE parent_id = pabellon_id;
            INSERT INTO menu_items (id, parent_id, name, label, route, icon, position, enabled, visible_in_sidebar, requires_auth, created_at)
            VALUES (max_id + 1, pabellon_id, 'surgical_stage', 'Etapas Quirúrgicas', 'app_maintainers_surgery_surgical_stage_index', 'bx bx-list-ol', max_pos + 1, true, true, true, NOW());
        END IF;
        
        -- Item 13: Items de Etapa
        IF NOT EXISTS (SELECT 1 FROM menu_items WHERE route = 'app_maintainers_surgery_surgical_stage_item_index') THEN
            SELECT COALESCE(MAX(id), 0) INTO max_id FROM menu_items;
            SELECT COALESCE(MAX(position), -1) INTO max_pos FROM menu_items WHERE parent_id = pabellon_id;
            INSERT INTO menu_items (id, parent_id, name, label, route, icon, position, enabled, visible_in_sidebar, requires_auth, created_at)
            VALUES (max_id + 1, pabellon_id, 'surgical_stage_item', 'Items de Etapa', 'app_maintainers_surgery_surgical_stage_item_index', 'bx bx-detail', max_pos + 1, true, true, true, NOW());
        END IF;
        
    END $$;
EOF
    
    if [ $? -eq 0 ]; then
        echo "✅ Items insertados en $DB"
    else
        echo "❌ Error al insertar en $DB"
    fi
done

echo "========================================="
echo "✅ Script completado exitosamente"
echo "========================================="
