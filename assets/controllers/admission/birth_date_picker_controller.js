import { Controller } from '@hotwired/stimulus';
import flatpickr from 'flatpickr';

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
        const maxDate = this.element.dataset.maxDate || 'today';
        const currentValue = this.element.value || null;

        this.picker = flatpickr(this.element, {
            locale: spanishLocale,
            altInput: true,
            altInputClass: 'form-control',
            altFormat: 'd-m-Y',
            dateFormat: 'Y-m-d',
            maxDate,
            defaultDate: currentValue,
            disableMobile: true,
            monthSelectorType: 'static',
            appendTo: document.body,
        });
    }

    disconnect() {
        if (this.picker) {
            this.picker.destroy();
            this.picker = null;
        }
    }
}
