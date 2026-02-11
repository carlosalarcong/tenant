# 🤖 COPILOT PROMPT - PARTE 3: JAVASCRIPT (Drag & Drop + Interactividad)

## 📋 CONTEXTO

**Objetivo**: Implementar funcionalidad JavaScript para el Tree View
**Librería**: SortableJS (drag & drop)
**Endpoints**: Los creados en PART_1_BACKEND
**Template**: El creado en PART_2_FRONTEND

---

## 🎯 PROMPT 1: Agregar SortableJS y Configuración Básica

**Copiar y pegar este prompt en Copilot:**

```
Necesito agregar JavaScript al archivo templates/admin/menu_config/index.html.twig para implementar drag & drop con SortableJS.

CONTEXTO:
- El árbol HTML ya está creado con clases .tree-item, .tree-children
- Necesito cargar SortableJS desde CDN
- Cada .tree-item tiene data-id, data-level, data-parent-id
- Necesito permitir drag & drop dentro del mismo nivel y entre niveles
- Al soltar, debe actualizar la jerarquía vía AJAX

REQUISITOS:
1. Agregar bloque {% block javascripts %} al final del archivo (antes del {% endblock %} del content)
2. Incluir {{ parent() }} para mantener scripts del layout
3. Cargar SortableJS desde CDN:
   <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

4. Crear función initializeSortable() que:
   - Seleccione todos los contenedores .tree-children y el root .menu-tree-container
   - Aplique Sortable.create() con opciones:
     * group: 'menu-tree' (permite mover entre contenedores)
     * animation: 150
     * handle: '.tree-item-content' (solo arrastrar desde el contenido)
     * ghostClass: 'sortable-ghost'
     * chosenClass: 'sortable-chosen'
     * dragClass: 'dragging'
     * fallbackOnBody: true
     * swapThreshold: 0.65
     * onEnd: función que llame a updateHierarchy()

5. Función updateHierarchy(evt) que:
   - Obtenga el item que se movió: evt.item
   - Obtenga el nuevo parent del contenedor: evt.to.closest('.tree-item')
   - Obtenga la nueva posición: evt.newIndex
   - Extraiga itemId del data-id
   - Extraiga parentId del nuevo parent (o null si es raíz)
   - Haga fetch POST a /admin/menu-config/{itemId}/update-hierarchy con JSON:
     {
       parent_id: parentId,
       position: newIndex
     }
   - Muestre mensaje de éxito/error con toast o alert
   - Si hay error, revierta el movimiento (llamar a evt.item.remove() y volver a insertar)

6. Llamar a initializeSortable() cuando el DOM esté listo:
   document.addEventListener('DOMContentLoaded', initializeSortable);

CÓDIGO BASE:
{% block javascripts %}
    {{ parent() }}

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
    (function() {
        'use strict';

        // Función para inicializar Sortable en todos los contenedores
        function initializeSortable() {
            // Seleccionar todos los contenedores que pueden tener items
            const containers = document.querySelectorAll('.tree-children, .menu-tree-container');

            containers.forEach(container => {
                Sortable.create(container, {
                    // CONFIGURACIÓN AQUÍ
                });
            });
        }

        // Función para actualizar jerarquía vía AJAX
        function updateHierarchy(evt) {
            // CÓDIGO AQUÍ
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', initializeSortable);
    })();
    </script>
{% endblock %}

Por favor genera el bloque {% block javascripts %} COMPLETO con:
- Carga de SortableJS desde CDN
- initializeSortable() completo
- updateHierarchy() completo con fetch AJAX
- Manejo de errores
- Mensajes de confirmación
```

**Resultado esperado**: Bloque `{% block javascripts %}` con drag & drop funcionando

---

## 🎯 PROMPT 2: Agregar Funcionalidad de Colapsar/Expandir

**Copiar y pegar este prompt en Copilot:**

