import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['total', 'statusAlert', 'statusAlertText', 'totalCuentaDisplay', 'saldoCuenta', 'payButton'];

    static values = {
        totalCuenta: Number,   // total de servicios seleccionados (§3f)
    };

    connect() {
        // Inicializar estado visual de cada switch al cargar.
        this.element.querySelectorAll('[data-payment-method-switch]').forEach((sw) => {
            this.syncMethodState(sw, false);
        });

        // Formatear montos al perder el foco (delegación con focusout, que sí burbujea).
        this._handleFocusOut = (event) => {
            if (event.target.dataset.paymentAmountInput !== undefined) {
                this.formatAmountOnFocusOut(event.target);
            }
        };
        this.element.addEventListener('focusout', this._handleFocusOut);

        // Intentar leer el total de servicios desde el DOM si no vino como valor
        if (this.totalCuentaValue === 0) {
            this._syncTotalCuentaFromDOM();
        }

        this.recalculateTotal();
        this.validateContinueButton();
    }

    disconnect() {
        this.element.removeEventListener('focusout', this._handleFocusOut);
    }

    // ── Handlers de eventos ────────────────────────────────────────────────

    toggleMethod(event) {
        this.syncMethodState(event.currentTarget, true);
        this.recalculateTotal();
        this.validateContinueButton();
    }

    /** Botón ✕ — desactiva el método y oculta el panel. */
    deactivateMethod(event) {
        const methodCode = event.currentTarget.dataset.methodCode;
        if (!methodCode) return;

        const switchEl = this.element.querySelector(`[data-payment-method-switch="${methodCode}"]`);
        if (switchEl && switchEl.checked) {
            switchEl.checked = false;
            this.syncMethodState(switchEl, true);
            this.recalculateTotal();
            this.validateContinueButton();
        }
    }

    // ── Cálculo del total ──────────────────────────────────────────────────

    recalculateTotal() {
        let totalIngresado = 0;

        this.element.querySelectorAll('[data-payment-amount-input]').forEach((input) => {
            if (input.disabled) return;
            const value = this.parseAmount(input.value);
            if (!Number.isNaN(value) && value > 0) {
                totalIngresado += value;
            }
        });

        // Actualizar display "Total ingresado" (compatibilidad backward)
        if (this.hasTotalTarget) {
            this.totalTarget.textContent = totalIngresado > 0
                ? '$ ' + this.formatChilean(totalIngresado)
                : '$ 0';
            this.totalTarget.classList.toggle('has-value', totalIngresado > 0);
        }

        // Actualizar "Total Cuenta" y "Saldo Cuenta" (§3f)
        const totalCuenta = this.totalCuentaValue;

        if (this.hasTotalCuentaDisplayTarget) {
            this.totalCuentaDisplayTarget.textContent = '$ ' + this.formatChilean(totalCuenta);
        }

        if (this.hasSaldoCuentaTarget) {
            const saldo = Math.max(0, totalCuenta - totalIngresado);
            this.saldoCuentaTarget.textContent = '$ ' + this.formatChilean(saldo);

            // Habilitar "$ Efectuar Pago" cuando saldo = 0 y totalCuenta > 0
            if (this.hasPayButtonTarget) {
                const canPay = totalCuenta > 0 && saldo === 0;
                this.payButtonTarget.disabled = !canPay;
                this.payButtonTarget.title     = canPay
                    ? ''
                    : 'Se habilita cuando el Saldo Cuenta llega a $0';
            }
        }

        this.validateContinueButton();
    }

    // ── Validación del botón Continuar ─────────────────────────────────────

    validateContinueButton() {
        let anyActive     = false;
        let allHaveAmount = true;

        this.element.querySelectorAll('[data-payment-method-switch]').forEach((sw) => {
            if (!sw.checked) return;
            anyActive = true;

            const panel = this.element.querySelector(`[data-method-panel="${sw.dataset.paymentMethodSwitch}"]`);
            if (!panel) return;

            panel.querySelectorAll('[data-payment-amount-input]').forEach((input) => {
                if (!input.disabled) {
                    const val = this.parseAmount(input.value);
                    if (Number.isNaN(val) || val <= 0) {
                        allHaveAmount = false;
                    }
                }
            });
        });

        // Regla:
        //   sin activos          → permitir continuar
        //   activos + con monto  → permitir continuar
        //   activos + sin monto  → bloquear
        const canContinue = !anyActive || (anyActive && allHaveAmount);

        const btn = this.getContinueButton();
        if (btn) {
            btn.disabled = !canContinue;
            btn.title    = canContinue ? '' : 'Ingrese el monto en todos los medios de pago activos';
        }

        this.updateStatusAlert(anyActive, allHaveAmount);
    }

    // ── Alerta de estado ───────────────────────────────────────────────────

    updateStatusAlert(anyActive, allHaveAmount) {
        if (!this.hasStatusAlertTarget) return;

        const alert  = this.statusAlertTarget;
        const textEl = this.hasStatusAlertTextTarget ? this.statusAlertTextTarget : null;

        if (!anyActive) {
            alert.classList.remove('d-none', 'pgm-alert-warning');
            alert.classList.add('pgm-alert-info');
            if (textEl) textEl.textContent = 'Puede continuar sin resguardo, o active un medio de pago.';
        } else if (!allHaveAmount) {
            alert.classList.remove('d-none', 'pgm-alert-info');
            alert.classList.add('pgm-alert-warning');
            if (textEl) textEl.textContent = 'Complete el monto en todos los medios de pago activos para continuar.';
        } else {
            alert.classList.add('d-none');
        }
    }

    // ── Estado visual y funcional de la card ──────────────────────────────

    syncMethodState(switchElement, clearOnDisable) {
        const methodCode = switchElement.dataset.paymentMethodSwitch;
        if (!methodCode) return;

        const card  = switchElement.closest('[data-method-card]');
        const panel = this.element.querySelector(`[data-method-panel="${methodCode}"]`);
        if (!panel) return;

        const enabled = switchElement.checked;

        // Activar/desactivar la card (controla el slide-down via CSS max-height)
        if (card) {
            card.classList.toggle('is-active', enabled);
        }

        // Habilitar/deshabilitar campos del panel
        panel.querySelectorAll('input, select, textarea').forEach((field) => {
            field.disabled = !enabled;

            if (!enabled) {
                if (['checkbox', 'radio'].includes(field.type)) {
                    field.checked = false;
                } else if (clearOnDisable && field.type !== 'hidden') {
                    field.value = '';
                }
            }
        });
    }

    // ── Formato chileno de montos ──────────────────────────────────────────

    formatAmountOnFocusOut(input) {
        const value = this.parseAmount(input.value);
        if (!Number.isNaN(value) && value > 0) {
            input.value = this.formatChilean(value);
        }
        this.recalculateTotal();
    }

    /**
     * Parsea un monto que puede tener puntos de miles chilenos.
     * Ejemplos: "150.000" → 150000,  "150000" → 150000,  "1.250.000" → 1250000
     */
    parseAmount(raw) {
        if (!raw) return 0;
        const cleaned = String(raw).replace(/\./g, '').replace(',', '.');
        return parseFloat(cleaned);
    }

    /**
     * Formatea entero con separador de miles chileno (punto).
     * Ejemplo: 1250000 → "1.250.000"
     */
    formatChilean(value) {
        return Math.round(value).toLocaleString('es-CL');
    }

    // ── Utilidades ────────────────────────────────────────────────────────

    /**
     * Lee el total de servicios desde #services-total-display si está en el DOM
     * y el valor inicial es 0 (no fue pasado explícitamente).
     */
    _syncTotalCuentaFromDOM() {
        const servicesEl = document.getElementById('services-total-display');
        if (!servicesEl) return;

        const text    = servicesEl.textContent.replace(/[$.]/g, '').replace(',', '.').trim();
        const parsed  = parseFloat(text);

        if (!Number.isNaN(parsed) && parsed > 0) {
            this.totalCuentaValue = parsed;
        }
    }

    /**
     * Busca el botón Continuar que está en el form padre.
     * Usa data-payment-continue-btn en lugar de un Stimulus target porque
     * el botón puede vivir fuera del elemento del controlador.
     */
    getContinueButton() {
        const form = this.element.closest('form');
        return form ? form.querySelector('[data-payment-continue-btn]') : null;
    }
}
