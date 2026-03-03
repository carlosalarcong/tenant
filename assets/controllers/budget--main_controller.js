import { Controller } from '@hotwired/stimulus'

export default class extends Controller {

  static values = {
    personId:          Number,
    agreementsUrl:     String,
    searchServicesUrl: String,
    packageItemsUrl:   String,
    previewUrl:        String,
    saveUrl:           String,
  }

  static targets = [
    // Steps
    'step1', 'step2', 'step3', 'stepIndicator',
    // Step 1 — config
    'planTypeOpen', 'planTypePackaged',
    'openSection', 'packageSection',
    'insurancePlanSelect', 'surgeryPackagePlanSelect',
    'payerSelect', 'agreementSelect',
    'professionalSelect',
    'isAmbulatoryCheck', 'includesHonorariumsCheck',
    // Step 2 — prestaciones
    'openServicesSection', 'packageServicesSection',
    'serviceSearchInput', 'searchResults', 'selectedServicesList',
    'packageItemsContainer',
    // Step 3 — resumen
    'previewContent', 'observationText', 'saveBtn',
  ]

  connect() {
    this.currentStep = 1
    this.selectedServices = []      // [{id, name, code}]
    this.selectedPackageItems = []  // [{id, name, itemType}]
    this._searchTimeout = null
    this._updateSteps()
  }

  // ─── NAVEGACIÓN ────────────────────────────────────

  nextStep() {
    if (this.currentStep === 1 && !this._validateStep1()) return
    if (this.currentStep === 2 && !this._validateStep2()) return
    if (this.currentStep === 2) { this._loadPreview() }
    this.currentStep = Math.min(this.currentStep + 1, 3)
    this._updateSteps()
  }

  prevStep() {
    this.currentStep = Math.max(this.currentStep - 1, 1)
    this._updateSteps()
  }

  _updateSteps() {
    ;[this.step1Target, this.step2Target, this.step3Target].forEach((el, i) => {
      el.classList.toggle('d-none', i + 1 !== this.currentStep)
    })
    this.stepIndicatorTargets.forEach(el => {
      const s = parseInt(el.dataset.step)
      el.classList.toggle('active', s === this.currentStep)
      el.classList.toggle('text-primary', s === this.currentStep)
      el.classList.toggle('fw-bold', s === this.currentStep)
    })
  }

  _validateStep1() {
    const planType = this._planType()
    if (planType === 'open' && !this.insurancePlanSelectTarget.value) {
      alert('Debe seleccionar un Plan de Salud'); return false
    }
    if (planType === 'packaged' && !this.surgeryPackagePlanSelectTarget.value) {
      alert('Debe seleccionar un Plan Paquetizado'); return false
    }
    return true
  }

  _validateStep2() {
    const ids = this._planType() === 'open'
      ? this.selectedServices : this.selectedPackageItems
    if (!ids.length) {
      alert('Debe agregar al menos una prestación'); return false
    }
    return true
  }

  // ─── PLAN TYPE ─────────────────────────────────────

  changePlanType() {
    const isOpen = this.planTypeOpenTarget.checked
    this.openSectionTarget.classList.toggle('d-none', !isOpen)
    this.packageSectionTarget.classList.toggle('d-none', isOpen)
    this.openServicesSectionTarget.classList.toggle('d-none', !isOpen)
    this.packageServicesSectionTarget.classList.toggle('d-none', isOpen)
  }

  _planType() {
    return this.planTypeOpenTarget.checked ? 'open' : 'packaged'
  }

  // ─── CONVENIOS (AJAX) ──────────────────────────────

  async changePayer() {
    const payerId = this.payerSelectTarget.value
    const sel = this.agreementSelectTarget
    sel.innerHTML = '<option value="">Sin convenio</option>'
    if (!payerId) return
    const data = await fetch(`${this.agreementsUrlValue}?payerId=${payerId}`)
      .then(r => r.json())
    data.forEach(a => {
      sel.insertAdjacentHTML('beforeend', `<option value="${a.id}">${a.name}</option>`)
    })
  }

  // ─── ÍTEMS PLAN PAQUETIZADO (AJAX) ─────────────────

