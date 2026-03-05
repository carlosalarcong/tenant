import { Controller } from '@hotwired/stimulus';

/**
 * Sidebar Controller
 *
 * Gestiona el colapso/expansión de los ítems del sidebar.
 * Reemplaza el event delegation de Bootstrap para los collapses del sidebar,
 * garantizando que los menús pre-renderizados como abiertos por el servidor
 * puedan ser colapsados por el usuario.
 */
export default class extends Controller {

    connect() {
        // Pre-registrar instancias Bootstrap para todos los collapses del sidebar.
        // Esto es necesario porque Bootstrap inicializa collapses de forma lazy
        // (primer clic), pero cuando el servidor ya renderiza 'show', Bootstrap
        // no tiene instancia registrada y no puede colapsar el elemento.
        this.element.querySelectorAll('.collapse').forEach(el => {
            if (!bootstrap.Collapse.getInstance(el)) {
                new bootstrap.Collapse(el, { toggle: false });
            }
        });
    }

    /**
     * Maneja el clic en un ítem colapsable del sidebar.
     * Usar Stimulus en lugar del event delegation de Bootstrap evita
     * conflictos con Turbo y con Tooltips registrados en el mismo elemento.
     */
    toggleCollapse(event) {
        event.preventDefault();
        // Evitar que el event delegation de Bootstrap en document también dispare.
        // data-bs-toggle="collapse" se mantiene solo para los estilos CSS del chevron.
        event.stopPropagation();

        const trigger = event.currentTarget;
        const targetId = trigger.dataset.sidebarTarget;
        const collapseEl = document.getElementById(targetId);
        if (!collapseEl) return;

        const instance = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });

        // Actualizar atributos ARIA y clase 'collapsed' del trigger
        // (Bootstrap no los actualiza automáticamente porque el trigger
        // ya no tiene data-bs-toggle="collapse")
        collapseEl.addEventListener('shown.bs.collapse', () => {
            trigger.setAttribute('aria-expanded', 'true');
            trigger.classList.remove('collapsed');
        }, { once: true });

        collapseEl.addEventListener('hidden.bs.collapse', () => {
            trigger.setAttribute('aria-expanded', 'false');
            trigger.classList.add('collapsed');
        }, { once: true });

        instance.toggle();
    }
}
