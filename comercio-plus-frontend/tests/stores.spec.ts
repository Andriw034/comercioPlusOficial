import { test, expect } from '@playwright/test';

test('Listado de tiendas y detalle', async ({ page }) => {
  await page.goto('/stores'); // ajusta si aplica
  await expect(page.getByRole('heading', { name: /Tiendas/i })).toBeVisible();
  // Si tienes enlaces a detalle:
  // await page.getByRole('link', { name: /MotoParts/i }).click();
  // await expect(page).toHaveURL(/stores\/[a-z0-9-]+/i);
});
