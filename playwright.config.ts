import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './e2e',
  timeout: 30000,
  retries: 0,
  use: {
    baseURL: process.env.E2E_URL || 'http://localhost:8080',
    headless: true,
    locale: 'es-MX',
    screenshot: 'only-on-failure',
  },
  webServer: undefined,
});
