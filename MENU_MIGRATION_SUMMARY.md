# Migración del Sistema de Menús - Resumen

## ✅ Implementación Completada

Se ha migrado exitosamente el sistema de menús de **hardcoded** a **base de datos PostgreSQL** con las siguientes mejoras:

### 1. Lazy Loading (Solución al problema de timing)

**Problema anterior:**
- `MenuExtension.getGlobals()` se ejecutaba ANTES de que el tenant estuviera establecido
- Resultado: Siempre cargaba el menú hardcoded como fallback

**Solución implementada:**
- ✅ `MenuExtension` ya NO carga el menú en `getGlobals()`
- ✅ Expone función Twig `get_menu()` que se ejecuta **bajo demanda**
- ✅ Para cuando se llama, el tenant **YA está establecido**

**Archivos modificados:**
- `src/Twig/MenuExtension.php`: Cambió de `GlobalsInterface` a función Twig
- `src/Service/Menu/MenuBuilder.php`: Agregado método `buildMenuSafe()`
- `templates/partials/_sidebar.html.twig`: Usa `{% set menu_items = get_menu() %}`

### 2. Datos en Base de Datos

**Script de migración generado:**
```bash
php bin/console app:menu:export-to-sql --truncate > menu.sql
```

**Aplicado a 3 bases de datos tenant:**
- ✅ `melisalacolina`: 26 items insertados
- ✅ `melisahospital`: 26 items insertados
- ✅ `melisa_template`: 26 items insertados

**Verificación:**
```sql
-- Todas las BDs tienen exactamente 26 items
SELECT COUNT(*) FROM menu_items;
-- 26

-- Items de nivel superior (parent_id = NULL)
SELECT id, name, label FROM menu_items WHERE parent_id IS NULL;
-- dashboard, pacientes, citas, mantenedores, reportes, configuracion
```

### 3. Flujo de Carga del Menú

```
[1] Usuario accede → http://melisalacolina.melisaupgrade.prod
                     ↓
[2] TenantDatabaseSwitchListener (prioridad 1000)
    ├─ Detecta subdomain: melisalacolina
    ├─ Consulta tenant en melisa_central (MySQL)
    └─ Cambia conexión a PostgreSQL (melisalacolina)
                     ↓
[3] Template _sidebar.html.twig se renderiza
    └─ Llama a get_menu()
                     ↓
[4] MenuBuilder::buildMenuSafe()
    ├─ Obtiene tenant_id de la sesión
    ├─ Llama a MenuDefinition::getMenuStructure(tenant_id)
    └─ Enriquece el menú (is_active, should_expand)
                     ↓
[5] MenuDefinition::getMenuStructure()
    ├─ Busca en cache (TTL: 1 hora)
    ├─ Si no hay cache:
    │   ├─ Consulta MenuItemRepository::getMenuWithChildren()
    │   ├─ ✅ Si BD tiene datos: retorna y cachea
    │   └─ ❌ Si BD falla/vacía: fallback a getDefaultMenuStructure()
    └─ Retorna estructura de menú
```

### 4. Comandos Disponibles

#### Exportar menú hardcoded a SQL
```bash
php bin/console app:menu:export-to-sql --truncate > menu.sql
```

#### Verificar sistema de menú
```bash
php bin/console app:menu:verify
```

**Nota:** Los comandos de consola NO tienen contexto de tenant, por lo que pueden mostrar errores. Esto es **normal** y **NO afecta** el funcionamiento web.

#### Invalidar cache del menú
```bash
php bin/console cache:pool:clear cache.app
```

### 5. Estructura de la Tabla menu_items

```sql
CREATE TABLE menu_items (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,              -- Identificador único
    label VARCHAR(100) NOT NULL,             -- Texto visible
    route VARCHAR(255),                      -- Nombre de ruta Symfony (NO path)
    icon VARCHAR(100),                       -- Clase CSS del icono
    module VARCHAR(100),                     -- Módulo asociado
    parent_id INTEGER REFERENCES menu_items(id),  -- Jerarquía
    position INTEGER NOT NULL DEFAULT 0,     -- Ordenamiento
    enabled BOOLEAN NOT NULL DEFAULT true,
    visible_in_sidebar BOOLEAN NOT NULL DEFAULT true,
    requires_auth BOOLEAN NOT NULL DEFAULT true,
    required_roles JSON,                     -- Array de roles
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 6. Ventajas de la Migración

✅ **Personalización por Tenant**: Cada tenant puede tener su propio menú
✅ **Cambios sin despliegue**: Modificar menús sin tocar código
✅ **Auditoría**: Rastrear cambios en la BD (created_at, updated_at)
✅ **Permisos dinámicos**: Cambiar roles sin código
✅ **Fallback seguro**: Si BD falla, usa menú hardcoded

### 7. Agregar Nuevos Items al Menú

**Opción 1: SQL directo**
```sql
-- Item de nivel superior
INSERT INTO menu_items (name, label, route, icon, parent_id, position)
VALUES ('nuevo_modulo', 'Nuevo Módulo', 'app_nuevo_index', 'bx bx-star', NULL, 7);

-- Item hijo (usar ID del padre)
INSERT INTO menu_items (name, label, route, icon, parent_id, position)
VALUES ('sub_item', 'Sub Item', 'app_nuevo_sub', 'bx bx-circle', 26, 1);

-- Invalidar cache
-- php bin/console cache:pool:clear cache.app
```

**Opción 2: Exportar hardcoded actualizado**
1. Modificar `MenuDefinition::getDefaultMenuStructure()`
2. Ejecutar `php bin/console app:menu:export-to-sql --truncate > menu.sql`
3. Aplicar a BDs: `psql -d tenant -f menu.sql`
4. Invalidar cache

### 8. Verificación de Funcionamiento

**En ambiente web (recomendado):**
1. Acceder a http://melisalacolina.melisaupgrade.prod
2. Verificar logs en `var/log/dev.log`:
   - "🔧 MenuBuilder: Construyendo menú"
   - "✅ MenuBuilder: Menú construido exitosamente"
   - "Menu cargado desde BD"

**Ver logs en tiempo real:**
```bash
tail -f var/log/dev.log | grep -i "menu\|tenant"
```

### 9. Troubleshooting

**Problema: El menú sigue mostrando la versión hardcoded**

Solución:
```bash
# 1. Verificar que la BD tenga datos
psql -d melisalacolina -c "SELECT COUNT(*) FROM menu_items;"

# 2. Invalidar cache
php bin/console cache:pool:clear cache.app
php bin/console cache:clear

# 3. Verificar logs
tail -f var/log/dev.log | grep "Menu"
```

**Problema: Error "relation menu_items does not exist"**

- En comandos de consola: **Normal** (no hay contexto de tenant)
- En navegador web: **Problema** - verificar TenantDatabaseSwitchListener

---

## 📊 Estadísticas

- **Total de items migrados**: 26
- **Niveles de jerarquía**: 3 (raíz → categoría → item)
- **Tenants actualizados**: 3 (melisalacolina, melisahospital, melisa_template)
- **Cache TTL**: 1 hora (3600 segundos)
- **Tiempo de implementación**: ~2 horas

---

## 🎯 Próximos Pasos (Opcionales)

- [ ] Crear interfaz admin para gestionar menús (CRUD)
- [ ] Implementar permisos por roles en items del menú
- [ ] Agregar menú "Comercial" con items de Pagadores y Clínicas
- [ ] Crear comando de sincronización entre código y BD
- [ ] Implementar versionado de menús

---

**Fecha de implementación**: 2026-02-01
**Implementado por**: Claude Code
**Estado**: ✅ Completado y funcional
