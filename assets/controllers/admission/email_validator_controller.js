import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['email', 'secondaryEmail'];

    connect() {
        this.validateEmailField(this.emailTarget, 'El email principal no es válido.');
        this.validateEmailField(this.secondaryEmailTarget, 'El email secundario no es válido.');
    }

    validatePrimary() {
        this.validateEmailField(this.emailTarget, 'El email principal no es válido.');
    }

    validateSecondary() {
        this.validateEmailField(this.secondaryEmailTarget, 'El email secundario no es válido.');
    }

    validateOnSubmit(event) {
        const primaryOk = this.validateEmailField(this.emailTarget, 'El email principal no es válido.');
        const secondaryOk = this.validateEmailField(this.secondaryEmailTarget, 'El email secundario no es válido.');

        if (!primaryOk || !secondaryOk) {
            event.preventDefault();
            if (!primaryOk) {
                this.emailTarget.focus();
                return;
            }
            this.secondaryEmailTarget.focus();
        }
    }

    validateEmailField(input, message) {
        if (!input) {
            return true;
        }

        const value = (input.value || '').trim();
        if (value === '') {
            this.setValid(input);
            return true;
        }

        const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/i.test(value);
        if (!isValid) {
            this.setInvalid(input, message);
            return false;
        }

        this.setValid(input);
        return true;
    }

    setInvalid(input, message) {
        input.setCustomValidity(message);
        input.classList.add('is-invalid');
        const feedback = this.getOrCreateFeedback(input);
        feedback.textContent = message;
    }

    setValid(input) {
        input.setCustomValidity('');
        input.classList.remove('is-invalid');
        const feedback = input.parentElement?.querySelector('.invalid-feedback.admission-email-feedback');
        if (feedback) {
            feedback.remove();
        }
    }

    getOrCreateFeedback(input) {
        let feedback = input.parentElement?.querySelector('.invalid-feedback.admission-email-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback admission-email-feedback';
            input.insertAdjacentElement('afterend', feedback);
        }
        return feedback;
    }
}
