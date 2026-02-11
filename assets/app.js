import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

// Importar configuración optimizada de Turbo
import './turbo-config.js';

// Importar SweetAlert2
import Swal from 'sweetalert2';

// Hacer SweetAlert2 disponible globalmente
window.Swal = Swal;

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

document.addEventListener('DOMContentLoaded', () => {
    initializeBootstrapComponents();
});

// También configurar en el evento turbo:load para navegaciones Turbo
document.addEventListener('turbo:load', () => {
    initializeBootstrapComponents();
});

// Reinicializar Bootstrap después de cargar Turbo Frames
document.addEventListener('turbo:frame-load', () => {
    initializeBootstrapComponents();
});

// Función para inicializar componentes de Bootstrap
function initializeBootstrapComponents() {
    // Reinicializar todos los dropdowns
    const dropdownElementList = document.querySelectorAll('[data-bs-toggle="dropdown"]');
    dropdownElementList.forEach((dropdownToggleEl) => {
        // Verificar si ya existe una instancia
        const existingInstance = bootstrap.Dropdown.getInstance(dropdownToggleEl);
        if (!existingInstance) {
            new bootstrap.Dropdown(dropdownToggleEl);
        }
    });

    // Reinicializar tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach((tooltipTriggerEl) => {
        const existingInstance = bootstrap.Tooltip.getInstance(tooltipTriggerEl);
        if (!existingInstance) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        }
    });

    // Reinicializar popovers
    const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
    popoverTriggerList.forEach((popoverTriggerEl) => {
        const existingInstance = bootstrap.Popover.getInstance(popoverTriggerEl);
        if (!existingInstance) {
            new bootstrap.Popover(popoverTriggerEl);
        }
    });
}
