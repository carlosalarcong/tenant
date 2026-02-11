// Configuración global de Turbo para optimizar el rendimiento y evitar warnings

// Configuración de Turbo para reducir conflictos con import maps
import '@hotwired/turbo';

const Turbo = window.Turbo;

// Optimizar carga de páginas
document.addEventListener('turbo:load', function() {
    // Reinicializar tooltips de Bootstrap después de navegación Turbo
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Reinicializar animaciones de fade-in
    const cards = document.querySelectorAll('.card:not(.fade-in-applied)');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in', 'fade-in-applied');
    });
});

// Manejar errores de carga más elegantemente
document.addEventListener('turbo:fetch-request-error', function(event) {
    console.warn('Error de navegación Turbo:', event.detail.error);
});

export default Turbo;
