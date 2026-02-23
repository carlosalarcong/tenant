import { Controller } from '@hotwired/stimulus';

/**
 * cash-register-controller  (revenue--cash-register)
 *
 * Orquestador principal del módulo de caja.
 *
 * Responsabilidades:
 *  1. Escucha `patient:selected`       → activa el panel de prestaciones
 *  2. Escucha `services:total-changed` → actualiza el total visible y activa
 *                                        el panel de pago cuando total > 0
 *  3. Escucha `difference:approved`    → aplica el descuento al total visible
 *  4. `requestDifference()`            → carga el formulario de diferencia en
 *                                        turbo-frame#difference-panel con el
 *                                        total y paciente actuales
 *  5. `onPaymentFormSubmit()`          → inyecta patient_id y services_payload
 *                                        en el formulario justo antes del envío
 *
 * Eventos escuchados (bubbles: true desde controladores hijos):
 *   - patient:selected          { patientId }
 *   - services:total-changed    { total }
 *   - difference:approved       { discountAmount, totalAfterDiscount }
 *
 * Valores:
 *   - servicesUrl        (String) — URL GET del panel de prestaciones
 *   - differenceFormUrl  (String) — URL GET del formulario de diferencia
 *   - admissionRecordId  (Number) — ID admisión si contexto = from_admission
 *   - appointmentId      (Number) — ID cita    si contexto = from_appointment
 *
 * Targets:
 *   - servicesPanelWrapper — div que envuelve turbo-frame#services-panel
 *   - actionsBar           — barra total + botón descuento
 *   - totalDisplay         — <span> con el monto a cobrar
 *   - discountBadge        — badge "con descuento"
 *   - paymentSection       — div que envuelve turbo-frame#payment-panel
 *   - confirmButton        — botón "Confirmar pago"
 */
export default class extends Controller {
    static targets = [
        'servicesPanelWrapper',
        'actionsBar',
        'totalDisplay',
        'discountBadge',
        'paymentSection',
        'confirmButton',
    ];

    static values = {
        servicesUrl:       String,
        differenceFormUrl: String,
        admissionRecordId: Number,
        appointmentId:     Number,
    };

    connect() {
        this._patientId           = null;
        this._patientAccountId    = null;
        this._currentTotal        = 0;
        this._discountApplied     = false;
        this._discountedTotal     = null;
    }

    // ── Handlers de eventos ────────────────────────────────────────────────

    /**
     * Disparado por revenue--patient-search al seleccionar un paciente.
     * Activa el panel de prestaciones (lazy load).
     */
    onPatientSelected(event) {
        this._patientId = event.detail.patientId;

        // Cargar el panel de prestaciones en el frame (si aún no se cargó)
        const frame = document.getElementById('services-panel');
        if (frame && !frame.src && this.servicesUrlValue) {
            frame.src = this.servicesUrlValue;
        }
    }

    /**
     * Disparado por revenue--services-selector cada vez que el total cambia.
     * Muestra u oculta la barra de acciones y el panel de pago.
     */
    onServicesTotalChanged(event) {
        this._currentTotal     = event.detail.total ?? 0;
        this._discountApplied  = false;
        this._discountedTotal  = null;

        this._updateTotalDisplay(this._currentTotal);
        this._syncPanelsVisibility();
    }

    /**
     * Disparado por revenue--difference cuando el supervisor aprueba la diferencia.
     * Actualiza el total visible con el monto con descuento.
     */
    onDifferenceApproved(event) {
        const { discountAmount, totalAfterDiscount } = event.detail;

        this._discountApplied  = discountAmount > 0;
        this._discountedTotal  = totalAfterDiscount;

        this._updateTotalDisplay(totalAfterDiscount, true);

        // Scroll suave hacia el panel de pago para que el cajero no lo pierda
        if (this.hasPaymentSectionTarget) {
            this.paymentSectionTarget.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    // ── Diferencia ────────────────────────────────────────────────────────

    /**
     * Carga el formulario de solicitud de diferencia en turbo-frame#difference-panel.
     * Pasa el total actual (con o sin descuento) y el PatientAccount si está disponible.
     */
    requestDifference(event) {
        event.preventDefault();

        if (!this.differenceFormUrlValue) return;

        const frame = document.getElementById('difference-panel');
        if (!frame) return;

        const total = this._discountApplied
            ? (this._discountedTotal ?? this._currentTotal)
            : this._currentTotal;

        const url = new URL(this.differenceFormUrlValue, window.location.origin);
        url.searchParams.set('total_account', String(Math.round(total)));

        const patientAccountId = this._readPatientAccountId();
        if (patientAccountId) {
            url.searchParams.set('patient_account_id', patientAccountId);
        }

        frame.src = url.toString();
    }

    // ── Submit del formulario de pago ────────────────────────────────────

    /**
     * Intercepta el submit del formulario de confirmación de pago.
     * Inyecta en el form:
     *   - patient_id       (desde el campo oculto del frame patient-context)
     *   - services_payload (desde el campo oculto del frame services-panel)
     *
     * No llama a event.preventDefault() → Turbo procesa el submit normalmente
     * y obtiene la respuesta Turbo Stream del PaymentConfirmController.
     */
    onPaymentFormSubmit(event) {
        const patientIdInput    = document.getElementById('payment-patient-id');
        const servicesPayloadIn = document.getElementById('payment-services-payload');

        // patient_id — leer desde patient-context hidden field
        const ctxPatientId = document.getElementById('ctx-patient-id');
        if (ctxPatientId && patientIdInput) {
            patientIdInput.value = ctxPatientId.value;
        } else if (this._patientId && patientIdInput) {
            patientIdInput.value = this._patientId;
        }

        // services_payload — leer desde services-panel hidden field
        const servicesPayloadHidden = document.getElementById('services-payload-hidden');
        if (servicesPayloadHidden && servicesPayloadIn) {
            servicesPayloadIn.value = servicesPayloadHidden.value;
        }

        // Si no hay patient_id, cancelar y avisar
        if (patientIdInput && !patientIdInput.value) {
            event.preventDefault();
            alert('Seleccione un paciente antes de confirmar el pago.');
        }
    }

    // ── Privado ───────────────────────────────────────────────────────────

    _updateTotalDisplay(amount, withDiscount = false) {
        if (this.hasTotalDisplayTarget) {
            this.totalDisplayTarget.textContent = '$ ' + this._formatChilean(amount);
        }

        if (this.hasDiscountBadgeTarget) {
            this.discountBadgeTarget.style.display = withDiscount ? '' : 'none';
        }
    }

    _syncPanelsVisibility() {
        const hasTotal = this._currentTotal > 0;

        // Barra de acciones (total + botón diferencia)
        if (this.hasActionsBarTarget) {
            this.actionsBarTarget.classList.toggle('d-none', !hasTotal);
        }

        // Panel de pago (builder + botón confirmar)
        if (this.hasPaymentSectionTarget) {
            this.paymentSectionTarget.classList.toggle('d-none', !hasTotal);
        }
    }

    _readPatientAccountId() {
        // El frame patient-context renderiza un <input id="ctx-patient-account-id">
        return document.getElementById('ctx-patient-account-id')?.value ?? null;
    }

    _formatChilean(value) {
        return Math.round(Number(value)).toLocaleString('es-CL');
    }
}
