# Guía Rápida - Instalación de Menús

## Instalación Rápida (3 pasos)

### Paso 1: Ejecutar el Script

**Opción A - Script Bash Automático (RECOMENDADO):**
```bash
cd /var/www/html/melisa_tenant/docs/migracion/sql
./install_all_menus.sh
```

**Opción B - SQL Maestro:**
```bash
cd /var/www/html/melisa_tenant/docs/migracion/sql
psql -h localhost -U melisa -d melisalacolina -f 00_install_all_menus.sql
```

**Opción C - Scripts Individuales:**
```bash
cd /var/www/html/melisa_tenant/docs/migracion/sql
psql -h localhost -U melisa -d melisalacolina -f 06_menu_hospitalaria.sql
psql -h localhost -U melisa -d melisalacolina -f 07_menu_liquidaciones.sql
psql -h localhost -U melisa -d melisalacolina -f 08_menu_presupuesto.sql
psql -h localhost -U melisa -d melisalacolina -f 09_menu_taller.sql
```

### Paso 2: Limpiar Caché

```bash
cd /var/www/html/melisa_tenant
php bin/console cache:clear
```

### Paso 3: Verificar

```bash
# Verificar rutas
php bin/console debug:router | grep -E '(hospital|settlements|budget|workshop)'

# Verificar en base de datos
psql -h localhost -U melisa -d melisalacolina -c "
SELECT
    CASE
        WHEN name = 'maintainers.hospital' THEN 'Hospitalaria'
        WHEN name = 'maintainers.settlements' THEN 'Liquidaciones'
        WHEN name = 'maintainers.budget' THEN 'Presupuesto'
        WHEN name = 'maintainers.workshop' THEN 'Taller'
    END as categoria,
    (SELECT COUNT(*) FROM menu_items mi WHERE mi.parent_id = menu_items.id) as total
FROM menu_items
WHERE name IN ('maintainers.hospital', 'maintainers.settlements', 'maintainers.budget', 'maintainers.workshop')
ORDER BY position;
"
```

## Resultado Esperado

```
   categoria    | total
----------------+-------
 Hospitalaria   |    24
 Liquidaciones  |     5
 Presupuesto    |     3
 Taller         |     1
(4 rows)
```

## Credenciales

```
Host:     localhost
Database: melisalacolina
User:     melisa
Password: melisamelisa
```

## Troubleshooting Rápido

**Error: "No existe el menú padre maintenance"**
```sql
-- Crear manualmente:
INSERT INTO menu_items (name, label, icon, route, parent_id, position, enabled, visible_in_sidebar, requires_auth, module, required_roles, created_at)
VALUES ('maintenance', 'Mantenedores', 'bx bx-cog', NULL, NULL, 4, true, true, true, 'Mantenedores', '["ROLE_USER"]', NOW());
```

**Error: "Duplicate key"**
- Es normal, los scripts son idempotentes. Los menús ya existen.

**No aparecen en la web**
- Limpiar caché: `php bin/console cache:clear`
- Verificar permisos de usuario
- Revisar que las rutas existan en los controladores

## Archivos Creados

- `00_install_all_menus.sql` - Script SQL maestro
- `06_menu_hospitalaria.sql` - 24 mantenedores hospitalarios
- `07_menu_liquidaciones.sql` - 5 mantenedores de liquidaciones
- `08_menu_presupuesto.sql` - 3 mantenedores de presupuesto
- `09_menu_taller.sql` - 1 mantenedor de taller
- `install_all_menus.sh` - Script bash automático
- `README.md` - Documentación completa

## Más Información

Ver `README.md` para documentación detallada.
