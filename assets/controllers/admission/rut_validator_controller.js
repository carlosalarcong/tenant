import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['form', 'typeSelect', 'queryInput', 'errorMessage'];
    static values = {
        rutTypeId: Number,
    };

    connect() {
        this.updateRutUiState();
        this.validateRutIfNeeded();
    }

    onTypeChange() {
        this.updateRutUiState();
        this.validateRutIfNeeded();
    }

    onQueryInput() {
        if (this.isRutSelected()) {
            this.applyRutMask();
        }
        this.validateRutIfNeeded();
    }

    validateOnSubmit(event) {
        if (!this.validateRutIfNeeded()) {
            event.preventDefault();
            this.queryInputTarget.focus();
        }
    }

    validateRutIfNeeded() {
        if (!this.hasTypeSelectTarget || !this.hasQueryInputTarget) {
            return true;
        }

        if (!this.isRutSelected()) {
            this.clearError();
            return true;
        }

        const rawValue = this.queryInputTarget.value || '';
        const normalizedRut = this.normalizeRut(rawValue);

        if (normalizedRut.length < 2) {
            this.setError('Ingresa un RUT válido (ej: 12.345.678-5).');
            return false;
        }

        if (!this.isValidRut(normalizedRut)) {
            this.setError('RUT inválido. Verifica dígito verificador.');
            return false;
        }

        this.clearError();
        return true;
    }

    updateRutUiState() {
        if (!this.hasQueryInputTarget) {
            return;
        }

        if (this.isRutSelected()) {
            this.queryInputTarget.placeholder = 'Ejemplo: 12.345.678-9';
            this.queryInputTarget.maxLength = 12;
            this.applyRutMask();
            return;
        }

        this.queryInputTarget.placeholder = 'Ejemplo: 12345678-9 o Carlos Alarcón';
        this.queryInputTarget.removeAttribute('maxLength');
    }

    isRutSelected() {
        const selectedValue = Number(this.typeSelectTarget.value || 0);
        if (this.rutTypeIdValue > 0) {
            return selectedValue === this.rutTypeIdValue;
        }

        const selectedText = this.typeSelectTarget.options[this.typeSelectTarget.selectedIndex]?.text || '';
        return selectedText.trim().toLowerCase() === 'rut';
    }

    normalizeRut(value) {
        return value
            .replace(/\./g, '')
            .replace(/-/g, '')
            .replace(/\s+/g, '')
            .toUpperCase();
    }

    applyRutMask() {
        const normalized = this.normalizeRut(this.queryInputTarget.value || '');
        if (normalized === '') {
            return;
        }

        if (normalized.length === 1) {
            this.queryInputTarget.value = normalized;
            return;
        }

        const body = normalized.slice(0, -1).replace(/\D/g, '');
        const verifier = normalized.slice(-1).replace(/[^0-9K]/g, '');
        const bodyWithDots = body.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        this.queryInputTarget.value = verifier ? `${bodyWithDots}-${verifier}` : bodyWithDots;
    }

    isValidRut(normalizedRut) {
        if (!/^\d+[0-9K]$/.test(normalizedRut)) {
            return false;
        }

        const body = normalizedRut.slice(0, -1);
        const verifier = normalizedRut.slice(-1);

        let sum = 0;
        let multiplier = 2;

        for (let i = body.length - 1; i >= 0; i -= 1) {
            sum += Number(body[i]) * multiplier;
            multiplier = multiplier === 7 ? 2 : multiplier + 1;
        }

        const remainder = 11 - (sum % 11);
        const expectedVerifier = remainder === 11 ? '0' : remainder === 10 ? 'K' : String(remainder);

        return verifier === expectedVerifier;
    }

    setError(message) {
        this.queryInputTarget.setCustomValidity(message);
        this.queryInputTarget.classList.add('is-invalid');

        if (this.hasErrorMessageTarget) {
            this.errorMessageTarget.textContent = message;
            this.errorMessageTarget.classList.remove('d-none');
        }
    }

    clearError() {
        this.queryInputTarget.setCustomValidity('');
        this.queryInputTarget.classList.remove('is-invalid');

        if (this.hasErrorMessageTarget) {
            this.errorMessageTarget.textContent = '';
            this.errorMessageTarget.classList.add('d-none');
        }
    }
}
