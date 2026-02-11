# 🤖 COPILOT PROMPT - PARTE 4: TESTING Y VALIDACIÓN FINAL

## 📋 RESUMEN DE LO IMPLEMENTADO

Has completado las partes 1-3:
- ✅ **PARTE 1**: Backend - Endpoints AJAX
- ✅ **PARTE 2**: Frontend - Tree View HTML/Twig
- ✅ **PARTE 3**: JavaScript - Drag & Drop + Interactividad

Ahora vamos a validar, testear y hacer ajustes finales.

---

## 🎯 CHECKLIST DE VALIDACIÓN

### ✅ PASO 1: Validar Archivos Modificados

**Verificar que existen estos archivos:**

```bash
# Controller
ls -la src/Controller/Admin/MenuConfigController.php

# Template
ls -la templates/admin/menu_config/index.html.twig

# Verificar métodos en el controller (debe aparecer 9 métodos)
grep -E "public function|private function" src/Controller/Admin/MenuConfigController.php
```

**Resultado esperado:**
```
public function index()
public function new()
public function edit()
public function delete()
public function toggle()
public function clearCache()
public function updateHierarchy()          # NUEVO
public function getMenuTree()              # NUEVO
public function addChild()                 # NUEVO
private function processForm()
private function getTenantId()
private function isCircularReference()     # NUEVO
private function isDescendantOf()          # NUEVO
private function serializeMenuItem()       # NUEVO
```

---

### ✅ PASO 2: Verificar Rutas

**Ejecutar en terminal:**

```bash
php bin/console debug:router | grep admin_menu_config
```

**Resultado esperado:**
```
admin_menu_config_index             GET     /admin/menu-config
admin_menu_config_new               GET|POST /admin/menu-config/new
admin_menu_config_edit              GET|POST /admin/menu-config/{id}/edit
admin_menu_config_delete            POST    /admin/menu-config/{id}/delete
admin_menu_config_toggle            POST    /admin/menu-config/{id}/toggle
admin_menu_config_clear_cache       POST    /admin/menu-config/clear-cache
admin_menu_config_update_hierarchy  POST    /admin/menu-config/{id}/update-hierarchy  ← NUEVO
admin_menu_config_api_tree          GET     /admin/menu-config/api/tree                ← NUEVO
admin_menu_config_add_child         POST    /admin/menu-config/{id}/add-child          ← NUEVO
```

---

### ✅ PASO 3: Test del Endpoint API Tree

**Ejecutar:**

```bash
# Opción 1: Con curl
curl http://melisalacolina.melisaupgrade.prod:8081/admin/menu-config/api/tree

# Opción 2: Abrir en navegador
# http://melisalacolina.melisaupgrade.prod:8081/admin/menu-config/api/tree
```

**Resultado esperado: JSON válido**

```json
[
  {
    "id": 1,
    "name": "dashboard",
    "label": "Dashboard",
    "route": "app_dashboard_default",
    "icon": "bx bx-home-circle",
    "position": 0,
    "enabled": true,
    "level": 1,
    "hasChildren": false,
    "childrenCount": 0,
    "children": []
  },
  {
    "id": 4,
    "name": "mantenedores",
    "label": "Mantenedores",
    "level": 1,
    "hasChildren": true,
    "childrenCount": 5,
    "children": [
      {
        "id": 27,
        "name": "maintenance_comercial",
        "label": "Comercial",
        "level": 2,
        "hasChildren": true,
        "childrenCount": 4,
        "children": [...]
      }
    ]
  }
]
```

---

### ✅ PASO 4: Verificar Interfaz Web

**Abrir en navegador:**
```
http://melisalacolina.melisaupgrade.prod:8081/admin/menu-config
```

**Checklist visual:**

1. ✅ Header con título "Configuración del Menú - melisalacolina"
2. ✅ Toolbar con 5 elementos:
   - Input de búsqueda
   - Botón "Expandir Todo"
   - Botón "Colapsar Todo"
   - Botón "Limpiar Caché"
   - Botón "Nuevo Item"
