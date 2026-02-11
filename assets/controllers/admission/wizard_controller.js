import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'branchSelect',
        'serviceSelect',
        'payerSelect',
        'agreementSelect',
        'bedSelect',
        'professionalSelect',
        'specialtySelect',
        'originSelect',
        'errorMessage',
    ];

    connect() {
        if (this.hasBranchSelectTarget) {
            this.loadInitialData();
        }
    }

    async loadInitialData() {
        await Promise.all([
            this.loadServices(),
            this.loadPayers(),
            this.loadProfessionals(),
            this.loadSpecialties(),
            this.loadOrigins(),
        ]);

        if (this.hasPayerSelectTarget && this.payerSelectTarget.value && this.hasAgreementSelectTarget) {
            await this.loadAgreements(this.payerSelectTarget.value);
        }

        if (this.hasServiceSelectTarget && this.serviceSelectTarget.value && this.hasBedSelectTarget) {
            await this.loadBeds(this.serviceSelectTarget.value);
        }
    }

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

    async loadServices() {
        if (!this.hasServiceSelectTarget) {
            return;
        }
        
        this.setLoadingState(this.serviceSelectTarget, true);
        
        try {
            const branchId = this.hasBranchSelectTarget ? this.branchSelectTarget.value : '';
            const query = branchId ? `?branch=${encodeURIComponent(branchId)}` : '';
            const data = await this.fetchJson(`/api/admission/services${query}`);
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
        if (!this.hasPayerSelectTarget) {
            return;
        }
        
        this.setLoadingState(this.payerSelectTarget, true);
        
        try {
            const branchId = this.hasBranchSelectTarget ? this.branchSelectTarget.value : '';
            const query = branchId ? `?branch=${encodeURIComponent(branchId)}` : '';
            const data = await this.fetchJson(`/api/admission/payers${query}`);
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
        if (!this.hasProfessionalSelectTarget) {
            return;
        }

        this.setLoadingState(this.professionalSelectTarget, true);

        try {
            const branchId = this.hasBranchSelectTarget ? this.branchSelectTarget.value : '';
            const query = branchId ? `?branch=${encodeURIComponent(branchId)}` : '';
            const data = await this.fetchJson(`/api/admission/professionals${query}`);
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
        if (!this.hasSpecialtySelectTarget) {
            return;
        }

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
        if (!this.hasOriginSelectTarget) {
            return;
        }

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

        items.forEach((item) => {
            const option = document.createElement('option');
            option.value = item.id;
            option.text = item.name;
            if (String(item.id) === String(currentValue)) {
                option.selected = true;
            }
            selectElement.add(option);
        });
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
