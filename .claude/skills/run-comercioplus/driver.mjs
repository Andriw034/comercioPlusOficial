#!/usr/bin/env node
// Driver para manejar ComercioPlus corriendo en local.
// Ejecutar SIEMPRE desde la raíz del repo (necesita ./node_modules/@playwright/test).
//
//   node .claude/skills/run-comercioplus/driver.mjs <comando> [args]
//
// Comandos:
//   health                      Verifica MySQL / backend :8000 / vite :5173
//   api <METHOD> <path> [json]  Llama la API con token de merchant
//   shot <ruta> [nombre]        Screenshot de una ruta pública
//   dash <ruta> [nombre]        Screenshot de una ruta autenticada (inyecta token)
//   eval <ruta> <js>            Ejecuta JS en la página y muestra el resultado
//   smoke                       Flujo completo: público + login + dashboard
//
// Flags: --mobile (viewport Pixel 7)  --headed (ver el navegador)

import { chromium, devices } from '@playwright/test'
import { mkdirSync, writeFileSync } from 'node:fs'
import { dirname, join, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'

const HERE = dirname(fileURLToPath(import.meta.url))
const SHOTS = resolve(HERE, 'screenshots')
const FRONTEND = process.env.CP_FRONTEND_URL || 'http://127.0.0.1:5173'
const API = process.env.CP_API_URL || 'http://127.0.0.1:8000'
const EMAIL = process.env.CP_EMAIL || 'admin@comercioplus.local'
const PASSWORD = process.env.CP_PASSWORD || 'password123'

const argv = process.argv.slice(2)
const flags = new Set(argv.filter((a) => a.startsWith('--')))
const args = argv.filter((a) => !a.startsWith('--'))
const cmd = args[0]

const MOBILE = flags.has('--mobile')
const HEADED = flags.has('--headed')

const log = (...a) => console.log(...a)
const die = (msg) => { console.error(`ERROR: ${msg}`); process.exit(1) }
const slug = (s) => s.replace(/^\//, '').replace(/[^a-z0-9]+/gi, '-').replace(/^-|-$/g, '') || 'home'

// ── auth ────────────────────────────────────────────────────────────────
// OJO: /api/login NO devuelve el sobre {success,data}. Devuelve {user, token} pelado.
async function login() {
  const res = await fetch(`${API}/api/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ email: EMAIL, password: PASSWORD }),
  })
  if (!res.ok) die(`login falló: HTTP ${res.status} — ${(await res.text()).slice(0, 200)}`)
  const json = await res.json()
  if (!json.token) die(`login sin token: ${JSON.stringify(json).slice(0, 200)}`)
  return json
}

// ── browser ─────────────────────────────────────────────────────────────
// Devuelve { browser, page, problems } — problems junta errores de consola
// y requests fallidos, que es como se detecta una página rota sin mirarla.
async function openPage({ auth = false } = {}) {
  const browser = await chromium.launch({ headless: !HEADED })
  const ctx = await browser.newContext(MOBILE ? { ...devices['Pixel 7'] } : { viewport: { width: 1440, height: 900 } })
  const problems = []

  if (auth) {
    const { token, user } = await login()
    // El token se inyecta antes de que cargue la app: RequireAuth lo lee de
    // localStorage en el primer render. Sin esto, redirige a /login.
    await ctx.addInitScript(([t, u]) => {
      localStorage.setItem('token', t)
      localStorage.setItem('user', JSON.stringify(u))
    }, [token, user])
  }

  const page = await ctx.newPage()
  page.on('console', (m) => { if (m.type() === 'error') problems.push(`console: ${m.text().slice(0, 200)}`) })
  page.on('pageerror', (e) => problems.push(`pageerror: ${String(e).slice(0, 200)}`))
  page.on('response', (r) => { if (r.status() >= 400) problems.push(`http ${r.status()}: ${r.url().replace(FRONTEND, '')}`) })
  return { browser, page, problems }
}

// TRAMPA: el dashboard pinta KPIs de ejemplo (ventas $1.240.000, 47 pedidos)
// mientras carga, y recién después los reemplaza por los reales. Si el
// screenshot se toma antes, se reportan cifras inventadas como si fueran
// datos. Se espera a que desaparezca "Cargando resumen del dia...".
async function settleDashboard(page) {
  await page.waitForFunction(() => !document.body.innerText.includes('Cargando resumen'), { timeout: 20_000 })
    .catch(() => {})
  await page.waitForTimeout(1500)
}

async function goto(page, path) {
  const url = path.startsWith('http') ? path : `${FRONTEND}${path.startsWith('/') ? path : `/${path}`}`
  await page.goto(url, { waitUntil: 'networkidle', timeout: 60_000 })
  // La app pinta datos después del fetch; networkidle solo no alcanza en /stores.
  await page.waitForTimeout(1200)
  if (path.includes('/dashboard')) await settleDashboard(page)
  return url
}

async function capture(page, path, name) {
  mkdirSync(SHOTS, { recursive: true })
  const file = join(SHOTS, `${name || slug(path)}${MOBILE ? '-mobile' : ''}.png`)
  await page.screenshot({ path: file, fullPage: true })
  return file
}

// ── comandos ────────────────────────────────────────────────────────────
const commands = {
  async health() {
    let bad = 0
    for (const [name, url] of [['backend', `${API}/api/health`], ['vite', FRONTEND], ['proxy vite→api', `${FRONTEND}/api/health`]]) {
      try {
        const r = await fetch(url)
        log(`${r.ok ? 'OK  ' : 'FAIL'} ${name.padEnd(16)} ${url}  HTTP ${r.status}`)
        if (!r.ok) bad++
      } catch (e) {
        log(`FAIL ${name.padEnd(16)} ${url}  ${e.cause?.code || e.message}`)
        bad++
      }
    }
    process.exit(bad ? 1 : 0)
  },

  async api() {
    const [, method = 'GET', path = '/api/me', body] = args
    if (!path.startsWith('/')) die('la ruta debe empezar con / (ej: /api/merchant/store)')
    const { token } = await login()
    const res = await fetch(`${API}${path}`, {
      method: method.toUpperCase(),
      headers: { Authorization: `Bearer ${token}`, Accept: 'application/json', 'Content-Type': 'application/json' },
      body: body || undefined,
    })
    const text = await res.text()
    log(`HTTP ${res.status}`)
    try { log(JSON.stringify(JSON.parse(text), null, 2).slice(0, 4000)) } catch { log(text.slice(0, 2000)) }
    process.exit(res.ok ? 0 : 1)
  },

  async shot() { await shotCmd({ auth: false }) },
  async dash() { await shotCmd({ auth: true }) },

  async eval() {
    const [, path, js] = args
    if (!path || !js) die('uso: eval <ruta> "<js>"')
    const { browser, page } = await openPage({ auth: true })
    await goto(page, path)
    log(JSON.stringify(await page.evaluate(js), null, 2))
    await browser.close()
  },

  // Login real por la UI (llena el formulario y hace click), no por inyección
  // de token. Es la forma de verificar que auth funciona de punta a punta.
  async flow() {
    const { browser, page, problems } = await openPage({ auth: false })
    await goto(page, '/login')
    await page.fill('#email', EMAIL)
    await page.fill('#password', PASSWORD)
    await capture(page, '/login', 'flow-1-login-lleno')
    await page.click('button[type=submit]')
    // Tras el submit la app navega sola; no hay spinner estable que esperar.
    await page.waitForURL(/\/dashboard/, { timeout: 30_000 }).catch(() => {})
    await settleDashboard(page)
    const file = await capture(page, '/dashboard', 'flow-2-dashboard')
    const ok = page.url().includes('/dashboard')
    log(`url final: ${page.url()}`)
    log(`token en localStorage: ${await page.evaluate(() => Boolean(localStorage.getItem('token')))}`)
    log(`${ok ? 'OK' : 'FAIL'} login por UI → ${file}`)
    const real = problems.filter((p) => !/iconify|cloudinary|favicon|storage\//i.test(p))
    if (real.length) log(`problemas: ${[...new Set(real)].slice(0, 4).join(' | ')}`)
    await browser.close()
    process.exit(ok ? 0 : 1)
  },

  // Abre el asistente de repuestos, hace una pregunta y captura la respuesta.
  //   driver.mjs chat "que banda sirve para ybr 125"
  async chat() {
    const [, pregunta = 'que banda sirve para yamaha ybr 125 2016'] = args
    // El boton flotante solo existe dentro de DashboardLayout, asi que hace
    // falta sesion de merchant.
    const { browser, page, problems } = await openPage({ auth: true })
    await goto(page, '/dashboard')

    await page.click('button[aria-label="Abrir asistente de repuestos"]')
    await page.waitForSelector('text=Asistente de Repuestos', { timeout: 10_000 })

    await page.fill('input[placeholder*="pregunta"], textarea[placeholder*="pregunta"]', pregunta)
    await page.keyboard.press('Enter')
    await page.waitForTimeout(4000)

    // El chat autoscrollea al final; se sube para ver el aviso y las primeras
    // tarjetas, que es donde esta la informacion que importa revisar.
    // Ojo: hay que acotar al panel del chat (.z-50). El dashboard de fondo tiene
    // sus propios contenedores con scroll y avisos ambar.
    await page.evaluate(() => {
      const panel = document.querySelector('.z-50 .overflow-y-auto')
      if (panel) panel.scrollTop = 0
    })
    await page.waitForTimeout(600)

    const aviso = await page.locator('.z-50 .bg-amber-50').first().innerText().catch(() => null)
    if (aviso) log(`AVISO en pantalla: ${aviso.replace(/\s+/g, ' ').trim()}`)

    const file = await capture(page, '/asistente', 'asistente')
    log(`pregunta: ${pregunta}`)
    log(`captura: ${file}`)
    const real = problems.filter((p) => !/iconify|cloudinary|favicon|storage\//i.test(p))
    if (real.length) log(`problemas: ${[...new Set(real)].slice(0, 4).join(' | ')}`)
    await browser.close()
  },

  async smoke() {
    const steps = [
      { path: '/', auth: false, expect: 'ComercioPlus' },
      { path: '/stores', auth: false },
      { path: '/login', auth: false, expect: 'orreo' },       // "Correo"/"correo"
      { path: '/dashboard', auth: true, expect: 'ashboard' },
      { path: '/dashboard/inventory', auth: true },
      { path: '/dashboard/orders', auth: true },
    ]
    let failed = 0
    for (const step of steps) {
      const { browser, page, problems } = await openPage({ auth: step.auth })
      let status = 'OK'
      try {
        await goto(page, step.path)
        const text = await page.locator('body').innerText()
        if (step.expect && !text.includes(step.expect)) { status = `FAIL (no contiene "${step.expect}")`; failed++ }
        if (page.url().includes('/login') && step.auth) { status = 'FAIL (rebotó a /login)'; failed++ }
        const file = await capture(page, step.path)
        log(`${status.padEnd(34)} ${step.path.padEnd(24)} → ${file.replace(SHOTS, 'screenshots')}`)
      } catch (e) {
        failed++
        log(`FAIL (${e.message.split('\n')[0].slice(0, 60)})  ${step.path}`)
      }
      // Ruido esperado: Cloudinary/iconify pueden fallar sin red. Solo se
      // reportan; no tumban el smoke.
      const real = problems.filter((p) => !/iconify|cloudinary|favicon/i.test(p))
      if (real.length) log(`     problemas: ${[...new Set(real)].slice(0, 4).join(' | ')}`)
      await browser.close()
    }
    writeFileSync(join(SHOTS, 'smoke-result.txt'), `failed=${failed}\n`)
    log(failed ? `\n${failed} paso(s) fallaron` : '\nsmoke OK — todos los pasos pasaron')
    process.exit(failed ? 1 : 0)
  },
}

async function shotCmd({ auth }) {
  const [, path = '/', name] = args
  const { browser, page, problems } = await openPage({ auth })
  const url = await goto(page, path)
  const file = await capture(page, path, name)
  log(`${url} → ${file}`)
  log(`title: ${await page.title()}`)
  const real = problems.filter((p) => !/iconify|cloudinary|favicon/i.test(p))
  if (real.length) log(`problemas: ${[...new Set(real)].slice(0, 6).join(' | ')}`)
  await browser.close()
}

if (!cmd || !commands[cmd]) {
  console.error(`comandos: ${Object.keys(commands).join(', ')}`)
  process.exit(1)
}
await commands[cmd]()
