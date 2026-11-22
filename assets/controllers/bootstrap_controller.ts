import { Controller } from '@hotwired/stimulus';
import { Drawer, Strength } from 'bootstrap';
import { createIcons, ArrowRight, GraduationCap, Presentation, Plus } from 'lucide';

export default class extends Controller {
  connect() {
    Array.from(document.querySelectorAll('.strength'))
      .forEach(elem => new Strength(elem))

    Array.from(document.querySelectorAll('dialog.drawer'))
      .forEach(elem => new Drawer(elem))

    createIcons({
      icons: {
        ArrowRight,
        GraduationCap,
        Plus,
        Presentation,
      }
    })
  }
}
