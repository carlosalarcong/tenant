import { Controller } from '@hotwired/stimulus';

/**
 * difference-controller  (revenue--difference)
 *
 * Gestiona el flujo de diferencias (descuentos con autorización de supervisor):
 *
 *  - Calcula el "total con descuento" mientras el cajero escribe el monto.
 *  - Después de que el formulario se envía y la solicitud queda pendiente,
 *    arranca un polling de 3 s sobre turbo-frame#difference-status.
 *  - Cuando el frame detecta un estado final (autorizado/rechazado/anulado),
 *    detiene el polling.
 *  - Si el estado final es aprobado (autorizada | auto_aprobada), emite el
 *    evento `difference:approved` con el detalle del descuento para que otros
 *    controladores (ej. services-selector) actualicen el total a cobrar.
 *
 * Targets:
 *   - statusFrame — <turbo-frame id="difference-status">
 *
 * Valores:
 *   - differenceId (Number) — ID de la Difference ya creada (0 = formulario)
 *   - statusUrl    (String) — URL del endpoint GET /{id}/status
 *   - cancelUrl    (String) — URL del endpoint POST /{id}/cancel
 */
export default class extends Controller {
    static targets = ['statusFrame'];

    static values = {
        differenceId: Number,
        statusUrl:    String,
        cancelUrl:    String,
    };

    connect() {
        this._pollingTimer = null;

        // El controlador se conecta tanto en la vista del formulario (id=0)
        // como en la vista post-solicitud (id>0).
        if (!this.differenceIdValue || this.differenceIdValue <= 0) {
            return;
        }

        if (!this.hasStatusFrameTarget) return;

        const frame = this.statusFrameTarget;

        // Escuchar la carga del frame para detectar el estado
        this._handleFrameLoad = this._onFrameLoad.bind(this);
        frame.addEventListener('turbo:frame-load', this._handleFrameLoad);

        // Si el frame ya tiene contenido con estado final (auto-aprobación
        // inmediata), procesarlo sin iniciar polling.
        const existingStatus = this._readStatusFromFrame(frame);
        if (existingStatus && this._isFinalStatus(existingStatus)) {
            this._handleFinalStatus(existingStatus, frame);
            return;
        }

        // Arrancar polling: cargar el frame inmediatamente y luego cada 3 s
        this._loadStatusFrame();
        this._pollingTimer = setInterval(() => this._loadStatusFrame(), 3_000);
    }

    disconnect() {
        this._stopPolling();
        if (this.hasStatusFrameTarget) {
            this.statusFrameTarget.removeEventListener('turbo:frame-load', this._handleFrameLoad);
        }
    }

    // ── Cálculo en tiempo real del descuento ──────────────────────────────

    /**
     * Recalcula el campo "total con descuento" cuando el cajero edita el monto
     * del descuento en el formulario.
     *
     * data-action: "input->revenue--difference#onDiscountInput"
     */
    onDiscountInput(event) {
        const discountInput = event.target;
        const form          = discountInput.closest('form');
        if (!form) return;

        const totalAccountInput     = form.querySelector('[name="total_account"]');
        const totalAfterInput       = form.querySelector('[name="total_after_discount"]');

        if (!totalAccountInput || !totalAfterInput) return;

        const totalAccount = parseFloat(totalAccountInput.value) || 0;
        const discount     = parseFloat(discountInput.value)     || 0;
        const afterDiscount = Math.max(0, totalAccount - discount);

        totalAfterInput.value = afterDiscount.toFixed(0);
    }

    // ── Cierre del panel (sin solicitar) ──────────────────────────────────

    /**
     * Cierra el panel de diferencias sin enviar el formulario.
     * Vacía el contenido de turbo-frame#difference-panel.
     *
     * data-action: "click->revenue--difference#closeDifference"
     */
    closeDifference(event) {
        event.preventDefault();
        const panel = document.getElementById('difference-panel');
        if (panel) {
            panel.innerHTML = '';
        }
    }

    // ── Privado ───────────────────────────────────────────────────────────

    _loadStatusFrame() {
        if (!this.hasStatusFrameTarget || !this.statusUrlValue) return;

        const frame = this.statusFrameTarget;

        // Forzar recarga añadiendo o actualizando el src con cache-bust mínimo
        const url = new URL(this.statusUrlValue, window.location.origin);
        frame.src = url.toString();
    }

    _onFrameLoad(event) {
        const frame  = event.target;
        const status = this._readStatusFromFrame(frame);

        if (!status) return;

        if (this._isFinalStatus(status)) {
            this._stopPolling();
            this._handleFinalStatus(status, frame);
        }
    }

    _readStatusFromFrame(frame) {
        // El _status.html.twig pone data-difference-status en el <turbo-frame>
        return frame.dataset.differenceStatus ?? null;
    }

    _isFinalStatus(status) {
        return ['autorizada', 'auto_aprobada', 'rechazada', 'anulada'].includes(status);
    }

    _handleFinalStatus(status, frame) {
        if (status === 'autorizada' || status === 'auto_aprobada') {
            const discountAmount     = parseFloat(frame.dataset.discountAmount     ?? '0');
            const totalAfterDiscount = parseFloat(frame.dataset.totalAfterDiscount ?? '0');

            this.element.dispatchEvent(
                new CustomEvent('difference:approved', {
                    bubbles: true,
                    detail:  { discountAmount, totalAfterDiscount },
                })
            );
        }
        // Para rechazada / anulada el frame ya muestra el mensaje al usuario;
        // no se emite ningún evento adicional.
    }

    _stopPolling() {
        if (this._pollingTimer !== null) {
            clearInterval(this._pollingTimer);
            this._pollingTimer = null;
        }
    }
}
