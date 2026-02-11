# Módulo Comercial Agregado al Menú

## ✅ Implementación Completada

Se ha agregado exitosamente el **módulo Comercial** con toda su estructura jerárquica al sistema de menús.

### 📊 Resumen de Cambios

**Total de items agregados**: 27
**Estructura**: 1 categoría principal + 4 subcategorías + 22 items funcionales
**Bases de datos actualizadas**: 3 (melisalacolina, melisahospital, melisa_template)
**Total items en BD**: 53 (antes: 26)

---

## 🏗️ Estructura del Módulo Comercial

```
Mantenedores
  └─ 📂 Comercial (ID: 27)
      │
      ├─ 📁 Tipos (6 items)
      │   ├─ Tipos de Cama
      │   ├─ Tipos de Bloqueo
      │   ├─ Tipos de Cancelación
      │   ├─ Tipos de Consulta
      │   ├─ Tipos de Tratamiento
      │   └─ Tipos de Financiador
      │
      ├─ 📁 Básicos (7 items)
      │   ├─ Empresas Solicitantes
      │   ├─ Especialidades por Sucursal
      │   ├─ Financiadores por Sucursal
      │   ├─ Artículos Quirúrgicos
      │   ├─ Derivadores Externos
      │   ├─ Especialidades
      │   └─ Salas
      │
      ├─ 📁 Patologías (6 items)
      │   ├─ Artículos GES
      │   ├─ ENO
      │   ├─ GES
      │   ├─ Items Presupuestarios
      │   ├─ Servicios
      │   └─ Paquetes
      │
      └─ 📁 Complejos (3 items)
          ├─ Tipos de Cama
          ├─ Financiadores
          └─ Acciones Clínicas
```

---

## 🔐 Permisos

**Todos los items del módulo Comercial requieren**: `ROLE_ADMIN`

**Definido en BD**:
```json
{
  "required_roles": ["ROLE_ADMIN"]
}
```

Solo usuarios con rol `ROLE_ADMIN` verán este módulo en el sidebar.

---

## 📝 Rutas Symfony Configuradas

Todos los items tienen rutas Symfony asignadas siguiendo el patrón:

```
app_maintainers_commercial_{nombre}_index
```

**Ejemplos**:
- Tipos de Cama: `app_maintainers_commercial_bed_type_index`
- Financiadores: `app_maintainers_commercial_payer_index`
- Especialidades: `app_maintainers_commercial_specialty_index`

**Verificar rutas**:
```bash
php bin/console debug:router | grep commercial
```

---

## 🗂️ Ubicación en la BD

**Tabla**: `menu_items` (PostgreSQL)

**IDs asignados**:
- Comercial (principal): ID 27
- Tipos: ID 28 → Items 29-34
- Básicos: ID 35 → Items 36-42
- Patologías: ID 43 → Items 44-49
- Complejos: ID 50 → Items 51-53

**Consulta SQL para ver la estructura**:
```sql
SELECT
    id,
    parent_id,
    name,
    label,
    route,
    position
FROM menu_items
WHERE module = 'comercial' OR name = 'maintenance_comercial'
ORDER BY parent_id NULLS FIRST, position;
```

---

## 🔧 Verificación

### 1. Verificar datos en BD

```bash
# Contar items totales
PGPASSWORD=melisamelisa psql -h localhost -U melisa -d melisalacolina \
  -c "SELECT COUNT(*) FROM menu_items;"
# Resultado esperado: 53

# Ver estructura de Comercial
PGPASSWORD=melisamelisa psql -h localhost -U melisa -d melisalacolina \
  -c "SELECT id, name, label FROM menu_items WHERE id BETWEEN 27 AND 53;"
```

### 2. Invalidar cache (si es necesario)

```bash
php bin/console cache:pool:clear cache.app
```

### 3. Ver en el navegador

1. Acceder con usuario que tenga `ROLE_ADMIN`
2. Ir a **Mantenedores**
3. Buscar **Comercial** en el sidebar
4. Expandir las subcategorías: Tipos, Básicos, Patologías, Complejos

---

## 🎨 Iconos Utilizados

| Categoría | Icono | Clase CSS |
|-----------|-------|-----------|
| Comercial | 💼 | `bx bx-briefcase` |
| Tipos | 📂 | `bx bx-category` |
| Básicos | 📋 | `bx bx-list-ul` |
| Patologías | ❤️ | `bx bx-health` |
| Complejos | ⚙️ | `bx bx-cog` |

