import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  toggle(event) {
    const button = event.currentTarget
    const key = button.dataset.panelKey
    if (!key) return

    const panel = this.element.querySelector(`[data-panel-key="${key}"].nur-side-section__body`)
    if (!panel) return

    panel.classList.toggle('d-none')
    const isHidden = panel.classList.contains('d-none')

    const label = button.querySelector('[data-role="toggle-label"]')
    if (label) {
      label.textContent = isHidden ? 'Mostrar' : 'Ocultar'
    }

    const icon = button.querySelector('i')
    if (icon) {
      icon.classList.toggle('bx-chevron-down', isHidden)
      icon.classList.toggle('bx-chevron-up', !isHidden)
    }
  }
}
