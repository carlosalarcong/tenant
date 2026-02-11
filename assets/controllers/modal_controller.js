import { Controller } from '@hotwired/stimulus';

/**
 * Modal Controller - Sistema Universal de Modales con Turbo Frame
 * 
 * Maneja la carga dinámica de contenido en modales Bootstrap
 * Compatible con Turbo Frame para carga eficiente
 * 
 * Uso:
 * <button data-bs-toggle="modal" 
 *         data-bs-target="#myModal"
 *         data-modal-url="{{ path('route') }}"
 *         data-modal-title="Título">
 *     Abrir Modal
 * </button>
 */
export default class extends Controller {
    static targets = ['title', 'frame', 'loader'];
    static values = {
        size: String,
        autoClose: { type: Boolean, default: true }
    };

    connect() {
        // Event listeners para el ciclo de vida del modal
        this.element.addEventListener('show.bs.modal', this.onShow.bind(this));
        this.element.addEventListener('hidden.bs.modal', this.onHidden.bind(this));
        
        // Auto-cerrar modal después de submit exitoso
        if (this.autoCloseValue) {
            this.submitEndHandler = this.onSubmitEnd.bind(this);
            document.addEventListener('turbo:submit-end', this.submitEndHandler);
        }
    }

    disconnect() {
        // Limpiar event listeners
        if (this.submitEndHandler) {
            document.removeEventListener('turbo:submit-end', this.submitEndHandler);
        }
    }

    /**
     * Se ejecuta cuando el modal se está abriendo
     */
    onShow(event) {
        const button = event.relatedTarget;
        
        if (!button) return;
        
        // Obtener datos del botón
        const url = button.dataset.modalUrl || button.getAttribute('data-modal-url');
        const title = button.dataset.modalTitle || button.getAttribute('data-modal-title');
        
        // Actualizar título si se proporciona
        if (title && this.hasTitleTarget) {
            this.updateTitle(title);
        }
        
        // Cargar contenido si se proporciona URL
        if (url && this.hasFrameTarget) {
            this.loadContent(url);
        }
    }

    /**
     * Carga contenido en el turbo-frame
     */
    loadContent(url) {
        this.frameTarget.setAttribute('src', url);
        this.frameTarget.reload();
    }

    /**
     * Actualiza el título del modal
     */
    updateTitle(title) {
        this.titleTarget.innerHTML = title;
    }

    /**
     * Se ejecuta cuando el modal se ha cerrado completamente
     */
    onHidden() {
        if (!this.hasFrameTarget) return;
        
        // Limpiar el contenido del frame
        this.frameTarget.removeAttribute('src');
        
        // Restaurar loader si existe
        if (this.hasLoaderTarget) {
            this.frameTarget.innerHTML = this.loaderTarget.outerHTML;
        }
    }

    /**
     * Detecta cuando un formulario dentro del modal se envió exitosamente
     */
    onSubmitEnd(event) {
        // Verificar que el submit fue exitoso y viene de este modal
        if (!event.detail.success) return;
        if (!event.target.closest(`#${this.frameTarget.id}`)) return;
        
        // Cerrar el modal
        const modalInstance = bootstrap.Modal.getInstance(this.element);
        if (modalInstance) {
            modalInstance.hide();
        }
    }

    /**
     * Método público para abrir el modal programáticamente
     */
    open(url = null, title = null) {
        if (url) this.loadContent(url);
        if (title) this.updateTitle(title);
        
        const modalInstance = new bootstrap.Modal(this.element);
        modalInstance.show();
    }

    /**
     * Método público para cerrar el modal programáticamente
     */
    close() {
        const modalInstance = bootstrap.Modal.getInstance(this.element);
        if (modalInstance) {
            modalInstance.hide();
        }
    }
}
