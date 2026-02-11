# Scripts SQL de Migración - Menús de Mantenedores

## Descripción

Este directorio contiene los scripts SQL para insertar los menús de las diferentes categorías de mantenedores en la base de datos del sistema MELISA.

## Base de Datos

**Configuración:**
- **Host:** localhost
- **Database:** melisalacolina
- **User:** melisa
- **Pass:** melisamelisa

## Scripts Disponibles

### 06_menu_hospitalaria.sql
**Categoría:** Hospitalaria
**Total mantenedores:** 24
**Descripción:** Mantenedores relacionados con atención hospitalaria, nutrición, prescripciones y exámenes físicos.

**Mantenedores incluidos:**
- Atención y Cuidado: care_category, care_closure_destination, care_intervention
- Acciones Clínicas: clinical_action_answer, clinical_action_category, clinical_action_question
- Tipos Generales: dosage_type, eating_disorder_history, intoxication_state, medical_device
- Nutrición: nutritional_diagnosis, nutritionist_bmi_index, nutritionist_index_classification, nutritionist_te_index
- Examen Físico: physical_exam_base_field, physical_exam_field, physical_exam_grouping
- Prescripciones: prescription_dispensation, prescription_dosage, prescription_format, prescription_frequency, prescription_route, prescription_rule_detail, prescription_type

---

### 07_menu_liquidaciones.sql
**Categoría:** Liquidaciones
**Total mantenedores:** 5
**Descripción:** Mantenedores relacionados con cuentas bancarias, asociaciones de usuarios, participación profesional y bases de liquidaciones.

**Mantenedores incluidos:**
- bank_account (Cuentas Bancarias)
- company_user_association (Asociación Usuario-Empresa)
- daily_uf (UF Diaria)
- professional_participation (Participación Profesional)
- settlement_base (Base de Liquidaciones)

---

### 08_menu_presupuesto.sql
**Categoría:** Presupuesto
**Total mantenedores:** 3
**Descripción:** Mantenedores relacionados con pies de página de presupuestos y configuraciones por financiador.

**Mantenedores incluidos:**
- budget_footer (Pie de Presupuesto)
- budget_footer_by_funder (Pie de Presupuesto por Financiador)
- budget_funder_footer (Pie del Financiador)

---

### 09_menu_taller.sql
**Categoría:** Taller
**Total mantenedores:** 1
**Descripción:** Mantenedor para gestión de talleres/workshops grupales.

**Mantenedores incluidos:**
- workshop (Talleres)

---

## Instrucciones de Uso

### 1. Conectarse a la Base de Datos

```bash
# Opción A: Usando psql
psql -h localhost -U melisa -d melisalacolina

# Opción B: Usando docker (si aplica)
docker exec -it melisa-postgres psql -U melisa -d melisalacolina
```

### 2. Ejecutar los Scripts

Los scripts deben ejecutarse en el orden numérico (06, 07, 08, 09):

```bash
# Desde la terminal de PostgreSQL
\i /var/www/html/melisa_tenant/docs/migracion/sql/06_menu_hospitalaria.sql
\i /var/www/html/melisa_tenant/docs/migracion/sql/07_menu_liquidaciones.sql
\i /var/www/html/melisa_tenant/docs/migracion/sql/08_menu_presupuesto.sql
\i /var/www/html/melisa_tenant/docs/migracion/sql/09_menu_taller.sql
```

O ejecutar todos de una vez:

```bash
# Desde la terminal del sistema operativo
cat docs/migracion/sql/06_menu_hospitalaria.sql | psql -h localhost -U melisa -d melisalacolina
cat docs/migracion/sql/07_menu_liquidaciones.sql | psql -h localhost -U melisa -d melisalacolina
cat docs/migracion/sql/08_menu_presupuesto.sql | psql -h localhost -U melisa -d melisalacolina
cat docs/migracion/sql/09_menu_taller.sql | psql -h localhost -U melisa -d melisalacolina
```

### 3. Limpiar Caché de Symfony

