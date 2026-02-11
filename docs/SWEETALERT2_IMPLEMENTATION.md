# Implementación de SweetAlert2 para Confirmaciones de Eliminación

## Resumen
Se reemplazó `confirm()` nativo por **SweetAlert2** usando un controller Stimulus reutilizable.

## Estado Actual (implementación real)

### 1) Controller Stimulus de confirmación
**Archivo**: `assets/controllers/confirm_delete_controller.js`

- Controller: `confirm-delete`
- Acción: `submit->confirm-delete#confirm`
- Importa SweetAlert2 directamente (`import Swal from 'sweetalert2'`)
- Usa fallback a `window.Swal` y, si no existe, fallback final a `confirm()` nativo
- Previene doble envío con flag `confirmed`

Uso en formulario:

```twig
<form method="post"
      data-controller="confirm-delete"
      data-action="submit->confirm-delete#confirm"
      data-confirm-delete-title-value="{{ 'maintainers.common.confirm_delete_title'|trans({}, 'maintainers') }}"
      data-confirm-delete-text-value="{{ 'maintainers.common.confirm_delete_text'|trans({}, 'maintainers') }}"
      data-confirm-delete-confirm-button-text-value="{{ 'maintainers.common.yes_delete'|trans({}, 'maintainers') }}"
      data-confirm-delete-cancel-button-text-value="{{ 'maintainers.common.cancel'|trans({}, 'maintainers') }}">
    <button type="submit">Eliminar</button>
</form>
```

### 2) Inicialización de frontend
**Archivos**:
- `assets/bootstrap.js`
- `assets/app.js`
- `templates/base.html.twig`
- `config/packages/framework.yaml`

Cambios relevantes:

- Stimulus se inicia con `startStimulusApp()` en `assets/bootstrap.js`
- `framework.asset_mapper.paths` incluye `assets/` para auto-discovery de controllers
- `{{ importmap('app') }}` se carga en `<head>` (no en el bloque de scripts al final) para evitar conflictos en navegación Turbo
- `assets/app.js` mantiene `window.Swal = Swal` y reinicialización de componentes Bootstrap en `DOMContentLoaded`, `turbo:load` y `turbo:frame-load`

### 3) Turbo
**Archivo**: `assets/turbo-config.js`

- Usa `import '@hotwired/turbo'`
- No usa `import { Turbo } ...` (esa forma generaba error)
- No fuerza `data-turbo-permanent` sobre importmaps
- No intercepta/oculta warnings de consola

## Templates con eliminación usando SweetAlert2

- `templates/maintainers/_table_row.html.twig`
- `templates/maintainers/_base_index.html.twig`

En ambos: se eliminó `onsubmit="return confirm(...)"` y se usa `data-controller="confirm-delete"`.

## Traducciones

**Archivo**: `translations/maintainers.es.yaml`

Claves utilizadas:
- `maintainers.common.confirm_delete_title`
- `maintainers.common.confirm_delete_text`
- `maintainers.common.yes_delete`
- `maintainers.common.cancel`

## Problemas resueltos durante la implementación

1. Controller no conectaba:
- Causa: AssetMapper sin `assets/` en `framework.asset_mapper.paths`.
- Solución: agregar `assets/`.

2. Error Turbo por export inválido:
- Causa: `import { Turbo } from '@hotwired/turbo'`.
- Solución: `import '@hotwired/turbo'`.

3. Conflictos de importmap en navegación Turbo:
- Causa: importmap reinyectado y/o manipulado como permanente.
- Solución: mover `importmap('app')` a `<head>` y eliminar manipulación de importmap en JS.

## Compatibilidad

- Turbo Frames
- Turbo Drive
- Páginas legacy
- Responsive

## Verificación rápida

1. Ir a cualquier mantenedor con botón eliminar.
2. Click en eliminar.
3. Debe aparecer SweetAlert2.
4. Confirmar: envía formulario y elimina.
5. Cancelar: no envía.
