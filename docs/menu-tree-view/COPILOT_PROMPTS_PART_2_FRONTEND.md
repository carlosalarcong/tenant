# 🤖 COPILOT PROMPT - PARTE 2: FRONTEND (HTML/Twig Tree View)

## 📋 CONTEXTO

**Archivo a modificar**: `templates/admin/menu_config/index.html.twig`
**Objetivo**: Reemplazar la tabla plana por un Tree View interactivo
**Framework CSS**: Bootstrap 5 (ya instalado)
**Iconos**: Boxicons (ya disponible)

---

## 🎯 PROMPT COMPLETO PARA REEMPLAZAR index.html.twig

**Copiar y pegar este prompt COMPLETO en Copilot:**

```
Necesito reemplazar COMPLETAMENTE el archivo templates/admin/menu_config/index.html.twig con un Tree View interactivo para gestionar menús con 4 niveles de profundidad.

CONTEXTO DEL SISTEMA:
- Symfony 7.4 con Twig
- Bootstrap 5 ya está disponible
- Boxicons para iconos (bx bx-*)
- Los datos vienen de {{ menu_items }} (array de objetos MenuItem)
- Cada MenuItem tiene: id, name, label, route, icon, position, enabled, parent, children

ESTRUCTURA DE DATOS:
MenuItem {
  id: int
  name: string
  label: string
  route: string|null
  icon: string|null
  module: string|null
  position: int
  enabled: boolean
  visibleInSidebar: boolean
  parent: MenuItem|null
  children: Collection<MenuItem>
}

DISEÑO REQUERIDO:
1. Header con título "Configuración del Menú - {{ tenant_name }}"
2. Toolbar con botones:
   - [🔍 Buscar...]  input de búsqueda
   - [▼ Expandir Todo] botón
   - [▶ Colapsar Todo] botón
   - [+ Nuevo Item] botón (enlace a admin_menu_config_new)
   - [🔄 Limpiar Caché] botón (form POST a admin_menu_config_clear_cache)
3. Mensajes flash (success) con Bootstrap alert
4. Contenedor del árbol con clase "menu-tree-container"
5. Árbol jerárquico con 4 niveles:
   - Nivel 1 (raíz): Borde izquierdo azul, sin indentación
   - Nivel 2: Borde verde, 1.5rem de margen izquierdo
   - Nivel 3: Borde naranja, 3rem de margen izquierdo
   - Nivel 4: Borde rojo, 4.5rem de margen izquierdo

ESTRUCTURA HTML DEL ÁRBOL:
<div class="menu-tree-container" id="menuTreeContainer">
  <div class="tree-item" data-id="{{ item.id }}" data-level="1" data-name="{{ item.name }}">
    <div class="tree-item-content">
      <!-- Botón toggle si tiene hijos -->
      {% if item.children|length > 0 %}
        <button class="tree-toggle" type="button">
          <i class="bx bx-chevron-down"></i>
        </button>
      {% else %}
        <span class="tree-toggle-spacer"></span>
      {% endif %}

      <!-- Icono del item -->
      <i class="{{ item.icon|default('bx bx-file') }}"></i>

      <!-- Label principal -->
      <span class="tree-label">{{ item.label }}</span>

      <!-- Badge con contador si tiene hijos -->
      {% if item.children|length > 0 %}
        <span class="badge bg-secondary">{{ item.children|length }}</span>
      {% endif %}

      <!-- Ruta (si existe) -->
      {% if item.route %}
        <small class="route-info">{{ item.route }}</small>
      {% endif %}

      <!-- Acciones -->
      <div class="tree-actions">
        <!-- Toggle enabled/disabled -->
        <form method="POST" action="{{ path('admin_menu_config_toggle', {id: item.id}) }}" class="d-inline action-form">
          <button type="submit"
                  class="btn btn-sm action-btn {{ item.enabled ? 'btn-success' : 'btn-secondary' }}"
                  title="{{ item.enabled ? 'Activo' : 'Inactivo' }}">
            <i class="bx bx-{{ item.enabled ? 'check' : 'x' }}"></i>
          </button>
        </form>

        <!-- Botón editar -->
        <a href="{{ path('admin_menu_config_edit', {id: item.id}) }}"
           class="btn btn-sm btn-info action-btn"
           title="Editar">
          <i class="bx bx-edit"></i>
        </a>

        <!-- Botón agregar hijo -->
        <button type="button"
                class="btn btn-sm btn-success action-btn btn-add-child"
                data-parent-id="{{ item.id }}"
                title="Agregar hijo">
          <i class="bx bx-plus"></i>
        </button>

        <!-- Botón eliminar -->
        <form method="POST"
              action="{{ path('admin_menu_config_delete', {id: item.id}) }}"
              class="d-inline action-form"
              onsubmit="return confirm('¿Eliminar {{ item.label }}?');">
          <button type="submit"
                  class="btn btn-sm btn-danger action-btn"
                  title="Eliminar">
            <i class="bx bx-trash"></i>
          </button>
        </form>
      </div>
    </div>

    <!-- Contenedor de hijos (si existen) -->
    {% if item.children|length > 0 %}
      <div class="tree-children">
        {% for child in item.children %}
          {{ RENDERIZAR RECURSIVAMENTE (llamar a macro) }}
        {% endfor %}
      </div>
    {% endif %}
  </div>
</div>

REQUISITOS ESPECÍFICOS:
1. Usar macro recursivo para renderizar hijos: {% macro render_tree_item(item, level) %}
2. La macro debe llamarse a sí misma para los children
3. Agregar data-attributes: data-id, data-level, data-name, data-parent-id
4. Los botones de acción deben tener clases para JavaScript: btn-add-child, tree-toggle
5. El árbol debe ser scrollable si es muy largo
6. Agregar mensaje si menu_items está vacío
7. Agregar leyenda al final: "Arrastra los items para reorganizar"

MACRO RECURSIVO NECESARIO:
{% macro render_tree_item(item, level) %}
  <div class="tree-item"
       data-id="{{ item.id }}"
       data-level="{{ level }}"
       data-name="{{ item.name }}"
       data-parent-id="{{ item.parent ? item.parent.id : '' }}">

    {# Contenido del item como se describió arriba #}

    {# Hijos recursivos #}
    {% if item.children|length > 0 %}
      <div class="tree-children">
        {% for child in item.children %}
          {{ _self.render_tree_item(child, level + 1) }}
        {% endfor %}
      </div>
    {% endif %}
  </div>
{% endmacro %}

ESTRUCTURA FINAL DEL ARCHIVO:
{% extends 'app_layout.html.twig' %}

{% block title %}Configuración de Menú - {{ tenant_name }}{% endblock %}

{% block content %}
<div class="container-fluid">
  <div class="card">
    <div class="card-header">
      <!-- Toolbar -->
    </div>
    <div class="card-body">
      <!-- Mensajes flash -->
      <!-- Árbol -->
      <!-- Leyenda -->
    </div>
  </div>
</div>
{% endblock %}

{% macro render_tree_item(item, level) %}
  <!-- Macro recursivo -->
{% endmacro %}

Por favor genera el archivo COMPLETO templates/admin/menu_config/index.html.twig con:
- Header y toolbar completos
- Mensajes flash de Bootstrap
- Árbol con macro recursivo para 4 niveles
- Todos los botones de acción
- Data-attributes para JavaScript
- Leyenda al final
- Manejo de lista vacía
```

