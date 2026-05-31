import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright Configuration — Sistem Penggajian PT IPM
 * E2E Test Suite
 */
export default defineConfig({
  testDir: './e2e/tests',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: 1,
  workers: 3,
  timeout: 60000,
  reporter: [
    ['html', { open: 'never', outputFolder: 'e2e/reports/html' }],
    ['json', { outputFile: 'e2e/reports/test-results.json' }],
    ['list'],
  ],
  use: {
    baseURL: 'http://127.0.0.1:8000',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'on-first-retry',
    actionTimeout: 30000,
    navigationTimeout: 60000,
  },
  webServer: {
    command: 'php artisan serve --port=8000 --no-reload',
    url: 'http://127.0.0.1:8000',
    reuseExistingServer: true,
    timeout: 120000,
    stdout: 'pipe',
    stderr: 'pipe',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
  outputDir: 'e2e/reports/test-artifacts',
});
