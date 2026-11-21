import { Controller } from '@hotwired/stimulus';
import { Strength } from 'bootstrap';
import { createIcons, ArrowRight, GraduationCap } from 'lucide';

export default class extends Controller {
  connect() {
    Array.from(document.querySelectorAll('.strength'))
      .forEach(elem => new Strength(elem))

    createIcons({
      icons: {
        ArrowRight,
        GraduationCap,
      }
    })
  }
}
