import { Controller } from '@hotwired/stimulus';
import { renderStreamMessage } from '@hotwired/turbo';

/**
 * services-selector-controller  (revenue--services-selector)
 *
 * Gestiona la selección y edición de prestaciones en el panel de caja.
 *
 * Flujo:
 *  1. El usuario escribe en el buscador → debounce 300ms → fetch JSON /search
 *  2. Dropdown de resultados → al seleccionar, pre-carga nombre e id
 *  3. El cajero ingresa cantidad, monto y descuento → clic en "+"
 *  4. Fetch POST /add → Turbo Stream appends la fila → recalculateAll()
 *  5. Clic "×" en una fila → Fetch DELETE /remove → Turbo Stream removes la fila → recalculateAll()
 *  6. Cambio de cantidad o descuento en una fila existente → recalculateAll() en tiempo real
 *
 * recalculateAll():
 *   - Lee todas las filas del DOM (data attributes + inputs)
 *   - Actualiza #services-total-display
 *   - Serializa a JSON en el hidden services_payload
 *   - Emite CustomEvent 'services:total-changed' con { total }
 *
 * Valores Stimulus:
 *   - searchUrl (String)
 *   - addUrl    (String)
 *   - removeUrl (String)
 *   - csrfAdd   (String)
 *   - csrfRemove (String)
 */
export default class extends Controller {
    static targets = [
        'searchInput',
        'searchDropdown',
        'selectedItemId',
        'selectedItemName',
        'addQuantity',
        'addUnitAmount',
        'addDiscount',
        'addButton',
        'totalDisplay',
        'payloadHidden',
    ];

    static values = {
        searchUrl:   String,
        addUrl:      String,
        removeUrl:   String,
        csrfAdd:     String,
        csrfRemove:  String,
    };

    connect() {
        this._debounceTimer      = null;
        this._handleDocumentClick = this._onDocumentClick.bind(this);
        document.addEventListener('click', this._handleDocumentClick);
        this.recalculateAll();
    }

    disconnect() {
        clearTimeout(this._debounceTimer);
        document.removeEventListener('click', this._handleDocumentClick);
    }

    // ── Búsqueda con autocomplete ──────────────────────────────────────────

    onSearchInput(event) {
        clearTimeout(this._debounceTimer);
        const q = event.target.value.trim();

        if (!q) {
            this.selectedItemIdTarget.value   = '';
            this.selectedItemNameTarget.value = '';
            this._syncAddButton();
        }

        if (q.length < 2) {
            this._hideDropdown();
            return;
        }

        this._debounceTimer = setTimeout(() => this._fetchSearch(q), 300);
    }

    onSearchKeydown(event) {
        if (event.key === 'Escape') this._hideDropdown();
    }

    async _fetchSearch(query) {
        try {
            const url = new URL(this.searchUrlValue, window.location.origin);
            url.searchParams.set('q', query);
            const res = await fetch(url.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!res.ok) return;
            this._renderDropdown(await res.json());
        } catch {
            // Silenciar errores de red
        }
    }

    _renderDropdown(results) {
        const dd = this.searchDropdownTarget;

        if (results.length === 0) {
            dd.innerHTML =
                '<button type="button" class="list-group-item list-group-item-action disabled text-muted small">' +
                'Sin resultados</button>';
        } else {
            dd.innerHTML = results
                .map(
                    (r) =>
                        `<button type="button"
                                 class="list-group-item list-group-item-action py-2"
                                 data-item-id="${r.id}"
                                 data-item-name="${this._esc(r.name)}"
                                 data-unit-amount="${r.unitAmount ?? ''}">
                            <span class="fw-semibold">${this._esc(r.name)}</span>
                            ${r.unitAmount !== null ? `<span class="text-muted small"> — $ ${this._formatChilean(r.unitAmount)}</span>` : ''}
                         </button>`
                )
                .join('');
        }

        dd.style.display = 'block';
        dd.querySelectorAll('[data-item-id]').forEach((btn) => {
            btn.addEventListener('click', () => this._selectItem(btn));
        });
    }

    _selectItem(btn) {
        this._hideDropdown();
        this.searchInputTarget.value      = btn.dataset.itemName;
        this.selectedItemIdTarget.value   = btn.dataset.itemId;
        this.selectedItemNameTarget.value = btn.dataset.itemName;
        if (btn.dataset.unitAmount) {
            this.addUnitAmountTarget.value = btn.dataset.unitAmount;
        }
        this._syncAddButton();
    }

    _hideDropdown() {
        if (this.hasSearchDropdownTarget) {
            this.searchDropdownTarget.style.display = 'none';
            this.searchDropdownTarget.innerHTML     = '';
        }
    }

    _syncAddButton() {
        if (!this.hasAddButtonTarget) return;
        this.addButtonTarget.disabled = !this.selectedItemIdTarget.value;
    }

    // ── Agregar fila ──────────────────────────────────────────────────────

