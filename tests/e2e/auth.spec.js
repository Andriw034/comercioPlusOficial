
import { test, expect } from '@playwright/test';
import { faker } from '@faker-js/faker';

test.describe('Authentication & Authorization', () => {
  test('should allow a user to register and be redirected to the dashboard', async ({ page }) => {
    // Navigate to the registration page
    await page.goto('/register');

    // Generate fake user data
    const name = faker.person.fullName();
    const email = faker.internet.email();
    const password = faker.internet.password() + 'A1!'; // Ensure password meets complexity if any

    // Fill out the registration form
    await page.locator('input[name="name"]').fill(name);
    await page.locator('input[name="email"]').fill(email);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('input[name="password_confirmation"]').fill(password);

    // Click the register button
    await page.getByRole('button', { name: /Register/i }).click();

    // Assert that the user is redirected to the dashboard
    await expect(page).toHaveURL('/dashboard');

    // Assert that the dashboard content is visible
    await expect(page.getByRole('heading', { name: /Dashboard/i })).toBeVisible();
  });
});
