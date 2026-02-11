# Propuesta de Mejora: Interfaz de Administración de Menús

## 📊 Análisis de la Interfaz Actual

### ❌ Problemas Detectados

1. **Visualización Limitada de Jerarquía**
   - Solo muestra 2 niveles en el selector de padre
   - Difícil entender la estructura completa con 4 niveles
   - Tabla plana poco intuitiva

2. **Selector de Padre Incompleto**
   ```twig
   {# Actual: Solo 2 niveles #}
   {% for item in all_items %}
       <option>{{ item.label }}</option>
       {% for child in item.children %}
           <option>└─ {{ child.label }}</option>
       {% endfor %}
   {% endfor %}
   ```
   - No permite seleccionar items de nivel 3 o 4 como padres

3. **Reordenamiento Manual**
   - Campo numérico "position" poco intuitivo
   - No hay drag & drop
   - Tedioso reorganizar múltiples items

4. **Vista No Jerárquica**
   - Tabla plana con "└─" como único indicador
   - No hay colapsar/expandir
   - Difícil de navegar con 53 items

---

## 🎯 Propuestas de Mejora

### **OPCIÓN 1: Tree View con Drag & Drop** ⭐ **RECOMENDADA**

**Descripción**: Interfaz moderna tipo árbol con funcionalidad drag & drop para reorganizar.

**Características**:
```
┌─────────────────────────────────────────────────────────┐
│ Configuración del Menú            [+ Nuevo] [🔄 Cache]  │
├─────────────────────────────────────────────────────────┤
│                                                          │
│ 📂 Dashboard                          [✓] [✏️] [🗑️]     │
│                                                          │
│ ▼ 📂 Mantenedores                    [✓] [✏️] [🗑️]     │
│   ├─ ▼ 📁 Mantenimiento Básico      [✓] [✏️] [🗑️]     │
│   │   ├─ 📄 Sexo                     [✓] [✏️] [🗑️]     │
│   │   ├─ 📄 Estado Civil             [✓] [✏️] [🗑️]     │
│   │   └─ 📄 Ocupación                [✓] [✏️] [🗑️]     │
│   │                                                      │
│   ├─ ▶ 📁 Estructura                [✓] [✏️] [🗑️]     │
│   │                                                      │
│   └─ ▼ 📂 Comercial                 [✓] [✏️] [🗑️]     │
│       ├─ ▼ 📁 Tipos                  [✓] [✏️] [🗑️]     │
│       │   ├─ 📄 Tipos de Cama        [✓] [✏️] [🗑️]     │
│       │   ├─ 📄 Tipos de Bloqueo     [✓] [✏️] [🗑️]     │
│       │   └─ 📄 Tipos de Cancelación [✓] [✏️] [🗑️]     │
│       │                                                  │
│       ├─ ▶ 📁 Básicos                [✓] [✏️] [🗑️]     │
│       ├─ ▶ 📁 Patologías             [✓] [✏️] [🗑️]     │
│       └─ ▶ 📁 Complejos              [✓] [✏️] [🗑️]     │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

**Ventajas**:
- ✅ Visualización clara de 4 niveles
- ✅ Colapsar/expandir secciones
- ✅ Drag & drop para reordenar (dentro del mismo nivel y entre niveles)
- ✅ Reorganización automática de `position`
- ✅ Iconos visuales para cada tipo
- ✅ Estado on/off con un click
- ✅ Acciones inline (editar, eliminar)

**Tecnologías**:
- **JavaScript Vanilla** + SortableJS (15KB gzipped)
- **JsTree** (librería especializada en árboles)
- **Nestable2** (especializada en jerarquías drag & drop)

**Implementación**: ~4-6 horas

---

### **OPCIÓN 2: Accordion Jerárquico con Inline Editing**

**Descripción**: Vista tipo acordeón con edición inline de campos básicos.

**Características**:
```
┌─────────────────────────────────────────────────────────┐
│ [▼] Dashboard                                            │
│     Ruta: app_dashboard_default  Icon: bx-home  [✏️]     │
├─────────────────────────────────────────────────────────┤
│ [▼] Mantenedores                                         │
│     ├─ [▼] Mantenimiento Básico                         │
│     │   ├─ [▶] Sexo                                      │
│     │   │   Label: Sexo                                  │
│     │   │   Ruta: app_maintainers_gender_index          │
│     │   │   [Guardar] [Cancelar]                         │
│     │   │                                                 │
│     │   ├─ [▶] Estado Civil                              │
│     │   └─ [▶] Ocupación                                 │
│     │                                                     │
│     ├─ [▼] Comercial                                     │
│     │   ├─ [▼] Tipos (6 items)                           │
│     │   │   ├─ Tipos de Cama                             │
│     │   │   └─ ...                                       │
│     │   ├─ [▶] Básicos (7 items)                         │
│     │   ├─ [▶] Patologías (6 items)                      │
│     │   └─ [▶] Complejos (3 items)                       │
└─────────────────────────────────────────────────────────┘
```

**Ventajas**:
- ✅ Navegación intuitiva
- ✅ Edición rápida de campos comunes
- ✅ Contador de items por categoría
- ✅ No requiere página separada para editar

**Desventajas**:
- ⚠️ No tiene drag & drop nativo
- ⚠️ Requiere más clics para reorganizar

**Implementación**: ~2-3 horas

---

### **OPCIÓN 3: Tree View + Modal para Edición Rápida**

**Descripción**: Árbol visual + modal emergente para editar sin cambiar de página.

**Características**:
```
Vista Principal (Árbol):
┌─────────────────────────────────────────────────┐
│ 📂 Dashboard [⚙️]                                │
│ 📂 Mantenedores [⚙️]                             │
│   └─ 📁 Comercial [⚙️]                           │
│       ├─ 📁 Tipos [⚙️]                           │
│       │   └─ 📄 Tipos de Cama [⚙️]               │
└─────────────────────────────────────────────────┘

