import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        // Selectores de carga dinámica
        'branchSelect',
        'serviceSelect',
        'payerSelect',
        'agreementSelect',
        'bedSelect',
        'careTypeSelect',
        'insurancePlanSelect',
        'servicePackageSelect',
        'professionalSelect',
        'specialtySelect',
        'originSelect',
        // Toggle "otro origen"
        'otherOriginToggle',
        'otherOriginInput',
        'otherOriginContainer',
        // Autofill tipo cama
        'bedTypeInput',
        // Errores de API
        'errorMessage',
        // Botón submit (estado loading)
        'submitBtn',
        'submitBtnLabel',
        'submitBtnSpinner',
        // Búsqueda de tutor
        'tutorDocumentInput',
        'tutorNameInput',
        'tutorSearchBtn',
        'tutorSearchBtnLabel',
        'tutorSearchSpinner',
    ];

    connect() {
        if (this.hasBranchSelectTarget) {
            this.loadInitialData();
        }
        // Escuchar eventos de envío de formulario para estado loading
        const form = this.element.querySelector('form');
        if (form) {
            this._onSubmitStart = this.onFormSubmitStart.bind(this);
            this._onSubmitEnd   = this.onFormSubmitEnd.bind(this);
            form.addEventListener('turbo:submit-start', this._onSubmitStart);
            form.addEventListener('turbo:submit-end',   this._onSubmitEnd);
        }
    }

    disconnect() {
        const form = this.element.querySelector('form');
        if (form && this._onSubmitStart) {
            form.removeEventListener('turbo:submit-start', this._onSubmitStart);
            form.removeEventListener('turbo:submit-end',   this._onSubmitEnd);
        }
    }

    // ── Carga inicial de todos los selectores ──────────────────────────────

    async loadInitialData() {
        await Promise.all([
            this.loadServices(),
            this.loadPayers(),
            this.loadCareTypes(),
            this.loadInsurancePlans(),
            this.loadServicePackages(),
            this.loadProfessionals(),
            this.loadSpecialties(),
            this.loadOrigins(),
        ]);

        if (this.hasPayerSelectTarget && this.payerSelectTarget.value && this.hasAgreementSelectTarget) {
            await this.loadAgreements(this.payerSelectTarget.value);
        }

        if (this.hasServiceSelectTarget && this.serviceSelectTarget.value && this.hasBedSelectTarget) {
            await this.loadBeds(this.serviceSelectTarget.value);
            this.syncBedType();
        }

        this.toggleOtherOriginField();
    }

    // ── Handlers de cambio ─────────────────────────────────────────────────

    async onBranchChange() {
        await Promise.all([
            this.loadServices(),
            this.loadPayers(),
            this.loadProfessionals(),
        ]);

        if (this.hasAgreementSelectTarget) {
            this.clearSelect(this.agreementSelectTarget);
        }
        if (this.hasBedSelectTarget) {
            this.clearSelect(this.bedSelectTarget);
        }
    }

    async onServiceChange(event) {
        const serviceId = event.target.value;
        if (!serviceId || !this.hasBedSelectTarget) {
            if (this.hasBedSelectTarget) {
                this.clearSelect(this.bedSelectTarget);
            }
            return;
        }

        await this.loadBeds(serviceId);
        this.syncBedType();
    }

    async onPayerChange(event) {
        const payerId = event.target.value;
        if (!payerId || !this.hasAgreementSelectTarget) {
            if (this.hasAgreementSelectTarget) {
                this.clearSelect(this.agreementSelectTarget);
            }
            return;
        }

        await this.loadAgreements(payerId);
    }

    onBedChange() {
        this.syncBedType();
    }

    onOtherOriginToggle() {
        this.toggleOtherOriginField();
    }

    // ── Toggle animado "Otro origen" ───────────────────────────────────────

    toggleOtherOriginField() {
        if (!this.hasOtherOriginToggleTarget || !this.hasOtherOriginInputTarget) {
            return;
        }

        const enabled = this.otherOriginToggleTarget.checked;
        this.otherOriginInputTarget.disabled = !enabled;
        this.otherOriginInputTarget.required  = enabled;

        if (this.hasOtherOriginContainerTarget) {
            if (enabled) {
                this.otherOriginContainerTarget.classList.add('is-visible');
            } else {
                this.otherOriginContainerTarget.classList.remove('is-visible');
                this.otherOriginInputTarget.value = '';
            }
        } else if (!enabled) {
            this.otherOriginInputTarget.value = '';
        }
    }

    // ── Estado loading del botón Submit ───────────────────────────────────

    onFormSubmitStart() {
        if (!this.hasSubmitBtnTarget) return;

        this.submitBtnTarget.disabled = true;
        if (this.hasSubmitBtnLabelTarget)   this.submitBtnLabelTarget.classList.add('d-none');
        if (this.hasSubmitBtnSpinnerTarget) this.submitBtnSpinnerTarget.classList.remove('d-none');
    }

    onFormSubmitEnd() {
        if (!this.hasSubmitBtnTarget) return;

        this.submitBtnTarget.disabled = false;
        if (this.hasSubmitBtnLabelTarget)   this.submitBtnLabelTarget.classList.remove('d-none');
        if (this.hasSubmitBtnSpinnerTarget) this.submitBtnSpinnerTarget.classList.add('d-none');
    }

    // ── Búsqueda de tutor por N° documento ────────────────────────────────

    async searchTutor() {
        if (!this.hasTutorDocumentInputTarget) return;

        const documentNumber = this.tutorDocumentInputTarget.value.trim();
        if (!documentNumber) {
            this.tutorDocumentInputTarget.focus();
            return;
        }

        // Mostrar spinner
        if (this.hasTutorSearchBtnTarget)       this.tutorSearchBtnTarget.disabled = true;
        if (this.hasTutorSearchBtnLabelTarget)  this.tutorSearchBtnLabelTarget.classList.add('d-none');
        if (this.hasTutorSearchSpinnerTarget)   this.tutorSearchSpinnerTarget.classList.remove('d-none');

        try {
            const data = await this.fetchJson(
                `/api/admission/tutor-search?document=${encodeURIComponent(documentNumber)}`
            );
            if (data && data.name && this.hasTutorNameInputTarget) {
                this.tutorNameInputTarget.value = data.name;
            }
        } catch (error) {
            // El endpoint aún no existe; no mostramos error al usuario
            console.warn('Búsqueda de tutor no disponible:', error.message);
        } finally {
            if (this.hasTutorSearchBtnTarget)       this.tutorSearchBtnTarget.disabled = false;
            if (this.hasTutorSearchBtnLabelTarget)  this.tutorSearchBtnLabelTarget.classList.remove('d-none');
            if (this.hasTutorSearchSpinnerTarget)   this.tutorSearchSpinnerTarget.classList.add('d-none');
        }
    }

    // ── Carga de datos vía API ─────────────────────────────────────────────

    async loadServices() {
        if (!this.hasServiceSelectTarget) return;

        this.setLoadingState(this.serviceSelectTarget, true);
        try {
            const branchId = this.hasBranchSelectTarget ? this.branchSelectTarget.value : '';
            const query    = branchId ? `?branch=${encodeURIComponent(branchId)}` : '';
            const data     = await this.fetchJson(`/api/admission/services${query}`);
            this.populateSelect(this.serviceSelectTarget, data);
            this.hideError();
        } catch (error) {
            this.handleFetchError('servicios', error);
            this.clearSelect(this.serviceSelectTarget);
        } finally {
            this.setLoadingState(this.serviceSelectTarget, false);
        }
    }

    async loadPayers() {
        if (!this.hasPayerSelectTarget) return;

        this.setLoadingState(this.payerSelectTarget, true);
        try {
            const branchId = this.hasBranchSelectTarget ? this.branchSelectTarget.value : '';
            const query    = branchId ? `?branch=${encodeURIComponent(branchId)}` : '';
            const data     = await this.fetchJson(`/api/admission/payers${query}`);
            this.populateSelect(this.payerSelectTarget, data);
            this.hideError();
        } catch (error) {
            this.handleFetchError('financiadores', error);
            this.clearSelect(this.payerSelectTarget);
        } finally {
            this.setLoadingState(this.payerSelectTarget, false);
        }
    }

    async loadAgreements(payerId) {
        this.setLoadingState(this.agreementSelectTarget, true);
        try {
            const data = await this.fetchJson(`/api/admission/agreements?payer=${encodeURIComponent(payerId)}`);
            this.populateSelect(this.agreementSelectTarget, data);
            this.hideError();
        } catch (error) {
            this.handleFetchError('convenios', error);
            this.clearSelect(this.agreementSelectTarget);
        } finally {
            this.setLoadingState(this.agreementSelectTarget, false);
        }
    }

    async loadBeds(serviceId) {
        this.setLoadingState(this.bedSelectTarget, true);
        try {
            const data = await this.fetchJson(`/api/admission/beds?service=${encodeURIComponent(serviceId)}`);
            this.populateSelect(this.bedSelectTarget, data);
            this.hideError();
        } catch (error) {
            this.handleFetchError('camas', error);
            this.clearSelect(this.bedSelectTarget);
        } finally {
            this.setLoadingState(this.bedSelectTarget, false);
        }
    }

    async loadProfessionals() {
        if (!this.hasProfessionalSelectTarget) return;

        this.setLoadingState(this.professionalSelectTarget, true);
        try {
            const branchId = this.hasBranchSelectTarget ? this.branchSelectTarget.value : '';
            const query    = branchId ? `?branch=${encodeURIComponent(branchId)}` : '';
            const data     = await this.fetchJson(`/api/admission/professionals${query}`);
            this.populateSelect(this.professionalSelectTarget, data);
            this.hideError();
        } catch (error) {
            this.handleFetchError('profesionales', error);
            this.clearSelect(this.professionalSelectTarget);
        } finally {
            this.setLoadingState(this.professionalSelectTarget, false);
        }
    }

    async loadSpecialties() {
        if (!this.hasSpecialtySelectTarget) return;

        this.setLoadingState(this.specialtySelectTarget, true);
        try {
            const data = await this.fetchJson('/api/admission/specialties');
            this.populateSelect(this.specialtySelectTarget, data);
            this.hideError();
        } catch (error) {
            this.handleFetchError('especialidades', error);
            this.clearSelect(this.specialtySelectTarget);
        } finally {
            this.setLoadingState(this.specialtySelectTarget, false);
        }
    }

    async loadOrigins() {
        if (!this.hasOriginSelectTarget) return;

        this.setLoadingState(this.originSelectTarget, true);
        try {
            const data = await this.fetchJson('/api/admission/origins');
            this.populateSelect(this.originSelectTarget, data);
            this.hideError();
        } catch (error) {
            this.handleFetchError('orígenes', error);
            this.clearSelect(this.originSelectTarget);
        } finally {
            this.setLoadingState(this.originSelectTarget, false);
        }
    }

    async loadCareTypes() {
        if (!this.hasCareTypeSelectTarget) return;

        this.setLoadingState(this.careTypeSelectTarget, true);
        try {
            const data = await this.fetchJson('/api/admission/care-types');
            this.populateSelect(this.careTypeSelectTarget, data);
            this.hideError();
        } catch (error) {
            this.handleFetchError('tipos de atención', error);
            this.clearSelect(this.careTypeSelectTarget);
        } finally {
            this.setLoadingState(this.careTypeSelectTarget, false);
        }
    }

    async loadInsurancePlans() {
        if (!this.hasInsurancePlanSelectTarget) return;

        this.setLoadingState(this.insurancePlanSelectTarget, true);
        try {
            const data = await this.fetchJson('/api/admission/insurance-plans');
            this.populateSelect(this.insurancePlanSelectTarget, data);
            this.hideError();
        } catch (error) {
            this.handleFetchError('planes previsionales', error);
            this.clearSelect(this.insurancePlanSelectTarget);
        } finally {
            this.setLoadingState(this.insurancePlanSelectTarget, false);
        }
    }

    async loadServicePackages() {
        if (!this.hasServicePackageSelectTarget) return;

        this.setLoadingState(this.servicePackageSelectTarget, true);
        try {
            const data = await this.fetchJson('/api/admission/service-packages');
            this.populateSelect(this.servicePackageSelectTarget, data);
            this.hideError();
        } catch (error) {
            this.handleFetchError('paquetes', error);
            this.clearSelect(this.servicePackageSelectTarget);
        } finally {
            this.setLoadingState(this.servicePackageSelectTarget, false);
        }
    }

    // ── Utilidades ────────────────────────────────────────────────────────

    async fetchJson(url) {
        const response = await fetch(url, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            const errorData = await response.json().catch(() => ({}));
            throw new Error(errorData.error || `HTTP ${response.status}`);
        }

        return response.json();
    }

    populateSelect(selectElement, items) {
        const currentValue = selectElement.value;
        selectElement.innerHTML = '<option value="">-- Seleccionar --</option>';

        if (this.hasBedSelectTarget && selectElement === this.bedSelectTarget) {
            const groups = new Map();
            const ungrouped = [];

            items.forEach((item) => {
                const roomName = (item.roomName || '').trim();
                if (roomName !== '') {
                    if (!groups.has(roomName)) {
                        groups.set(roomName, []);
                    }
                    groups.get(roomName).push(item);
                } else {
                    ungrouped.push(item);
                }
            });

            groups.forEach((groupItems, roomName) => {
                const optgroup = document.createElement('optgroup');
                optgroup.label = roomName;

                groupItems.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.text = item.name;
                    if (item.bedTypeName) {
                        option.dataset.bedTypeName = item.bedTypeName;
                    }
                    if (String(item.id) === String(currentValue)) {
                        option.selected = true;
                    }
                    optgroup.appendChild(option);
                });

                selectElement.appendChild(optgroup);
            });

            ungrouped.forEach((item) => {
                const option = document.createElement('option');
                option.value = item.id;
                option.text = item.name;
                if (item.bedTypeName) {
                    option.dataset.bedTypeName = item.bedTypeName;
                }
                if (String(item.id) === String(currentValue)) {
                    option.selected = true;
                }
                selectElement.add(option);
            });

            this.syncBedType();
            return;
        }

        items.forEach((item) => {
            const option = document.createElement('option');
            option.value = item.id;
            option.text  = item.name;
            if (item.bedTypeName) {
                option.dataset.bedTypeName = item.bedTypeName;
            }
            if (String(item.id) === String(currentValue)) {
                option.selected = true;
            }
            selectElement.add(option);
        });

        if (this.hasBedSelectTarget && selectElement === this.bedSelectTarget) {
            this.syncBedType();
        }
    }

    clearSelect(selectElement) {
        selectElement.innerHTML = '<option value="">-- Seleccionar --</option>';
    }

    setLoadingState(selectElement, isLoading) {
        if (isLoading) {
            selectElement.disabled = true;
            selectElement.innerHTML = '<option value="">Cargando...</option>';
        } else {
            selectElement.disabled = false;
        }
    }

    syncBedType() {
        if (!this.hasBedTypeInputTarget || !this.hasBedSelectTarget) return;

        const selectedOption = this.bedSelectTarget.selectedOptions[0];
        this.bedTypeInputTarget.value = selectedOption?.dataset?.bedTypeName || '';
    }

    handleFetchError(resourceName, error) {
        const message = `Error al cargar ${resourceName}: ${error.message}`;
        console.error(message, error);

        if (this.hasErrorMessageTarget) {
            this.errorMessageTarget.textContent = message;
            this.errorMessageTarget.classList.remove('d-none');
        }
    }

    hideError() {
        if (this.hasErrorMessageTarget) {
            this.errorMessageTarget.classList.add('d-none');
        }
    }
}
