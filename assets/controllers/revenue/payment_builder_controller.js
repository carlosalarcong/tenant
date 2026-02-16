import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    // Muestra el monto total ingresado en todos los métodos activos.
    static targets = ['total'];
    // URL base para pedir fragmentos HTML de filas por método.
    static values = {
        fragmentUrlTemplate: String,
    };

    connect() {
        // Inicializa estado visual y disabled según switches al cargar.
        this.element.querySelectorAll('[data-payment-method-switch]').forEach((switchElement) => {
            this.syncMethodState(switchElement);
        });

        // Inicializa el total al cargar el bloque.
        this.recalculateTotal();
    }

    async addRow(event) {
        // Lee el methodCode del botón "Agregar fila".
        const methodCode = event.currentTarget.dataset.methodCode;
        if (!methodCode) {
            return;
        }

        // Contenedor donde se insertan las filas de ese método.
        const rowsContainer = this.rowsContainerFor(methodCode);
        if (!rowsContainer) {
            return;
        }

        // nextIndex evita colisión en nombres: payment_batch[rows][method][index][field]
        const index = Number(rowsContainer.dataset.nextIndex || '0');
        const url = this.buildFragmentUrl(methodCode, index);

        try {
            // Solicita al backend un fragmento HTML de una fila ya renderizada por Symfony Form.
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`No se pudo obtener la fila (${response.status})`);
            }

            const html = await response.text();
            rowsContainer.insertAdjacentHTML('beforeend', html);
            // Incrementa el índice para la próxima fila dinámica.
            rowsContainer.dataset.nextIndex = String(index + 1);
            this.recalculateTotal();
        } catch (error) {
            // eslint-disable-next-line no-console
            console.error(error);
        }
    }

    removeRow(event) {
        // Elimina únicamente la fila donde se hizo click en "Quitar".
        const row = event.currentTarget.closest('[data-payment-row]');
        if (row) {
            row.remove();
        }

        this.recalculateTotal();
    }

    toggleMethod(event) {
        // Activa/desactiva el panel completo del método (switch por método).
        this.syncMethodState(event.currentTarget);
        this.recalculateTotal();
    }

    recalculateTotal() {
        // Suma todos los campos marcados como monto y que no estén deshabilitados.
        let total = 0;

        this.element.querySelectorAll('[data-payment-amount-input]').forEach((input) => {
            if (input.disabled) {
                return;
            }

            const value = parseFloat(input.value || '0');
            if (!Number.isNaN(value) && value > 0) {
                total += value;
            }
        });

        if (this.hasTotalTarget) {
            this.totalTarget.textContent = total.toFixed(2);
        }
    }

    rowsContainerFor(methodCode) {
        // Busca el bloque de filas de un método específico (cash, credit_card, check).
        return this.element.querySelector(`[data-method-rows="${methodCode}"]`);
    }

    buildFragmentUrl(methodCode, index) {
        // Reemplaza el placeholder de método y agrega el índice de fila.
        return `${this.fragmentUrlTemplateValue.replace('__METHOD__', methodCode)}?index=${index}`;
    }

    syncMethodState(switchElement) {
        const methodCode = switchElement.dataset.paymentMethodSwitch;
        if (!methodCode) {
            return;
        }

        const panel = this.element.querySelector(`[data-method-panel="${methodCode}"]`);
        if (!panel) {
            return;
        }

        const enabled = switchElement.checked;
        panel.classList.toggle('d-none', !enabled);

        // Si está desactivado, deshabilita inputs para que no se envíen en el POST.
        panel.querySelectorAll('input, select, textarea').forEach((field) => {
            field.disabled = !enabled;

            if (!enabled && ['checkbox', 'radio'].includes(field.type)) {
                field.checked = false;
            }
        });
    }
}
