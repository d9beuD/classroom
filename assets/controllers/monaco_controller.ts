import { Controller } from '@hotwired/stimulus';
import loader from '@monaco-editor/loader';

type Monaco = Awaited<ReturnType<typeof loader.init>>
type Editor = ReturnType<Monaco['editor']['create']>

// Monaco has no Mermaid support: a small Monarch grammar covering the common diagram syntax.
const mermaidLanguage = {
  tokenizer: {
    root: [
      [/%%.*$/, 'comment'],
      [/\b(graph|flowchart|sequenceDiagram|classDiagram|stateDiagram-v2|stateDiagram|erDiagram|gantt|pie|journey|gitGraph|mindmap|timeline|quadrantChart|requirementDiagram)\b/, 'keyword'],
      [/\b(TB|TD|BT|RL|LR)\b/, 'keyword'],
      [/\b(subgraph|end|direction|participant|actor|activate|deactivate|autonumber|loop|alt|else|opt|par|and|critical|break|rect|note|over|left of|right of|class|classDef|style|linkStyle|click|state|section|title|dateFormat|axisFormat)\b/, 'keyword'],
      [/"[^"]*"/, 'string'],
      [/[-=.~]+[>xo)]?|<[-=.]+/, 'operator'],
      [/[[\](){}]/, '@brackets'],
      [/\d+(\.\d+)?/, 'number'],
    ],
  },
}

// Monaco's PHP grammar starts in HTML and only switches to PHP after `<?php`: this variant
// starts directly in PHP code (an opening tag is still accepted), for snippets.
const PHP_SNIPPET = 'php-snippet'

const registerPhpSnippet = async (monaco: Monaco) => {
  const php = monaco.languages.getLanguages().find(language => language.id === 'php') as any
  const { language, conf } = await php.loader()

  monaco.languages.register({ id: PHP_SNIPPET })
  monaco.languages.setLanguageConfiguration(PHP_SNIPPET, conf)
  monaco.languages.setMonarchTokensProvider(PHP_SNIPPET, {
    ...language,
    tokenizer: {
      ...language.tokenizer,
      root: [
        [/<\?((php)|=)?/, 'metatag.php'],
        { include: 'phpRoot' },
      ],
    },
  })
}

const editorLanguage = (language: string) => language === 'php' ? PHP_SNIPPET : language

let monacoPromise: Promise<Monaco> | undefined

const loadMonaco = () => monacoPromise ??= loader.init().then(async monaco => {
  monaco.languages.register({ id: 'mermaid' })
  monaco.languages.setMonarchTokensProvider('mermaid', mermaidLanguage as any)
  await registerPhpSnippet(monaco)
  return monaco
})

/**
 * Replaces a textarea (input target) with a Monaco editor (editor target, which must be
 * data-live-ignore'd inside a live component). The textarea stays the form field: it is
 * kept in sync on every change, and gets a bubbling `change` event on blur.
 */
export default class extends Controller<HTMLElement> {
  static targets = ['input', 'editor']
  static values = { language: { type: String, default: 'plaintext' } }

  declare readonly inputTarget: HTMLTextAreaElement
  declare readonly editorTarget: HTMLElement
  declare readonly languageValue: string

  private monaco?: Monaco
  private editor?: Editor
  private connected = false

  async connect() {
    this.connected = true
    const monaco = await loadMonaco()
    // Disconnected (e.g. moved by drag and drop) while Monaco was loading.
    if (!this.connected || this.editor) return

    this.monaco = monaco
    const editor = this.editor = monaco.editor.create(this.editorTarget, {
      value: this.inputTarget.value,
      language: editorLanguage(this.languageValue),
      theme: matchMedia('(prefers-color-scheme: dark)').matches ? 'vs-dark' : 'vs',
      ariaLabel: this.inputTarget.getAttribute('aria-label') ?? undefined,
      automaticLayout: true,
      // Dropping a dragged section card on the editor must not insert anything.
      dropIntoEditor: { enabled: false },
      minimap: { enabled: false },
      scrollBeyondLastLine: false,
      scrollbar: { alwaysConsumeMouseWheel: false },
    })
    this.inputTarget.hidden = true

    // Grow with the content instead of scrolling inside a fixed box.
    const resize = () => {
      this.editorTarget.style.height = `${Math.max(120, editor.getContentHeight())}px`
      editor.layout()
    }
    editor.onDidContentSizeChange(resize)
    resize()

    editor.onDidChangeModelContent(() => {
      this.inputTarget.value = editor.getValue()
    })
    // One live component update per edit session, not one per keystroke.
    editor.onDidBlurEditorText(() => {
      this.inputTarget.dispatchEvent(new Event('change', { bubbles: true }))
    })
  }

  disconnect() {
    this.connected = false
    this.editor?.dispose()
    this.editor = undefined
    this.inputTarget.hidden = false
  }

  languageValueChanged() {
    const model = this.editor?.getModel()
    if (model) this.monaco?.editor.setModelLanguage(model, editorLanguage(this.languageValue))
  }
}
