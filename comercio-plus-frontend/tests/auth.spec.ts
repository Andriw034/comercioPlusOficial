import { test, expect } from '@playwright/test'

test.describe('Auth básica', () => {
  test('Ir a Login y ver formulario', async ({ page }) => {
    await page.goto('/login') // ajusta si usas otra ruta
    await expect(page.getByRole('heading', { name: /Iniciar sesión/i })).toBeVisible()
    await expect(page.getByLabel(/Email/i)).toBeVisible()
    await expect(page.getByLabel(/Contraseña/i)).toBeVisible()
  })
})
