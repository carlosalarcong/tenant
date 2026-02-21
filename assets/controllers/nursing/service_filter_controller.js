import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['input', 'card', 'empty']

  filter() {
    const query = this.inputTarget.value.trim().toLowerCase()

    let visible = 0
    this.cardTargets.forEach((card) => {
      const name = card.dataset.serviceName.toLowerCase()
      const matches = name.includes(query)
      card.style.display = matches ? '' : 'none'
      if (matches) visible++
    })

    this.emptyTarget.style.display = visible === 0 ? '' : 'none'
  }

  clear() {
    this.inputTarget.value = ''
    this.filter()
    this.inputTarget.focus()
  }
}
