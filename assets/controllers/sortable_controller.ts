import { Controller } from '@hotwired/stimulus';
import Sortable from 'sortablejs';

/**
 * Drag-and-drop sorting of the direct children, by their [data-sortable-handle].
 * After a drop, each child's hidden position input gets its new index and a bubbling
 * `change` event, so a parent live component picks up the new order.
 */
export default class extends Controller<HTMLElement> {
  private sortable?: Sortable

  connect() {
    this.sortable = Sortable.create(this.element, {
      handle: '[data-sortable-handle]',
      animation: 150,
      // By default the dragged element's text is put in the drag data, and editors under the
      // drop point (Monaco, inputs) paste it. Empty data still starts the drag in Firefox.
      setData: (dataTransfer) => dataTransfer.setData('text', ''),
      onEnd: () => this.updatePositions(),
    })
  }

  disconnect() {
    this.sortable?.destroy()
  }

  private updatePositions() {
    Array.from(this.element.children).forEach((item, index) => {
      const input = item.querySelector<HTMLInputElement>('input[name$="[position]"]')
      if (!input || input.value === String(index)) return

      input.value = String(index)
      input.dispatchEvent(new Event('change', { bubbles: true }))
    })
  }
}