Modal de Edición Rápida (al hacer clic en ⚙️):
┌─────────────────────────────────────────────────┐
│ Editar: Tipos de Cama                      [×]  │
├─────────────────────────────────────────────────┤
│ Label: [Tipos de Cama____________]             │
│ Ruta:  [app_maintainers_..._____]              │
│ Icono: [bx bx-bed______________] 🔍            │
│ Padre: [Tipos ▼]                                │
│                                                  │
│ [✓] Habilitado  [✓] Visible  [✓] Requiere Auth │
│                                                  │
│                    [Cancelar] [Guardar cambios] │
└─────────────────────────────────────────────────┘
```

**Ventajas**:
- ✅ Edición rápida sin recargar página
- ✅ Vista clara del árbol
- ✅ UX moderna
- ✅ Menos navegación entre páginas

**Implementación**: ~3-4 horas

---

## 🏆 RECOMENDACIÓN FINAL: **Opción 1 + Mejoras**

**Propongo implementar la OPCIÓN 1 con las siguientes mejoras adicionales**:

### 1. **Tree View Interactivo con SortableJS**

```html
<div class="menu-tree">
    <!-- Nivel 1: Raíz -->
    <div class="tree-item" data-id="4" data-level="1">
        <div class="tree-item-content">
            <button class="tree-toggle">▼</button>
            <i class="bx bx-cog"></i>
            <span class="tree-label">Mantenedores</span>
            <div class="tree-actions">
                <button class="btn-toggle-status" title="Activo">✓</button>
                <button class="btn-edit">✏️</button>
                <button class="btn-add-child">➕</button>
                <button class="btn-delete">🗑️</button>
            </div>
        </div>

        <!-- Nivel 2: Categorías -->
        <div class="tree-children" data-sortable="true">
            <div class="tree-item" data-id="27" data-level="2">
                <div class="tree-item-content">
                    <button class="tree-toggle">▼</button>
                    <i class="bx bx-briefcase"></i>
                    <span class="tree-label">Comercial</span>
                    <span class="badge">27 items</span>
                    <div class="tree-actions">...</div>
                </div>

                <!-- Nivel 3: Subcategorías -->
                <div class="tree-children" data-sortable="true">
                    <div class="tree-item" data-id="28" data-level="3">
                        <div class="tree-item-content">
                            <button class="tree-toggle">▼</button>
                            <i class="bx bx-category"></i>
                            <span class="tree-label">Tipos</span>
                            <span class="badge">6 items</span>
                            <div class="tree-actions">...</div>
                        </div>

                        <!-- Nivel 4: Items finales -->
                        <div class="tree-children" data-sortable="true">
                            <div class="tree-item" data-id="29" data-level="4">
                                <div class="tree-item-content">
                                    <i class="bx bx-bed"></i>
                                    <span class="tree-label">Tipos de Cama</span>
                                    <small class="route">app_maintainers_..._bed_type_index</small>
                                    <div class="tree-actions">...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

