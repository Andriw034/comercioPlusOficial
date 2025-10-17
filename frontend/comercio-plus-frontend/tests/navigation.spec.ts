import { test, expect } from '@playwright/test';

test.describe('Navegación principal', () => {
  test('Home → Explorar productos / Crear mi tienda', async ({ page }) => {
    await page.goto('/');
    await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
    await page.getByRole('button', { name: /Explorar/i }).click();
    // Ajusta: si /products existe
    await expect(page).toHaveURL(/(products|explorar)/i);

    await page.goto('/');
    await page.getByRole('button', { name: /Crear mi tienda/i }).click();
    // Ajusta: si tu ruta es /stores/create
    await expect(page).toHaveURL(/(stores\/create|register|onboarding)/i);
  });

  test('Menú: Categorías / Destacados / Branding (anclas)', async ({ page }) => {
    await page.goto('/');
    await page.getByRole('link', { name: /Categorías/i }).click();
    await expect(page.locator('#categorias')).toBeVisible();

    await page.getByRole('link', { name: /Destacados/i }).click();
    await expect(page.locator('#destacados')).toBeVisible();

    await page.getByRole('link', { name: /Branding/i }).click();
    await expect(page.locator('#branding')).toBeVisible();
  });
});
