# Testing Manual - Sistema de Permisos (Fase 2.2)

## Objetivo
Probar manualmente el **PermissionVoter** implementado en la Fase 2.1 para verificar su funcionamiento correcto con datos reales.

---

## Checklist de Testing Manual

- [x] Implementar `SecuredResourceInterface` en entidad `Person`
- [x] Crear comando para generar datos de prueba
- [x] Crear controlador de prueba con `#[IsGranted]`
- [x] Crear vistas Twig para las pruebas
- [ ] Ejecutar comando de creación de datos
- [ ] Probar acceso con permisos
- [ ] Verificar queries en Symfony Profiler
- [ ] Probar acceso sin permisos (error 403)

---

## 1. Preparar Datos de Prueba

### 1.1 Ejecutar Comando de Creación de Datos

```bash
# Reemplaza '5' con el ID de tu tenant
php bin/console app:create-test-permissions --tenant=5
```

**¿Qué hace este comando?**
- Busca el primer usuario (`Member`) existente en la base de datos
- Crea 2 grupos: **Médicos** y **Administrativos**
- Asigna al usuario al grupo **Médicos**
- Crea 3 permisos individuales:
  - `person #1` → VIEW + EDIT (todos los campos)
  - `person #2` → VIEW del campo `email` solamente
  - `person *` → VIEW de todas las personas
- Crea 2 permisos de grupo:
  - **Médicos** → VIEW + EDIT de todas las personas
  - **Administrativos** → Solo VIEW de todas las personas

### 1.2 Verificar Datos Creados

```sql
-- Ver grupos creados
SELECT * FROM member_group;

-- Ver permisos individuales
SELECT * FROM permission;

-- Ver permisos de grupo
SELECT * FROM group_permission;

-- Ver membresía de grupos
SELECT * FROM member_group_membership;
```

---

## 2. Probar en el Navegador

### 2.1 Acceder al Listado de Prueba

**URL:** `http://localhost/person/test`

**Qué verificar:**
- ✅ Se muestra la lista de personas (sin verificación de permisos)
- ✅ Aparece el botón "Ver" para cada persona
- ✅ Aparece el botón "Verificar" para ver permisos detallados

---

### 2.2 Probar Vista con Permiso Concedido

**URL:** `http://localhost/person/test/1`

**Escenario:** Ver la persona #1 (el comando creó permiso VIEW + EDIT)

**Qué verificar:**
- ✅ La página carga correctamente (sin error 403)
- ✅ Muestra mensaje "¡Permiso concedido!"
- ✅ Muestra los datos de la persona
- ✅ En la tabla de permisos, VIEW y EDIT aparecen como "CONCEDIDO"
- ✅ DELETE aparece como "DENEGADO" (no se creó permiso DELETE)
- ✅ Aparece el botón "Editar"

**Protección aplicada:**
```php
#[IsGranted(PermissionVoter::VIEW, subject: 'person')]
```

---

### 2.3 Probar Vista sin Permiso (Esperado: Error 403)

**URL:** `http://localhost/person/test/999`

**Escenario:** Ver una persona que NO existe o para la cual NO hay permiso específico

**Qué verificar:**
- ❌ Si existe permiso amplio (`person *`), SÍ cargará (por cascada)
- ❌ Si NO existe permiso amplio, debe mostrar **Access Denied (403)**

**Cómo forzar el error 403:**
```sql
-- Eliminar temporalmente el permiso amplio
DELETE FROM permission WHERE domain = 'person' AND resource_id IS NULL;
```

Luego intenta acceder a `http://localhost/person/test/999` → Debería dar error 403.

---

### 2.4 Probar Verificación Manual de Permisos

**URL:** `http://localhost/person/test/1/manual-check`

**Qué verificar:**
- ✅ Muestra tabla con permisos de recurso (VIEW, EDIT, DELETE)
- ✅ Muestra tabla con permisos de campos específicos
- ✅ Los estados (CONCEDIDO/DENEGADO) coinciden con los permisos creados
- ✅ Se muestran ejemplos de código PHP

---

### 2.5 Probar Ruta de Edición (Requiere Permiso EDIT)

**URL:** `http://localhost/person/test/1/edit`

**Escenario:** Editar persona #1 (tiene permiso EDIT)

**Qué verificar:**
- ✅ La página carga correctamente
- ✅ Muestra formulario de edición (readonly, solo demo)
- ✅ Muestra mensaje "¡Permiso concedido!"

**Probar sin permiso:**
```bash
# Acceder a una persona sin permiso EDIT
http://localhost/person/test/999/edit
```
→ Debería dar **Access Denied (403)** si no hay permiso amplio de EDIT.

---

## 3. Verificar Queries en Symfony Profiler

### 3.1 Abrir el Profiler

1. Carga cualquier página del test (ej: `/person/test/1`)
2. Observa la **barra de debug** en la parte inferior
3. Haz clic en el icono de **base de datos** (muestra número de queries)

### 3.2 Queries Esperadas (Sin Cache)

**Para cada verificación de permiso, deberías ver:**

1. **Query 1:** Cargar permisos del usuario
```sql
SELECT * FROM permission 
WHERE member_id = ? AND domain = 'person';
```

2. **Query 2:** Cargar grupos del usuario
```sql
SELECT * FROM member_group_membership 
WHERE member_id = ?;
```