  async changeSurgeryPackagePlan() {
    const planId = this.surgeryPackagePlanSelectTarget.value
    const container = this.packageItemsContainerTarget
    container.innerHTML = '<p class="text-muted small">Seleccione un plan para ver los ítems</p>'
    if (!planId) return
    container.innerHTML = '<p class="small"><span class="spinner-border spinner-border-sm"></span> Cargando...</p>'
    const items = await fetch(`${this.packageItemsUrlValue}?planId=${planId}`)
      .then(r => r.json())
    if (!items.length) {
      container.innerHTML = '<p class="text-muted small">Sin ítems para este plan</p>'
      return
    }
    container.innerHTML = items.map(i => `
      <div class="form-check">
        <input class="form-check-input" type="checkbox" id="pi-${i.id}"
               value="${i.id}" data-name="${i.name}" data-item-type="${i.itemType}"
               data-action="change->budget--main#togglePackageItem">
        <label class="form-check-label" for="pi-${i.id}">
          <span class="badge bg-secondary me-1">${i.itemType}</span>${i.name}
        </label>
      </div>`).join('')
    this.selectedPackageItems = []
  }

  togglePackageItem(event) {
    const { value: id, dataset: { name, itemType } } = event.target
    const intId = parseInt(id)
    if (event.target.checked) {
      this.selectedPackageItems.push({ id: intId, name, itemType })
    } else {
      this.selectedPackageItems = this.selectedPackageItems.filter(i => i.id !== intId)
    }
  }

  // ─── BÚSQUEDA DE SERVICIOS (DEBOUNCE) ──────────────

  searchServices() {
    clearTimeout(this._searchTimeout)
    this._searchTimeout = setTimeout(() => this._doSearch(), 300)
  }

  async _doSearch() {
    const q = this.serviceSearchInputTarget.value.trim()
    const container = this.searchResultsTarget
    if (q.length < 2) { container.innerHTML = ''; return }
    const services = await fetch(
      `${this.searchServicesUrlValue}?q=${encodeURIComponent(q)}`
    ).then(r => r.json())
    container.innerHTML = services.length
      ? services.map(s => `
          <div class="d-flex justify-content-between align-items-center border-bottom py-1 px-2">
            <span><small class="text-muted me-2">${s.code}</small>${s.name}</span>
            <button type="button" class="btn btn-sm btn-outline-primary ms-2"
                    data-id="${s.id}" data-name="${s.name}" data-code="${s.code}"
                    data-action="click->budget--main#addService">
              + Agregar
            </button>
          </div>`).join('')
      : '<p class="text-muted small px-2 py-1">Sin resultados</p>'
  }

  addService(event) {
    const { id, name, code } = event.currentTarget.dataset
    const intId = parseInt(id)
    if (this.selectedServices.find(s => s.id === intId)) return
    this.selectedServices.push({ id: intId, name, code })
    this._renderSelectedServices()
  }

  removeService(event) {
    const id = parseInt(event.currentTarget.dataset.id)
    this.selectedServices = this.selectedServices.filter(s => s.id !== id)
    this._renderSelectedServices()
  }

  _renderSelectedServices() {
    const list = this.selectedServicesListTarget
    if (!this.selectedServices.length) {
      list.innerHTML = '<li class="list-group-item text-muted small">Sin prestaciones</li>'
      return
    }
    list.innerHTML = this.selectedServices.map(s => `
      <li class="list-group-item d-flex justify-content-between align-items-center">
        <span><small class="text-muted me-2">${s.code}</small>${s.name}</span>
        <button type="button" class="btn btn-sm btn-outline-danger"
                data-id="${s.id}" data-action="click->budget--main#removeService">✕</button>
      </li>`).join('')
  }

  // ─── PREVIEW ───────────────────────────────────────

