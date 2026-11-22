import { Controller } from '@hotwired/stimulus';
import { Drawer, Strength } from 'bootstrap';
import { createIcons, ArrowRight, GraduationCap, Presentation, Plus, Trash } from 'lucide';

const renderIcons = () => createIcons({
  icons: {
    ArrowRight,
    GraduationCap,
    Plus,
    Presentation,
    Trash,
  }
})

// Live component re-renders bring back raw <i data-lucide> tags, so icons are rendered again after each one.
const onLiveConnect = (event: Event) => {
  (event as CustomEvent).detail.component.on('render:finished', renderIcons)
}

export default class extends Controller {
  connect() {
    Array.from(document.querySelectorAll('.strength'))
      .forEach(elem => new Strength(elem))

    Array.from(document.querySelectorAll('dialog.drawer'))
      .forEach(elem => new Drawer(elem))

    renderIcons()
    this.element.addEventListener('live:connect', onLiveConnect)
  }

  disconnect() {
    this.element.removeEventListener('live:connect', onLiveConnect)
  }
}
