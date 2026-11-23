import { Controller } from '@hotwired/stimulus';
import { createIcons, Check, Copy } from 'lucide';

const LABEL = 'Copier le code'

// <i data-lucide> rendered like everywhere else (bootstrap_controller), hence its `lucide` classes.
const setIcon = (button: HTMLButtonElement, name: 'copy' | 'check') => {
  button.innerHTML = `<i data-lucide="${name}" aria-hidden="true"></i>`
  createIcons({ icons: { Check, Copy }, root: button })
}

/**
 * Adds a "copy" button to every Shiki code block of the element, shown on hover and keyboard focus.
 * The result is announced to screen readers through a status region.
 */
export default class extends Controller<HTMLElement> {
  private status!: HTMLElement

  connect() {
    this.status = document.createElement('p')
    this.status.className = 'visually-hidden'
    this.status.setAttribute('role', 'status')
    this.element.append(this.status)

    this.element.querySelectorAll<HTMLPreElement>('pre.shiki').forEach(pre => {
      // The button sits outside the <pre>, which scrolls horizontally.
      const block = document.createElement('div')
      block.className = 'code-block'
      pre.replaceWith(block)
      block.append(pre, this.button(pre))
    })
  }

  disconnect() {
    this.element.querySelectorAll('.code-block').forEach(block => block.replaceWith(block.querySelector('pre')!))
    this.status.remove()
  }

  private button(pre: HTMLPreElement): HTMLButtonElement {
    const button = document.createElement('button')
    button.type = 'button'
    button.className = 'btn-icon code-copy'
    button.dataset.bsTheme = 'dark'
    button.ariaLabel = LABEL
    button.title = LABEL
    setIcon(button, 'copy')
    button.addEventListener('click', () => this.copy(pre, button))

    return button
  }

  private async copy(pre: HTMLPreElement, button: HTMLButtonElement) {
    try {
      // Trailing spaces: left where Shiki removed a [!code …] comment.
      await navigator.clipboard.writeText((pre.querySelector('code')?.textContent ?? '').replace(/[ \t]+$/gm, ''))
      this.announce('Code copié')
      button.classList.add('copied')
      setIcon(button, 'check')
      setTimeout(() => {
        button.classList.remove('copied')
        setIcon(button, 'copy')
      }, 2000)
    } catch {
      this.announce('Impossible de copier le code')
    }
  }

  // Emptied first, so that the same message is announced again on a second copy.
  private announce(message: string) {
    this.status.textContent = ''
    requestAnimationFrame(() => { this.status.textContent = message })
  }
}