Todos los items usan iconos de la librería **Boxicons** (`bx bx-*`).

---

## ➕ Agregar Nuevos Items al Módulo Comercial

### Opción 1: SQL Directo

```sql
-- Agregar item bajo "Tipos" (parent_id = 28)
INSERT INTO menu_items (
    parent_id, name, label, route, icon, module,
    position, enabled, visible_in_sidebar, requires_auth,
    required_roles, created_at
) VALUES (
    28,
    'nuevo_tipo',
    'Nuevo Tipo',
    'app_maintainers_commercial_nuevo_tipo_index',
    'bx bx-star',
    'comercial',
    7,  -- siguiente posición
    true,
    true,
    true,
    '["ROLE_ADMIN"]',
    NOW()
);

-- Invalidar cache
-- php bin/console cache:pool:clear cache.app
```

### Opción 2: Actualizar el SQL base

1. Editar `/tmp/menu_comercial_correcto.sql`
2. Agregar el nuevo INSERT en la sección correspondiente
3. Aplicar a todas las BDs:
   ```bash
   PGPASSWORD=melisamelisa psql -h localhost -U melisa -d melisalacolina \
     -f /tmp/menu_comercial_correcto.sql
   ```

---

## 🚨 Importante

### Lazy Loading Activo

El menú se carga **bajo demanda** (lazy loading) después de que el tenant esté establecido.

**NO hacer**:
```twig
{# ❌ Esto ya no funciona #}
{% for item in menu_items %}
```

**Hacer**:
```twig
{# ✅ Correcto #}
{% set menu_items = get_menu() %}
{% for item in menu_items %}
```

### Cache del Menú

- **TTL**: 1 hora (3600 segundos)
- **Invalidación**: Automática tras TTL o manual con `cache:pool:clear cache.app`
- **Scope**: Por tenant (cada tenant tiene su cache independiente)

---

## 📄 Archivos Relacionados

| Archivo | Propósito |
|---------|-----------|
| `/tmp/menu_comercial_correcto.sql` | SQL completo con módulo Comercial |
| `MENU_MIGRATION_SUMMARY.md` | Documentación de la migración inicial |
| `src/Service/Menu/MenuDefinition.php` | Lógica de carga del menú |
| `src/Service/Menu/MenuBuilder.php` | Constructor del menú (lazy loading) |
| `src/Twig/MenuExtension.php` | Función Twig `get_menu()` |
| `templates/partials/_sidebar.html.twig` | Renderizado del sidebar |

---

## 📊 Estadísticas Finales

| Métrica | Valor |
|---------|-------|
| Total items en BD | 53 |
| Items módulo Comercial | 27 |
| Subcategorías | 4 |
| Profundidad máxima | 4 niveles |
| Items con rutas Symfony | 22 |
| Bases de datos actualizadas | 3 |
| Permisos requeridos | ROLE_ADMIN |

---

## ✅ Checklist de Implementación

- [x] SQL generado con estructura completa
- [x] Aplicado a `melisalacolina`
- [x] Aplicado a `melisahospital`
- [x] Aplicado a `melisa_template`
- [x] Cache invalidado
- [x] Estructura verificada en BD
- [x] Lazy loading funcionando
- [x] Permisos configurados (ROLE_ADMIN)
- [x] Iconos asignados
- [x] Rutas Symfony configuradas
- [x] Documentación creada

---

**Fecha de implementación**: 2026-02-01
**Implementado por**: Claude Code
**Estado**: ✅ Completado y verificado

---

## 🔍 Troubleshooting

**Problema**: El módulo Comercial no aparece en el menú

**Soluciones**:
1. Verificar que el usuario tenga `ROLE_ADMIN`:
   ```sql
   SELECT roles FROM user WHERE id = <user_id>;
   ```
2. Invalidar cache:
   ```bash
   php bin/console cache:pool:clear cache.app
   ```
3. Verificar que el item esté en BD:
   ```sql
   SELECT * FROM menu_items WHERE name = 'maintenance_comercial';
   ```

**Problema**: Los items no tienen rutas

**Solución**: Verificar que los controladores existan:
```bash
php bin/console debug:router | grep commercial
```

Si faltan rutas, crear los controladores correspondientes.

---

Para más información sobre el sistema de menús, ver `MENU_MIGRATION_SUMMARY.md`.
