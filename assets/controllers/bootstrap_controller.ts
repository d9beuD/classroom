import { Controller } from '@hotwired/stimulus';
import { Drawer, Strength } from 'bootstrap';
import { createIcons, ArrowRight, ChevronDown, GraduationCap, GripVertical, Presentation, Plus, Trash } from 'lucide';

const renderIcons = () => createIcons({
  icons: {
    ArrowRight,
    ChevronDown,
    GraduationCap,
    GripVertical,
    Plus,
    Presentation,
    Trash,
  }
})

// Live component re-renders bring back raw <i data-lucide> tags, so icons are rendered again after each one.
const onLiveConnect = (event: Event) => {
  (event as CustomEvent).detail.component.on('render:finished', renderIcons)
}

// A combobox with data-combobox-input="<id>" writes its value to that (server-rendered) input
// and fires a native `change`, which forms and live components listen to.
const onComboboxChange = (event: Event) => {
  const id = (event.target as HTMLElement).dataset.comboboxInput
  const input = id ? document.getElementById(id) as HTMLInputElement | null : null
  if (!input) return

  input.value = (event as Event & { value: string }).value
  input.dispatchEvent(new Event('change', { bubbles: true }))
}

export default class extends Controller {
  connect() {
    Array.from(document.querySelectorAll('.strength'))
      .forEach(elem => new Strength(elem))

    Array.from(document.querySelectorAll('dialog.drawer'))
      .forEach(elem => new Drawer(elem))

    renderIcons()
    this.element.addEventListener('live:connect', onLiveConnect)
    this.element.addEventListener('change.bs.combobox', onComboboxChange)
  }

  disconnect() {
    this.element.removeEventListener('live:connect', onLiveConnect)
    this.element.removeEventListener('change.bs.combobox', onComboboxChange)
  }
}
