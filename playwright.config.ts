import { defineConfig, devices } from '@playwright/test'

const stripTrailingSlash = (value: string) => value.replace(/\/+$/, '')

const frontendBaseUrl = process.env.E2E_FRONTEND_URL || 'http://127.0.0.1:5173'
const apiBaseUrl = process.env.E2E_API_BASE_URL || 'http://127.0.0.1:8000/api'
const apiOrigin = stripTrailingSlash(apiBaseUrl).replace(/\/api$/i, '')
const useManagedServers = process.env.E2E_SKIP_WEBSERVER !== '1'

export default defineConfig({
  testDir: './tests-e2e',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: 1,
  reporter: [['list'], ['html', { open: 'never' }]],
  timeout: 120_000,
  use: {
    baseURL: frontendBaseUrl,
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
    {
      name: 'mobile-chrome',
      use: { ...devices['Pixel 7'] },
    },
  ],
  webServer: useManagedServers
    ? [
        {
          command: 'php artisan serve --host=127.0.0.1 --port=8000',
          url: apiOrigin,
          reuseExistingServer: !process.env.CI,
          timeout: 120_000,
        },
        {
          command: 'npm --prefix comercio-plus-frontend run dev -- --host 127.0.0.1 --port 5173',
          url: frontendBaseUrl,
          reuseExistingServer: !process.env.CI,
          timeout: 180_000,
        },
      ]
    : undefined,
})
