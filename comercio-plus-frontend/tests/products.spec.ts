import { test, expect } from '@playwright/test';

test.describe('Listado de productos', () => {
  test('Ver grilla y precios', async ({ page }) => {
    await page.goto('/products'); // ajusta si tu ruta es otra
    await expect(page.getByRole('heading', { name: /Productos/i })).toBeVisible();
    // Cards presentes
    const cards = page.locator('[data-test="product-card"]');
    await expect(cards.first()).toBeVisible();
  });
});