```
Necesito agregar funcionalidad para colapsar/expandir el árbol en el JavaScript del archivo templates/admin/menu_config/index.html.twig.

CONTEXTO:
- Ya tengo el bloque {% block javascripts %} creado
- Cada item tiene botón .tree-toggle con icono bx-chevron-down
- Al hacer click en el toggle, debe colapsar/expandir los hijos
- También hay botones #btnExpandAll y #btnCollapseAll en el toolbar

REQUISITOS:
1. Función toggleTreeItem(treeItem):
   - Agregar/quitar clase 'collapsed' al .tree-item
   - Si collapsed: ocultar .tree-children (display: none)
   - Si expandido: mostrar .tree-children (display: block)
   - Rotar el icono del toggle (ya está en CSS con transform)

2. Event listener para todos los .tree-toggle:
   - Al hacer click, obtener el .tree-item padre
   - Llamar a toggleTreeItem(treeItem)
   - Prevenir que se active el drag & drop al hacer click

3. Función expandAll():
   - Seleccionar todos los .tree-item
   - Remover clase 'collapsed'
   - Mostrar todos los .tree-children

4. Función collapseAll():
   - Seleccionar todos los .tree-item
   - Agregar clase 'collapsed'
   - Ocultar todos los .tree-children

5. Event listeners para botones del toolbar:
   - #btnExpandAll → click → expandAll()
   - #btnCollapseAll → click → collapseAll()

CÓDIGO A AGREGAR (dentro del script existente):

// Función para toggle individual
function toggleTreeItem(treeItem) {
    // CÓDIGO AQUÍ
}

// Función para expandir todo
function expandAll() {
    // CÓDIGO AQUÍ
}

// Función para colapsar todo
function collapseAll() {
    // CÓDIGO AQUÍ
}

// Event listeners
function initializeTreeToggles() {
    // Toggles individuales
    document.querySelectorAll('.tree-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const treeItem = this.closest('.tree-item');
            toggleTreeItem(treeItem);
        });
    });

    // Botones del toolbar
    const btnExpandAll = document.getElementById('btnExpandAll');
    const btnCollapseAll = document.getElementById('btnCollapseAll');

    if (btnExpandAll) {
        btnExpandAll.addEventListener('click', expandAll);
    }

    if (btnCollapseAll) {
        btnCollapseAll.addEventListener('click', collapseAll);
    }
}

// Llamar en DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    initializeSortable();
    initializeTreeToggles(); // AGREGAR ESTA LÍNEA
});

Por favor agrega estas funciones al bloque de JavaScript existente.
```

**Resultado esperado**: Funcionalidad de colapsar/expandir completa

---

## 🎯 PROMPT 3: Agregar Búsqueda en el Árbol

**Copiar y pegar este prompt en Copilot:**

```
Necesito agregar funcionalidad de búsqueda/filtro en el árbol de menú en el JavaScript.

CONTEXTO:
- Hay un input #treeSearch en el toolbar
- Al escribir, debe filtrar los items del árbol por nombre o label
- Debe expandir automáticamente los padres de los items encontrados
- Debe resaltar los items que coinciden

REQUISITOS:
1. Función searchTree(searchTerm):
   - Si searchTerm está vacío, mostrar todos los items
   - Si hay término de búsqueda:
     * Buscar en data-name y .tree-label de cada .tree-item
     * Ocultar items que no coincidan (display: none)
     * Mostrar items que coincidan
     * Expandir todos los padres de items coincidentes
     * Agregar clase 'search-highlight' a items coincidentes

2. Debounce para no buscar en cada tecla:
   - Esperar 300ms después de dejar de escribir

3. Event listener en #treeSearch:
   - input event
   - Llamar a searchTree con debounce

CÓDIGO A AGREGAR:

// Función de debounce
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Función de búsqueda
function searchTree(searchTerm) {
    const term = searchTerm.toLowerCase().trim();
    const allItems = document.querySelectorAll('.tree-item');

    if (!term) {
        // Mostrar todos y quitar highlights
        allItems.forEach(item => {
            item.style.display = '';
            item.classList.remove('search-highlight');
        });
        return;
    }

    // Buscar coincidencias
    allItems.forEach(item => {
        const name = item.dataset.name?.toLowerCase() || '';
        const label = item.querySelector('.tree-label')?.textContent.toLowerCase() || '';

        const matches = name.includes(term) || label.includes(term);

        if (matches) {
            // Mostrar item y resaltar
            item.style.display = '';
            item.classList.add('search-highlight');

            // Expandir todos los padres
            let parent = item.parentElement.closest('.tree-item');
            while (parent) {
                parent.classList.remove('collapsed');
                const children = parent.querySelector('.tree-children');
                if (children) {
                    children.style.display = 'block';
                }
                parent = parent.parentElement.closest('.tree-item');
            }
        } else {
            // Ocultar item
            item.style.display = 'none';
            item.classList.remove('search-highlight');
        }
    });
}

// Inicializar búsqueda
function initializeSearch() {
    const searchInput = document.getElementById('treeSearch');
    if (searchInput) {
        const debouncedSearch = debounce((e) => {
            searchTree(e.target.value);
        }, 300);

        searchInput.addEventListener('input', debouncedSearch);
    }
}

// Llamar en DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    initializeSortable();
    initializeTreeToggles();
    initializeSearch(); // AGREGAR ESTA LÍNEA
});

// Agregar estilos para highlight en el bloque {% block stylesheets %}:
.search-highlight {
    background-color: #fff3cd !important;
    border-left-color: #ffc107 !important;
}

.search-highlight .tree-item-content {
    background-color: #fff3cd !important;
}

Por favor agrega estas funciones al JavaScript y los estilos al bloque de CSS.
```

