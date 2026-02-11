import { Controller } from '@hotwired/stimulus';

/**
 * Search and Filter Controller
 * 
 * Gestiona búsqueda en tiempo real con debounce y filtros para mantenedores.
 * Compatible con Turbo Drive para navegación sin recargas.
 * 
 * Targets:
 * - form: Formulario de búsqueda/filtros
 * - input: Campo de búsqueda (text/search)
 * 
 * Actions:
 * - debounce: Búsqueda con delay (evita consultas excesivas)
 * - submit: Submit inmediato (para filtros select)
 * - preventRefresh: Previene refresh manual innecesario
 */
export default class extends Controller {
    static targets = ['form', 'input'];

    /**
     * Inicializa el controller
     * Configura debounce para búsqueda con delay de 300ms
     */
    connect() {
        // Timeout ID para debounce manual
        this.debounceTimeout = null;
        this.isSubmitting = false;
    }

    /**
     * Submit con debounce (para input type="search"/"text")
     * Espera 300ms después del último keypress antes de hacer submit
     * 
     * @param {Event} event - Evento input
     */
    debounce(event) {
        // Limpiar timeout anterior si existe
        if (this.debounceTimeout) {
            clearTimeout(this.debounceTimeout);
        }

        // Resetear página a 1 en nueva búsqueda
        const pageInput = this.formTarget.querySelector('input[name="page"]');
        if (pageInput) {
            pageInput.value = '1';
        }

        // Configurar nuevo timeout
        this.debounceTimeout = setTimeout(() => {
            this.submitForm();
        }, 300); // 300ms de delay
    }

    /**
     * Submit inmediato (para filtros select)
     * Usado cuando cambia un select o se hace click en botón
     * 
     * @param {Event} event - Evento change/click
     */
    submit(event) {
        // Resetear página a 1 cuando cambia filtro
        const pageInput = this.formTarget.querySelector('input[name="page"]');
        if (pageInput) {
            pageInput.value = '1';
        }

        this.submitForm();
    }

    /**
     * Ejecuta el submit del formulario
     * Turbo Drive intercepta automáticamente el submit y hace fetch AJAX
     */
    submitForm() {
        if (this.isSubmitting) {
            return; // Prevenir múltiples submits simultáneos
        }

        this.isSubmitting = true;

        // Turbo Drive maneja el submit automáticamente
        // No necesitamos fetch manual, solo disparar el evento
        this.formTarget.requestSubmit();

        // Reset flag después de un breve delay
        setTimeout(() => {
            this.isSubmitting = false;
        }, 100);
    }

    /**
     * Previene el comportamiento default del submit
     * Turbo Drive ya maneja la navegación sin reload
     * 
     * @param {Event} event - Evento submit
     */
    preventRefresh(event) {
        // Turbo Drive intercepta automáticamente, no necesitamos prevenir
        // Este método existe para clarity en el template
    }

    /**
     * Cleanup al desconectar el controller
     */
    disconnect() {
        if (this.debounceTimeout) {
            clearTimeout(this.debounceTimeout);
        }
    }
}