### 2. **Funcionalidades JavaScript**

```javascript
// Drag & Drop
Sortable.create(treeContainer, {
    group: 'menu-items',
    animation: 150,
    fallbackOnBody: true,
    swapThreshold: 0.65,
    ghostClass: 'sortable-ghost',
    onEnd: function(evt) {
        // Actualizar position y parent_id vía AJAX
        updateMenuHierarchy(evt.item.dataset.id, evt.newIndex, evt.to.parentNode.dataset.id);
    }
});

// Colapsar/Expandir
function toggleTree(element) {
    element.classList.toggle('collapsed');
    const children = element.querySelector('.tree-children');
    children.style.display = children.style.display === 'none' ? 'block' : 'none';
}

// Edición inline rápida
function quickEdit(itemId) {
    // Mostrar modal con formulario
    showEditModal(itemId);
}

// Toggle status (enabled/disabled)
function toggleStatus(itemId) {
    fetch(`/admin/menu-config/${itemId}/toggle`, { method: 'POST' })
        .then(() => updateTreeItemVisual(itemId));
}
```

### 3. **Selector de Padre Mejorado**

```html
<!-- En el modal de edición -->
<label>Item Padre</label>
<select name="parent_id" class="form-select">
    <option value="">📁 Ninguno (Raíz)</option>
    <optgroup label="Nivel 1">
        <option value="4">📂 Mantenedores</option>
    </optgroup>
    <optgroup label="Nivel 2 (Mantenedores)">
        <option value="5">  📁 Mantenimiento Básico</option>
        <option value="27"> 📁 Comercial</option>
    </optgroup>
    <optgroup label="Nivel 3 (Comercial)">
        <option value="28">    📁 Tipos</option>
        <option value="35">    📁 Básicos</option>
        <option value="43">    📁 Patologías</option>
        <option value="50">    📁 Complejos</option>
    </optgroup>
</select>
```

### 4. **Funciones Adicionales**

- **Búsqueda/Filtro**: Campo de búsqueda para filtrar por nombre/label
- **Expandir/Colapsar Todo**: Botones para expandir o colapsar todos los niveles
- **Vista Previa**: Ver cómo se vería en el sidebar real
- **Duplicar Item**: Clonar un item con sus propiedades
- **Mover Múltiples**: Seleccionar varios items y moverlos a otro padre
- **Validación de Profundidad**: Advertir si se intenta crear más de 4 niveles

### 5. **Endpoints AJAX Necesarios**