    async onAddClick() {
        const billingItemId = this.selectedItemIdTarget.value;
        if (!billingItemId) return;

        const formData = new FormData();
        formData.append('billingItemId', billingItemId);
        formData.append('quantity',      this.addQuantityTarget.value    || '1');
        formData.append('unitAmount',    this.addUnitAmountTarget.value  || '0');
        formData.append('discount',      this.addDiscountTarget.value    || '0');
        formData.append('_csrf_token',   this.csrfAddValue);

        try {
            const res = await fetch(this.addUrlValue, {
                method:  'POST',
                body:    formData,
                headers: { Accept: 'text/vnd.turbo-stream.html' },
            });

            if (!res.ok) return;

            renderStreamMessage(await res.text());
            this._resetAddForm();
            setTimeout(() => this.recalculateAll(), 50);
        } catch {
            // Silenciar errores de red
        }
    }

    _resetAddForm() {
        this.searchInputTarget.value      = '';
        this.selectedItemIdTarget.value   = '';
        this.selectedItemNameTarget.value = '';
        this.addQuantityTarget.value      = '1';
        this.addUnitAmountTarget.value    = '';
        this.addDiscountTarget.value      = '0';
        this._syncAddButton();
    }

    // ── Eliminar fila ─────────────────────────────────────────────────────

    async onRemoveClick(event) {
        const rowId = event.params.rowId;
        if (!rowId) return;

        try {
            const res = await fetch(this.removeUrlValue, {
                method:  'DELETE',
                body:    JSON.stringify({ rowId, _csrf_token: this.csrfRemoveValue }),
                headers: {
                    'Content-Type': 'application/json',
                    Accept:          'text/vnd.turbo-stream.html',
                },
            });

            if (!res.ok) return;

            renderStreamMessage(await res.text());
            setTimeout(() => this.recalculateAll(), 50);
        } catch {
            // Silenciar errores de red
        }
    }

    // ── Cambios en tiempo real dentro de una fila ─────────────────────────

    onRowChange(event) {
        const input = event.target;
        const rowId = input.dataset.rowId;
        if (!rowId) return;

        const row = document.getElementById(`service-row-${rowId}`);
        if (!row) return;

        const unitAmount = parseFloat(row.dataset.unitAmount || '0');
        const quantity   = parseInt(row.querySelector('[data-field="quantity"]')?.value || '0', 10);
        const discount   = parseFloat(row.querySelector('[data-field="discount"]')?.value || '0');
        const lineTotal  = Math.max(0, quantity * unitAmount - discount);

        const totalCell = document.getElementById(`service-row-total-${rowId}`);
        if (totalCell) {
            totalCell.textContent = '$ ' + this._formatChilean(lineTotal);
        }

        this.recalculateAll();
    }

    // ── Total global + payload ────────────────────────────────────────────

    recalculateAll() {
        const rows       = this._readRowsFromDom();
        const grandTotal = rows.reduce((sum, r) => sum + r.totalAmount, 0);

        // Actualizar total visible
        if (this.hasTotalDisplayTarget) {
            this.totalDisplayTarget.textContent = '$ ' + this._formatChilean(grandTotal);
        }

        // Serializar payload para envío con el formulario principal
        if (this.hasPayloadHiddenTarget) {
            this.payloadHiddenTarget.value = JSON.stringify(
                rows.map((r) => ({
                    rowId:         r.rowId,
                    billingItemId: r.billingItemId,
                    name:          r.name,
                    quantity:      r.quantity,
                    unitAmount:    r.unitAmount.toFixed(2),
                    discount:      r.discount.toFixed(2),
                    totalAmount:   r.totalAmount.toFixed(2),
                }))
            );
        }

        // Notificar al panel de pagos
        this.element.dispatchEvent(
            new CustomEvent('services:total-changed', {
                bubbles: true,
                detail:  { total: grandTotal },
            })
        );
    }

    _readRowsFromDom() {
        const rows      = [];
        const tableBody = document.getElementById('services-table-body');
        if (!tableBody) return rows;

        tableBody.querySelectorAll('[data-service-row]').forEach((tr) => {
            const rowId         = tr.dataset.rowId         ?? '';
            const billingItemId = parseInt(tr.dataset.billingItemId ?? '0', 10);
            const name          = tr.dataset.itemName      ?? '';
            const unitAmount    = parseFloat(tr.dataset.unitAmount  ?? '0');
            const quantity      = parseInt(tr.querySelector('[data-field="quantity"]')?.value ?? '0', 10);
            const discount      = parseFloat(tr.querySelector('[data-field="discount"]')?.value ?? '0');
            const totalAmount   = Math.max(0, quantity * unitAmount - discount);

            rows.push({ rowId, billingItemId, name, quantity, unitAmount, discount, totalAmount });
        });

        return rows;
    }

    // ── Utilidades ────────────────────────────────────────────────────────

    _onDocumentClick(event) {
        if (!this.element.contains(event.target)) this._hideDropdown();
    }

    _formatChilean(value) {
        return Math.round(Number(value)).toLocaleString('es-CL');
    }

    _esc(str) {
        return String(str ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
}