3. ✅ Árbol jerárquico visible
4. ✅ Items con colores diferentes por nivel:
   - Nivel 1: Borde azul
   - Nivel 2: Borde verde
   - Nivel 3: Borde naranja
   - Nivel 4: Borde rojo
5. ✅ Cada item tiene 4 botones:
   - ✓ (verde o gris)
   - ✏️ (azul)
   - ➕ (verde)
   - 🗑️ (rojo)
6. ✅ Items con hijos tienen badge con número
7. ✅ Items con hijos tienen botón ▼
8. ✅ Leyenda al final

---

## 🧪 TESTS FUNCIONALES

### TEST 1: Colapsar/Expandir

**Pasos:**
1. Buscar item "Comercial" (id=27)
2. Click en el botón ▼ (chevron)
3. ✅ Verificar que los hijos se ocultan
4. ✅ Verificar que el ícono rota 90°
5. Click de nuevo
6. ✅ Verificar que los hijos se muestran

---

### TEST 2: Expandir/Colapsar Todo

**Pasos:**
1. Click en "Colapsar Todo"
2. ✅ Todos los items con hijos deben colapsarse
3. Click en "Expandir Todo"
4. ✅ Todos los items deben expandirse

---

### TEST 3: Búsqueda

**Pasos:**
1. Escribir "tipos" en el buscador
2. ✅ Esperar 300ms (debounce)
3. ✅ Ver que se resaltan items con "tipos"
4. ✅ Ver que "Comercial" se expande automáticamente
5. ✅ Ver que otros items se ocultan
6. Borrar búsqueda
7. ✅ Ver que todos los items vuelven a aparecer

---

### TEST 4: Drag & Drop - Mismo Nivel

**Pasos:**
1. Buscar "Tipos de Cama" (primer hijo de "Tipos")
2. Arrastrar y soltar DESPUÉS de "Tipos de Bloqueo"
3. ✅ Ver mensaje de confirmación (o loading)
4. ✅ Verificar que cambió de posición visualmente
5. Recargar la página (F5)
6. ✅ Verificar que mantiene la nueva posición

**Verificar en BD:**
```sql
SELECT id, name, position FROM menu_items WHERE parent_id = 28 ORDER BY position;
-- "Tipos de Cama" debe tener position=1 (antes era 0)
```

---

### TEST 5: Drag & Drop - Cambiar de Padre

**Pasos:**
1. Buscar "Tipos de Cama" (id=29, padre=28 "Tipos")
2. Arrastrar y soltar dentro de "Básicos" (id=35)
3. ✅ Ver confirmación
4. ✅ Verificar que aparece bajo "Básicos"
5. Recargar página
6. ✅ Verificar que se mantiene bajo "Básicos"

**Verificar en BD:**
```sql
SELECT id, name, parent_id FROM menu_items WHERE id = 29;
-- parent_id debe ser 35 (antes era 28)
```

---

### TEST 6: Agregar Hijo

**Pasos:**
1. Buscar "Tipos" (id=28)
2. Click en botón ➕ "Agregar hijo"
3. ✅ Ver confirmación "¿Crear nuevo item hijo?"
4. Click OK
5. ✅ Ver loading spinner
6. ✅ Debe redirigir a `/admin/menu-config/{nuevo_id}/edit`
7. ✅ Ver formulario de edición
8. Editar:
   - Name: "test_item"
   - Label: "Item de Prueba"
   - Route: "app_test"
9. Guardar
10. ✅ Volver al index
11. ✅ Ver el nuevo item bajo "Tipos"

---

### TEST 7: Validación de Referencia Circular

**Pasos:**
1. Intentar arrastrar "Comercial" (id=27) dentro de "Tipos" (id=28)
   - "Tipos" es HIJO de "Comercial", así que sería circular
2. ✅ Debe mostrar error: "No se puede crear referencia circular"
3. ✅ El item debe volver a su posición original