```php
// Actualizar posición y padre vía AJAX
#[Route('/{id}/update-hierarchy', name: 'update_hierarchy', methods: ['POST'])]
public function updateHierarchy(Request $request, MenuItem $menuItem): JsonResponse
{
    $data = json_decode($request->getContent(), true);

    $menuItem->setPosition($data['position']);

    if (isset($data['parent_id'])) {
        $parent = $this->menuRepository->find($data['parent_id']);
        $menuItem->setParent($parent);
    } else {
        $menuItem->setParent(null);
    }

    $this->entityManager->flush();
    $this->menuDefinition->invalidateCache($this->getTenantId($request));

    return new JsonResponse(['success' => true]);
}

// Obtener estructura completa como JSON
#[Route('/api/tree', name: 'api_tree', methods: ['GET'])]
public function getMenuTree(): JsonResponse
{
    $menuItems = $this->menuRepository->getMenuWithChildren();
    return new JsonResponse($this->serializeTree($menuItems));
}
```

---

## 📦 Librerías Recomendadas

### **SortableJS** (Recomendada)
```html
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
```
- ✅ 15KB gzipped
- ✅ Sin dependencias
- ✅ Touch support (móviles)
- ✅ Muy configurable
- ✅ Licencia MIT

### Alternativas:

**Nestable2** (Especializada en jerarquías)
```html
<script src="https://cdn.jsdelivr.net/npm/nestable2@1.6.0/jquery.nestable.min.js"></script>
```
- ✅ Especializada en árboles anidados
- ⚠️ Requiere jQuery

**JsTree** (Completa pero pesada)
```html
<script src="https://cdn.jsdelivr.net/npm/jstree@3.3.15/dist/jstree.min.js"></script>
```
- ✅ Muy completa (búsqueda, checkboxes, plugins)
- ⚠️ ~100KB (más pesada)
- ⚠️ Requiere jQuery

---

## 💡 Wireframe de la Interfaz Propuesta

```
┌──────────────────────────────────────────────────────────────────┐
│ Configuración del Menú - melisalacolina                          │
├──────────────────────────────────────────────────────────────────┤
│                                                                   │
│ [🔍 Buscar...]  [▼ Expandir Todo] [▶ Colapsar Todo] [+ Nuevo]   │
│                                                 [🔄 Limpiar Caché]│
│                                                                   │
│ ┌────────────────────────────────────────────────────────────┐  │
│ │ 📂 Dashboard                     [✓] [✏️] [➕] [🗑️]         │  │
│ │                                                              │  │
│ │ ▼ 📂 Mantenedores                [✓] [✏️] [➕] [🗑️]         │  │
│ │   ├─ ▼ 📁 Mantenimiento Básico  [✓] [✏️] [➕] [🗑️]         │  │
│ │   │   ├─ 📄 Sexo                 [✓] [✏️] [🗑️]             │  │
│ │   │   │   app_maintainers_gender_index                      │  │
│ │   │   │                                                      │  │
│ │   │   ├─ 📄 Estado Civil         [✓] [✏️] [🗑️]             │  │
│ │   │   └─ 📄 Ocupación            [✓] [✏️] [🗑️]             │  │
│ │   │                                                          │  │
│ │   ├─ ▶ 📁 Estructura (3)         [✓] [✏️] [➕] [🗑️]         │  │
│ │   │                                                          │  │
│ │   └─ ▼ 📂 Comercial (27)         [✓] [✏️] [➕] [🗑️]         │  │
│ │       ├─ ▼ 📁 Tipos (6)          [✓] [✏️] [➕] [🗑️]         │  │
│ │       │   ├─ 📄 Tipos de Cama    [✓] [✏️] [🗑️]             │  │
│ │       │   ├─ 📄 Tipos de Bloqueo [✓] [✏️] [🗑️]             │  │
│ │       │   └─ ... (4 más)                                    │  │
│ │       │                                                      │  │
│ │       ├─ ▶ 📁 Básicos (7)        [✓] [✏️] [➕] [🗑️]         │  │
│ │       ├─ ▶ 📁 Patologías (6)     [✓] [✏️] [➕] [🗑️]         │  │
│ │       └─ ▶ 📁 Complejos (3)      [✓] [✏️] [➕] [🗑️]         │  │
│ │                                                              │  │
│ │ ▼ 📂 Reportes                    [✓] [✏️] [➕] [🗑️]         │  │
│ │                                                              │  │
│ │ ▼ 📂 Configuración               [✓] [✏️] [➕] [🗑️]         │  │
│ │   └─ 📄 Configuración de Menú   [✓] [✏️] [🗑️]             │  │
│ └────────────────────────────────────────────────────────────┘  │
│                                                                   │
│ Leyenda: ✓=Toggle Estado | ✏️=Editar | ➕=Agregar Hijo | 🗑️=Eliminar│
│                                                                   │
│ Arrastra los items para reorganizar. Los cambios se guardan      │
│ automáticamente.                                                  │
└──────────────────────────────────────────────────────────────────┘
```

