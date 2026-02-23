/**
 * revenue--bonoweb
 *
 * Stimulus controller para la integración BonoWeb/Snabb FONASA.
 *
 * Modos de operación (controlado por `modeValue`):
 *
 *   "polling" (default):
 *     - Recarga el Turbo Frame `bonoweb-status` cada `pollInterval` ms.
 *     - Al detectar estado final (Done/Canceled) detiene el polling.
 *     - Si el estado es "Done" emite el evento `bonoweb:confirmed`.
 *
 *   "confirmed":
 *     - Se monta en la tarjeta de éxito (_confirmed.html.twig).
 *     - En connect() auto-completa los campos del payment row con bonoweb.
 *     - Emite `bonoweb:confirmed` para que otros controladores reaccionen.
 *
 * Targets:
 *   statusFrame – el <turbo-frame id="bonoweb-status"> (modo polling)
 *
 * Values:
 *   statusUrl  – URL del endpoint GET /{voucherId}/status
 *   voucherId  – UUID del voucher (para referencia)
 *   copago     – Monto copago (rellenado en modo "confirmed")
 *   mode       – "polling" | "confirmed"
 */
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {

    static targets = ["statusFrame"]

    static values = {
        statusUrl:    String,
        voucherId:    String,
        copago:       { type: Number, default: 0 },
        mode:         { type: String, default: "polling" },
        pollInterval: { type: Number, default: 3000 },
    }

    /** Estados finales — el polling se detiene al recibirlos */
    static FINAL_STATUSES = ["Done", "Canceled"]

    // ── Ciclo de vida ──────────────────────────────────────────────────────────

    connect() {
        if (this.modeValue === "confirmed") {
            this._handleConfirmed()
        }
        // En modo polling el timer arranca cuando el statusFrame se conecta
    }

    disconnect() {
        this._stopPolling()
    }

    // ── Target callbacks ───────────────────────────────────────────────────────

    /**
     * Llamado por Stimulus cuando el <turbo-frame id="bonoweb-status"> se
     * conecta al DOM (incluida cada vez que Turbo lo recarga).
     */
    statusFrameTargetConnected(frame) {
        const status = frame.dataset.bonowebStatus ?? ""

        if (this._isFinal(status)) {
            this._stopPolling()

            if (status === "Done") {
                this._emitConfirmed(frame)
            }
        } else {
            // Seguir polling mientras el estado no sea final
            this._scheduleNextPoll()
        }
    }

    // ── Polling ────────────────────────────────────────────────────────────────

    _scheduleNextPoll() {
        this._stopPolling()
        this._timer = setTimeout(() => this._reloadStatusFrame(), this.pollIntervalValue)
    }

    _reloadStatusFrame() {
        if (!this.hasStatusFrameTarget) return

        const frame = this.statusFrameTarget
        if (this.statusUrlValue) {
            frame.src = this.statusUrlValue
        }
        frame.reload()
    }

    _stopPolling() {
        if (this._timer) {
            clearTimeout(this._timer)
            this._timer = null
        }
    }

    // ── Confirmación ───────────────────────────────────────────────────────────

    /**
     * Modo "confirmed": auto-completa los campos readonly del payment row
     * y emite el evento bonoweb:confirmed.
     */
    _handleConfirmed() {
        this._fillPaymentRow(this.copagoValue, this.voucherIdValue)
        this._dispatchConfirmed(this.copagoValue, this.voucherIdValue)
    }

    /**
     * Modo "polling": el status frame llegó a estado Done.
     * Lee los datos desde los data-attributes del frame.
     */
    _emitConfirmed(frame) {
        const copago    = parseFloat(frame.dataset.bonowebCopago    ?? "0")
        const voucherId = frame.dataset.bonowebVoucherId ?? ""
        this._fillPaymentRow(copago, voucherId)
        this._dispatchConfirmed(copago, voucherId)
    }

    /**
     * Rellena los inputs readonly del payment row con método "bonoweb".
     * Busca por data-method-code="bonoweb" en el documento completo.
     */
    _fillPaymentRow(copago, voucherId) {
        const row = document.querySelector('[data-method-code="bonoweb"]')
        if (!row) return

        const amountInput  = row.querySelector('[data-payment-amount-input]')
        const voucherInput = row.querySelector('[data-bonoweb-voucher-input]')

        if (amountInput)  amountInput.value  = copago
        if (voucherInput) voucherInput.value = voucherId
    }

    /**
     * Dispara el evento bonoweb:confirmed con datos del voucher.
     * Sube por el árbol DOM para que controladores padre puedan escucharlo.
     */
    _dispatchConfirmed(copago, voucherId) {
        this.element.dispatchEvent(new CustomEvent("bonoweb:confirmed", {
            bubbles: true,
            detail: { copago, voucherId },
        }))
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    _isFinal(status) {
        return this.constructor.FINAL_STATUSES.includes(status)
    }
}
