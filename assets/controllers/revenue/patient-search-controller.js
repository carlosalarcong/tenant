import { Controller } from '@hotwired/stimulus';

/**
 * patient-search-controller
 *
 * Gestiona el buscador de pacientes para el flujo de caja.
 *
 * Sub-tabs:
 *   - Simple   → búsqueda por RUT/identificación (onInput debounced + onSearch button)
 *   - Avanzada → búsqueda por nombre/apellidos con parámetro de coincidencia
 *
 * Flujo compartido:
 *  1. Se renderiza un dropdown tipo list-group con los resultados JSON.
 *  2. Al seleccionar un paciente:
 *       a) Se carga el Turbo Frame #patient-context con el endpoint /{id}/context.
 *       b) Se emite el evento nativo `patient:selected` con { patientId }.
 *
 * Valores Stimulus:
 *   - searchUrl (String): URL del endpoint JSON /find
 *
 * Targets:
 *   - simpleTab:       contenedor del panel Simple
 *   - advancedTab:     contenedor del panel Avanzada
 *   - input:           <input> de identificación (Simple)
 *   - nombre:          <input> nombre (Avanzada)
 *   - apellidoPaterno: <input> apellido paterno (Avanzada)
 *   - apellidoMaterno: <input> apellido materno (Avanzada)
 *   - matchType:       <input type="radio"> parámetros de búsqueda (múltiple)
 *   - dropdown:        contenedor del dropdown de resultados
 */
export default class extends Controller {
    static targets = [
        'simpleTab',
        'advancedTab',
        'input',
        'nombre',
        'apellidoPaterno',
        'apellidoMaterno',
        'matchType',
        'dropdown',
    ];

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

    // ── Tab switching ──────────────────────────────────────────────────────

    showSimple(event) {
        event.preventDefault();
        this.simpleTabTarget.style.display = '';
        this.advancedTabTarget.style.display = 'none';
        this._setActiveTab(event.currentTarget);
        this.hideDropdown();
    }

    showAdvanced(event) {
        event.preventDefault();
        this.simpleTabTarget.style.display = 'none';
        this.advancedTabTarget.style.display = '';
        this._setActiveTab(event.currentTarget);
        this.hideDropdown();
    }

    // ── Handlers Simple ────────────────────────────────────────────────────

    onInput(event) {
        clearTimeout(this._debounceTimer);
        const query = event.target.value.trim();

        if (query.length < 2) {
            this.hideDropdown();
            return;
        }

        this._debounceTimer = setTimeout(() => this._fetch({ q: query }), 300);
    }

    onKeydown(event) {
        if (event.key === 'Escape') {
            this.hideDropdown();
        }
    }

    /** Botón 🔍 Buscar en tab Simple. */
    onSearch(event) {
        event.preventDefault();
        const query = this.inputTarget.value.trim();

        if (query.length < 2) {
            return;
        }

        this._fetch({ q: query });
    }

    // ── Handlers Avanzada ──────────────────────────────────────────────────

    /** Botón 🔍 Buscar en tab Avanzada. */
    onSearchAdvanced(event) {
        event.preventDefault();
        const nombre          = this.nombreTarget.value.trim();
        const apellidoPaterno = this.apellidoPaternoTarget.value.trim();
        const apellidoMaterno = this.apellidoMaternoTarget.value.trim();

        if (!nombre && !apellidoPaterno && !apellidoMaterno) {
            return;
        }

        const matchType = this.matchTypeTargets.find((r) => r.checked)?.value ?? 'exact';

        this._fetch({
            nombre,
            apellido_paterno: apellidoPaterno,
            apellido_materno: apellidoMaterno,
            match_type: matchType,
        });
    }

    /** Botón ✏ Limpiar en tab Avanzada. */
    onClear(event) {
        event.preventDefault();
        this.nombreTarget.value          = '';
        this.apellidoPaternoTarget.value = '';
        this.apellidoMaternoTarget.value = '';
        this.matchTypeTargets.forEach((r) => {
            r.checked = r.value === 'exact';
        });
        this.hideDropdown();
    }

    // ── Fetch ──────────────────────────────────────────────────────────────

    async _fetch(params) {
        try {
            const url = new URL(this.searchUrlValue, window.location.origin);
            for (const [key, value] of Object.entries(params)) {
                if (value !== '' && value !== null && value !== undefined) {
                    url.searchParams.set(key, value);
                }
            }

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
                const patientId  = parseInt(btn.dataset.patientId, 10);
                const contextUrl = btn.dataset.contextUrl;
                this._selectPatient(patientId, contextUrl);
            });
        });
    }

    // ── Selección del paciente ─────────────────────────────────────────────

    _selectPatient(patientId, contextUrl) {
        this.hideDropdown();

        // Mostrar el nombre en el input Simple si está activo
        const selectedBtn = this.dropdownTarget.querySelector(`[data-patient-id="${patientId}"]`);
        if (this.hasInputTarget && selectedBtn && this.simpleTabTarget.style.display !== 'none') {
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
            this.dropdownTarget.innerHTML     = '';
        }
    }

    _setActiveTab(clickedBtn) {
        const nav = clickedBtn.closest('.nav');
        if (!nav) return;
        nav.querySelectorAll('.nav-link').forEach((l) => l.classList.remove('active'));
        clickedBtn.classList.add('active');
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