  async _loadPreview() {
    this.previewContentTarget.innerHTML =
      '<p class="text-center py-3"><span class="spinner-border"></span> Calculando precios...</p>'
    try {
      const res = await fetch(this.previewUrlValue, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(this._buildPayload()),
      })
      const data = await res.json()
      this._renderPreview(data.rows || [], this._planType())
    } catch {
      this.previewContentTarget.innerHTML =
        '<p class="text-danger">Error al calcular precios.</p>'
    }
  }

  _renderPreview(rows, planType) {
    const el = this.previewContentTarget
    if (!rows.length) {
      el.innerHTML = '<p class="text-muted">Sin prestaciones.</p>'; return
    }
    let total = 0
    const fmt = v => new Intl.NumberFormat('es-CL',
      { style: 'currency', currency: 'CLP' }).format(v || 0)

    if (planType === 'open') {
      el.innerHTML = `
        <table class="table table-sm table-bordered">
          <thead class="table-light"><tr>
            <th>Prestación</th>
            <th class="text-end">Honorario</th>
            <th class="text-end">Pabellón</th>
            <th class="text-end">Total fila</th>
          </tr></thead>
          <tbody>
            ${rows.map(r => {
              const row = (r.unitPrice || 0) + (r.theatreAmount || 0)
              total += row
              return `<tr>
                <td>${r.name}</td>
                <td class="text-end">${fmt(r.unitPrice)}</td>
                <td class="text-end">${fmt(r.theatreAmount)}
                  <small class="text-muted">(${r.theatreRate === 1 ? '100%' : '50%'})</small></td>
                <td class="text-end fw-bold">${fmt(row)}</td>
              </tr>`
            }).join('')}
          </tbody>
          <tfoot><tr class="table-light">
            <td colspan="3" class="text-end fw-bold">TOTAL</td>
            <td class="text-end fw-bold">${fmt(total)}</td>
          </tr></tfoot>
        </table>`
    } else {
      el.innerHTML = `
        <table class="table table-sm table-bordered">
          <thead class="table-light"><tr>
            <th>Ítem</th><th>Tipo</th>
            <th class="text-end">Precio Isapre</th><th class="text-end">%</th>
          </tr></thead>
          <tbody>
            ${rows.map(r => {
              total += r.priceIsapre || 0
              const lbl = r.priceRate === 0 ? 'excluido'
                        : r.priceRate === 1 ? '100%' : '50%'
              return `<tr>
                <td>${r.name}</td>
                <td><span class="badge bg-secondary">${r.itemType}</span></td>
                <td class="text-end">${fmt(r.priceIsapre)}</td>
                <td class="text-end"><small class="text-muted">${lbl}</small></td>
              </tr>`
            }).join('')}
          </tbody>
          <tfoot><tr class="table-light">
            <td colspan="2" class="text-end fw-bold">TOTAL</td>
            <td class="text-end fw-bold">${fmt(total)}</td><td></td>
          </tr></tfoot>
        </table>`
    }
  }

  // ─── SAVE ──────────────────────────────────────────

  async save() {
    const btn = this.saveBtnTarget
    btn.disabled = true
    btn.textContent = 'Guardando...'
    const payload = this._buildPayload()
    payload.observation = this.observationTextTarget.value || null
    try {
      const res = await fetch(this.saveUrlValue, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      })
      const data = await res.json()
      if (data.success) {
        window.location.href = data.redirectUrl
      } else {
        alert('Error: ' + (data.error || 'desconocido'))
        btn.disabled = false; btn.textContent = 'Guardar Presupuesto'
      }
    } catch {
      alert('Error de conexión')
      btn.disabled = false; btn.textContent = 'Guardar Presupuesto'
    }
  }

  _buildPayload() {
    return {
      planType:            this._planType(),
      planId:              parseInt(this._planType() === 'open'
                             ? this.insurancePlanSelectTarget.value
                             : this.surgeryPackagePlanSelectTarget.value) || null,
      payerId:             parseInt(this.payerSelectTarget.value) || null,
      agreementId:         parseInt(this.agreementSelectTarget.value) || null,
      professionalId:      parseInt(this.professionalSelectTarget.value) || null,
      isAmbulatory:        this.isAmbulatoryCheckTarget.checked,
      includesHonorariums: this.includesHonorariumsCheckTarget.checked,
      ids: this._planType() === 'open'
        ? this.selectedServices.map(s => s.id)
        : this.selectedPackageItems.map(i => i.id),
    }
  }
}
