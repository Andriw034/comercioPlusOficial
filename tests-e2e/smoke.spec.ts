import { test, expect } from '@playwright/test'

test.describe('Smoke tests for Comercio Plus', () => {
  test('Welcome page loads and displays title and description', async ({ page }) => {
    await page.goto('/')
    await expect(page.locator('h1')).toHaveText('Bienvenido a Comercio Plus')
    await expect(page.locator('p')).toContainText('La plataforma de e-commerce para tiendas de repuestos de motos')
  })

  test('Navigation links work with Inertia', async ({ page }) => {
    await page.goto('/')
    await page.click('text=Dashboard')
    await expect(page).toHaveURL('/dashboard')
    await expect(page.locator('h1')).toHaveText('Dashboard')
    await page.click('text=Tiendas')
    await expect(page).toHaveURL('/stores')
    await expect(page.locator('h1')).toHaveText('Tiendas')
  })

  test('Dashboard renders stats cards', async ({ page }) => {
    await page.goto('/dashboard')
    await expect(page.locator('h1')).toHaveText('Dashboard')
    await expect(page.locator('text=Total Productos')).toBeVisible()
    await expect(page.locator('text=Total Órdenes')).toBeVisible()
    await expect(page.locator('text=Ingresos Totales')).toBeVisible()
    await expect(page.locator('text=Órdenes Pendientes')).toBeVisible()
  })

  test('Stores index shows list and pagination', async ({ page }) => {
    await page.goto('/stores')
    await expect(page.locator('h1')).toHaveText('Tiendas')
    await expect(page.locator('div.bg-white')).toHaveCountGreaterThan(0)
    // TODO: Add pagination button tests if pagination is implemented in UI
  })
})