**Si no funciona, el backend debe validar:**
```php
if ($this->isCircularReference($menuItem, $newParent)) {
    return new JsonResponse([
        'success' => false,
        'error' => 'No se puede crear una referencia circular'
    ], 400);
}
```

---

### TEST 8: Toggle Estado

**Pasos:**
1. Buscar "Tipos de Cama" (id=29)
2. Click en botón ✓ (verde)
3. ✅ El botón debe cambiar a gris con X
4. Recargar página
5. ✅ Debe mantener estado deshabilitado
6. Click de nuevo
7. ✅ Debe volver a verde con ✓

---

### TEST 9: Eliminar Item

**Pasos:**
1. Buscar el item creado en TEST 6 ("Item de Prueba")
2. Click en botón 🗑️
3. ✅ Ver confirmación "¿Eliminar Item de Prueba?"
4. Click OK
5. ✅ El item debe desaparecer
6. Recargar página
7. ✅ El item no debe aparecer

---

### TEST 10: Limpiar Cache

**Pasos:**
1. Click en "Limpiar Caché"
2. ✅ Ver mensaje "Caché del menú invalidado exitosamente"
3. Abrir otra pestaña
4. Ir al sidebar de la aplicación
5. ✅ Los cambios deben reflejarse

---

## 🐛 TROUBLESHOOTING COMÚN

### Problema 1: SortableJS no carga

**Verificar en consola del navegador:**
```javascript
console.log(typeof Sortable);
// Debe ser: "function"
```

**Si es undefined:**
```html
<!-- Verificar que esté en el Twig: -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
```

---

### Problema 2: Drag & drop no funciona

**Verificar en consola:**
```javascript
// Agregar al inicio de initializeSortable():
console.log('Contenedores encontrados:', document.querySelectorAll('.tree-children').length);
```

**Si es 0:**
- Verificar que el HTML tenga clase `tree-children`
- Verificar que el árbol no esté vacío

---

### Problema 3: AJAX retorna error 500

**Ver logs de Symfony:**
```bash
tail -f var/log/dev.log | grep ERROR
```

**Verificar en navegador (Network tab):**
1. Abrir DevTools → Network
2. Hacer drag & drop
3. Click en la petición `update-hierarchy`
4. Ver "Response" → debe mostrar el error

---

### Problema 4: Los cambios no persisten

**Verificar que el endpoint guarda en BD:**
```php
// En updateHierarchy():
$this->entityManager->flush(); // ← DEBE EXISTIR
$this->menuDefinition->invalidateCache($this->getTenantId($request)); // ← DEBE EXISTIR
```

---

### Problema 5: Items desaparecen al hacer drag

**Verificar en JavaScript:**
```javascript
// En updateHierarchy():
if (!response.ok) {
    // REVERTIR el movimiento
    evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
}
```

---

## 🎨 PROMPT PARA AJUSTES FINALES DE CSS

**Si necesitas mejorar los estilos, usar este prompt:**

```
Necesito mejorar los estilos CSS del Tree View en templates/admin/menu_config/index.html.twig

AJUSTES REQUERIDOS:
1. Hacer el árbol más responsive en móviles
2. Mejorar el hover de los items
3. Agregar animación suave al colapsar/expandir
4. Mejorar el contraste de los badges
5. Hacer más visible el estado de dragging

CÓDIGO CSS A AGREGAR/MODIFICAR:

/* Responsive */
@media (max-width: 768px) {
    .tree-item[data-level="2"] { margin-left: 1rem; }
    .tree-item[data-level="3"] { margin-left: 1.5rem; }
    .tree-item[data-level="4"] { margin-left: 2rem; }

    .route-info { display: none; }
    .tree-actions { gap: 0.1rem; }
    .action-btn { padding: 0.2rem 0.4rem; }
}

/* Animaciones */
.tree-children {
    transition: all 0.3s ease;
}

.tree-toggle i {
    transition: transform 0.2s ease;
}

/* Hover mejorado */
.tree-item-content:hover {
    background: #e9ecef;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    transform: translateX(2px);
}

/* Dragging state mejorado */
.tree-item.sortable-chosen {
    opacity: 0.7;
}

.tree-item.sortable-ghost {
    background: linear-gradient(90deg, #c8e6c9 0%, #a5d6a7 100%);
    border-left-width: 5px;
}

/* Badges mejorados */
.badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

Por favor agrega estos estilos al bloque {% block stylesheets %} existente.
```

