import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static values = {
    admissionId: Number,
  }

  connect() {
    this.handleBeforeUnload = this.handleBeforeUnload.bind(this)
    window.addEventListener('beforeunload', this.handleBeforeUnload)
  }

  disconnect() {
    window.removeEventListener('beforeunload', this.handleBeforeUnload)
    this.releaseLock()
  }

  handleBeforeUnload() {
    this.releaseLock()
  }

  releaseLock() {
    if (!this.hasAdmissionIdValue) return

    fetch(`/nursing/patient/${this.admissionIdValue}/unlock`, {
      method: 'DELETE',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
      keepalive: true,
    }).catch(() => {})
  }
}