---

## 🎨 CSS Propuesto

```css
.menu-tree {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 1rem;
}

.tree-item {
    margin: 0.25rem 0;
    border-left: 2px solid transparent;
    transition: all 0.2s;
}

.tree-item[data-level="1"] { border-left-color: #3498db; }
.tree-item[data-level="2"] { border-left-color: #2ecc71; margin-left: 1.5rem; }
.tree-item[data-level="3"] { border-left-color: #f39c12; margin-left: 3rem; }
.tree-item[data-level="4"] { border-left-color: #e74c3c; margin-left: 4.5rem; }

.tree-item-content {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 4px;
    cursor: move;
    transition: all 0.2s;
}

.tree-item-content:hover {
    background: #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.tree-item.dragging {
    opacity: 0.5;
}

.sortable-ghost {
    opacity: 0.4;
    background: #c8e6c9;
}

.tree-toggle {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    margin-right: 0.5rem;
    transition: transform 0.2s;
}

.tree-item.collapsed .tree-toggle {
    transform: rotate(-90deg);
}

.tree-item.collapsed .tree-children {
    display: none;
}

.tree-label {
    flex: 1;
    font-weight: 500;
}

.route {
    color: #6c757d;
    font-size: 0.85rem;
    font-family: 'Courier New', monospace;
}

.tree-actions {
    display: flex;
    gap: 0.25rem;
}

.tree-actions button {
    padding: 0.25rem 0.5rem;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-toggle-status {
    background: #28a745;
    color: white;
}

.btn-toggle-status.disabled {
    background: #6c757d;
}

.btn-edit {
    background: #17a2b8;
    color: white;
}

.btn-add-child {
    background: #28a745;
    color: white;
}

.btn-delete {
    background: #dc3545;
    color: white;
}

.badge {
    background: #6c757d;
    color: white;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    margin-left: 0.5rem;
}
```

---

## 📊 Comparación de Opciones

| Característica | Opción 1 | Opción 2 | Opción 3 |
|----------------|----------|----------|----------|
| Visualización 4 niveles | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| Drag & Drop | ⭐⭐⭐⭐⭐ | ⭐⭐ | ⭐⭐⭐ |
| Facilidad de uso | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐⭐ |
| Edición rápida | ⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ |
| Complejidad impl. | ⭐⭐⭐ | ⭐⭐⭐⭐ | ⭐⭐⭐ |
| Tiempo desarrollo | 4-6h | 2-3h | 3-4h |
| Dependencias | SortableJS | Ninguna | Bootstrap Modal |

**Conclusión**: **Opción 1** ofrece la mejor experiencia de usuario para gestionar jerarquías de 4 niveles.

---

## ✅ Recomendación Final

**Implementar OPCIÓN 1: Tree View con Drag & Drop**

**Beneficios**:
1. Visualización clara de los 4 niveles
2. Reorganización intuitiva con drag & drop
3. Menos clics para gestionar el menú
4. Escalable a futuros cambios
5. UX moderna y profesional

**Stack Tecnológico**:
- SortableJS para drag & drop
- AJAX para actualizaciones sin recargar
- Bootstrap para estilos base
- JavaScript Vanilla (sin jQuery)

**Estimación**: 4-6 horas de desarrollo + 1-2 horas de testing

---

¿Quieres que implemente esta solución?
