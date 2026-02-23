import { Controller } from '@hotwired/stimulus';

/**
 * post-payment-controller  (revenue--post-payment)
 *
 * Gestiona la pantalla de resumen post-pago:
 *
 *  - Tabs: alterna entre "Detalle del pago" e "Historial del paciente".
 *    El historial se carga vía Turbo Frame de forma lazy la primera vez.
 *
 *  - Anulación: solicita confirmación al usuario mediante window.confirm
 *    antes de enviar el formulario de void. Si el usuario cancela, el form
 *    no se envía y no ocurre ningún cambio.
 *
 * Targets:
 *   - tabList     — <ul> contenedor de los botones de tab
 *   - tabDetail   — contenedor del panel de detalle
 *   - tabHistory  — contenedor del panel de historial (lazy)
 *   - voidForm    — <form> oculto de anulación
 *
 * Valores:
 *   - historyUrl (String) — URL del endpoint /{id}/history
 *   - voidUrl    (String) — URL del endpoint /{id}/void (solo para trazabilidad)
 */
export default class extends Controller {
    static targets = ['tabList', 'tabDetail', 'tabHistory', 'voidForm'];

    static values = {
        historyUrl: String,
        voidUrl:    String,
    };

    connect() {
        this._historyLoaded = false;
    }

    // ── Tabs ──────────────────────────────────────────────────────────────

    /**
     * Alterna entre los paneles "detail" e "history".
     * La primera vez que se abre "history", carga el Turbo Frame vía src.
     *
     * data-action: "click->revenue--post-payment#switchTab"
     * data-revenue--post-payment-tab-param: "detail" | "history"
     */
    switchTab(event) {
        const tab = event.params.tab;

        // Actualizar estado activo en los botones
        if (this.hasTabListTarget) {
            this.tabListTarget.querySelectorAll('[data-action*="switchTab"]').forEach((btn) => {
                btn.classList.toggle('active', btn.dataset.revenuePostPaymentTabParam === tab);
            });
        }

        // Mostrar / ocultar paneles
        const showDetail  = tab === 'detail';
        const showHistory = tab === 'history';

        if (this.hasTabDetailTarget)  this.tabDetailTarget.style.display  = showDetail  ? '' : 'none';
        if (this.hasTabHistoryTarget) this.tabHistoryTarget.style.display = showHistory ? '' : 'none';

        // Lazy-load del historial la primera vez que se abre el tab
        if (showHistory && !this._historyLoaded) {
            this._loadHistory();
        }
    }

    _loadHistory() {
        if (!this.historyUrlValue) return;

        const frame = this.hasTabHistoryTarget
            ? this.tabHistoryTarget.querySelector('turbo-frame#post-payment-history')
            : document.getElementById('post-payment-history');

        if (frame && !frame.src) {
            frame.src = this.historyUrlValue;
            this._historyLoaded = true;
        }
    }

    // ── Anulación con confirmación ────────────────────────────────────────

    /**
     * Muestra un diálogo de confirmación antes de enviar el formulario de anulación.
     * Si el usuario confirma, envía el formulario oculto.
     *
     * data-action: "click->revenue--post-payment#confirmVoid"
     */
    confirmVoid(event) {
        event.preventDefault();

        const confirmed = window.confirm(
            '¿Estás seguro de que deseas anular este pago?\n\nEsta acción marcará el pago y el folio como anulados y no se puede deshacer.'
        );

        if (!confirmed) return;

        if (this.hasVoidFormTarget) {
            this.voidFormTarget.submit();
        }
    }
}
