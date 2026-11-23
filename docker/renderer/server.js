// Rendering service for the Symfony app (not exposed outside Docker Compose).
//   POST /highlight { code, lang }  -> { html }  Shiki, light + dark themes; unknown languages as plain text
//   POST /mermaid   { code, svgId } -> { svg }   Mermaid through headless Chromium
//   GET  /health                    -> 204
// Errors answer 4xx/5xx with { error }.
import { createServer } from 'node:http'
import { codeToHtml } from 'shiki'
import { renderMermaid } from '@mermaid-js/mermaid-cli'
import puppeteer from 'puppeteer'

const PORT = Number(process.env.PORT ?? 3000)
const MAX_BODY = 1024 * 1024

// One browser for all diagrams, relaunched if it dies.
let browserPromise
const browser = () => browserPromise ??= puppeteer.launch({
  headless: true,
  executablePath: process.env.PUPPETEER_EXECUTABLE_PATH,
  args: ['--no-sandbox', '--disable-dev-shm-usage'],
}).then(instance => {
  instance.on('disconnected', () => { browserPromise = undefined })
  return instance
}, error => {
  browserPromise = undefined
  throw error
})

const SHIKI_THEMES = { themes: { light: 'github-light', dark: 'github-dark' } }

async function highlight({ code, lang }) {
  try {
    return { html: await codeToHtml(code, { ...SHIKI_THEMES, lang: lang || 'text' }) }
  } catch {
    // A language Shiki doesn't know (Monaco knows a few more): plain text rather than an error.
    return { html: await codeToHtml(code, { ...SHIKI_THEMES, lang: 'text' }) }
  }
}

async function mermaid({ code, svgId }) {
  const { data } = await renderMermaid(await browser(), code, 'svg', {
    backgroundColor: 'transparent',
    mermaidConfig: { securityLevel: 'strict' },
    // No embedded fonts (~190 KB of base64 + licences per diagram): the page's own fonts are used.
    fontEmbed: false,
    svgId,
  })
  return { svg: new TextDecoder().decode(data) }
}

const routes = { '/highlight': highlight, '/mermaid': mermaid }

class HttpError extends Error {
  constructor(status, message) { super(message); this.status = status }
}

async function readJson(request) {
  let body = ''
  for await (const chunk of request) {
    body += chunk
    if (body.length > MAX_BODY) throw new HttpError(413, 'Body too large')
  }
  try {
    const payload = JSON.parse(body)
    if (typeof payload?.code !== 'string') throw new Error()
    return payload
  } catch {
    throw new HttpError(400, 'Expected a JSON object with a "code" string')
  }
}

const send = (response, status, payload) => {
  response.writeHead(status, payload ? { 'Content-Type': 'application/json' } : {})
  response.end(payload ? JSON.stringify(payload) : undefined)
}

createServer(async (request, response) => {
  try {
    if (request.method === 'GET' && request.url === '/health') return send(response, 204)

    const route = routes[request.url]
    if (!route) throw new HttpError(404, 'Not found')
    if (request.method !== 'POST') throw new HttpError(405, 'Method not allowed')

    send(response, 200, await route(await readJson(request)))
  } catch (error) {
    // Anything else (e.g. a Mermaid syntax error) is the request's content being unrenderable.
    send(response, error instanceof HttpError ? error.status : 422, { error: String(error?.message ?? error) })
  }
}).listen(PORT, () => console.log(`Renderer listening on ${PORT}`))