---

## 📊 MÉTRICAS DE ÉXITO

Después de completar todo, debes poder:

| Funcionalidad | Test | Resultado Esperado |
|---------------|------|-------------------|
| Ver árbol jerárquico | Abrir `/admin/menu-config` | ✅ Ver 53 items organizados |
| Drag & drop mismo nivel | Arrastrar item | ✅ Cambia posición |
| Drag & drop cambiar padre | Arrastrar a otro contenedor | ✅ Cambia de padre |
| Colapsar/Expandir | Click en ▼ | ✅ Oculta/muestra hijos |
| Búsqueda | Escribir "tipos" | ✅ Filtra y resalta |
| Agregar hijo | Click ➕ | ✅ Crea nuevo item |
| Toggle estado | Click ✓ | ✅ Activa/desactiva |
| Eliminar | Click 🗑️ | ✅ Elimina item |
| Validación circular | Arrastrar padre a hijo | ✅ Muestra error |
| Persistencia | Recargar página | ✅ Cambios guardados |

---

## ✅ CHECKLIST FINAL

Antes de dar por terminado:

- [ ] Todos los tests 1-10 pasan
- [ ] No hay errores en la consola del navegador
- [ ] No hay errores en `var/log/dev.log`
- [ ] El árbol se ve bien en desktop
- [ ] El árbol se ve bien en móvil (responsive)
- [ ] Drag & drop funciona en todos los niveles
- [ ] Los cambios persisten al recargar
- [ ] La búsqueda funciona correctamente
- [ ] Los botones de acción funcionan
- [ ] El cache se invalida correctamente
- [ ] La validación circular funciona

---

## 📝 DOCUMENTACIÓN PARA EL USUARIO FINAL

**Crear un archivo de ayuda:**

```markdown
# Guía de Uso: Administración de Menús

## ¿Cómo reorganizar el menú?

1. **Arrastrar y soltar**: Haz click y arrastra cualquier item a su nueva posición
2. **Cambiar de categoría**: Arrastra un item dentro de otra categoría
3. **Los cambios se guardan automáticamente**

## Funciones del Toolbar

- 🔍 **Buscar**: Filtra items por nombre
- ▼ **Expandir Todo**: Muestra todos los niveles
- ▶ **Colapsar Todo**: Oculta todos los hijos
- 🔄 **Limpiar Caché**: Actualiza el menú en el sidebar (usar después de hacer cambios)
- ➕ **Nuevo Item**: Crea un nuevo item de menú

## Botones de Acción

Cada item tiene 4 botones:

- ✓ **Toggle Estado**: Activa/desactiva el item (verde=activo, gris=inactivo)
- ✏️ **Editar**: Modifica nombre, ruta, icono, etc.
- ➕ **Agregar Hijo**: Crea un item hijo directamente
- 🗑️ **Eliminar**: Elimina el item (pide confirmación)

## Limitaciones

- **Máximo 4 niveles** de profundidad
- **No puedes** arrastrar un padre dentro de sus propios hijos (circular)
- **Después de cambios importantes**, hacer click en "Limpiar Caché"
```

---

## 🎉 ¡IMPLEMENTACIÓN COMPLETA!

Si llegaste aquí y todos los tests pasan, **¡felicitaciones!** 🎊

Has implementado exitosamente un Tree View moderno con:
- ✅ Drag & Drop profesional
- ✅ 4 niveles de jerarquía
- ✅ Búsqueda en tiempo real
- ✅ Colapsar/Expandir
- ✅ Validaciones robustas
- ✅ AJAX sin recargar página
- ✅ Responsive design

---

**Fecha**: 2026-02-01
**Versión**: 1.0
**Estado**: ✅ Listo para producción