**Resultado esperado**: Archivo `index.html.twig` completamente nuevo

---

## 🎨 PROMPT PARA AGREGAR ESTILOS CSS EMBEBIDOS

**Si prefieres CSS embebido en el Twig (más simple), usar este prompt:**

```
Necesito agregar estilos CSS al archivo templates/admin/menu_config/index.html.twig que acabo de crear.

CONTEXTO:
- Es un Tree View con 4 niveles de profundidad
- Usa Bootstrap 5 como base
- Necesito estilos personalizados para el árbol

REQUISITOS:
1. Agregar bloque {% block stylesheets %} al inicio (después de extends)
2. Incluir {{ parent() }} para mantener estilos del layout
3. Agregar estilos para:

/* Contenedor del árbol */
.menu-tree-container {
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 8px;
  padding: 1rem;
  max-height: 70vh;
  overflow-y: auto;
}

/* Items del árbol */
.tree-item {
  margin: 0.25rem 0;
  border-left: 3px solid transparent;
  transition: all 0.2s ease;
}

/* Colores por nivel */
.tree-item[data-level="1"] {
  border-left-color: #3498db;
  margin-left: 0;
}

.tree-item[data-level="2"] {
  border-left-color: #2ecc71;
  margin-left: 1.5rem;
}

.tree-item[data-level="3"] {
  border-left-color: #f39c12;
  margin-left: 3rem;
}

.tree-item[data-level="4"] {
  border-left-color: #e74c3c;
  margin-left: 4.5rem;
}

/* Contenido del item */
.tree-item-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  background: #f8f9fa;
  border-radius: 4px;
  transition: all 0.2s ease;
  cursor: move;
}

.tree-item-content:hover {
  background: #e9ecef;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Toggle button */
.tree-toggle {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.25rem;
  color: #6c757d;
  transition: transform 0.2s ease;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.tree-toggle:hover {
  color: #495057;
}

.tree-item.collapsed .tree-toggle i {
  transform: rotate(-90deg);
}

.tree-toggle-spacer {
  width: 24px;
  display: inline-block;
}

/* Label y elementos */
.tree-label {
  flex: 1;
  font-weight: 500;
  color: #212529;
}

.route-info {
  color: #6c757d;
  font-size: 0.75rem;
  font-family: 'Courier New', monospace;
  max-width: 300px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Acciones */
.tree-actions {
  display: flex;
  gap: 0.25rem;
}

.action-btn {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.action-form {
  margin: 0;
}

/* Hijos colapsables */
.tree-children {
  margin-top: 0.25rem;
}

.tree-item.collapsed > .tree-children {
  display: none;
}

/* Estado de arrastre */
.tree-item.dragging {
  opacity: 0.5;
}

.sortable-ghost {
  opacity: 0.4;
  background: #c8e6c9;
}

/* Toolbar */
.toolbar-search {
  max-width: 300px;
}

/* Leyenda */
.tree-legend {
  margin-top: 1rem;
  padding: 0.75rem;
  background: #f8f9fa;
  border-radius: 4px;
  font-size: 0.875rem;
  color: #6c757d;
}

/* Badges */
.badge {
  font-size: 0.7rem;
  padding: 0.25rem 0.5rem;
}

Por favor agrega estos estilos en un bloque {% block stylesheets %} al inicio del archivo.
```