**Resultado esperado**: Búsqueda funcionando con resaltado

---

## 🎯 PROMPT 4: Agregar Funcionalidad "Agregar Hijo"

**Copiar y pegar este prompt en Copilot:**

```
Necesito agregar funcionalidad para el botón "Agregar Hijo" que crea un nuevo item hijo directamente desde el árbol.

CONTEXTO:
- Cada item tiene botón .btn-add-child con data-parent-id
- Al hacer click, debe llamar al endpoint POST /admin/menu-config/{parentId}/add-child
- Debe agregar el nuevo item al árbol sin recargar la página
- El nuevo item debe ser editable inmediatamente

REQUISITOS:
1. Event listener para todos los .btn-add-child
2. Al hacer click:
   - Obtener parentId del data-parent-id
   - Mostrar loading spinner en el botón
   - Hacer fetch POST a /admin/menu-config/{parentId}/add-child
   - Recibir JSON con el nuevo item
   - Agregar el nuevo item al árbol DOM
   - Expandir el padre si estaba colapsado
   - Redirigir a la página de edición del nuevo item

CÓDIGO A AGREGAR:

// Función para agregar hijo
async function addChildItem(parentId) {
    try {
        const response = await fetch(`/admin/menu-config/${parentId}/add-child`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Error al crear item hijo');
        }

        const data = await response.json();

        if (data.success && data.item) {
            // Redirigir a editar el nuevo item
            window.location.href = `/admin/menu-config/${data.item.id}/edit`;
        } else {
            alert('Error: ' + (data.error || 'No se pudo crear el item'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al crear item hijo: ' + error.message);
    }
}

// Inicializar botones "Agregar Hijo"
function initializeAddChildButtons() {
    document.querySelectorAll('.btn-add-child').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const parentId = this.dataset.parentId;
            if (!parentId) {
                alert('Error: No se pudo obtener el ID del padre');
                return;
            }

            // Confirmar
            if (confirm('¿Crear nuevo item hijo?')) {
                // Mostrar loading
                const originalHtml = this.innerHTML;
                this.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i>';
                this.disabled = true;

                addChildItem(parentId).finally(() => {
                    this.innerHTML = originalHtml;
                    this.disabled = false;
                });
            }
        });
    });
}

// Llamar en DOMContentLoaded
document.addEventListener('DOMContentLoaded', function() {
    initializeSortable();
    initializeTreeToggles();
    initializeSearch();
    initializeAddChildButtons(); // AGREGAR ESTA LÍNEA
});

Por favor agrega estas funciones al JavaScript existente.
```

**Resultado esperado**: Botón "Agregar Hijo" funcionando

---

## 📝 CÓDIGO JAVASCRIPT COMPLETO

Después de ejecutar todos los prompts, el bloque `{% block javascripts %}` debe verse así:

