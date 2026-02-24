import { expect, test, type APIRequestContext, type Page } from '@playwright/test'

const API_BASE_URL = process.env.E2E_API_BASE_URL || 'http://127.0.0.1:8000/api'
const FRONTEND_BASE_URL = process.env.E2E_FRONTEND_URL || 'http://127.0.0.1:5173'

const authHeaders = (token: string) => ({
  Accept: 'application/json',
  Authorization: `Bearer ${token}`,
  'X-Requested-With': 'XMLHttpRequest',
})

const uiRegister = async (page: Page, params: {
  name: string
  email: string
  password: string
  role: 'merchant' | 'client'
}) => {
  await page.goto('/register')

  if (params.role === 'client') {
    await page.getByRole('button', { name: /Cliente/i }).click()
  }

  await page.getByLabel(/Nombre Completo/i).fill(params.name)
  await page.getByLabel(/Correo Electronico/i).fill(params.email)
  await page.getByLabel(/^Contrasena$/i).fill(params.password)
  await page.getByLabel(/Confirmar Contrasena/i).fill(params.password)
  await page.getByRole('button', { name: /Crear Cuenta/i }).click()
}

const uiLogin = async (page: Page, params: { email: string; password: string }) => {
  await page.goto('/login')
  await page.getByLabel(/Correo Electronico/i).fill(params.email)
  await page.getByLabel(/^Contrasena$/i).fill(params.password)
  await page.getByRole('button', { name: /Iniciar Sesion/i }).click()
}

const readSessionToken = async (page: Page) => {
  return page.evaluate(() => sessionStorage.getItem('token') || localStorage.getItem('token') || '')
}

const clearBrowserSession = async (page: Page) => {
  await page.evaluate(() => {
    localStorage.clear()
    sessionStorage.clear()
  })
}

test.describe.serial('Smoke E2E ComercioPlus', () => {
  test('register/login merchant+client, create store+product, cart + checkout, merchant sees order', async ({ page, request }) => {
    test.setTimeout(180_000)

    const stamp = `${Date.now()}`
    const merchantEmail = `e2e.merchant.${stamp}@gmail.com`
    const clientEmail = `e2e.client.${stamp}@gmail.com`
    const password = 'Secret123!'

    let storeId = 0
    let storeSlug = ''

    // 1) Merchant register UI
    await uiRegister(page, {
      name: 'E2E Merchant',
      email: merchantEmail,
      password,
      role: 'merchant',
    })

    await expect(page).toHaveURL(/\/dashboard\/(store|products)/)

    const merchantTokenAfterRegister = await readSessionToken(page)
    expect(merchantTokenAfterRegister).toBeTruthy()

    // 2) Create store + category + product via API with merchant token
    const storeResponse = await request.post(`${API_BASE_URL}/stores`, {
      headers: authHeaders(merchantTokenAfterRegister),
      data: {
        name: `Tienda E2E ${stamp}`,
        description: 'Store creada por smoke e2e',
        is_visible: true,
      },
    })
    expect(storeResponse.ok()).toBeTruthy()
    const storeJson = await storeResponse.json()
    storeId = Number(storeJson.id)
    storeSlug = String(storeJson.slug || '')
    expect(storeId).toBeGreaterThan(0)
    expect(storeSlug.length).toBeGreaterThan(0)

    const categoryResponse = await request.post(`${API_BASE_URL}/categories`, {
      headers: authHeaders(merchantTokenAfterRegister),
      data: {
        name: `Categoria E2E ${stamp}`,
        description: 'Categoria de prueba e2e',
      },
    })
    expect(categoryResponse.ok()).toBeTruthy()
    const categoryJson = await categoryResponse.json()
    const categoryId = Number(categoryJson.id)
    expect(categoryId).toBeGreaterThan(0)

    const productResponse = await request.post(`${API_BASE_URL}/products`, {
      headers: authHeaders(merchantTokenAfterRegister),
      data: {
        name: `Producto E2E ${stamp}`,
        price: 12345,
        stock: 9,
        category_id: categoryId,
        description: 'Producto de prueba e2e',
        status: 'active',
        codes: [{ type: 'barcode', value: `770001${stamp}`, is_primary: true }],
      },
    })
    expect(productResponse.ok()).toBeTruthy()

    // 3) Merchant login UI (session fresh)
    await clearBrowserSession(page)
    await uiLogin(page, { email: merchantEmail, password })
    await expect(page).toHaveURL(/\/dashboard\/(products|store)/)
    await expect(page.getByText(new RegExp(`Tienda E2E ${stamp}`))).toBeVisible()

    // 4) Client register + login UI
    await clearBrowserSession(page)
    await uiRegister(page, {
      name: 'E2E Client',
      email: clientEmail,
      password,
      role: 'client',
    })
    await expect(page).toHaveURL('/')

    await clearBrowserSession(page)
    await uiLogin(page, { email: clientEmail, password })
    await expect(page).toHaveURL('/')

    // 5) Client browse store and add to cart
    await page.goto(`/stores/${storeSlug}/products`)
    await expect(page.getByRole('heading', { name: /Productos Destacados/i })).toBeVisible()
    await page.getByRole('button', { name: /Agregar al Carrito/i }).first().click()

    await page.goto('/cart')
    await expect(page.getByRole('heading', { name: /Carrito de compras/i })).toBeVisible()

    // 6) Checkout: create order real + mock only external payment URL
    await page.route(`${API_BASE_URL}/payments/wompi/create`, async (route) => {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          checkoutUrl: `${FRONTEND_BASE_URL}/checkout/success?e2e=1`,
          transactionId: `tx-e2e-${stamp}`,
          reference: `E2E-${stamp}`,
        }),
      })
    })

    await page.goto('/checkout')
    await page.getByLabel(/Email/i).fill(clientEmail)
    await page.getByLabel(/Nombre completo/i).fill('E2E Client')
    await page.getByLabel(/Telefono/i).fill('3001234567')
    await page.getByRole('button', { name: /Tarjeta/i }).click()
    await page.getByRole('button', { name: /Pagar ahora/i }).click()

    await expect(page).toHaveURL(/\/checkout\/success/)

    const orderId = await page.evaluate(() => Number(localStorage.getItem('last_order_id') || '0'))
    expect(orderId).toBeGreaterThan(0)

    // 7) Merchant login + verify order appears in merchant API and dashboard page
    await clearBrowserSession(page)
    await uiLogin(page, { email: merchantEmail, password })
    await expect(page).toHaveURL(/\/dashboard\/(products|store)/)

    const merchantTokenAfterLogin = await readSessionToken(page)
    expect(merchantTokenAfterLogin).toBeTruthy()

    const merchantOrdersResponse = await request.get(`${API_BASE_URL}/merchant/orders`, {
      headers: authHeaders(merchantTokenAfterLogin),
    })
    expect(merchantOrdersResponse.ok()).toBeTruthy()

    const merchantOrdersJson = await merchantOrdersResponse.json()
    const orderIds = Array.isArray(merchantOrdersJson?.data)
      ? merchantOrdersJson.data.map((row: any) => Number(row.id))
      : []
    expect(orderIds).toContain(orderId)

    await page.goto('/dashboard/orders')
    await expect(page.getByRole('heading', { name: /Pedidos/i })).toBeVisible()
    await expect(page.locator('table')).toContainText(String(orderId))
  })
})
