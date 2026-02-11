import { Controller } from '@hotwired/stimulus';

const spanishLocale = {
    weekdays: {
        shorthand: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'],
        longhand: ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
    },
    months: {
        shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
    },
    firstDayOfWeek: 1,
    rangeSeparator: ' a ',
    weekAbbreviation: 'Sem',
    scrollTitle: 'Desplaza para incrementar',
    toggleTitle: 'Click para alternar',
    amPM: ['AM', 'PM'],
    yearAriaLabel: 'Anio',
    monthAriaLabel: 'Mes',
    hourAriaLabel: 'Hora',
    minuteAriaLabel: 'Minuto',
    time_24hr: true,
};

export default class extends Controller {
    connect() {
        this.initializePicker();
    }

    async initializePicker() {
        const maxDate = this.element.dataset.maxDate || 'today';
        const currentValue = this.element.value || null;

        try {
            const flatpickrModule = await import('flatpickr');
            const flatpickr = flatpickrModule.default || flatpickrModule;

            this.picker = flatpickr(this.element, {
                locale: spanishLocale,
                clickOpens: true,
                altInput: true,
                altInputClass: 'form-control',
                altFormat: 'd-m-Y',
                dateFormat: 'Y-m-d',
                maxDate,
                defaultDate: currentValue,
                allowInput: true,
                disableMobile: true,
                monthSelectorType: 'static',
            });

            if (!this.picker || !this.picker.calendarContainer) {
                throw new Error('Flatpickr no inicializo correctamente');
            }

            this.openTarget = this.picker.altInput || this.element;
            this.picker.set('positionElement', this.openTarget);
            this.openHandler = () => this.open();
            this.openTarget.addEventListener('focus', this.openHandler);
            this.openTarget.addEventListener('click', this.openHandler);
        } catch (error) {
            console.error('No se pudo inicializar el datepicker de Fecha Nacimiento.', error);
            this.element.type = 'date';
            this.element.lang = 'es-CL';
            this.element.placeholder = '';
        }
    }

    open() {
        if (this.picker) {
            this.picker.open();
        }
    }

    disconnect() {
        if (this.openTarget && this.openHandler) {
            this.openTarget.removeEventListener('focus', this.openHandler);
            this.openTarget.removeEventListener('click', this.openHandler);
        }
        if (this.picker) {
            this.picker.destroy();
        }
    }
}
