import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

/**
 * Drag-and-drop sorting of the [data-sortable-item] children, by their [data-sortable-handle].
 * Other children (e.g. insert buttons between items) stay in place and are not counted.
 * After a drop, either:
 * - with a url value: POSTs the items' data-sortable-id, in order, as JSON `{ ids }`
 *   (with the token value in an X-CSRF-Token header), and reloads the page if that fails;
 * - otherwise: gives each item's hidden position input its new index and a bubbling `change`
 *   event, so a parent live component picks up the new order.
 */
export default class extends Controller<HTMLElement> {
  static values = { url: String, token: String }

  declare readonly hasUrlValue: boolean
  declare readonly urlValue: string
  declare readonly tokenValue: string

  private sortable?: Sortable

  connect() {
    this.sortable = Sortable.create(this.element, {
      draggable: '[data-sortable-item]',
      handle: '[data-sortable-handle]',
      animation: 150,
      // By default the dragged element's text is put in the drag data, and editors under the
      // drop point (Monaco, inputs) paste it. Empty data still starts the drag in Firefox.
      setData: (dataTransfer) => dataTransfer.setData('text', ''),
      onEnd: () => this.hasUrlValue ? this.saveOrder() : this.updatePositions(),
    })
  }

  disconnect() {
    this.sortable?.destroy()
  }

  private items() {
    return Array.from(this.element.querySelectorAll<HTMLElement>(':scope > [data-sortable-item]'))
  }

  private async saveOrder() {
    const ids = this.items().map(item => Number(item.dataset.sortableId))
    const response = await fetch(this.urlValue, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': this.tokenValue },
      body: JSON.stringify({ ids }),
    }).catch(() => null)

    // Not saved: show the real order again rather than a misleading one.
    if (!response?.ok) window.location.reload()
  }

  private updatePositions() {
    this.items().forEach((item, index) => {
      const input = item.querySelector<HTMLInputElement>('input[name$="[position]"]')
      if (!input || input.value === String(index)) return

      input.value = String(index)
      input.dispatchEvent(new Event('change', { bubbles: true }))
    })
  }
}
