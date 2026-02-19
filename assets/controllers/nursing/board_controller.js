import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['serviceCard', 'board']

  connect() {
    // setup inicial
  }

  selectService(event) {
    this.serviceCardTargets.forEach((card) => card.classList.remove('active'))
    event.currentTarget.classList.add('active')
  }
}