3. **Query 3:** Cargar permisos de los grupos
```sql
SELECT * FROM group_permission 
WHERE member_group_id IN (?, ?) AND domain = 'person';
```

**Total estimado:** 2-4 queries por request (sin cache)

### 3.3 Captura Importante

Anota cuántas queries se ejecutan:
- **Request a `/person/test/1`:** _____ queries
- **Request a `/person/test/1/manual-check`:** _____ queries

Estas métricas son importantes para comparar con la **Fase 2.3** (con cache).

---

## 4. Pruebas de Cascada

### 4.1 Cascada de Permisos Individuales

Los permisos se buscan en este orden (de más específico a más general):

1. `domain + resourceId + fieldName` → Más específico
2. `domain + resourceId + NULL` → Todos los campos del recurso
3. `domain + NULL + fieldName` → Campo específico en todos los recursos
4. `domain + NULL + NULL` → Todos los recursos y campos

**Ejemplo de prueba:**

```sql
-- Crear permiso MUY específico para campo 'email' de persona #2
INSERT INTO permission (member_id, domain, resource_id, field_name, can_view, can_edit, can_delete)
VALUES (1, 'person', 2, 'email', 1, 0, 0);
```

**URL:** `http://localhost/person/test/2/manual-check`

**Verificar:**
- `edit_email` → DENEGADO (el permiso dice can_edit=0)
- `view_email` → CONCEDIDO (si se verifica VIEW)
- Otros campos dependen de permisos más generales

### 4.2 Prioridad: Usuario > Grupo

**Escenario:** Crear permiso individual que DENIEGA, pero el grupo PERMITE

```sql
-- El grupo Médicos ya tiene permiso EDIT para todas las personas
-- Crear permiso individual que DENIEGA EDIT para persona #3
INSERT INTO permission (member_id, domain, resource_id, field_name, can_view, can_edit, can_delete)
VALUES (1, 'person', 3, NULL, 1, 0, 0);  -- can_edit=0 (DENEGAR)
```

**URL:** `http://localhost/person/test/3/manual-check`

**Verificar:**
- EDIT debe estar **DENEGADO** (prioridad de usuario sobre grupo)
- VIEW debe estar **CONCEDIDO**

---

## 5. Pruebas de Seguridad

### 5.1 Usuario No Autenticado

```bash
# Cerrar sesión
# Luego intentar acceder a:
http://localhost/person/test/1
```

**Resultado esperado:** Redirigir a `/login` o error 403.

### 5.2 Usuario sin Permisos

```sql
-- Eliminar TODOS los permisos del usuario
DELETE FROM permission WHERE member_id = 1;
DELETE FROM member_group_membership WHERE member_id = 1;
```

**Intentar acceder:** `http://localhost/person/test/1`

**Resultado esperado:** Access Denied (403)

---

## 6. Resumen de Resultados

### Checklist Final

| Test | Resultado | Notas |
|------|-----------|-------|
| ✅ Comando de datos ejecutado | [ ] | |
| ✅ Listado carga correctamente | [ ] | |
| ✅ Vista con permiso funciona | [ ] | |
| ✅ Vista sin permiso da 403 | [ ] | |
| ✅ Verificación manual muestra permisos | [ ] | |
| ✅ Ruta edit con permiso funciona | [ ] | |
| ✅ Ruta edit sin permiso da 403 | [ ] | |
| ✅ Queries visibles en Profiler | [ ] | |
| ✅ Cascada de permisos funciona | [ ] | |
| ✅ Prioridad usuario > grupo funciona | [ ] | |

### Métricas de Rendimiento (Sin Cache)

| Métrica | Valor |
|---------|-------|
| Queries por request | _____ |
| Tiempo de respuesta | _____ ms |
| Memoria usada | _____ MB |

---

## 7. Troubleshooting

### Error: "Cannot autowire service PersonTestController"

**Solución:** Verificar que el controlador esté en `src/Controller/` y use el namespace correcto.

### Error: "Unable to find template person_test/list.html.twig"

**Solución:** Verificar que las vistas estén en `templates/person_test/`.

### Error: "No route found for GET /person/test"

**Solución:** Limpiar cache de rutas:
```bash
php bin/console cache:clear
```

### No aparece la barra de debug

**Solución:** Verificar que estés en entorno `dev`:
```bash
# En .env
APP_ENV=dev
APP_DEBUG=true
```

### Las queries no aparecen en el Profiler

**Solución:** Instalar el bundle del profiler:
```bash
composer require --dev symfony/profiler-pack
```

---

## 8. Siguiente Paso: Fase 2.3

Una vez completado el testing manual, puedes proceder con la **Fase 2.3: Implementación de Cache**.

**Objetivo de la Fase 2.3:**
- Reducir de **40-80 queries** a solo **2 queries** por request
- Implementar `PermissionCacheService`
- Mantener la misma funcionalidad

**Comando para limpiar datos de prueba (opcional):**
```sql
DELETE FROM permission WHERE domain = 'person';
DELETE FROM group_permission WHERE domain = 'person';
DELETE FROM member_group_membership;
DELETE FROM member_group;
```

---

## Recursos

- **Voter Guide:** `docs/VOTERS_GUIDE.md`
- **Test Unitarios:** `tests/Unit/Security/Voter/PermissionVoterTest.php`
- **Controlador de Prueba:** `src/Controller/PersonTestController.php`
- **Comando de Datos:** `src/Command/CreateTestPermissionsCommand.php`
