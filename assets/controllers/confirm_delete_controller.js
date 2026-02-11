import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';

/**
 * Stimulus controller para manejar confirmaciones de eliminación con SweetAlert2
 * 
 * Uso en template:
 * <form data-controller="confirm-delete" 
 *       data-confirm-delete-title-value="¿Estás seguro?" 
 *       data-confirm-delete-text-value="Esta acción no se puede deshacer"
 *       data-action="submit->confirm-delete#confirm">
 *   <button type="submit">Eliminar</button>
 * </form>
 */
export default class extends Controller {
    static values = {
        title: { type: String, default: '¿Estás seguro?' },
        text: { type: String, default: 'Esta acción no se puede deshacer' },
        confirmButtonText: { type: String, default: 'Sí, eliminar' },
        cancelButtonText: { type: String, default: 'Cancelar' },
        icon: { type: String, default: 'warning' }
    }

    // Flag para evitar loop infinito
    confirmed = false;

    connect() {
        console.log('✅ Confirm Delete Controller conectado');

        // Verificar que SweetAlert2 esté disponible
        if (!Swal && typeof window.Swal === 'undefined') {
            console.error('❌ SweetAlert2 no está disponible');
        }
    }

    async confirm(event) {
        console.log('🔔 Confirm triggered, confirmed flag:', this.confirmed);
        
        // Si ya fue confirmado, permitir el submit
        if (this.confirmed) {
            console.log('✅ Formulario confirmado, permitiendo submit');
            return true;
        }

        // Prevenir el submit
        event.preventDefault();
        event.stopPropagation();

        console.log('⏸️ Submit prevenido, mostrando SweetAlert2');

        const swal = Swal || window.Swal;

        // Verificar que SweetAlert2 esté disponible
        if (!swal) {
            console.error('❌ SweetAlert2 no disponible, usando confirm nativo');
            if (confirm(this.titleValue + '\n' + this.textValue)) {
                this.confirmed = true;
                this.element.submit();
            }
            return;
        }

        try {
            const result = await swal.fire({
                title: this.titleValue,
                text: this.textValue,
                icon: this.iconValue,
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: this.confirmButtonTextValue,
                cancelButtonText: this.cancelButtonTextValue,
                reverseButtons: true,
                focusCancel: true
            });

            console.log('SweetAlert2 resultado:', result);

            // Si el usuario confirma, marcar como confirmado y reenviar
            if (result.isConfirmed) {
                console.log('✅ Usuario confirmó eliminación');
                this.confirmed = true;
                this.element.submit();
            } else {
                console.log('❌ Usuario canceló eliminación');
            }
        } catch (error) {
            console.error('❌ Error en SweetAlert2:', error);
            // Fallback a confirm nativo
            if (confirm(this.titleValue + '\n' + this.textValue)) {
                this.confirmed = true;
                this.element.submit();
            }
        }
    }
}