Después de ejecutar los scripts, es **OBLIGATORIO** limpiar la caché:

```bash
cd /var/www/html/melisa_tenant
php bin/console cache:clear
```

### 4. Verificar las Rutas

Verificar que las rutas de los mantenedores existan:

```bash
# Verificar rutas de Hospitalaria
php bin/console debug:router | grep hospital

# Verificar rutas de Liquidaciones
php bin/console debug:router | grep settlements

# Verificar rutas de Presupuesto
php bin/console debug:router | grep budget

# Verificar rutas de Taller
php bin/console debug:router | grep workshop
```

### 5. Verificar Inserción en Base de Datos

```sql
-- Ver todos los menús de Hospitalaria
SELECT name, label, route, position
FROM menu_items
WHERE name LIKE 'maintainers.hospital%'
ORDER BY position;

-- Ver todos los menús de Liquidaciones
SELECT name, label, route, position
FROM menu_items
WHERE name LIKE 'maintainers.settlements%'
ORDER BY position;

-- Ver todos los menús de Presupuesto
SELECT name, label, route, position
FROM menu_items
WHERE name LIKE 'maintainers.budget%'
ORDER BY position;

-- Ver todos los menús de Taller
SELECT name, label, route, position
FROM menu_items
WHERE name LIKE 'maintainers.workshop%'
ORDER BY position;

-- Contar total de menús insertados por categoría
SELECT
    CASE
        WHEN name LIKE 'maintainers.hospital%' THEN 'Hospitalaria'
        WHEN name LIKE 'maintainers.settlements%' THEN 'Liquidaciones'
        WHEN name LIKE 'maintainers.budget%' THEN 'Presupuesto'
        WHEN name LIKE 'maintainers.workshop%' THEN 'Taller'
    END as categoria,
    COUNT(*) as total
FROM menu_items
WHERE name LIKE 'maintainers.hospital%'
   OR name LIKE 'maintainers.settlements%'
   OR name LIKE 'maintainers.budget%'
   OR name LIKE 'maintainers.workshop%'
GROUP BY categoria
ORDER BY categoria;
```

## Características de los Scripts

### Idempotencia
Todos los scripts son **idempotentes**, lo que significa que pueden ejecutarse múltiples veces sin crear registros duplicados gracias a:
- `WHERE NOT EXISTS` en la inserción del menú padre
- `ON CONFLICT (name) DO NOTHING` en las inserciones de menús hijos

### Validaciones
Cada script incluye:
- Verificación de existencia del menú padre "Mantenedores"
- Validación de creación del menú de categoría
- Conteo de registros insertados
- Mensajes informativos (NOTICE)
- Advertencias (WARNING) si no se insertan todos los registros esperados

### Rollback
Cada script incluye un bloque comentado con instrucciones para deshacer los cambios:
```sql
/*
DELETE FROM menu_items WHERE parent_id = (SELECT id FROM menu_items WHERE name = 'maintainers.XXX');
DELETE FROM menu_items WHERE name = 'maintainers.XXX';
*/
```

## Estructura de Menús

### Jerarquía
```
Mantenedores (maintenance)
├── Hospitalaria (maintainers.hospital)
│   ├── Categorías de Atención
│   ├── Destinos de Cierre de Atención
│   ├── ... (24 mantenedores)
│
├── Liquidaciones (maintainers.settlements)
│   ├── Cuentas Bancarias
│   ├── Asociación Usuario-Empresa
│   ├── ... (5 mantenedores)
│
├── Presupuesto (maintainers.budget)
│   ├── Pie de Presupuesto
│   ├── Pie de Presupuesto por Financiador
│   └── Pie del Financiador
│
└── Taller (maintainers.workshop)
    └── Talleres
```

### Convenciones de Nombres

**Nombres de menú:**
- Formato: `maintainers.{category}.{entity}`
- Ejemplo: `maintainers.hospital.care_category`

**Rutas:**
- Formato: `app_maintainers_{category}_{entity}_index`
- Ejemplo: `app_maintainers_hospital_care_category_index`

