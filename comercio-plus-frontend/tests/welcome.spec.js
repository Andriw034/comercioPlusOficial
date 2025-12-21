import { test, expect } from '@playwright/test'

test.describe('Welcome Page', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost:5173/')
  })

  test('should display hero section with correct content', async ({ page }) => {
    // Check hero title
    await expect(page.getByTestId('hero-title')).toBeVisible()
    await expect(page.getByTestId('hero-title')).toContainText('Crea tu tienda de repuestos y accesorios de moto en minutos')

    // Check hero subtitle
    await expect(page.getByTestId('hero-subtitle')).toBeVisible()
    await expect(page.getByTestId('hero-subtitle')).toContainText('Multi-tenant, tiendas verificadas, pagos seguros')

    // Check CTAs
    await expect(page.getByTestId('cta-explorar')).toBeVisible()
    await expect(page.getByTestId('cta-explorar')).toContainText('Explorar tiendas')

    await expect(page.getByTestId('cta-crear')).toBeVisible()
    await expect(page.getByTestId('cta-crear')).toContainText('Crear mi tienda')

    await expect(page.getByTestId('cta-como')).toBeVisible()
    await expect(page.getByTestId('cta-como')).toContainText('Cómo funciona')
  })

  test('should display trust bar with correct stats', async ({ page }) => {
    // Check trust bar elements
    await expect(page.getByText('+1000 productos')).toBeVisible()
    await expect(page.getByText('Tiendas verificadas')).toBeVisible()
    await expect(page.getByText('Pagos seguros')).toBeVisible()
  })

  test('should display branding card', async ({ page }) => {
    await expect(page.getByTestId('branding-card')).toBeVisible()
    await expect(page.getByText('Branding asistido por IA')).toBeVisible()
    await expect(page.getByText('Probar branding IA')).toBeVisible()
    await expect(page.getByText('Ver ejemplo')).toBeVisible()
  })

  test('should display categories section', async ({ page }) => {
    await expect(page.getByText('Explora categorías')).toBeVisible()

    // Check that category chips are displayed
    const categoryChips = page.getByTestId('category-chip')
    await expect(categoryChips.first()).toBeVisible()

    // Check some expected categories
    await expect(page.getByText('Accesorios')).toBeVisible()
    await expect(page.getByText('Aceites')).toBeVisible()
    await expect(page.getByText('Frenos')).toBeVisible()
  })

  test('should display featured products section', async ({ page }) => {
    await expect(page.getByText('Productos destacados')).toBeVisible()
    await expect(page.getByText('Ver todo →')).toBeVisible()

    // Check that product cards are displayed
    const productCards = page.getByTestId('product-card')
    await expect(productCards).toHaveCount(4)

    // Check first product details
    await expect(page.getByText('Casco integral básico')).toBeVisible()
    await expect(page.getByText('$230,000')).toBeVisible()
  })

  test('should navigate to stores page when clicking "Explorar tiendas"', async ({ page }) => {
    await page.getByTestId('cta-explorar').click()
    await expect(page).toHaveURL(/.*\/stores/)
  })

  test('should navigate to create store page when clicking "Crear mi tienda"', async ({ page }) => {
    await page.getByTestId('cta-crear').click()
    await expect(page).toHaveURL(/.*\/stores\/create/)
  })

  test('should navigate to how it works page when clicking "Cómo funciona"', async ({ page }) => {
    await page.getByTestId('cta-como').click()
    await expect(page).toHaveURL(/.*\/how-it-works/)
  })

  test('should navigate to products page when clicking "Ver todo"', async ({ page }) => {
    await page.getByText('Ver todo →').click()
    await expect(page).toHaveURL(/.*\/products/)
  })

  test('should have responsive design', async ({ page }) => {
    // Test mobile viewport
    await page.setViewportSize({ width: 375, height: 667 })
    await expect(page.getByTestId('hero-title')).toBeVisible()

    // Test tablet viewport
    await page.setViewportSize({ width: 768, height: 1024 })
    await expect(page.getByTestId('hero-title')).toBeVisible()

    // Test desktop viewport
    await page.setViewportSize({ width: 1920, height: 1080 })
    await expect(page.getByTestId('hero-title')).toBeVisible()
  })
})
