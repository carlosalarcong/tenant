# 🎯 Sistema Universal de Modales

Sistema centralizado y reutilizable para manejo de modales con Turbo Frame en toda la aplicación.

## 📁 Estructura

```
templates/
  components/                      ← ✅ Componentes UI UNIVERSALES (toda la app)
    _modal_base.html.twig          ← Modal base reutilizable en CUALQUIER módulo
  
  maintainers/                     ← ✅ Templates del módulo de mantenedores
    modern_index.html.twig         ← Index moderno de mantenedores
    _modal_form.html.twig          ← Formulario genérico para TODOS los mantenedores
    _base_index.html.twig          ← Template base para index de mantenedores
    _base_form.html.twig           ← Template base para forms de mantenedores
    basic/                         ← Subdirectorio con index específicos
  
  caja/                            ← ✅ Templates del módulo de caja
    _modal_form.html.twig          ← Formulario específico de caja
  
  configuracion/                   ← ✅ Templates del módulo de configuración
    _modal_form.html.twig          ← Formulario específico de configuración

assets/
  controllers/
    modal_controller.js            ← Stimulus controller universal
```

### 📌 Reglas de Organización

1. **`templates/components/`** → Componentes UI reutilizables en TODA la aplicación
   - Modales base, cards, alerts, breadcrumbs, etc.
   - No contienen lógica de negocio específica

2. **`templates/{modulo}/`** → Templates específicos del módulo
   - Aunque sean genéricos DENTRO del módulo, permanecen en su carpeta
   - Ejemplo: `_modal_form.html.twig` es genérico para todos los mantenedores, pero pertenece al módulo maintainers

3. **Separación clara**: Componente universal vs. Template de módulo
   - ✅ `_modal_base.html.twig` en `components/` → funciona para caja, config, mantenedores
   - ✅ `_modal_form.html.twig` en `maintainers/` → solo para patrón AbstractMantenedorController

## 🚀 Uso Básico

### 1. Incluir el Modal en tu Template

```twig
{# En cualquier template (index, lista, etc.) #}

{% include 'components/_modal_base.html.twig' with {
    'modal_id': 'myModal',
    'modal_title': 'Título del Modal',
    'frame_id': 'form-frame',
    'modal_size': 'lg'  {# sm, md, lg, xl #}
} %}
```

### 2. Botón para Abrir el Modal

```twig
{# Botón para crear nuevo #}
<button type="button" 
        class="btn btn-primary"
        data-bs-toggle="modal" 
        data-bs-target="#myModal"
        data-modal-url="{{ path('mi_ruta_create') }}"
        data-modal-title="<i class='bx bx-plus'></i> Nuevo Registro">
    Nuevo
</button>

{# Botón para editar #}
<button type="button" 
        class="btn btn-sm btn-primary"
        data-bs-toggle="modal" 
        data-bs-target="#myModal"
        data-modal-url="{{ path('mi_ruta_edit', {'id': item.id}) }}"
        data-modal-title="<i class='bx bx-edit'></i> Editar Registro">
    Editar
</button>
```

### 3. Crear el Template del Formulario

```twig
{# templates/mi_modulo/_modal_form.html.twig #}

<turbo-frame id="form-frame">
    {{ form_start(form, {
        'action': action_url|default(''),
        'attr': {
            'novalidate': 'novalidate',
            'data-turbo-frame': '_top'
        }
    }) }}
    
    <div class="mb-3">
        {{ form_row(form.nombre) }}
    </div>
    
    {# ... más campos ... #}
    
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cancelar
        </button>
        <button type="submit" class="btn btn-primary">
            Guardar
        </button>
    </div>

    {{ form_end(form) }}
</turbo-frame>
```

## ⚙️ Opciones de Configuración

### Parámetros del Modal

| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `modal_id` | string | - | **Requerido**. ID único del modal |
| `modal_title` | string | 'Modal' | Título del modal (acepta HTML) |
| `frame_id` | string | - | **Requerido**. ID del turbo-frame |
| `frame_src` | string | null | URL inicial (opcional) |
| `modal_size` | string | 'lg' | Tamaño: sm, md, lg, xl |
| `header_class` | string | '' | Clase CSS adicional para el header |
| `loading_text` | string | 'Cargando...' | Texto del spinner |
| `auto_close` | boolean | true | Auto-cerrar al guardar |

### Data Attributes del Botón

| Atributo | Descripción |
|----------|-------------|
| `data-bs-toggle="modal"` | Activa el modal Bootstrap |
| `data-bs-target="#modalId"` | ID del modal a abrir |
| `data-modal-url` | URL a cargar en el turbo-frame |
| `data-modal-title` | Título dinámico (acepta HTML) |

## 📚 Ejemplos Completos

### Ejemplo 1: Mantenedores

