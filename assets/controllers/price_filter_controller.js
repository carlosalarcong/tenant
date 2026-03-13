import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static targets = ['payerType', 'payer']
    static values  = { url: String }

    filterPayers() {
        const payerTypeId = this.payerTypeTarget.value
        const payerSelect = this.payerTarget

        if (!payerTypeId) {
            this.#showAllOptions(payerSelect)
            return
        }

        const url = this.urlValue.replace('__ID__', payerTypeId)

        fetch(url)
            .then(r => r.json())
            .then(payers => {
                payerSelect.innerHTML = '<option value="">— Seleccione Financiador —</option>'
                payers.forEach(p => {
                    const opt = document.createElement('option')
                    opt.value = p.id
                    opt.textContent = p.name
                    payerSelect.appendChild(opt)
                })
            })
    }

    #showAllOptions(select) {
        select.querySelectorAll('option[data-payer-type]').forEach(o => o.style.display = '')
    }
}
