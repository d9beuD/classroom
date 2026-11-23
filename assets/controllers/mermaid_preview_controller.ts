import { Controller } from '@hotwired/stimulus';

// Loaded from the CDN on the first preview only, like Monaco (too big for the importmap).
const MERMAID_URL = 'https://cdn.jsdelivr.net/npm/mermaid@12.3.0/dist/mermaid.esm.min.mjs'

let mermaidPromise: Promise<any> | undefined
let renderCount = 0

const loadMermaid = () => mermaidPromise ??= import(MERMAID_URL).then(({ default: mermaid }) => {
  mermaid.initialize({
    startOnLoad: false,
    securityLevel: 'strict',
    // Same themes as the server rendering (docker/renderer).
    theme: matchMedia('(prefers-color-scheme: dark)').matches ? 'redux-dark-color' : 'redux-color',
  })
  return mermaid
})

/**
 * Code / preview toggle of a Mermaid section. The source is the section's textarea (kept in sync
 * by the Monaco controller); the preview target must be data-live-ignore'd inside a live component.
 * The state is per block and client-side only.
 */
export default class extends Controller<HTMLElement> {
  static targets = ['source', 'editor', 'preview', 'toggle']

  declare readonly sourceTarget: HTMLTextAreaElement
  declare readonly editorTarget: HTMLElement
  declare readonly previewTarget: HTMLElement
  declare readonly toggleTarget: HTMLButtonElement

  toggle() {
    this.previewTarget.hidden ? this.showPreview() : this.showCode()
  }

  async showPreview() {
    this.toggleTarget.setAttribute('aria-pressed', 'true')
    this.editorTarget.hidden = true
    this.previewTarget.hidden = false
    this.previewTarget.textContent = 'Rendu du diagramme…'

    const id = `mermaid-preview-${++renderCount}`
    try {
      const mermaid = await loadMermaid()
      // Parse first: an invalid diagram then fails without rendering Mermaid's error image.
      await mermaid.parse(this.sourceTarget.value)
      const { svg } = await mermaid.render(id, this.sourceTarget.value)
      this.previewTarget.innerHTML = svg
    } catch (error) {
      // A failed render leaves its temporary element at the end of <body>.
      document.getElementById(`d${id}`)?.remove()
      document.getElementById(id)?.remove()

      const message = document.createElement('pre')
      message.className = 'mermaid-preview-error'
      message.textContent = error instanceof Error ? error.message : String(error)
      this.previewTarget.replaceChildren(message)
    }
  }

  showCode() {
    this.toggleTarget.setAttribute('aria-pressed', 'false')
    this.previewTarget.hidden = true
    this.previewTarget.replaceChildren()
    this.editorTarget.hidden = false
  }

  // Escape on the focused preview goes back to the code.
  keydown(event: KeyboardEvent) {
    if (event.key === 'Escape') this.showCode()
  }
}