```twig
{# templates/maintainers/modern_index.html.twig #}

{# Botón Nuevo #}
<button type="button" 
        class="btn btn-primary"
        data-bs-toggle="modal" 
        data-bs-target="#maintainerModal"
        data-modal-url="{{ path(create_route) }}"
        data-modal-title="<i class='bx bx-plus'></i> Nuevo {{ page_title }}">
    Nuevo Registro
</button>

{# Botón Editar en tabla #}
<button type="button"
        class="btn btn-sm"
        data-bs-toggle="modal" 
        data-bs-target="#maintainerModal"
        data-modal-url="{{ path(edit_route, {'id': item.id}) }}"
        data-modal-title="<i class='bx bx-edit'></i> Editar {{ page_title }}">
    <i class="bx bx-edit-alt"></i>
</button>

{# Incluir modal al final del template #}
{% include 'components/_modal_base.html.twig' with {
    'modal_id': 'maintainerModal',
    'modal_title': page_title,
    'frame_id': 'maintainer-form',
    'modal_size': 'lg'
} %}
```

### Ejemplo 2: Caja

```twig
{# templates/caja/index.html.twig #}

<button data-bs-toggle="modal" 
        data-bs-target="#cajaModal"
        data-modal-url="{{ path('caja_nueva_transaccion') }}"
        data-modal-title="💰 Nueva Transacción">
    Nueva Transacción
</button>

{% include 'components/_modal_base.html.twig' with {
    'modal_id': 'cajaModal',
    'frame_id': 'caja-form',
    'modal_size': 'xl'
} %}
```

### Ejemplo 3: Configuración

```twig
{# templates/configuracion/index.html.twig #}

<button data-bs-toggle="modal" 
        data-bs-target="#configModal"
        data-modal-url="{{ path('config_edit_setting', {'key': 'app.name'}) }}"
        data-modal-title="⚙️ Editar Configuración">
    Editar
</button>

{% include 'components/_modal_base.html.twig' with {
    'modal_id': 'configModal',
    'frame_id': 'config-form',
    'modal_size': 'md'
} %}
```

## 🎨 Personalización

### Estilos Personalizados

```twig
{% include 'components/_modal_base.html.twig' with {
    'modal_id': 'customModal',
    'header_class': 'bg-success',  {# Fondo verde #}
    'frame_id': 'custom-frame'
} %}
```

### Desactivar Auto-cierre

```twig
{% include 'components/_modal_base.html.twig' with {
    'modal_id': 'noAutoCloseModal',
    'frame_id': 'form-frame',
    'auto_close': false  {# No cierra automáticamente #}
} %}
```

## 🔧 API JavaScript

Puedes controlar el modal programáticamente:

```javascript
// Obtener el controller
const modal = document.querySelector('[data-controller="modal"]');
const controller = application.getControllerForElementAndIdentifier(modal, 'modal');

// Abrir con URL y título
controller.open('/path/to/form', 'Título Dinámico');

// Cerrar
controller.close();

// Cargar nuevo contenido
controller.loadContent('/nueva/url');

// Actualizar título
controller.updateTitle('Nuevo Título');
```

## ✅ Checklist para Agregar un Nuevo Modal

1. ✅ Incluir `_modal_base.html.twig` en tu template
2. ✅ Agregar botón con `data-bs-toggle` y `data-modal-url`
3. ✅ Crear template del formulario con `<turbo-frame id="...">`
4. ✅ Asegurar que el form tenga `data-turbo-frame="_top"`
5. ✅ Controller debe renderizar el template correcto para Turbo Frame
6. ✅ Probar: abrir, cerrar, guardar

## 🐛 Troubleshooting

### El modal no se abre
- Verificar que el `modal_id` coincida con `data-bs-target`
- Verificar que Bootstrap esté cargado

### El formulario no se carga
- Verificar la URL en `data-modal-url`
- Verificar que el controller detecte Turbo Frame
- Revisar que el `frame_id` coincida

### El modal no se cierra al guardar
- Verificar que `auto_close` no esté en `false`
- Verificar que el form tenga `data-turbo-frame="_top"`
- Revisar que el submit sea exitoso (200/302)

## 📝 Notas Importantes

- ⚠️ El `frame_id` debe ser **único** por modal
- ⚠️ El formulario **debe** estar dentro de `<turbo-frame>`
- ⚠️ Usar `data-turbo-frame="_top"` para redirecciones
- ⚠️ El controller debe usar `TenantEntityManager` para entidades Tenant

## 🎓 Recursos

- [Turbo Frames Documentation](https://turbo.hotwired.dev/handbook/frames)
- [Stimulus Controllers](https://stimulus.hotwired.dev/)
- [Bootstrap Modals](https://getbootstrap.com/docs/5.3/components/modal/)