```twig
{% block javascripts %}
    {{ parent() }}

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

    <script>
    (function() {
        'use strict';

        // ========================================
        // DRAG & DROP (PROMPT 1)
        // ========================================
        function initializeSortable() {
            // CÓDIGO GENERADO
        }

        function updateHierarchy(evt) {
            // CÓDIGO GENERADO
        }

        // ========================================
        // COLAPSAR/EXPANDIR (PROMPT 2)
        // ========================================
        function toggleTreeItem(treeItem) {
            // CÓDIGO GENERADO
        }

        function expandAll() {
            // CÓDIGO GENERADO
        }

        function collapseAll() {
            // CÓDIGO GENERADO
        }

        function initializeTreeToggles() {
            // CÓDIGO GENERADO
        }

        // ========================================
        // BÚSQUEDA (PROMPT 3)
        // ========================================
        function debounce(func, wait) {
            // CÓDIGO GENERADO
        }

        function searchTree(searchTerm) {
            // CÓDIGO GENERADO
        }

        function initializeSearch() {
            // CÓDIGO GENERADO
        }

        // ========================================
        // AGREGAR HIJO (PROMPT 4)
        // ========================================
        async function addChildItem(parentId) {
            // CÓDIGO GENERADO
        }

        function initializeAddChildButtons() {
            // CÓDIGO GENERADO
        }

        // ========================================
        // INICIALIZACIÓN
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            initializeSortable();
            initializeTreeToggles();
            initializeSearch();
            initializeAddChildButtons();
        });
    })();
    </script>
{% endblock %}
```

---

## ✅ VALIDACIÓN

Después de implementar, probar:

### Test 1: Drag & Drop
1. ✅ Arrastrar "Tipos de Cama" (id=29) a otra posición dentro de "Tipos"
2. ✅ Ver mensaje de confirmación
3. ✅ Verificar en BD: `SELECT position FROM menu_items WHERE id=29;`

### Test 2: Drag & Drop entre Niveles
1. ✅ Arrastrar "Tipos de Cama" de "Tipos" a "Básicos"
2. ✅ Ver que cambia de padre
3. ✅ Verificar: `SELECT parent_id FROM menu_items WHERE id=29;` (debe ser 35)

### Test 3: Colapsar/Expandir
1. ✅ Click en ▼ de "Comercial" → debe colapsar
2. ✅ Click de nuevo → debe expandir
3. ✅ Click en "Expandir Todo" → todos los items deben expandirse

### Test 4: Búsqueda
1. ✅ Escribir "tipos" en el buscador
2. ✅ Ver que se resaltan items con "tipos" en el nombre
3. ✅ Ver que se expanden los padres automáticamente

### Test 5: Agregar Hijo
1. ✅ Click en ➕ de "Tipos"
2. ✅ Ver confirmación
3. ✅ Debe redirigir a página de edición del nuevo item

---

## 🐛 DEBUGGING

### Ver logs en consola

```javascript
// Agregar al inicio de updateHierarchy():
console.log('Actualizando jerarquía:', {
    itemId: evt.item.dataset.id,
    parentId: parentId,
    position: evt.newIndex
});
```

### Ver errores de AJAX

```javascript
// En el catch de fetch:
console.error('Error AJAX:', error);
console.error('Response:', await response.text());
```

---

## 🔧 TROUBLESHOOTING

**Problema**: Drag & drop no funciona
```javascript
// Verificar que SortableJS se cargó:
console.log(typeof Sortable); // Debe ser "function"

// Verificar que se encontraron contenedores:
console.log(document.querySelectorAll('.tree-children').length);
```

**Problema**: AJAX retorna 404
```javascript
// Verificar rutas:
php bin/console debug:router | grep update_hierarchy

// Verificar que el fetch usa la URL correcta:
console.log(`/admin/menu-config/${itemId}/update-hierarchy`);
```

**Problema**: Circular reference error
```javascript
// El backend debe validar esto y retornar error claro:
{success: false, error: "No se puede crear referencia circular"}
```

---

## ➡️ SIGUIENTE PASO

Una vez completada esta parte, continuar con:
**📄 COPILOT_PROMPTS_PART_4_TESTING.md** (Testing y ajustes finales)

---

**Fecha**: 2026-02-01
**Versión**: 1.0
**Estado**: ✅ Listo para usar con Copilot
