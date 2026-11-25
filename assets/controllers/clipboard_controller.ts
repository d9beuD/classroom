import { Controller } from '@hotwired/stimulus'

/**
 * Copies the clicked element's `data-clipboard-text-param` to the clipboard, and shows the result in
 * the `status` target (a role="status" element, so screen readers announce it) for two seconds.
 */
export default class extends Controller<HTMLElement> {
  static targets = ['status']
  declare readonly statusTarget: HTMLElement
  private timeout?: number

  disconnect() {
    clearTimeout(this.timeout)
  }

  async copy({ params: { text } }: { params: { text: string } }) {
    try {
      await navigator.clipboard.writeText(text)
      this.announce('Lien Markdown copié')
    } catch {
      this.announce('Impossible de copier le lien')
    }
  }

  // Emptied first, so that the same message is announced again on a second copy.
  private announce(message: string) {
    clearTimeout(this.timeout)
    this.statusTarget.textContent = ''
    requestAnimationFrame(() => { this.statusTarget.textContent = message })
    this.timeout = window.setTimeout(() => { this.statusTarget.textContent = '' }, 2000)
  }
}
