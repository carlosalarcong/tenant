import { Controller } from '@hotwired/stimulus';

/**
 * patient-search-controller
 *
 * Gestiona el buscador de pacientes para el flujo de caja.
 *
 * Funcionamiento:
 *  1. El usuario escribe en el <input> → debounce 300 ms → fetch JSON a /find?q=...
 *  2. Se renderiza un dropdown tipo list-group con los resultados.
 *  3. Al seleccionar un paciente:
 *       a) Se carga el Turbo Frame #patient-context con el endpoint /{id}/context.
 *       b) Se emite el evento nativo `patient:selected` con { patientId } para
 *          que el orquestador principal pueda reaccionar.
 *
 * Valores Stimulus:
 *   - searchUrl (String): URL del endpoint JSON /find
 *
 * Targets:
 *   - input:    <input type="text"> de búsqueda
 *   - dropdown: contenedor del dropdown de resultados
 */
export default class extends Controller {
    static targets = ['input', 'dropdown'];

    static values = {
        searchUrl: String,
    };

    connect() {
        this._debounceTimer = null;
        this._handleDocumentClick = this._onDocumentClick.bind(this);
        document.addEventListener('click', this._handleDocumentClick);
    }

    disconnect() {
        clearTimeout(this._debounceTimer);
        document.removeEventListener('click', this._handleDocumentClick);
    }

    // ── Handlers de eventos ────────────────────────────────────────────────

    onInput(event) {
        clearTimeout(this._debounceTimer);
        const query = event.target.value.trim();

        if (query.length < 2) {
            this.hideDropdown();
            return;
        }

        this._debounceTimer = setTimeout(() => this._fetchResults(query), 300);
    }

    onKeydown(event) {
        if (event.key === 'Escape') {
            this.hideDropdown();
        }
    }

    // ── Fetch ──────────────────────────────────────────────────────────────

    async _fetchResults(query) {
        try {
            const url = new URL(this.searchUrlValue, window.location.origin);
            url.searchParams.set('q', query);

            const response = await fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) return;

            const data = await response.json();
            this._renderDropdown(data);
        } catch {
            // Silenciar errores de red para no bloquear al usuario
        }
    }

    // ── Renderizado del dropdown ───────────────────────────────────────────

    _renderDropdown(results) {
        const dropdown = this.dropdownTarget;

        if (results.length === 0) {
            dropdown.innerHTML =
                '<button type="button" class="list-group-item list-group-item-action disabled text-muted small">' +
                'Sin resultados para la búsqueda</button>';
        } else {
            dropdown.innerHTML = results
                .map(
                    (r) =>
                        `<button type="button"
                                class="list-group-item list-group-item-action py-2"
                                data-patient-id="${r.id}"
                                data-context-url="${this._esc(r.contextUrl)}">
                            <div class="fw-semibold">${this._esc(r.fullName)}</div>
                            <div class="text-muted small">${this._esc(r.rut)} | ${this._esc(r.birthDate ?? '—')}</div>
                        </button>`
                )
                .join('');
        }

        dropdown.style.display = 'block';

        dropdown.querySelectorAll('[data-patient-id]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const patientId = parseInt(btn.dataset.patientId, 10);
                const contextUrl = btn.dataset.contextUrl;
                this._selectPatient(patientId, contextUrl);
            });
        });
    }

    // ── Selección del paciente ─────────────────────────────────────────────

    _selectPatient(patientId, contextUrl) {
        this.hideDropdown();

        // Mostrar el nombre elegido en el input
        const selectedBtn = this.dropdownTarget.querySelector(`[data-patient-id="${patientId}"]`);
        if (this.hasInputTarget && selectedBtn) {
            const name = selectedBtn.querySelector('.fw-semibold')?.textContent ?? '';
            this.inputTarget.value = name;
        }

        // Cargar el Turbo Frame de contexto
        const contextFrame = document.getElementById('patient-context');
        if (contextFrame && contextUrl) {
            contextFrame.src = contextUrl;
        }

        // Notificar al orquestador principal
        this.element.dispatchEvent(
            new CustomEvent('patient:selected', {
                bubbles: true,
                detail: { patientId },
            })
        );
    }

    // ── Utilidades ────────────────────────────────────────────────────────

    hideDropdown() {
        if (this.hasDropdownTarget) {
            this.dropdownTarget.style.display = 'none';
            this.dropdownTarget.innerHTML = '';
        }
    }

    _onDocumentClick(event) {
        if (!this.element.contains(event.target)) {
            this.hideDropdown();
        }
    }

    /** Escapa caracteres especiales HTML para inserción en atributos/contenido. */
    _esc(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
}