**Íconos:**
- Librería: Boxicons (bx-)
- Ejemplos: `bx bx-plus-medical`, `bx bx-calculator`, `bx bx-receipt`

## Prerequisitos

Antes de ejecutar estos scripts, asegurarse de que:

1. ✅ Existe el menú padre "Mantenedores" con `name = 'maintenance'`
2. ✅ La tabla `menu_items` tiene la estructura correcta
3. ✅ Los controladores están implementados en:
   - `src/Controller/Maintainers/Hospital/`
   - `src/Controller/Maintainers/Settlements/`
   - `src/Controller/Maintainers/Budget/`
   - `src/Controller/Maintainers/Workshop/`
4. ✅ Las rutas están definidas en los controladores
5. ✅ Los templates existen en:
   - `templates/maintainers/hospital/`
   - `templates/maintainers/settlements/`
   - `templates/maintainers/budget/`
   - `templates/maintainers/workshop/`

## Troubleshooting

### Error: No existe el menú padre "maintenance"
```
Solución: Crear primero el menú padre de Mantenedores
INSERT INTO menu_items (name, label, icon, route, parent_id, position, enabled, visible_in_sidebar, requires_auth, module, required_roles, created_at)
VALUES ('maintenance', 'Mantenedores', 'bx bx-cog', NULL, NULL, 4, true, true, true, 'Mantenedores', '["ROLE_USER"]', NOW());
```

### Error: Duplicate key value violates unique constraint
```
Solución: Los registros ya existen. Esto es normal si el script se ejecutó previamente.
Para verificar: SELECT * FROM menu_items WHERE name LIKE 'maintainers.hospital%';
```

### Error: relation "menu_items" does not exist
```
Solución: Ejecutar las migraciones de base de datos primero
php bin/console doctrine:migrations:migrate
```

### Las rutas no existen después de insertar
```
Solución:
1. Verificar que los controladores estén implementados
2. Limpiar caché: php bin/console cache:clear
3. Verificar routing: php bin/console debug:router
```

## Resumen de Inserción

| Categoría       | Script                       | Mantenedores | Posición |
|-----------------|------------------------------|--------------|----------|
| Hospitalaria    | 06_menu_hospitalaria.sql     | 24           | 6        |
| Liquidaciones   | 07_menu_liquidaciones.sql    | 5            | 7        |
| Presupuesto     | 08_menu_presupuesto.sql      | 3            | 8        |
| Taller          | 09_menu_taller.sql           | 1            | 9        |
| **TOTAL**       |                              | **33**       |          |

## Documentación Relacionada

- `/docs/migracion/hospitalaria/` - Plan de migración de Hospitalaria
- `/docs/migracion/liquidaciones/` - Plan de migración de Liquidaciones
- `/docs/migracion/presupuesto/` - Plan de migración de Presupuesto
- `/docs/migracion/taller/` - Plan de migración de Taller
- `/docs/migracion/ESTADO_MIGRACION_CATEGORIAS.md` - Estado general de migración

## Notas Importantes

1. **Multi-tenant:** Estos menús se insertan para TODOS los tenants. Si se necesita configuración específica por tenant, debe manejarse a nivel de aplicación.

2. **Permisos:** Todos los menús requieren `ROLE_USER`. Para permisos más específicos, modificar el campo `required_roles`.

3. **Orden de Ejecución:** El orden de los scripts (06-09) no es crítico, pero se recomienda seguirlo para mantener consistencia en las posiciones de menú.

4. **Actualizaciones:** Si se agregan nuevos mantenedores a una categoría, agregar los INSERT en el script correspondiente y volver a ejecutarlo (es idempotente).

5. **Testing:** Después de insertar los menús, verificar en la interfaz web que:
   - Los menús aparecen en el sidebar
   - Los enlaces funcionan correctamente
   - Los permisos se respetan

---

**Fecha de creación:** 2026-02-04
**Última actualización:** 2026-02-04
**Versión:** 1.0
**Autor:** Sistema MELISA