**Resultado esperado**: Estilos CSS agregados al archivo Twig

---

## 📦 ESTRUCTURA COMPLETA DEL ARCHIVO FINAL

Después de ejecutar ambos prompts, el archivo debe tener:

```twig
{% extends 'app_layout.html.twig' %}

{% block stylesheets %}
    {{ parent() }}
    <style>
        /* ESTILOS GENERADOS POR PROMPT 2 */
    </style>
{% endblock %}

{% block title %}Configuración de Menú - {{ tenant_name }}{% endblock %}

{% block content %}
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h4 class="mb-0">
                    <i class="bx bx-menu"></i> Configuración del Menú
                </h4>

                <div class="d-flex gap-2 flex-wrap">
                    <!-- Búsqueda -->
                    <input type="text"
                           id="treeSearch"
                           class="form-control form-control-sm toolbar-search"
                           placeholder="🔍 Buscar...">

                    <!-- Expandir/Colapsar -->
                    <button type="button" id="btnExpandAll" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-chevron-down"></i> Expandir Todo
                    </button>
                    <button type="button" id="btnCollapseAll" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-chevron-right"></i> Colapsar Todo
                    </button>

                    <!-- Limpiar Cache -->
                    <form method="POST" action="{{ path('admin_menu_config_clear_cache') }}" class="d-inline">
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="bx bx-refresh"></i> Limpiar Caché
                        </button>
                    </form>

                    <!-- Nuevo Item -->
                    <a href="{{ path('admin_menu_config_new') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-plus"></i> Nuevo Item
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Flash messages -->
            {% for message in app.flashes('success') %}
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            {% endfor %}

            <!-- Árbol de menú -->
            {% if menu_items is empty %}
                <div class="alert alert-warning">
                    <i class="bx bx-error-circle"></i>
                    No hay items de menú configurados.
                    <a href="{{ path('admin_menu_config_new') }}" class="alert-link">Crear primer item</a>
                </div>
            {% else %}
                <div class="menu-tree-container" id="menuTreeContainer">
                    {% for item in menu_items %}
                        {{ _self.render_tree_item(item, 1) }}
                    {% endfor %}
                </div>

                <!-- Leyenda -->
                <div class="tree-legend">
                    <i class="bx bx-info-circle"></i>
                    <strong>Instrucciones:</strong>
                    Arrastra los items para reorganizar.
                    Click en <i class="bx bx-chevron-down"></i> para expandir/colapsar.
                    Los cambios se guardan automáticamente.
                </div>
            {% endif %}
        </div>
    </div>
</div>
{% endblock %}

{% macro render_tree_item(item, level) %}
    <!-- CÓDIGO GENERADO POR PROMPT 1 -->
{% endmacro %}
```

---

## ✅ VALIDACIÓN

Después de implementar, verificar:

1. ✅ Abrir `http://melisalacolina.melisaupgrade.prod:8081/admin/menu-config`
2. ✅ Ver árbol jerárquico con 4 niveles
3. ✅ Ver botones de acción en cada item
4. ✅ Ver colores diferentes por nivel (azul, verde, naranja, rojo)
5. ✅ Toolbar con búsqueda, expandir/colapsar, cache, nuevo
6. ✅ Badges con contador de hijos

---

## 🔧 TROUBLESHOOTING

**Problema**: Los iconos no se ven
```twig
{# Verificar que en app_layout.html.twig esté: #}
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
```

**Problema**: El árbol no se ve bien en móvil
```css
/* Agregar en estilos: */
@media (max-width: 768px) {
    .tree-item[data-level="3"] { margin-left: 2rem; }
    .tree-item[data-level="4"] { margin-left: 2.5rem; }
    .route-info { display: none; }
}
```

---

## ➡️ SIGUIENTE PASO

Una vez completada esta parte, continuar con:
**📄 COPILOT_PROMPTS_PART_3_JAVASCRIPT.md** (Drag & Drop con SortableJS)

---

**Fecha**: 2026-02-01
**Versión**: 1.0
**Estado**: ✅ Listo para usar con Copilot
