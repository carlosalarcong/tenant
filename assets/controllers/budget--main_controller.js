import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'step1',
        'step2',
        'step3',
        'payerSelect',
        'agreementSelect',
        'modeSelect',
        'openPlanWrapper',
        'packagePlanWrapper',
        'insurancePlanSelect',
        'surgeryPackagePlanSelect',
        'professionalSelect',
        'careTypeSelect',
        'originSelect',
        'includesHonorariumsInput',
        'expiresAtInput',
        'observationInput',
        'serviceSearchInput',
        'serviceSearchResults',
        'servicesList',
        'previewFrame',
    ];

    static values = {
        personId: Number,
        agreementsUrl: String,
        searchUrl: { type: String, default: '' },
        validateUrl: String,
        previewUrl: String,
        saveUrl: String,
        showUrlTemplate: String,
    };

    connect() {
        this.services = [];
        this._searchTimeout = null;
        this._renderServices();
        this._syncPlanVisibility();
    }

    goToStep2(event) {
        event.preventDefault();

        if (!this.payerSelectTarget.value) {
            alert('Seleccione un financiador antes de continuar.');
            return;
        }

        if (!this._getSelectedPlanId()) {
            alert('Seleccione un plan antes de continuar.');
            return;
        }

        this._showStep(2);
    }

    goToStep1(event) {
        event.preventDefault();
        this._showStep(1);
    }

    backToStep2(event) {
        event.preventDefault();
        this._showStep(2);
    }

    async goToStep3(event) {
        event.preventDefault();

        if (this.services.length === 0) {
            alert('Agregue al menos una prestación antes de ver el resumen.');
            return;
        }

        const response = await fetch(this.previewUrlValue, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(this._buildPayload()),
        });

        const html = await response.text();
        this.previewFrameTarget.innerHTML = html;
        this._showStep(3);
    }

    async onPayerChange() {
        const payerId = this.payerSelectTarget.value;
        this._resetAgreements();

        if (!payerId) {
            return;
        }

        const url = new URL(this.agreementsUrlValue, window.location.origin);
        url.searchParams.set('payerId', payerId);

        const response = await fetch(url.toString(), {
            headers: { 'Accept': 'application/json' },
        });

        const agreements = await response.json();
        agreements.forEach((agreement) => {
            const option = document.createElement('option');
            option.value = agreement.id;
            option.textContent = agreement.name;
            this.agreementSelectTarget.appendChild(option);
        });
    }

    onModeChange() {
        this._syncPlanVisibility();
    }

    searchService() {
        const q = this.serviceSearchInputTarget.value.trim();

        window.clearTimeout(this._searchTimeout);

        if (q.length < 2 || !this.searchUrlValue) {
            this._clearSearchResults();
            return;
        }

        this._searchTimeout = window.setTimeout(async () => {
            const url = new URL(this.searchUrlValue, window.location.origin);
            url.searchParams.set('q', q);
            url.searchParams.set('modalidad', this.modeSelectTarget.value);

            const response = await fetch(url.toString(), {
                headers: { 'Accept': 'application/json' },
            });

            const items = await response.json();
            this._renderSearchResults(items);
        }, 300);
    }

    async selectService(event) {
        event.preventDefault();

        const item = {
            id: Number.parseInt(event.currentTarget.dataset.id || '0', 10),
            code: event.currentTarget.dataset.code || '',
            name: event.currentTarget.dataset.name || '',
            itemType: event.currentTarget.dataset.itemType || null,
        };

        const planId = this._getSelectedPlanId();
        const branchPayerId = this._getSelectedBranchPayerId();

        if (!planId) {
            this._renderSearchError('Seleccione un plan antes de agregar prestaciones.');
            return;
        }

        const response = await fetch(this.validateUrlValue, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                medicalServiceId: item.id,
                planId,
                branchPayerId,
                modalidad: this.modeSelectTarget.value,
            }),
        });

        const data = await response.json();
        if (!data.valid) {
            this._renderSearchError(data.error ?? 'No fue posible validar la prestación.');
            return;
        }

        this.addService({
            id: item.id,
            label: item.name,
            priceIsapre: data.priceIsapre,
            itemType: item.itemType ?? 'pabellon',
        });

        this.serviceSearchInputTarget.value = '';
        this._clearSearchResults();
    }

    addService(serviceData) {
        const exists = this.services.some((service) => service.id === serviceData.id);
        if (exists) {
            this._renderServiceMessage('La prestación ya fue agregada.', 'warning');
            return;
        }

        this.services.push(serviceData);
        this._renderServices();
    }

    removeService(event) {
        const index = Number.parseInt(event.currentTarget.dataset.index, 10);
        if (!Number.isInteger(index)) {
            return;
        }

        this.services.splice(index, 1);
        this._renderServices();
    }

    async save(event) {
        event.preventDefault();

        const response = await fetch(this.saveUrlValue, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(this._buildPayload()),
        });

        const data = await response.json();
        if (!response.ok || !data.success) {
            alert(data.error ?? 'No fue posible guardar el presupuesto.');
            return;
        }

        const url = this.showUrlTemplateValue.replace('__ID__', String(data.budgetId));
        if (window.Turbo?.visit) {
            window.Turbo.visit(url);
            return;
        }

        window.location.href = url;
    }

    _showStep(step) {
        this.step1Target.classList.toggle('d-none', step !== 1);
        this.step2Target.classList.toggle('d-none', step !== 2);
        this.step3Target.classList.toggle('d-none', step !== 3);
    }

    _syncPlanVisibility() {
        const isOpen = this.modeSelectTarget.value === 'abierta';
        this.openPlanWrapperTarget.classList.toggle('d-none', !isOpen);
        this.packagePlanWrapperTarget.classList.toggle('d-none', isOpen);
    }

    _renderServices() {
        if (this.services.length === 0) {
            this.servicesListTarget.innerHTML = '<div class="text-muted">Aún no hay prestaciones agregadas.</div>';
            return;
        }

        this.servicesListTarget.innerHTML = this.services.map((service, index) => `
            <div class="d-flex justify-content-between align-items-center border rounded px-3 py-2 mb-2">
                <div>
                    <div class="fw-semibold">${this._escapeHtml(service.label)}</div>
                    <div class="text-muted small">ID ${service.id}</div>
                </div>
                <button class="btn btn-sm btn-outline-danger" data-index="${index}" data-action="click->budget--main#removeService">
                    Quitar
                </button>
            </div>
        `).join('');
    }

    _resetAgreements() {
        this.agreementSelectTarget.innerHTML = '<option value="">Sin convenio</option>';
    }

    _renderSearchResults(items) {
        if (!this.hasServiceSearchResultsTarget) {
            return;
        }

        if (!Array.isArray(items) || items.length === 0) {
            this.serviceSearchResultsTarget.innerHTML = '<div class="list-group"><div class="list-group-item text-muted">Sin resultados</div></div>';
            return;
        }

        this.serviceSearchResultsTarget.innerHTML = `
            <ul class="list-group">
                ${items.map((item) => `
                    <li class="list-group-item p-0">
                        <button
                            type="button"
                            class="btn btn-link text-start text-decoration-none w-100 px-3 py-2"
                            data-action="click->budget--main#selectService"
                            data-id="${item.id}"
                            data-code="${this._escapeHtml(item.code ?? '')}"
                            data-name="${this._escapeHtml(item.name ?? '')}"
                            data-item-type="${this._escapeHtml(item.itemType ?? '')}">
                            <div class="fw-semibold">${this._escapeHtml(item.name ?? '')}</div>
                            <div class="small text-muted">${this._escapeHtml(item.code ?? 'Sin código')}</div>
                        </button>
                    </li>
                `).join('')}
            </ul>
        `;
    }

    _renderSearchError(message) {
        if (!this.hasServiceSearchResultsTarget) {
            return;
        }

        this.serviceSearchResultsTarget.innerHTML = `<div class="text-danger small mt-2">${this._escapeHtml(message)}</div>`;
    }

    _clearSearchResults() {
        if (this.hasServiceSearchResultsTarget) {
            this.serviceSearchResultsTarget.innerHTML = '';
        }
    }

    _getSelectedPlanId() {
        return this.modeSelectTarget.value === 'abierta'
            ? Number.parseInt(this.insurancePlanSelectTarget.value || '0', 10)
            : Number.parseInt(this.surgeryPackagePlanSelectTarget.value || '0', 10);
    }

    _getSelectedBranchPayerId() {
        const select = this.modeSelectTarget.value === 'abierta'
            ? this.insurancePlanSelectTarget
            : this.surgeryPackagePlanSelectTarget;

        const option = select.selectedOptions[0];
        return Number.parseInt(option?.dataset.branchPayerId || '0', 10);
    }

    _buildPayload() {
        return {
            personId: this.personIdValue,
            payerId: Number.parseInt(this.payerSelectTarget.value || '0', 10) || null,
            agreementId: Number.parseInt(this.agreementSelectTarget.value || '0', 10) || null,
            insurancePlanId: Number.parseInt(this.insurancePlanSelectTarget.value || '0', 10) || null,
            surgeryPackagePlanId: Number.parseInt(this.surgeryPackagePlanSelectTarget.value || '0', 10) || null,
            professionalId: Number.parseInt(this.professionalSelectTarget.value || '0', 10) || null,
            careTypeId: Number.parseInt(this.careTypeSelectTarget.value || '0', 10) || null,
            originId: Number.parseInt(this.originSelectTarget.value || '0', 10) || null,
            modalidad: this.modeSelectTarget.value,
            includesHonorariums: this.includesHonorariumsInputTarget.checked,
            expiresAt: this.expiresAtInputTarget.value || null,
            observation: this.observationInputTarget.value.trim() || null,
            serviceIds: this.services.map((service) => service.id),
        };
    }

    _escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
}
